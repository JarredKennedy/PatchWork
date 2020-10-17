<?php

namespace PatchWork\Asset_Source;

use PatchWork\Writeable_Asset_Source;
use PatchWork\Types\File_Tree;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Installed_Asset_Source implements Writeable_Asset_Source {

	/** @var string The absolute path (with trailing slash) to the directory root for the asset source. */
	protected $directory;

	/** @var PatchWork\Types\File_Tree */
	protected $file_tree;

	public function __construct( $directory ) {
		$this->directory = trailingslashit( $directory );
	}

	/**
	 * Returns the file tree of the asset source.
	 * 
	 * @since 0.1.0
	 * 
	 * @return PatchWork\Types\File_Tree
	 */
	public function get_file_tree() {
		if ( $this->file_tree instanceof File_Tree ) {
			return $this->file_tree;
		}

		$recursive_dir_list = function( $directory ) use ( &$recursive_dir_list ) {
			$root = new File_Tree;
			$root->name = basename( $directory );
			$root->checksum = 0;

			$directory = trailingslashit( $directory );

			$last_node = null;
			$dir = @opendir( $directory );
			if ( $dir ) {
				while ( ( $file = readdir( $dir ) ) !== false ) {
					if ( in_array( $file, array( '.', '..' ), true ) ) {
						continue;
					}

					if ( is_dir( $directory . $file ) ) {
						$node = $recursive_dir_list( $directory . $file );
					} else {
						$node = new File_Tree;
						$node->name = $file;

						$file_hash = unpack( 'N', hash_file( 'crc32b', $directory . $file, true ) );
						$checksum = current( $file_hash );

						$node->checksum = $checksum;
					}

					if ( $last_node ) {
						$last_node->sibling = $node;
					} else {
						$root->first_child = $node;
					}

					$last_node = $node;
				}

				closedir( $dir );
			} elseif ( is_file( $path = untrailingslashit( $directory ) ) ) {
				// Support for single-file plugins, for now. Might be removed if they
				// become a problem.
				$file_hash = unpack( 'N', hash_file( 'crc32b', $path, true ) );
				$checksum = current( $file_hash );
				$root->checksum = $checksum;
			}

			return $root;
		};

		$tree = $recursive_dir_list( $this->directory );

		return $tree;
	}

	public function get_file( $file_path, $create = false ) {
		$file_path = $this->normalize_file_path( $file_path );
		$mode = $create ? 'w+b' : 'r+b';

		$file = @fopen( $this->directory . $file_path, $mode );

		return $file;
	}

	public function file_exists( $file_path ) {
		$file_path = $this->normalize_file_path( $file_path );

		return file_exists( $this->directory . $file_path );
	}

	public function get_file_checksum( $file_path ) {
		return null; // stub
	}

	public function mkdir( $path, $recursive = false ) {
		return null; // stub
	}

	public function delete_file( $file_path ) {
		$file_path = $this->normalize_file_path( $file_path );
		@unlink( $this->directory . $file_path );
	}

	protected function normalize_file_path( $file_path ) {
		$basename = trailingslashit( basename( $this->directory ) );

		if ( strpos( $file_path, $basename ) === 0 ) {
			$file_path = substr( $file_path, strlen( $basename ) );
		}

		return $file_path;
	}

}