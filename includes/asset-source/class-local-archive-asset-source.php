<?php

namespace PatchWork\Asset_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PatchWork\Asset_Source;
use PatchWork\Types\File_Tree;
use PatchWork\Types\Zip_CDH;
use PatchWork\Zip_Reader;

class Local_Archive_Asset_Source implements Asset_Source {

	protected $zip_reader;

	protected $archive_path;

	protected $estimated_cdh_size;

	protected $file_tree;

	public function __construct( Zip_Reader $zip_reader, $archive_path, $estimated_cdh_size = 0 ) {
		$this->zip_reader = $zip_reader;
		$this->archive_path = $archive_path;
		$this->estimated_cdh_size = $estimated_cdh_size;
	}

	public function get_file_tree() {
		if ( $this->file_tree instanceof File_Tree ) {
			return $this->file_tree;
		}

		mbstring_binary_safe_encoding();

		$cdh_list = $this->read_package_cdh();

		reset_mbstring_encoding();

		$file_tree = patchwork_cdh_to_file_tree( $cdh_list );

		return $file_tree;
	}

	public function get_file( $file_path ) {
		return $this->zip_reader->get_file( $this->archive_path, $file_path );
	}

	public function file_exists( $file_path ) {
		$paths = explode( '/', $file_path );

		$node = $this->get_file_tree();

		$path = array_shift( $paths );
		while ( $node ) {
			if ( $node->name == $path ) {
				if ( current( $paths ) ) {
					$path = array_shift( $paths );
					$node = $node->first_child;
					continue;
				} else {
					return true;
				}
			}

			$node = $node->sibling;
		}

		return false;
	}

	public function get_file_checksum( $file_path ) {
		return null; // stub
	}

	protected function read_package_cdh() {
		$archive = @fopen( $this->archive_path, 'rb' );

		$found_cdh = false;
		$cdh_signature = pack( 'C*', 0x50, 0x4b, 0x01, 0x02 );
		if ( $this->estimated_cdh_size > 0 ) {
			$offset = ( $this->estimated_cdh_size * -1 ) - 22;
			fseek( $archive, $offset, SEEK_END );

			if ( fread( $archive, 4 ) == $cdh_signature ) {
				$found_cdh = true;
				fseek( $archive, -4, SEEK_CUR );
			}
		}

		if ( ! $found_cdh ) {
			$end_cdh_signature = pack( 'C*', 0x50, 0x4b, 0x05, 0x06 );

			fseek( $archive, -22, SEEK_END );

			if ( fread( $archive, 4 ) != $end_cdh_signature ) {
				fclose( $archive );
				throw new \RuntimeException( 'Failed to find end cdh record' );
			}

			fseek( $archive, 8, SEEK_CUR );

			$end_cdh = unpack( 'Vsize/Voffset', fread( $archive, 8 ) );

			// Move to the start of the CDH
			fseek( $archive, $end_cdh['offset'] );
		}

		$cdh_list = array();
		while ( fread( $archive, 4 ) == $cdh_signature ) {
			// We're at the start of a CDH.

			fseek( $archive, 12, SEEK_CUR ); // Skip unwanted fields.

			$cdh = new Zip_CDH();

			$fields = unpack( 'Vcrc/Vcompressed_size/Vuncompressed_size/vfilename_length/vextra_length/vcomment_length', fread( $archive, 18 ) );
			fseek( $archive, 8, SEEK_CUR ); // Skip 8 bytes of fields we don't want.

			$offset = unpack( 'V', fread( $archive, 4 ) );
			
			$filename = fread( $archive, $fields['filename_length'] );
			fseek( $archive, $fields['extra_length'] + $fields['comment_length'], SEEK_CUR ); // More data we don't care about.

			$cdh->filename = $filename;
			$cdh->file_offset = $offset[1];
			$cdh->crc = $fields['crc'];
			$cdh->compressed_size = $fields['compressed_size'];
			$cdh->uncompressed_size = $fields['uncompressed_size'];

			$cdh_list[] = $cdh;
		}

		return $cdh_list;
	}

	protected function download_archive() {
		
	}

}