<?php

namespace PatchWork\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File_Tree_Diff extends File_Tree {

	const UNCHANGED = 0;

	const CHANGE_ADDED = 1;

	const CHANGE_REMOVED = 2;

	const CHANGE_MODIFIED = 3;

	public $status;

}