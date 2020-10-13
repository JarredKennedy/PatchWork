<?php

namespace PatchWork;

use PatchWork\Types\Patch_Header;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Patch {

	/**
	 * @var PatchWork\Types\Patch_Header The header of this patch.
	 */
	protected $header;

	/**
	 * @var PatchWork\Diff[] Diffs in this patch
	 */
	protected $diffs;

	public function __construct( Patch_Header $header, $diffs = array() ) {
		$this->header = $header;

		$diffs = array_filter( (array) $diffs, function( $diff ) {
			return ( $diff instanceof Diff );
		} );

		$this->diffs = $diffs;
	}

	public function get_header() {
		return $this->header;
	}

	public function get_diffs() {
		return $this->diffs;
	}

}