<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Patch_Parser {

	/**
	 * Parses a patch file to create a patch object.
	 * 
	 * @since 1.0.0
	 * 
	 * @param resource $patch_file_handle
	 */
	public function read_header_data( $patch_file_handle );

	/**
	 * Read the diff block table. Provides offsets for each diff block.
	 * 
	 * @since 1.0.0
	 * 
	 * @param resource $patch_file_handle
	 */
	public function read_diff_block_table( $patch_file_handle );

}