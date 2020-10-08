<?php

namespace PatchWork\Asset_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PatchWork\Asset;
use PatchWork\Asset_Source;

class Local_Archive_Asset_Source implements Asset_Source {

	protected $asset;

	protected $archive;

	public function __construct( Asset $asset, $archive ) {
		$this->asset = $asset;
	}

	public function get_file_tree() {
		
	}

	public function get_file( $file_path ) {

	}

	public function get_file_checksum( $file_path ) {

	}

}