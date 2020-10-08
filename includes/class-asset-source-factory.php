<?php

namespace PatchWork;

use PatchWork\Source\Installed_Asset_Source;
use PatchWork\Source\Local_Archive_Asset_Source;
use PatchWork\Source\Repository_Asset_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Source_Factory {

	public function __construct() {

	}

	/**
	 * Makes a source for an installed asset.
	 * 
	 * 
	 */
	public function make( Asset $asset ) {
		if ( $asset->get_type() === 'plugin' ) {
			$directory = plugin_dir_path( $asset->get_slug() );
		} else {
			$directory = get_theme_root( $asset->get_slug() );
		}

		$directory = trailingslashit( $directory );

		$asset_source = new Installed_Asset_Source( $asset, $directory );

		return $asset_source;
	}

	public function make_original( Asset $asset ) {

	}

}