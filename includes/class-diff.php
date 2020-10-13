<?php

namespace PatchWork;

use PatchWork\Types\Diff_OP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO: refactor this class so that the access modifiers make sense.
class Diff {

	public $file_path;

	protected $changes;

	protected $lines_added;

	protected $lines_deleted;

	public function __construct() {
		$this->changes = array();
		$this->lines_added = 0;
		$this->lines_deleted = 0;
	}

	public function add_op( Diff_OP $op ) {
		$this->lines_added += $op->patched_lines_effected;
		$this->lines_deleted += $op->original_lines_effected;

		$this->changes[] = $op;
	}

	public function get_ops() {
		return $this->changes;
	}

	public function get_lines_added() {
		return $this->lines_added;
	}

	public function get_lines_deleted() {
		return $this->lines_deleted;
	}

}