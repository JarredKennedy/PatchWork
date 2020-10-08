<?php

namespace PatchWork\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents a central directory header in a zip file.
 * 
 * @since 0.1.0
 */
class Zip_CDH {
	
	/** @var string The name of the file this CDH represents. This is the full path of the file relatibe to the root. */
	public $filename;

	/** @var int The byte offset, relative the the start of the zip file, where the local file header for the file can be found. */
	public $file_offset;

	/** @var int The CRC32b checksum of the uncompressed file this CDH represents. */
	public $crc;

	/** @var int The compressed size of the file this CDH represents. */
	public $compressed_size;

	/** @var int The uncompressed size of the file this CDH represents. */
	public $uncompressed_size;

}