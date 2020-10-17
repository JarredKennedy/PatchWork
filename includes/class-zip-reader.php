<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Zip_Reader {

	protected $has_zip_wrapper;

	protected $has_zip_archive;

	protected $archive;

	public function __construct() {
		$this->has_zip_wrapper = in_array( 'zip', stream_get_wrappers() ) && false;
		$this->has_zip_archive = class_exists( '\ZipArchive' );
	}

	public function get_file( $archive, $file_path ) {
		mbstring_binary_safe_encoding();

		$handle = null;
		if ( $this->archive && $this->archive->filename == $archive ) {
			return $this->archive->getStream( $file_path );
		} elseif ( $this->has_zip_wrapper ) {
			$handle = fopen( 'zip://' . $archive . '#' . $file_path, 'r+b' );
		} elseif ( $this->has_zip_archive ) {
			$this->archive = new \ZipArchive();
			$result = $this->archive->open( $archive );

			if ( $result !== true ) {
				reset_mbstring_encoding();
				throw new \RuntimeException('Failed to open archive');
			}

			$handle =  $this->archive->getStream( $file_path );
		} else {
			// TODO: Use that terrible ZIP class used in WP here.
			// Extract the file to a temp and return a handle to it.
			// Unlink the temp file in the destructor.
		}

		reset_mbstring_encoding();

		return $handle;
	}

	public function __destruct() {
		if ( $this->archive ) {
			$this->archive->close();
		}
	}

}