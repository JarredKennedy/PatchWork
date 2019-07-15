<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function _patchwork_get_vendor_public_key() {
	return file_get_contents( PATCHWORK_PATH . 'patchwork.vendors.pub' );
}

function &_patchwork_get_verification() {
	static $verification;

	if ( ! is_object( $verification ) ) {
		$verification = new \PatchWork\Verification( _patchwork_get_vendor_public_key() );
	}

	return $verification;
}

/**
 * Reads the patch format version from the patch file of the handle provided.
 * The purpose of using this function is to determine which patch parser class
 * should be used to read the patch file. Returns the integer representing the
 * patch file format version.
 * 
 * Can also return false if the version could not be read.
 * 
 * @since 1.0.0
 * 
 * @param resource $patch_file_handle The handle of the opened patch file.
 * 
 * @return int|false
 */
function _patchwork_get_patch_file_format_version( $patch_file_handle ) {
	$cursor = ftell( $patch_file_handle );
	rewind( $patch_file_handle );

	// The magic number is 7 bytes long, skip past it.
	fseek( $patch_file_handle, 7 );

	$version = fread( $patch_file_handle, 1 );

	if ( ! $version ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	$version = unpack( 'cversion', $version );

	if ( ! is_array( $version ) || ! isset( $version['version'] ) ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	fseek( $patch_file_handle, $cursor );

	return $version['version'];
}

/**
 * Return an instance of PatchWork\Patch_Parser capable of parsing patch files of the version specified.
 * 
 * @since 1.0.0
 * 
 * @param int $patch_file_version Specifies the patch file version the returned parser must be able to read.
 * 
 * @return 
 */
function _patchwork_get_patch_parser( $patch_file_version ) {
	$parser = new \PatchWork\Patch_Parser_V1();

	/**
	 * Filters the parser for the patch file version.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \PatchWork\Patch_Parser $parsrer The parser object capable of reading the 
	 */
	return apply_filters( 'patchwork_patch_parser', $parser, $patch_file_version );
}