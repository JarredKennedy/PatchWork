<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Patch {

	protected $patch_file_path;

	protected $asset;

	protected $file_hash;

	/**
	 * Constructor.
	 * 
	 * @param string $patch_id sha256 ID of the patch
	 */
	public function __construct( $patch_file ) {
		$this->patch_file = $patch_file;
	}

	/**
	 * Apply the patch to the target asset.
	 * 
	 * @since 1.0.0
	 */
	public function apply() {

	}

	/**
	 * Restore the assets files to the state they were before the patch was applied.
	 * 
	 * @since 1.0.0
	 */
	public function undo() {

	}

	public function get_hash() {
		return $this->file_hash;
	}

	/**
	 * 
	 */

}