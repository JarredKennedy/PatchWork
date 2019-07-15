<?php

namespace PatchWork;

use \WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Patch verification.
 * 
 * @since 0.1.0
 * 
 * Utility class to verify that a patch was created by a specific vendor, eg the
 * author of the asset the patch targets. Each patch can contain a signature field.
 * This signature field is the signed hash of the patch, signed by PatchWork with
 * the private key corresponding to the public key bundled with this plugin.
 */
class Verification {

	protected $public_key;

	public function __construct( $public_key ) {
		$this->public_key = $public_key;
	}

	public function verify( $patch ) {
		$patch_hash = $patch->get_hash();
		$signature = $patch->get_signature();

		if ( ! strlen( $signature ) ) {
			// If no signature was provided, no verification claim is being made, nothing to check.
			return false;
		}

		$verified = openssl_verify( $patch_hash, $signature, $this->public_key, OPENSSL_ALGO_SHA256 );

		if ( $verified < 0 ) {
			_patchwork_log( 'openssl_verify error in PatchWork\Verification::verify()', 'error' );
			return false;
		}

		$verified = $verified > 0;
		return $verified;
	}

	/**
	 * Verifies that a signature has been signed 
	 */
	public function verify_signature( $signature ) {

	}

}