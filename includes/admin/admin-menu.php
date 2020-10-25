<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\PatchWork\Admin\Menu::init();

add_action( 'admin_enqueue_scripts', 'patchwork_register_scripts' );

function patchwork_register_scripts() {
	if ( wp_script_is( 'wp-element', 'registered' ) ) {
		// Use the vendor packages from core, available >= 5.0.0
		$vendor_dependencies = array( 'wp-element', 'wp-i18n', 'wp-hooks', 'wp-primitives' );
	} else {
		wp_register_script( 'patchwork-admin-vendor', PATCHWORK_URL . '/app/packages/vendor.min.js' );
		$vendor_dependencies = array( 'patchwork-admin-vendor' );
	}

	$vendor_dependencies[] = 'patchwork-api';

	wp_register_script( 'patchwork-api', PATCHWORK_URL . '/app/dist/api.js', array( 'wp-api-fetch' ), PATCHWORK_VERSION, true );

	wp_localize_script( 'patchwork-api', 'patchwork', array(
		'pw_url'		=> PATCHWORK_URL,
		'pw_version'	=> PATCHWORK_VERSION
	) );

	wp_register_script( 'patchwork-admin', PATCHWORK_URL . '/app/dist/app.js', $vendor_dependencies, PATCHWORK_VERSION, true );

	wp_register_style( 'patchwork-admin', PATCHWORK_URL . '/app/dist/app.css', [] );
}