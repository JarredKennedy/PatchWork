<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_prescan_asset( $asset ) {
	$local_source = new PatchWork\Asset_Source\Installed_Asset_Source( $asset->get_path() );
	$original_source = patchwork_locate_asset_original_source( $asset );

	$local_file_tree = $local_source->get_file_tree();
	$original_file_tree = $original_source->get_file_tree();

	$changed_files = patchwork_diff_file_trees( $original_file_tree, $local_file_tree );

	$scan_data = array(
		'changed_files'	=> $changed_files
	);

	return $scan_data;
}

function patchwork_scan_asset( $scan_token ) {

}