<?php

namespace PatchWork;

use PatchWork\Types\Patch_Header;
use PatchWork\Types\Diff_OP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Patch reader for PatchWork Patch file format 1.
 * 
 * @since 1.0.0
 */
class Patch_Reader_V1 implements Patch_Reader {

	protected $magic_number;

	protected $diff_block_header_magic_number;

	protected $diff_block_body_magic_number;

	protected $version;

	public function __construct() {
		$this->magic_number = pack( 'C*', 0x1F, 0x50, 0x41, 0x54, 0x43, 0x48, 0x1A );
		$this->diff_block_header_magic_number = pack( 'C*', 0x1F, 0x50, 0x57, 0x44, 0x48, 0x1A );
		$this->diff_block_body_magic_number = pack( 'C*', 0x1F, 0x50, 0x57, 0x44, 0x42, 0x1A );
		$this->version = 1;
	}

	public function read( $patch_file_path, $headers_only = false, $diff_block_headers_only = false ) {
		if ( ! file_exists( $patch_file_path ) || ! is_readable( $patch_file_path ) ) {
			throw new \RuntimeException( 'Patch file not found' );
		}

		$file = @fopen( $patch_file_path, 'rb' );

		$header = $this->read_header( $file );

		if ( ! $headers_only ) {
			$diff_blocks = $this->read_diff_blocks( $file, $diff_block_headers_only );
		} else {
			$diff_blocks = array();
		}

		fclose( $file );

		$patch = new Patch( $header, $diff_blocks );

		return $patch;
	}

	/**
	 * Reads a patch file header.
	 * 
	 * @since 0.1.0
	 * 
	 * @param resource $file_handle Patch file handle.
	 * 
	 * @return PatchWork\Types\Patch_Header
	 */
	protected function read_header( $file_handle ) {
		$header = new Patch_Header();

		$magic_number = fread( $file_handle, 7 );

		if ( $magic_number != $this->magic_number ) {
			throw new RuntimeException( 'Magic number mismatch' );
		}

		$header->format_version = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->diff_blocks_offset = unpack( 'v', fread( $file_handle, 2 ) )[1];

		$header->target_asset_identifier_length = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->target_asset_identifier = fread( $file_handle, $header->target_asset_identifier_length );

		$header->vendor_id_length = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->vendor_id = fread( $file_handle, $header->vendor_id_length );

		$header->deprecation_trigger_version = fread( $file_handle, 30 );

		$header->deprecation_policy = unpack( 'C', fread( $file_handle, 1 ) )[1];

		// Leave the signature as binary
		$header->signature = fread( $file_handle, 128 );

		$header->author_name_length = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->author_name = fread( $file_handle, $header->author_name_length );

		$header->author_url_length = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->author_url = fread( $file_handle, $header->author_url_length );

		$header->description_length = unpack( 'C', fread( $file_handle, 1 ) )[1];

		$header->description = fread( $file_handle, $header->description_length );

		$header->total_lines_added = unpack( 'V', fread( $file_handle, 4 ) )[1];

		$header->total_lines_removed = unpack( 'V', fread( $file_handle, 4 ) )[1];

		$header->created_timestamp = unpack( 'V', fread( $file_handle, 4 ) )[1];

		// Leave checksum as binary
		$header->checksum = fread( $file_handle, 20 );

		return $header;
	}

	/**
	 * Reads a patch's diff blocks.
	 * 
	 * @since 0.1.0
	 * 
	 * @param resource $file_handle
	 * @param bool $headers_only
	 * 
	 * @return PatchWork\Diff[]
	 */
	protected function read_diff_blocks( $file_handle, $headers_only ) {
		$blocks = array();

		while ( fread( $file_handle, 6 ) === $this->diff_block_header_magic_number ) {
			$diff = new Diff();
			
			$block_size = unpack( 'V', fread( $file_handle, 4 ) )[1];

			$original_file_path_length = unpack( 'v', fread( $file_handle, 2 ) )[1];
			$diff->file_path = fread( $file_handle, $original_file_path_length );

			$patched_file_path_length = unpack( 'v', fread( $file_handle, 2 ) )[1];
			$patched_file_path = fread( $file_handle, $patched_file_path_length );

			$lines_added = unpack( 'v', fread( $file_handle, 2 ) )[1];
			$lines_deleted = unpack( 'v', fread( $file_handle, 2 ) )[1];

			if ( $headers_only ) {
				fseek( $file_handle, 12 + $original_file_path_length + $patched_file_path_length, SEEK_CUR );
				$blocks[] = $diff;
				continue;
			}

			while ( fread( $file_handle, 6 ) === $this->diff_block_body_magic_number ) {
				$op = new Diff_OP();

				$op->original_line_start = unpack( 'v', fread( $file_handle, 2 ) )[1];
				$op->original_lines_effected = unpack( 'v', fread( $file_handle, 2 ) )[1];
				$op->patched_line_start = unpack( 'v', fread( $file_handle, 2 ) )[1];
				$op->patched_lines_effected = unpack( 'v', fread( $file_handle, 2 ) )[1];

				if ( $op->original_lines_effected > 0 && $op->patched_lines_effected > 0 ) {
					$op->op = Diff_OP::OP_CHANGE;
				} elseif ( $op->original_lines_effected > 0 ) {
					$op->op = Diff_OP::OP_DELETE;
				} elseif ( $op->patched_lines_effected > 0 ) {
					$op->op = Diff_OP::OP_ADD;
				}

				$original_lines_length = unpack( 'V', fread( $file_handle, 4 ) )[1];
				if ( $original_lines_length ) {
					$op->original = fread( $file_handle, $original_lines_length );
				}

				$patched_lines_length = unpack( 'V', fread( $file_handle, 4 ) )[1];
				if ( $patched_lines_length ) {
					$op->patched = fread( $file_handle, $patched_lines_length );
				}

				$diff->add_op( $op );
			}

			$blocks[] = $diff;

		}

		return $blocks;
	}

}