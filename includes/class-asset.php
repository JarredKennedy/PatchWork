<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class for defining an Asset. An Asset is the target of a Patch - where the Patch is
 * applied. Every Patch applies to one Asset.
 * 
 * @since 0.1.0
 */
abstract class Asset {

	protected $path;

	protected $main_file;

	protected $update_protected;

	/**
	 * Returns the user-friendly name of the Asset.
	 */
	abstract function get_name();

	/**
	 * Returns the currently installed version of the Asset.
	 */
	abstract function get_version();

	/**
	 * Returns an alphanumeric string, not more than 30 characters long,
	 * which uniquely identifies the Asset.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	abstract function get_id();

	/**
	 * Returns the absolute path to the Asset.
	 */
	public function get_path() {
		return $this->path;
	}

}