<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Differ {

	/**
	 * Returns a diff representing the operations necessary to transform $original_lines
	 * into $current_lines.
	 * 
	 * @since 0.1.0
	 * 
	 * @param array<string> $original_lines
	 * @param array<string> $current_lines
	 * 
	 * @return PatchWork\Diff
	 */
	public function diff( $original_lines, $current_lines );

}