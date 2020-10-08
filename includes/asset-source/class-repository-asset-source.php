<?php

namespace PatchWork\Asset_Source;

use PatchWork\Asset;
use PatchWork\Asset_Source;
use PatchWork\WP_Org_Repo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repository_Asset_Source implements Asset_Source {

	protected $asset;

	protected $repository;

	protected $file_tree;

	protected $estimated_cdh_size;

	public function __construct( Asset $asset, WP_Org_Repo $repository ) {
		$this->asset = $asset;
		$this->repository = $repository;
	}

	/**
	 * Get the file tree of an asset in a repository.
	 * 
	 * @since 0.1.0
	 * 
	 * @return PatchWork\Types\File_Tree
	 */
	public function get_file_tree() {
		if ( $this->file_tree ) {
			return $this->file_tree;
		}

		$asset_info = $this->repository->get_asset_info( $this->asset );
		
		if ( version_compare( $asset_info['version'], $this->asset->get_version(), '=' ) ) {
			$package_url = $asset_info['download_link'];
		} else {
			foreach ( $asset_info['versions'] as $version => $url ) {
				if ( version_compare( $this->asset->get_version(), $version, '=' ) ) {
					$package_url = $url;
					break;
				}
			}
		}

		$tree = $this->repository->list_files( $this->asset, $package_url );

		return $tree;
	}

	public function get_file( $file_path ) {
		return null; // stub
	}

	public function get_file_checksum( $file_path ) {
		return null; // stub
	}

}