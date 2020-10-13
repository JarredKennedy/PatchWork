<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A Patch Reader reads PatchWork Patch (.pwp) files.
 * 
 * @since 0.1.0
 */
interface Patch_Reader {

	/**
	 * Parses a patch file to create a patch object.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $patch_file_path		The absolute path the patch file to be parsed.
	 * @param bool $headers_only			If true, the reader must only read the patch header from the patch file.
	 * @param bool $diff_block_headers_only	If true, and $headers_only is false, the reader must only read diff block headers, ignoring diff block bodies.
	 * 
	 * @return PatchWork\Patch
	 */
	public function read( $patch_file_path, $headers_only = false, $diff_block_headers_only = false );

}