<?php

namespace PatchWork;

use PatchWork\Types\Zip_CDH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Org_Repo {

	const API_URL = 'https://api.wordpress.org/';

	protected $cdh_entries;

	public function __construct() {
		$this->cdh_entries = array();
	}

	public function get_asset_info( Asset $asset ) {
		$cached = get_transient( 'pw_wporg_asset_info_' . $asset->get_id() );

		if ( $cached ) {
			return $cached;
		}

		$url = self::API_URL;
		$url .= ( $asset->get_type() === 'plugin' ) ? 'plugins' : 'themes';
		$url .= '/info/1.0/';
		$url .= $asset->get_id();
		$url .= '.json';

		$api_response = wp_remote_get( $url );

		$info = wp_remote_retrieve_body( $api_response );
		$info = json_decode( $info, true );

		set_transient( 'pw_wporg_asset_info_' . $asset->get_id(), $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}

	public function list_files( $asset, $package_url ) {
		// Make an original source for the asset.
		$source = patchwork()->source_factory->make( $asset );

		$estimated_cdh_size = 0;
		if ( $source ) {
			$estimated_cdh_size = patchwork_estimate_cdh_size( $source->get_file_tree() );
		}

		$cdh_list = $this->read_package_cdh( $package_url, $estimated_cdh_size );
		$file_tree = patchwork_cdh_to_file_tree( $cdh_list );

		return $file_tree;
	}

	public function read_package_cdh( $package_url, $estimated_cdh_size ) {
		if ( isset( $this->cdh_entries[$package_url] ) ) {
			return $this->cdh_entries[$package_url];
		}

		$headers = array();
		if ( $estimated_cdh_size > 0 ) {
			// 22 bytes is the size of an End of Central Directory Header block with no .ZIP file comment set
			// which downloads.wordpress.org doesn't set.
			$byte_offset = $estimated_cdh_size + 22;

			$headers['Range'] = 'bytes=-' . $byte_offset;
		}

		$response = wp_remote_get( $package_url, array(
			'headers'	=> $headers,
			'timeout'	=> 15
		) );

		$data = wp_remote_retrieve_body( $response );
		$content_length = (int) wp_remote_retrieve_header( $response, 'Content-Length' );

		if ( $estimated_cdh_size > 0 && $content_length != $byte_offset ) {
			// Not good, should log a notice.
		}

		$entries = array();
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

		$this->cdh_entries[$package_url] = $cdh_list;

		return $cdh_list;
	}

}