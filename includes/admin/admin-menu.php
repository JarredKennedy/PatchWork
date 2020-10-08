<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\PatchWork\Admin\Menu::init();

add_action( 'admin_enqueue_scripts', 'patchwork_register_scripts' );

function patchwork_register_scripts() {
	if ( wp_script_is( 'wp-element', 'registered' ) ) {
		// Use the vendor packages from core, available >= 5.0.0
		$vendor_dependencies = [ 'wp-element', 'wp-i18n', 'wp-hooks' ];
	} else {
		wp_register_script( 'patchwork-admin-vendor', PATCHWORK_URL . '/app/packages/vendor.min.js' );
		$vendor_dependencies = [ 'patchwork-admin-vendor' ];
	}

	wp_register_script( 'patchwork-admin', PATCHWORK_URL . '/app/dist/app.js', $vendor_dependencies, PATCHWORK_VERSION, true );

	wp_register_style( 'patchwork-admin', PATCHWORK_URL . '/app/dist/app.css', [] );
}