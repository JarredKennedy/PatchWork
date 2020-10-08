<?php

namespace PatchWork\Types;

class File_Tree {

	/** @var PatchWork\File_Tree The first node in the tree. */
	public $first_child;

	/** @var PatchWork\File_Tree The first sibling of this node. */
	public $sibling;

	/** @var string The name of the file or directory */
	public $name;

	/** @var int The checksum of the file */
	public $checksum;

}