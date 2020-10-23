<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A Patch Writer writes a PatchWork Patch (.pwp) file. It is not for writing a patches
 * changes to an asset.
 * 
 * @since 0.1.0
 */
interface Patch_Writer {

	/**
	 * Writes a patch to a patch file.
	 * 
	 * @param PatchWork\Patch $patch
	 * @param string $patch_file_path
	 * @param bool $overwrite_existing
	 */
	public function write( Patch $patch, $patch_file_path, $overwrite_existing = false );

}