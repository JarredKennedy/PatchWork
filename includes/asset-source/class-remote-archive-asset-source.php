<?php

namespace PatchWork\Asset_Source;

use PatchWork\Asset_Source;
use PatchWork\Types\Zip_CDH;
use PatchWork\Types\File_Tree;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Remote_Archive_Asset_Source implements Asset_Source {

	protected $archive_url;

	protected $estimated_cdh_size;

	protected $file_tree;

	protected $archive_size;

	public function __construct( $archive_url, $estimated_cdh_size = 0 ) {
		$this->archive_url = $archive_url;
		$this->estimated_cdh_size = $estimated_cdh_size;
	}

	public function get_file_tree() {
		if ( $this->file_tree instanceof File_tree ) {
			return $this->file_tree;
		}

		// try {
		$cdh_list = $this->read_package_cdh();
		// } catch (\RuntimeException $error) {}

		$file_tree = patchwork_cdh_to_file_tree( $cdh_list );

		$this->file_tree = $file_tree;

		return $file_tree;
	}

	public function get_file( $file_path ) {

	}

	public function get_file_checksum( $file_path ) {

	}

	protected function read_package_cdh() {
		$headers = array();
		if ( $this->estimated_cdh_size > 0 ) {
			// 22 bytes is the size of an End of Central Directory Header block with no .ZIP file comment set
			// which downloads.wordpress.org doesn't set.
			$byte_offset = $this->estimated_cdh_size + 22;

			$headers['Range'] = 'bytes=-' . $byte_offset;
		}

		$response = wp_remote_get( $this->archive_url, array(
			'headers'	=> $headers,
			'timeout'	=> 15
		) );

		$data = wp_remote_retrieve_body( $response );
		$content_length = (int) wp_remote_retrieve_header( $response, 'Content-Length' );

		if ( $this->estimated_cdh_size > 0 && $content_length != $byte_offset ) {
			// Not good, should log a notice.
		}

		$cdh_signature = pack( 'C*', 0x50, 0x4b, 0x01, 0x02 );

		// Our attempt to get only the CDH through to the end of the file failed. downloads.wordpress.org
		// has some issue (probably caching) where the server will sometimes ignore the Range request header.
		if ( substr( $data, 0, 4 ) != $cdh_signature ) {
			$end_cdh = substr( $data, -22 );
			$end_cdh_signature = pack( 'C*', 0x50, 0x4b, 0x05, 0x06 );

			if ( substr( $end_cdh, 0, 4 ) != $end_cdh_signature ) {
				$data = null;
				throw new \RuntimeException( 'Failed to find end cdh record' );
			}

			$end_cdh = unpack( 'Vsize/Voffset', substr( $end_cdh, 12, 8 ) );

			$data = substr( $data, $end_cdh['offset'], $end_cdh['size'] );
		}

		$cursor = 0;
		$cdh_list = array();
		while ( substr( $data, $cursor, 4 ) == $cdh_signature ) {
			// We're at the start of a CDH.
			$cursor += 16; // Data we don't care about.
			$cdh = new Zip_CDH();

			$fields = unpack( 'Vcrc/Vcompressed_size/Vuncompressed_size/vfilename_length/vextra_length/vcomment_length', substr( $data, $cursor, 18 ) );
			$cursor += 26; // Skip the 18 bytes of fields above, plus 8 bytes of fields we don't want.

			$offset = unpack( 'V', substr( $data, $cursor, 4 ) );
			$cursor += 4;
			
			$filename = substr( $data, $cursor, $fields['filename_length'] );
			$cursor += $fields['filename_length'] + $fields['extra_length'] + $fields['comment_length']; // The filename and more data we don't care about.

			$cdh->filename = $filename;
			$cdh->file_offset = $offset[1];
			$cdh->crc = $fields['crc'];
			$cdh->compressed_size = $fields['compressed_size'];
			$cdh->uncompressed_size = $fields['uncompressed_size'];

			$cdh_list[] = $cdh;
		}

		return $cdh_list;
	}

}