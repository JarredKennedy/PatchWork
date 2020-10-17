<?php

namespace PatchWork;

use PatchWork\Types\Patch_Header;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Patch writer for PatchWork Patch file format v1.
 * 
 * @since 0.1.0
 */
class Patch_Writer_V1 implements Patch_Writer {

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

	public function write( Patch $patch, $patch_file_path, $overwrite_existing = false ) {
		if ( ! $overwrite_existing && file_exists( $patch_file_path ) ) {
			throw new \RuntimeException( 'Tried to overwrite existing patch file' );
		}

		mbstring_binary_safe_encoding();

		$header = $patch->get_header();

		$this->finalize_header( $header );

		$file = @fopen( $patch_file_path, 'w+b' );

		$offsets = array();
		$this->write_header( $file, $header, $offsets );

		$header->total_lines_added = 0;
		$header->total_lines_removed = 0;
		foreach ( $patch->get_diffs() as $diff ) {
			$header->total_lines_added		+= $diff->get_lines_added();
			$header->total_lines_removed	+= $diff->get_lines_deleted();

			$this->write_diff_block( $file, $diff );
		}

		$this->finalize_patch_file( $file, $patch_file_path, $header, $offsets );

		reset_mbstring_encoding();

		fclose( $file );
	}

	protected function write_header( $file_handle, Patch_Header $header, &$offsets ) {
		// TODO: enforce string length limitations.

		fwrite( $file_handle, $this->magic_number );
		fwrite( $file_handle, pack( 'C', $this->version ) );
		fwrite( $file_handle, pack( 'v', $header->diff_blocks_offset ) );
		fwrite( $file_handle, pack( 'C', $header->target_asset_identifier_length ) );
		fwrite( $file_handle, $header->target_asset_identifier );
		fwrite( $file_handle, pack( 'C', $header->vendor_id_length ) );
		fwrite( $file_handle, $header->vendor_id );
		fwrite( $file_handle, str_pad( $header->deprecation_trigger_version, 30, "\0" ) );
		fwrite( $file_handle, pack( 'C', $header->deprecation_policy ) );
		fwrite( $file_handle, $header->signature );
		fwrite( $file_handle, pack( 'C', $header->author_name_length ) );
		fwrite( $file_handle, $header->author_name );
		fwrite( $file_handle, pack( 'C', $header->author_url_length ) );
		fwrite( $file_handle, $header->author_url );
		fwrite( $file_handle, pack( 'C', $header->description_length ) );
		fwrite( $file_handle, $header->description );
		
		$offsets['line_counts'] = ftell( $file_handle );

		fwrite( $file_handle, pack( 'x8' ) );
		fwrite( $file_handle, pack( 'V', $header->created_timestamp ) );
		fwrite( $file_handle, $header->checksum );

		$offsets['checksum'] = $offsets['line_counts'] + 12;
	}

	protected function write_diff_block( $file_handle, Diff $diff ) {
		$offset_before_block = ftell( $file_handle );

		fwrite( $file_handle, $this->diff_block_header_magic_number );

		$block_size_offset = ftell( $file_handle );
		fwrite( $file_handle, pack( 'x4' ) );

		$file_path_length = pack( 'v', strlen( $diff->file_path ) );

		fwrite( $file_handle, $file_path_length );
		fwrite( $file_handle, $diff->file_path );
		fwrite( $file_handle, $file_path_length );
		fwrite( $file_handle, $diff->file_path );
		fwrite( $file_handle, pack( 'v', $diff->get_lines_added() ) );
		fwrite( $file_handle, pack( 'v', $diff->get_lines_deleted() ) );

		foreach ( $diff->get_ops() as $op ) {
			fwrite( $file_handle, $this->diff_block_body_magic_number );
			fwrite( $file_handle, pack( 'v', $op->original_line_start ) );
			fwrite( $file_handle, pack( 'v', $op->original_lines_effected ) );
			fwrite( $file_handle, pack( 'v', $op->patched_line_start ) );
			fwrite( $file_handle, pack( 'v', $op->patched_lines_effected ) );

			$original_lines = '';
			$patched_lines = '';

			// TODO: use $diff->line_ending instead of hard-coded LF.
			// TODO: use $diff->eof_line_ending to determine whether to append a final line ending.

			if ( $op->original ) {
				$original_lines = implode( '', $op->original );
			}

			if ( $op->patched ) {
				$patched_lines = implode( '', $op->patched );
			}

			fwrite( $file_handle, pack( 'V', strlen( $original_lines ) ) );
			fwrite( $file_handle, $original_lines );
			fwrite( $file_handle, pack( 'V', strlen( $patched_lines ) ) );
			fwrite( $file_handle, $patched_lines );
		}

		$offset_after_block = ftell( $file_handle );

		fseek( $file_handle, $block_size_offset );
		fwrite( $file_handle, pack( 'V', $offset_after_block - $offset_before_block ) );
		fseek( $file_handle, $offset_after_block );
	}

	protected function finalize_header( Patch_Header $header ) {
		$header->target_asset_identifier_length = strlen( $header->target_asset_identifier );
		$header->vendor_id_length = strlen( $header->vendor_id );
		$header->author_name_length = strlen( $header->author_name );
		$header->author_url_length = strlen( $header->author_url );
		$header->description_length = strlen( $header->description );

		if ( ! is_int( $header->created_timestamp ) ) {
			$header->created_timestamp = time();
		}

		$header->diff_blocks_offset = $this->calculate_header_size( $header );
		$header->signature = pack( 'x128' );
		$header->checksum = pack( 'x20' );
	}

	protected function finalize_patch_file( $file_handle, $file_path, Patch_Header $header, $offsets ) {
		fseek( $file_handle, $offsets['line_counts'] );
		fwrite( $file_handle, pack( 'V', $header->total_lines_added ) );
		fwrite( $file_handle, pack( 'V', $header->total_lines_removed ) );
		
		$header->checksum = sha1_file( $file_path, true );

		fseek( $file_handle, $offsets['checksum'] );
		fwrite( $file_handle, $header->checksum );
	}

	protected function calculate_header_size( Patch_Header $header ) {
		// 218 bytes in fixed-length fields.
		$fixed_header_size = 206;

		$length = $fixed_header_size
			+ $header->target_asset_identifier_length
			+ $header->vendor_id_length
			+ $header->author_name_length
			+ $header->author_url_length
			+ $header->description_length;

		return $length;
	}

}