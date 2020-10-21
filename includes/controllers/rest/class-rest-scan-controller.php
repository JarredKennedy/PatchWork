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
	}

	public function prescan( $request ) {
		$tai = $request->get_param( 'asset' );
		$scan_token = uniqid( 'scan', true );

		$asset = patchwork_get_asset( $tai );

		// TODO: check asset exists.

		patchwork_prescan_asset( $asset );

		return array(
			'token'	=> $scan_token
		);
	}

	public function permissions_check( $request ) {
		return true;
	}

}