<?php

namespace PatchWork\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diff_OP {

	const OP_COPY = 0;

	const OP_ADD = 1;

	const OP_DELETE = 2;

	const OP_CHANGE = 3;

	public $op;

	public $original;

	public $patched;

	public $original_line_start;

	public $original_lines_effected;

	public $patched_line_start;

	public $patched_lines_effected;

}