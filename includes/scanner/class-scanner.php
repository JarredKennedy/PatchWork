<?php

namespace PatchWork\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scanner {

	/**
	 * @var PatchWork\Scanner\Scan_Config The paramaters of the scan.
	 */
	protected $config;

	/**
	 * @var PatchWork\AssetFactory
	 */
	protected $asset_factory;

	/**
	 * @var PatchWork\AssetSourceFactory
	 */
	protected $asset_source_factory;

	public function __construct( Scan_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Scans an asset. Returns a list of modified files in the asset.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Asset $asset The asset to scan
	 * 
	 * @return array An array of file paths which differ from the original
	 */
	public function scan( Asset $asset ) {

	}

}