<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_register_rest_routes() {
	$assets_controller = new PatchWork\Controllers\Rest\REST_Assets_Controller();
	$assets_controller->register_routes();

	$scan_controller = new PatchWork\Controllers\Rest\REST_Scan_Controller();
	$scan_controller->register_routes();
}

add_action( 'rest_api_init', 'patchwork_register_rest_routes' );