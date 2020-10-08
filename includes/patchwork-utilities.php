<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function _patchwork_get_vendor_public_key() {
	return file_get_contents( PATCHWORK_PATH . 'patchwork.vendors.pub' );
}

function &_patchwork_get_verification() {
	static $verification;

	if ( ! is_object( $verification ) ) {
		$verification = new \PatchWork\Verification( _patchwork_get_vendor_public_key() );
	}

	return $verification;
}

/**
 * Reads the patch format version from the patch file of the handle provided.
 * The purpose of using this function is to determine which patch parser class
 * should be used to read the patch file. Returns the integer representing the
 * patch file format version.
 * 
 * Can also return false if the version could not be read.
 * 
 * @since 1.0.0
 * 
 * @param resource $patch_file_handle The handle of the opened patch file.
 * 
 * @return int|false
 */
function _patchwork_get_patch_file_format_version( $patch_file_handle ) {
	$cursor = ftell( $patch_file_handle );
	rewind( $patch_file_handle );

	// The magic number is 7 bytes long, skip past it.
	fseek( $patch_file_handle, 7 );

	$version = fread( $patch_file_handle, 1 );

	if ( ! $version ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	$version = unpack( 'cversion', $version );

	if ( ! is_array( $version ) || ! isset( $version['version'] ) ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	fseek( $patch_file_handle, $cursor );

	return $version['version'];
}

/**
 * Return an instance of PatchWork\Patch_Parser capable of parsing patch files of the version specified.
 * 
 * @since 1.0.0
 * 
 * @param int $patch_file_version Specifies the patch file version the returned parser must be able to read.
 * 
 * @return 
 */
function _patchwork_get_patch_parser( $patch_file_version ) {
	$parser = new \PatchWork\Patch_Parser_V1();

	/**
	 * Filters the parser for the patch file version.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \PatchWork\Patch_Parser $parsrer The parser object capable of reading the 
	 */
	return apply_filters( 'patchwork_patch_parser', $parser, $patch_file_version );
}

/**
 * Returns the types of assets processed by PatchWork.
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function _patchwork_get_asset_types() {
	$asset_types = array( 'plugin', 'theme' );

	return apply_filters( 'patchwork_asset_types', $asset_types );
}

/**
 * Returns the estimated Central Directory Header (CDH) size in bytes.
 * 
 * @see docs/glossary.md	For a definition of CDH.
 * 
 * @since 0.1.0
 * 
 * @param \PatchWork\Types\File_Tree $file_tree
 * 
 * @return int
 */
function patchwork_estimate_cdh_size( \PatchWork\Types\File_Tree $file_tree ) {
	$size_of_tree = function( $tree, $path_length = 0 ) use( &$size_of_tree ) {
		$size = 0;
		$node = $tree;

		while ( $node ) {
			$name_length = strlen( $node->name ) + $path_length;
			$size += $name_length;
			$size += 46;

			if ( $node->first_child ) {
				$size++; // Add another byte for the trailing slash missed because this was a directory node.
				$size += $size_of_tree( $node->first_child, $name_length + 1 );
			}

			$node = $node->sibling;
		}

		return $size;
	};

	$estimated_cdh_size = $size_of_tree( $file_tree );

	return $estimated_cdh_size;
}

/**
 * Takes a CDH object and returns a file tree.
 * 
 * @see docs/glossary.md	For a definition of CDH.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Types\Zip_CDH[] $cdh_list
 * 
 * @return PatchWork\Types\File_Tree
 */
function patchwork_cdh_to_file_tree( $cdh_list ) {

	$make_tree = function( $files, $root = '.' ) use ( &$make_tree ) {
		// TODO: The candidates could be found faster by sorting the CDH list by depth first.
		$candidates = array_filter( $files, function( $cdh ) use ( $root ) {
			return dirname( $cdh->filename ) === $root;
		} );

		$last = null;
		$first = null;
		foreach ( $candidates as $file ) {
			$node = new PatchWork\Types\File_Tree();
			$node->name = basename( $file->filename );
			$node->checksum = $file->crc;

			if ( $last ) {
				$last->sibling = $node;
			} else {
				$first = $node;
			}

			// Is $file a directory?
			if ( $file->crc < 1 ) {
				$node->first_child = $make_tree( $files, untrailingslashit( $file->filename ) );
			}

			$last = $node;
		}

		return $first;
	};

	$tree = $make_tree( $cdh_list );

	return $tree;
}

/**
 * Computes a file tree representing the nodes that differ between
 * the two input file trees.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Types\File_Tree $tree_a
 * @param PatchWork\Types\File_Tree $tree_b
 * 
 * @return PatchWork\Types\File_Tree
 */
function patchwork_diff_file_trees( PatchWork\Types\File_Tree $tree_a, PatchWork\Types\File_Tree $tree_b ) {

}