<?php

namespace PatchWork\Controllers;

use PatchWork\Scanner\Scan_Config;
use PatchWork\Scanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Scan_Controller {

	public function __construct() {
		add_action( 'pw_do_scan', array( $this, 'do_scan' ) );
	}

	public function do_scan( $scan_id ) {
		$nonce = null;
		$assets_to_scan = array();

		$error = new WP_Error();

		if ( isset( $_POST['pw_nonce'] ) ) {
			$nonce = $_POST['pw_nonce'];
		}

		if ( isset( $_POST['pw_scan_targets'] ) && is_array( $_POST['pw_scan_targets'] ) ) {
			$assets_to_scan = array_map( function( $scan_target ) use ( $error ) {
				return patchwork_sanitize_target_asset_id( $scan_target, $error );
			} );
		}

		$config = Scan_Config::get_default_config();
		$scanner = new Scanner( $config );

		if ( isset( $_POST['pw_scan_id'] ) ) {
			$scan_id = sanitize_key( $_POST['pw_scan_id'] );

			$progress = new Progress();
		
			$scanner->set_progress( $progress );
		}
		
	}

}