<?php

namespace PatchWork\Controllers\Rest;

use \WP_REST_Controller;
use \WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_Scan_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'patchwork/v1/';
		$this->rest_base = 'scan';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<asset>(plugin|theme|anon):[a-zA-Z0-9\/\.-]+(:[a-zA-Z0-9\.-]+)?)/prescan',
			array(
				array(
					'methods'				=> WP_REST_Server::CREATABLE,
					'callback'				=> array( $this, 'prescan' ),
					'permission_callback'	=> array( $this, 'permissions_check' )
				)
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<scan_token>[a-zA-Z0-9\.-]+)/scan',
			array(
				array(
					'methods'				=> WP_REST_Server::CREATABLE,
					'callback'				=> array( $this, 'scan' ),
					'permission_callback'	=> array( $this, 'permissions_check' )
				)
			)
		);
	}

	public function prescan( $request ) {
		$tai = $request->get_param( 'asset' );
		$scan_token = uniqid( 'scan-' );

		$asset = patchwork_get_asset( $tai );

		// TODO: check asset exists.

		$scan_data = patchwork_prescan_asset( $asset );

		if ( ! $scan_data ) {
			return new \WP_Error( 'prescan_error', "Couldn't prescan for some reason" );
		}

		$scan_data['token'] = $scan_token;
		$scan_data['asset_id'] = $tai;

		set_transient( 'patchwork_scan_data_' . $scan_token, $scan_data, HOUR_IN_SECONDS );

		return array(
			'token'		=> $scan_token,
			'status'	=> is_null( $scan_data['changed_files'] ) ? 'unchanged' : 'modified'
		);
	}

	public function scan( $request ) {
		$scan_token = $request->get_param( 'scan_token' );
		$scan_data = get_transient( 'patchwork_scan_data_' . $scan_token );

		if ( ! $scan_data ) {
			return new \WP_Error( 'scan_error', "Couldn't find scan data for scan token" );
		}

		$changed_files = $scan_data['changed_files'];
		$asset = patchwork_get_asset( $scan_data['asset_id'] );
		$diffs = patchwork_scan_asset( $asset, $changed_files );

		set_transient( 'patchwork_scan_diff_' . $scan_token, $diffs, HOUR_IN_SECONDS );

		$diffs = array_map( function( $diff ) {
			$changes = array_map( function( $op ) {
				return array(
					'line'		=> array( $op->original_line_start, $op->patched_line_start ),
					'original'	=> $op->original,
					'patched'	=> $op->patched
				);
			}, $diff->get_ops() );

			return array (
				'file'			=> $diff->file_path,
				'lines_added'	=> $diff->get_lines_added(),
				'lines_deleted'	=> $diff->get_lines_deleted(),
				'changes'		=> $changes
			);
		}, $diffs );

		return $diffs;
	}

	public function permissions_check( $request ) {
		return true;
	}

}