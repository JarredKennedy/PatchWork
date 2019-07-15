<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the absolute path to an asset
 * 
 * @param string $asset_slug The slug of the asset, eg akismet/akismet.php
 * @param string $asset_type (optional) The type of asset. Either theme or plugin. Default: plugin
 * 
 * @return string
 */
function patchwork_get_asset_path( $asset_slug, $asset_type = 'plugin' ) {
	

	return apply_filters( 'patchwork_asset_path', $asset_path, $asset_slug, $asset_type );
}

/**
 * Returns the following:
 * 
 * type int		$max_file_size					The maximum size (in bytes) a file can be to be scanned by PatchWork. Default 1,048,576 (1MiB)
 * type array	$scan_file_extensions			An array of file extensions to limit the scan to. Empty array indicates no limit. Default []
 * type array	$scan_exclude_file_extensions	An array of file extensions to exclude from the
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function patchwork_get_asset_limits() {
	$limits = [
		'max_file_size'					=> MB_IN_BYTES,
		'scan_file_extensions'			=> [],
		'scan_exclude_file_extensions'	=> []
	];

	return apply_filters( 'patchwork_asset_limits', $limits );
}

function patchwork_is_asset_patched( $asset ) {
	return true;
}

function patchwork_get_asset_patches( $status = 'all' ) {
	$valid_statuses = [ 'all', 'active', 'inactive' ];

	if ( ! in_array( $status, $valid_statuses ) ) {
		$status = 'all';
	}

	
}