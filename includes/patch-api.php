<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns an array with the list of patch files
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function patchwork_list_patch_files() {

}

function patchwork_get_patch( $patch_file ) {
	$patch = new PatchWork\Patch(  );
}

/**
 * 
 */
function patchwork_create_patch( $asset, $extracted_changes ) {

}

/**
 * Verifies that the vendor of the patch is the same vendor of the asset
 * targetted by the patch. Returns true if the vendor is verified.
 * 
 * @since 1.0.0
 * 
 * @param PatchWork\Patch $patch The patch of which the vendor is being verified.
 * 
 * @return bool
 */
function patchwork_verify_patch_vendor( \PatchWork\Patch $patch ) {
	$verification = _patchwork_get_verification();

	$verified = $verification->verify( $patch );

	/**
	 * Filters the verified status of a vendor for a patch.
	 * 
	 * @since 1.0.0
	 * 
	 * @param bool $verified True when the patch vendor has been verified.
	 * @param \PatchWork\Patch $patch The patch of which the vendor is being verified.
	 */
	return apply_filters( 'patchwork_verify_patch_vendor', $verified, $patch );
}