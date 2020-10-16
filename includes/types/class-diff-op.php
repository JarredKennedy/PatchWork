<?php

namespace PatchWork\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diff_OP {

	public $original = array();

	public $patched = array();

	public $original_line_start;

	public $original_lines_effected;

	public $patched_line_start;

	public $patched_lines_effected;

}