<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_prescan_asset( $asset ) {
	$local_source = new PatchWork\Asset_Source\Installed_Asset_Source( $asset->get_path() );
	$original_location = patchwork_locate_asset_original_source( $asset );

	if ( is_wp_error( $original_location ) ) {
		$need_upload = array( 'pw_asset_not_found', 'pw_asset_version_unavailable', 'pw_asset_removed' );

		if ( count( array_intersect( $need_upload, $original_location->get_error_codes() ) ) > 0 ) {
			return array(
				'status' => 'missing_original'
			);
		}

		// Some other error.
		return new \WP_Error( 'pw_read_file_tree_failure', __( 'Failed to read current asset files.', 'patchwork' ) );
	}

	try {
		$local_file_tree = $local_source->get_file_tree();
	} catch ( \RuntimeException $error ) {
		return new \WP_Error( 'pw_read_file_tree_failure', __( 'Failed to read current asset files.', 'patchwork' ) );
	}

	$zip_reader = new PatchWork\Zip_Reader();

	$estimated_cdh_size = patchwork_estimate_cdh_size( $local_file_tree );

	if ( $original_location['host'] === 'local' ) {
		$original_source = new PatchWork\Asset_Source\Local_Archive_Asset_Source( $zip_reader, $original_location['target'], $estimated_cdh_size );
	} else {
		$original_source = new PatchWork\Asset_Source\Remote_Archive_Asset_Source( $zip_reader, $original_location['target'], $estimated_cdh_size );
	}

	try {
		$original_file_tree = $original_source->get_file_tree();
	} catch ( \RuntimeException $error ) {
		return new \WP_Error( 'pw_read_file_tree_failure', __( 'Failed to read original asset files.', 'patchwork' ) );
	}

	$changed_files = patchwork_diff_file_trees( $original_file_tree, $local_file_tree );

	$scan_data = array(
		'changed_files'	=> $changed_files
	);

	return $scan_data;
}

function patchwork_scan_asset( $asset, $changed_files ) {
	$local_source = new PatchWork\Asset_Source\Installed_Asset_Source( $asset->get_path() );
	$original_location = patchwork_locate_asset_original_source( $asset );

	if ( ! $original_location ) {
		// TODO: return a WP_Error or something.
		return false;
	}

	$zip_reader = new PatchWork\Zip_Reader();
	if ( $original_location['host'] === 'local' ) {
		$original_source = new PatchWork\Asset_Source\Local_Archive_Asset_Source( $zip_reader, $original_location['target'] );
	} else {
		$original_source = new PatchWork\Asset_Source\Remote_Archive_Asset_Source( $zip_reader, $original_location['target'] );
	}

	$diffs = patchwork_diff_files( $changed_files, $original_source, $local_source );

	return $diffs;
}