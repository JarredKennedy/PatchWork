<?php

namespace PatchWork\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Patch_Header {

	public $format_version;

	public $diff_blocks_offset;

	public $target_asset_identifier_length;

	public $target_asset_identifier;

	public $vendor_id_length;

	public $vendor_id;

	public $deprecation_trigger_version;

	public $deprecation_policy;

	public $signature;

	public $author_name_length;

	public $author_name;

	public $author_url_length;

	public $author_url;

	public $description_length;

	public $description;

	public $total_lines_added;

	public $total_lines_removed;

	public $created_timestamp;

	public $checksum;

}