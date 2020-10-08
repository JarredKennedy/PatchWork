<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface defining an Asset. An Asset is the target of a Patch - where the Patch is
 * applied. Every Patch applies to one Asset.
 * 
 * @since 0.1.0
 */
interface Asset {

	/**
	 * Returns the user-friendly name of the Asset.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	public function get_name();

	/**
	 * Returns the currently installed version of the Asset.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	public function get_version();

	/**
	 * Returns an alphanumeric string, not more than 30 characters long,
	 * which uniquely identifies the Asset.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	public function get_id();

	/**
	 * Returns the type of asset represented by this instance. It will
	 * be a string of either 'plugin' or 'theme'.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	public function get_type();

	/**
	 * Returns the slug of the asset. For themes this is called the 'stylesheet'
	 * for plugins it's the path, relative to the plugins directory, to the file
	 * in the plugin which contains the header.
	 * 
	 * @since 0.1.0
	 * 
	 * @return string
	 */
	public function get_slug();

}