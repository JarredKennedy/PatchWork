<?php

namespace PatchWork;

use \Text_Diff;
use \Text_Diff_Op_copy;
use \Text_Diff_Op_add;
use \Text_Diff_Op_change;
use \Text_Diff_Op_delete;
use PatchWork\Types\Diff_OP;

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
	 * @param string[] $original_lines
	 * @param string[] $current_lines
	 * 
	 * @return PatchWork\Diff
	 */
	public function diff( $original_lines, $current_lines ) {
		$text_diff = new Text_Diff( 'auto', array( $original_lines, $current_lines ) );

		$diff = $text_diff->getDiff();
		$diff = $this->text_diff_to_pw_diff( $diff );

		return $diff;
	}

	/**
	 * Transforms a series of Text_Diff Operations to a PatchWork Diff.
	 * Text_Diff uses instances of Text_Diff_Op to represent an operation
	 * (copy, delete, add, change)
	 * 
	 * @since 0.1.0
	 * 
	 * @param \Text_Diff[] $text_diffs
	 * 
	 * @return PatchWork\Diff
	 */
	protected function text_diff_to_pw_diff( $text_diffs ) {
		$diff = new Diff();

		$original_line_no = 1;
		$patched_line_no = 1;
		foreach ( $text_diffs as $text_diff ) {
			$count = 0;
			
			if ( $text_diff instanceof Text_Diff_Op_copy ) {
				$count = count( $text_diff->orig );
				$original_line_no += $count;
				$patched_line_no += $count;

				continue;
			}

			if ( $text_diff instanceof Text_Diff_Op_add ) {
				$op = new Diff_OP();
				$op->op = Diff_OP::OP_ADD;

				if ( isset( $text_diff->final ) && ! empty( $text_diff->final ) ) {
					$op->patched = $text_diff->final;
					$count = count( $op->patched );
				}

				$op->original_line_start = $original_line_no;
				$op->original_lines_effected = 0;
				$op->patched_line_start = $patched_line_no;
				$op->patched_lines_effected = $count;

				$patched_line_no += $count;

				$diff->add_op( $op );
			} elseif ( $text_diff instanceof Text_Diff_Op_delete ) {
				$op = new Diff_OP();
				$op->op = Diff_OP::OP_DELETE;

				if ( isset( $text_diff->orig ) && ! empty( $text_diff->orig ) ) {
					$op->original = $text_diff->orig;
					$count = count( $text_diff->orig );
				}

				$op->original_line_start = $original_line_no;
				$op->original_lines_effected = $count;
				$op->patched_line_start = $patched_line_no;
				$op->patched_lines_effected = 0;

				$original_line_no += $count;

				$diff->add_op( $op );
			} elseif ( $text_diff instanceof Text_Diff_Op_change ) {
				$op = new Diff_OP();
				$op->op = Diff_OP::OP_CHANGE;

				if ( isset( $text_diff->orig ) && ! empty( $text_diff->orig ) ) {
					$op->original = $text_diff->orig;
					$count = count( $text_diff->orig );
				}

				$op->original_line_start = $original_line_no;
				$op->original_lines_effected = $count;

				$original_line_no += $count;

				if ( isset( $text_diff->final ) && ! empty( $text_diff->final ) ) {
					$op->patched = $text_diff->final;
					$count = count( $text_diff->final );
				}

				$op->patched_line_start = $patched_line_no;
				$op->patched_lines_effected = $count;

				$patched_line_no += $count;

				$diff->add_op( $op );
			}

		}

		return $diff;
	}

}