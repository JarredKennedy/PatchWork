<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\PatchWork\Admin\Menu::init();

add_action( 'admin_enqueue_scripts', 'patchwork_register_scripts' );

function patchwork_register_scripts() {
	if ( wp_script_is( 'react', 'registered' ) ) {
		// Use the vendor packages from core, available >= 5.0.0
		$vendor_dependencies = [ 'react-dom', 'wp-polyfill-fetch' ];
	} else {
		wp_register_script( 'patchwork-admin-vendor', PATCHWORK_URL . '/app/packages/vendor.min.js' );
		$vendor_dependencies = [ 'patchwork-admin-vendor' ];
	}

	wp_register_script( 'patchwork-admin', PATCHWORK_URL . '/app/packages/admin.min.js' );
}