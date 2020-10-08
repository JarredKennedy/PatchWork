<?php

namespace PatchWork\Scanner;

use PatchWork\Asset;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scan_Config {

	/**
	 * @param array<PatchWork\Asset> Assets to scan.
	 */
	protected $targets = array();

	/**
	 * @param int|null The maximum size (in bytes) a single file of an asset can be to be scanned by the scanner. 
	 */
	protected $max_asset_file_size = 0;

	/**
	 * Get the default configuration.
	 * 
	 * @since 0.1.0
	 * 
	 * @return PatchWork\Scanner\Scan_Config
	 */
	public static function get_default_config() {
		$config = new self;

		$config->set_max_asset_file_size( MB_IN_BYTES );
	}

	/**
	 * Adds a target asset to be scanned.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Asset $asset The asset to be scanned.
	 */
	public function add_target( Asset $asset ) {
		if ( ! $this->has_target( $asset ) ) {
			array_push( $this->targets, $asset );
		}
	}

	/**
	 * Removes a target asset to be scanned.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Asset $asset The asset no longer being scanned.
	 */
	public function remove_target( Asset $asset ) {
		if ( $this->has_target( $asset ) ) {
			$index = array_search( $asset, $this->targets );
			unset( $this->targets[$index] );
		}
	}

	/**
	 * Gets an array of assets to scan.
	 * 
	 * @since 0.1.0
	 * 
	 * @return array<PatchWork\Asset>
	 */
	public function get_targets() {
		return $this->targets;
	}

	/**
	 * Returns true if an asset is set to be scanned.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Asset $asset The asset to check for.
	 * 
	 * @return bool
	 */
	public function has_target( Asset $asset ) {
		return in_array( $asset, $this->targets, true );
	}

	/**
	 * Sets the maximum size (in bytes) a single file of an asset can be to be scanned by the scanner.
	 * 
	 * @param int $byte_size The maximum byte size.
	 * 
	 * @since 0.1.0
	 */
	public function set_max_asset_file_size( $byte_size ) {
		if ( is_numeric( $byte_size ) ) {
			$this->max_asset_file_size = intval( $byte_size );
		}
	}

	/**
	 * Returns the maximum byte size a file can be for it to be scanned by the scanner.
	 * 
	 * @since 0.1.0
	 * 
	 * @return int
	 */
	public function get_max_asset_file_size() {
		return $this->max_asset_file_size;
	}

}