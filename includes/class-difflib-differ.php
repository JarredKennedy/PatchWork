<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Difflib_Differ implements Differ {

	protected $original_lines;

	protected $current_lines;

	public function diff( $original_lines, $current_lines ) {
		$this->original_lines = $original_lines;
		$this->current_lines = $current_lines;

		$differ = new Diff\Differ( $original_lines, $current_lines, array( 'context' => 0 ) );

		$diff = $this->to_pw_diff( $differ->getGroupedOpcodes() );

		return $diff;
	}

	protected function to_pw_diff( $diff ) {
        reset( $diff );

		$pw_diff = array_reduce( $diff, function( &$pw_diff, $diffBlock ) {
			$op = new Types\Diff_OP();
			$diffBlock = $diffBlock[0];

			$op->original_line_start = $diffBlock[1] + 1;
			$op->original_lines_effected = $diffBlock[2] - $diffBlock[1];
			$op->patched_line_start = $diffBlock[3] + 1;
			$op->patched_lines_effected = $diffBlock[4] - $diffBlock[3];

			if ( $diffBlock[0] === Diff\Sequence_Matcher::OP_INS ) {
				$op->patched = array_slice( $this->current_lines, $diffBlock[3], $op->patched_lines_effected );
			} elseif ( $diffBlock[0] === Diff\Sequence_Matcher::OP_DEL ) {
				$op->original = array_slice( $this->original_lines, $diffBlock[1], $op->original_lines_effected );
			} elseif ( $diffBlock[0] === Diff\Sequence_Matcher::OP_REP ) {
				$op->original = array_slice( $this->original_lines, $diffBlock[1], $op->original_lines_effected );
				$op->patched = array_slice( $this->current_lines, $diffBlock[3], $op->patched_lines_effected );
			}

			$pw_diff->add_op( $op );

			return $pw_diff;
		}, new Diff() );

        return $pw_diff;
	}

}