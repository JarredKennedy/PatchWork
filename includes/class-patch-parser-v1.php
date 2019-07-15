<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Patch parser for patch file format 1.
 * 
 * @since 1.0.0
 */
class Patch_Parser_V1 implements Patch_Parser {


	public function __construct() {}

	/**
	 * 
	 * 
	 * @since 1.0.0
	 * 
	 * @param resource $patch_file_handle The handle of the opened patch file.
	 */
	public function read_header_data( $patch_file_handle ) {

	}



}