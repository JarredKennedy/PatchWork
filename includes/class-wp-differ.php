<?php

namespace PatchWork;

use \Text_Diff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When this file is included, PatchWork will need Text_Diff. The other classes
 * are also loaded because core assumes if class_exists( 'Text_Diff' ) === true
 * then Text_Diff_Renderer and Text_Diff_Renderer_inline must have been loaded as well.
 */
if ( ! class_exists( 'Text_Diff', false ) ) {
	/** Text_Diff class */
	require ABSPATH . WPINC . '/Text/Diff.php';
	/** Text_Diff_Renderer class */
	require ABSPATH . WPINC . '/Text/Diff/Renderer.php';
	/** Text_Diff_Renderer_inline class */
	require ABSPATH . WPINC . '/Text/Diff/Renderer/inline.php';
}

/**
 * Uses code shipped with WordPress core to implement a Differ.
 * 
 * @since 0.1.0
 */
class WP_Differ implements Differ {

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
	public function diff( $original_lines, $current_lines ) {
		$text_diff = new Text_Diff( 'auto', array( $original_lines, $current_lines ) );

		$diff = $this->text_diff_to_pw_diff( $text_diff->getDiff() );

		return $diff;
	}

	/**
	 * Transforms a series of Text_Diff Operations to a PatchWork Diff.
	 * Text_Diff uses instances of Text_Diff_Op to represent an operation
	 * (copy, delete, add, change)
	 * 
	 * @since 0.1.0
	 * 
	 * @param array $edits
	 * 
	 * @return PatchWork\Diff
	 */
	protected function text_diff_to_pw_diff( $edits ) {
		
	}

}