<?php

namespace PatchWork\Asset_Source;

use PatchWork\Asset;
use PatchWork\Asset_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Installed_Asset_Source implements Asset_Source {

	protected $asset;

	/** @var string The absolute path (with trailing slash) to the directory root for the asset source. */
	protected $directory;

	public function __construct( Asset $asset, $directory ) {
		$this->asset = $asset;
		$this->directory = $directory;
	}

	public function get_file_tree() {

		$recursive_dir_list = function( $directory ) use ( &$recursive_dir_list ) {
			$tree = array();
			$basename = basename( $directory );
			$tree[ $basename ] = array();

			$directory = trailingslashit( $directory );

			$dir = @opendir( $directory );
			if ( $dir ) {
				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( in_array( $file, array( '.', '..' ), true ) ) {
						continue;
					}

					if ( is_dir( $directory . $file ) ) {
						$sub_tree = $recursive_dir_list( $directory . $file );

						$tree[ $basename ] = array_merge( $tree[ $basename ], $sub_tree );
					} else {
						$tree[ $basename ][] = $file;
					}
				}

				closedir( $dir );
			}

			return $tree;
		};

		$tree = $recursive_dir_list( $this->$directory );

		return $tree;
	}

	public function get_file( $file_path ) {
		return null; // stub
	}

	public function get_file_checksum( $file_path ) {
		return null; // stub
	}

}