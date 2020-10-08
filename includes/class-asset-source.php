<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Asset_Source {

	/**
	 * Returns the file tree for the asset source.
	 * 
	 * @since 0.1.0
	 * 
	 * @return PatchWork\Types\File_Tree
	 */
	public function get_file_tree();

	public function get_file( $file_path );

	public function get_file_checksum( $file_path );

}