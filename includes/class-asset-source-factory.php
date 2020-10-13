<?php

namespace PatchWork;

use PatchWork\Asset_Source\Installed_Asset_Source;
use PatchWork\Asset_Source\Local_Archive_Asset_Source;
use PatchWork\Asset_Source\Repository_Asset_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Source_Factory {

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

		$asset_source = new Installed_Asset_Source( $directory );

		return $asset_source;
	}

	/**
	 * Given an asset, resolve the source of its original version and return an
	 * Asset_Source for that version.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Asset $asset
	 * @param string $archive_path
	 * 
	 * @return PatchWork\Asset_Source
	 */
	public function make_archive_source( Asset $asset, $archive_path ) {
		if ( $archive_path ) {
			if ( ! is_readable( $archive_path ) ) {
				// throw
			}

			$asset_source = new Local_Archive_Asset_Source( $asset, $archive_path );
		} else {
			$asset_source = new Repository_Asset_Source( $asset, $this->repository );
		}

		return $asset_source;


	}

}