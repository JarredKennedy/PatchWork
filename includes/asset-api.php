<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_search_assets( $args ) {
	$defaults = array(
		'types'		=> array( 'anon', 'plugin', 'theme' ),
		'order_by'	=> 'name',
		'order'		=> 'ASC',
		'status'	=> null
	);

	$args = wp_parse_args( $args, $defaults );

	$assets = array();

	if ( in_array( 'plugin', $args['types'] ) ) {
		foreach ( get_plugins() as $slug => $header ) {
			$slug = plugin_basename( $slug );

			$id = sprintf( 'plugin:%s:%s', $slug, $header['Version'] );
			$status = is_plugin_active( $slug ) ? 'active' : 'inactive';
			$path = dirname( ABSPATH . 'wp-content/plugins/' . $slug );

			$assets[] = new PatchWork\Plugin( $header['Name'], $header['Version'], $id, $slug, $status, $path );
		}
	}

	if ( in_array( 'theme', $args['types'] ) ) {
		foreach ( get_themes() as $theme ) {
			$id = sprintf( 'theme:%s:%s', $slug, $theme->get( 'Version' ) );
			$status = ( get_stylesheet() == $theme->get_stylesheet() ) ? 'active' : 'inactive';

			$asset = new PatchWork\Theme( $theme->get( 'Name' ), $theme->get( 'Version' ), $id, $slug, $status, $theme->get_stylesheet_directory() );
		}
	}

	return $assets;
}

function patchwork_get_asset( $target_asset_identifier ) {
	$components = explode( ':', $target_asset_identifier );

	if ( count( $components ) < 2 ) {
		return new \WP_Error( 'invalid_tai', 'Target asset identifier is invalid' );
	}

	$type = array_shift( $components );
	$slug = array_shift( $components );

	if ( ! in_array( $type, array( 'anon', 'plugin', 'theme' ) ) ) {
		return new \WP_Error( 'invalid_tai', 'Target asset identifier is invalid' );
	}

	if ( $type === 'anon' || $type === 'plugin' ) {
		$slug = plugin_basename( $slug );
		$file = ABSPATH . 'wp-content/plugins/' . $slug;

		$plugin = get_plugin_data( $file );

		// check for errors.

		$id = sprintf( 'plugin:%s:%s', $slug, $plugin['Version'] );
		$status = is_plugin_active( $slug ) ? 'active' : 'inactive';
		$path = dirname( $file );

		$asset = new PatchWork\Plugin( $plugin['Name'], $plugin['Version'], $id, $slug, $status, $path );
	} else {
		$theme = wp_get_theme( $slug );

		// check for errors.

		$id = sprintf( 'theme:%s:%s', $slug, $theme->get( 'Version' ) );
		$status = ( get_stylesheet() == $theme->get_stylesheet() ) ? 'active' : 'inactive';

		$asset = new PatchWork\Theme( $theme->get( 'Name' ), $theme->get( 'Version' ), $id, $slug, $status, $theme->get_stylesheet_directory() );
	}

	return $asset;
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

function patchwork_locate_asset_original_source( PatchWork\Asset $asset ) {
	$location = array();
	$source_cache = get_option( 'patchwork_original_asset_sources', array() );

	$asset_id = $asset->get_id();
	if ( isset( $source_cache[$asset_id] ) ) {
		$location = $source_cache[$asset_id];
	}

	if ( ! isset( $location['host'] ) ) {
		// Check if this is a wordpress.org plugin.
		$repo = new PatchWork\WP_Org_Repo();
		$info = $repo->get_asset_info( $asset );

		$version = $asset->get_version();

		if ( version_compare( $info['version'], $version, '=' ) ) {
			$location['host'] = 'remote';
			$location['target'] = $info['download_link'];
		} elseif ( isset( $info['versions'] ) && is_array( $info['versions'] ) && isset( $info['versions'][$version] ) ) {
			$location['host'] = 'remote';
			$location['target'] = $info['versions'][$version];
		} else {
			// Version was not found.
			return false;
		}
	}

	$source_cache[$asset_id] = $location;

	update_option( 'patchwork_original_asset_sources', $source_cache );

	return $location;
}