<?php

namespace PatchWork\Controllers\Rest;

use \WP_REST_Controller;
use \WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_Assets_Controller extends WP_REST_Controller {

	/**
	 * Construct the controller.
	 * 
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'patchwork/v1/';
		$this->rest_base = 'assets';
	}

	/**
	 * Register the asset routes.
	 * 
	 * @since 0.1.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'				=> WP_REST_Server::READABLE,
					'callback'				=> array( $this, 'list_assets' ),
					'permission_callback'	=> array( $this, 'permissions_check' ),
					'args'					=> $this->get_collection_params(),
				)
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<asset>(plugin|theme|anon):[a-zA-Z0-9\/\.-]+(:[a-zA-Z0-9\.-]+)?)',
			array(
				array(
					'methods'				=> WP_REST_Server::READABLE,
					'callback'				=> array( $this, 'get_asset' ),
					'permission_callback'	=> array( $this, 'permissions_check' )
				)
			)
		);
	}

	public function list_assets( $request ) {
		$args = array();

		$assets = patchwork_search_assets( $args );

		$assets = array_map( function( $asset ) {
			return array(
				'id'		=> $asset->get_id(),
				'type'		=> $asset->get_type(),
				'name'		=> $asset->get_name(),
				'slug'		=> $asset->get_slug(),
				'version'	=> $asset->get_version(),
				'status'	=> $asset->get_status(),
				'path'		=> $asset->get_path()
			);
		}, $assets );

		return $assets;
	}

	public function get_asset( $request ) {
		$tai = $request->get_param( 'asset' );

		$asset = patchwork_get_asset( $tai );

		return array(
			'id'		=> $asset->get_id(),
			'type'		=> $asset->get_type(),
			'name'		=> $asset->get_name(),
			'slug'		=> $asset->get_slug(),
			'version'	=> $asset->get_version(),
			'status'	=> $asset->get_status()
		);
	}

	/**
	 * 
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['order'] = array(
			'default'		=> 'asc',
			'description'	=> __( 'Order sort attribute ascending or descending.', 'patchwork' ),
			'enum'			=> array( 'asc', 'desc' ),
			'type'			=> 'string'
		);

		$query_params['orderby'] = array(
			'default'		=> 'name',
			'description'	=> __( 'Sort collection by object attribute.', 'patchwork' ),
			'enum'			=> array(
					'id',
					'name',
					'author'
			),
			'type'			=> 'string',
		);

		$query_params['types'] = array(
			'description'	=> __( 'List of types of assets to include in the results.', 'patchwork' ),
			'type'			=> 'array',
			'items'			=> array(
				'type'	=> 'string',
			)
		);

		$query_params['status'] = array(
			'description'	=> __( 'List of asset statuses to include in the results.', 'patchwork' ),
			'type'			=> 'array',
			'items'			=> array(
				'type'	=> 'string'
			)
		);

		/**
		 * Filter collection parameters for the users controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_User_Query parameter.  Use the
		 * `rest_user_query` filter to set WP_User_Query arguments.
		 *
		 * @since 0.1.0
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'rest_assets_collection_params', $query_params );
	}

	/**
	 * manage_options is the permission required for access to any PatchWork API endpoint.
	 */
	public function permissions_check( $request ) {
		return true;
		// TODO: re-enable the permission check.
		return current_user_can( 'manage_options' );
	}

}