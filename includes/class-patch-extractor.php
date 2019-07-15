<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts patches from modified files.
 * 
 * @since 1.0.0
 * 
 * Utility class to build patches from changes in local files. The metadata for patch is also resolved and
 * set. This class does not detect changes in files, it just extracts them.
 */
class Patch_Extractor {

	/**
	 * @var array List of files in which changes have been detected.
	 */
	protected $changed_files;

	public function __construct( $changed_files ) {
		$this->changed_files = $changed_files;
	}

	public function extract() {

	}

}