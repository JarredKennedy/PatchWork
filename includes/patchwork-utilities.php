<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_get_vendor_public_key() {
	return file_get_contents( PATCHWORK_PATH . 'patchwork.vendors.pub' );
}

function patchwork_get_verification() {
	static $verification;

	if ( ! is_object( $verification ) ) {
		$verification = new \PatchWork\Verification( patchwork_get_vendor_public_key() );
	}

	return $verification;
}

/**
 * Reads the patch format version from the patch file of the handle provided.
 * The purpose of using this function is to determine which patch reader class
 * should be used to read the patch file. Returns the integer representing the
 * patch file format version.
 * 
 * Can also return false if the version could not be read.
 * 
 * @since 1.0.0
 * 
 * @param resource $patch_file_handle The handle of the opened patch file.
 * 
 * @return int|false
 */
function patchwork_get_patch_file_format_version( $patch_file_handle ) {
	$cursor = ftell( $patch_file_handle );
	rewind( $patch_file_handle );

	// The magic number is 7 bytes long, skip past it.
	fseek( $patch_file_handle, 7 );

	$version = fread( $patch_file_handle, 1 );

	if ( ! $version ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	$version = unpack( 'cversion', $version );

	if ( ! is_array( $version ) || ! isset( $version['version'] ) ) {
		_patchwork_log( '_patchwork_get_patch_file_format_version failed to unpack version, possibly corrupted patch file.', 'error' );
		return false;
	}

	fseek( $patch_file_handle, $cursor );

	return $version['version'];
}

/**
 * Return an instance of PatchWork\Patch_Reader capable of reading patch files of the version specified.
 * 
 * @since 1.0.0
 * 
 * @param int $patch_file_version Specifies the patch file version the returned reader must be able to read.
 * 
 * @return 
 */
function patchwork_get_patch_reader( $patch_file_version ) {
	$reader = new \PatchWork\Patch_Reader_V1();

	/**
	 * Filters the reader for the patch file version.
	 * 
	 * @since 0.1.0
	 * 
	 * @param \PatchWork\Patch_Reader $parsrer The patch reader instance.
	 */
	return apply_filters( 'patchwork_patch_reader', $reader, $patch_file_version );
}

function patchwork_get_patch_writer( $patch_file_version ) {
	$writer = new PatchWork\Patch_Writer_V1();

	/**
	 * Filters the writter for the patch file version.
	 * 
	 * @since 0.1.0
	 * 
	 * @param PatchWork\Patch_Writer $writer The patch writer
	 */
	return apply_filters( 'patchwork_patch_writer', $writer, $patch_file_version );
}

/**
 * Returns the types of assets processed by PatchWork.
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function _patchwork_get_asset_types() {
	$asset_types = array( 'plugin', 'theme' );

	return apply_filters( 'patchwork_asset_types', $asset_types );
}

/**
 * Returns the estimated Central Directory Header (CDH) size in bytes.
 * 
 * @see docs/glossary.md	For a definition of CDH.
 * 
 * @since 0.1.0
 * 
 * @param \PatchWork\Types\File_Tree $file_tree
 * 
 * @return int
 */
function patchwork_estimate_cdh_size( \PatchWork\Types\File_Tree $file_tree ) {
	$size_of_tree = function( $tree, $path_length = 0 ) use( &$size_of_tree ) {
		$size = 0;
		$node = $tree;

		while ( $node ) {
			$name_length = strlen( $node->name ) + $path_length;
			$size += $name_length;
			$size += 46;

			if ( $node->first_child ) {
				$size++; // Add another byte for the trailing slash missed because this was a directory node.
				$size += $size_of_tree( $node->first_child, $name_length + 1 );
			}

			$node = $node->sibling;
		}

		return $size;
	};

	$estimated_cdh_size = $size_of_tree( $file_tree );

	return $estimated_cdh_size;
}

/**
 * Takes a CDH object and returns a file tree.
 * 
 * @see docs/glossary.md	For a definition of CDH.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Types\Zip_CDH[] $cdh_list
 * 
 * @return PatchWork\Types\File_Tree
 */
function patchwork_cdh_to_file_tree( $cdh_list ) {

	$make_tree = function( $files, $root = '.' ) use ( &$make_tree ) {
		// TODO: The candidates could be found faster by sorting the CDH list by depth first.
		$candidates = array_filter( $files, function( $cdh ) use ( $root ) {
			return dirname( $cdh->filename ) === $root;
		} );

		$last = null;
		$first = null;
		foreach ( $candidates as $file ) {
			$node = new PatchWork\Types\File_Tree();
			$node->name = basename( $file->filename );
			$node->checksum = $file->crc;

			if ( $last ) {
				$last->sibling = $node;
			} else {
				$first = $node;
			}

			// Is $file a directory?
			if ( $file->crc < 1 ) {
				$node->first_child = $make_tree( $files, untrailingslashit( $file->filename ) );
			}

			$last = $node;
		}

		return $first;
	};

	$tree = $make_tree( $cdh_list );

	return $tree;
}

/**
 * Given two file trees, compute a tree of their differences. The input trees
 * will be sorted in this function.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Types\File_Tree $tree_a
 * @param PatchWork\Types\File_Tree $tree_b
 * 
 * @return PatchWork\Types\File_Tree_Diff|null
 */
function patchwork_diff_file_trees( PatchWork\Types\File_Tree $tree_a = null, PatchWork\Types\File_Tree $tree_b = null ) {
	$tree_a = patchwork_sort_file_tree( $tree_a );
	$tree_b = patchwork_sort_file_tree( $tree_b );

	$diff = null;
	$last = null;

	$node_a = $tree_a;
	$node_b = $tree_b;

	while ( $node_a || $node_b ) {
		$node = null;

		if ( ! $node_a ) {
			// tree_b has node_b and tree_a doesn't.
			// Something has been added.
			$node = new PatchWork\Types\File_Tree_Diff();
			$node->name = $node_b->name;
			$node->checksum = $node_b->checksum;
			$node->status = PatchWork\Types\File_Tree_Diff::CHANGE_ADDED;

			// recurse for added node_b->first_child
			if ( $node_b->first_child ) {
				$child = patchwork_diff_file_trees( null, $node_b->first_child );

				if ( $child ) {
					$node->first_child = $child;
				}
			}

			$node_b = $node_b->sibling;
		} elseif ( ! $node_b ) {
			// tree_a has node_a and tree_b doesn't.
			// Something has been removed.
			$node = new PatchWork\Types\File_Tree_Diff();
			$node->name = $node_a->name;
			$node->checksum = $node_a->checksum;
			$node->status = PatchWork\Types\File_Tree_Diff::CHANGE_REMOVED;

			// recurse for deleted node_a->first_child
			if ( $node_a->first_child ) {
				$child = patchwork_diff_file_trees( $node_a->first_child, null );

				if ( $child ) {
					$node->first_child = $child;
				}
			}

			$node_a = $node_a->sibling;
		} elseif ( $node_a->name == $node_b->name ) {

			if ( $node_a->checksum != $node_b->checksum ) {
				// node_a exists in tree_b, but has been modified.
				$node = new PatchWork\Types\File_Tree_Diff();
				$node->name = $node_a->name;
				$node->checksum = $node_a->checksum;
				$node->status = PatchWork\Types\File_Tree_Diff::CHANGE_MODIFIED;

				// Any two nodes with the same name and different checksums could
				// not be directories so there's no need to check for children.
			} elseif ( $node_a->first_child ) {
				$child = patchwork_diff_file_trees( $node_a->first_child, $node_b->first_child );

				if ( $child ) {
					$node = new PatchWork\Types\File_Tree_Diff();
					$node->name = $node_a->name;
					$node->checksum = $node_a->checksum;
					$node->status = PatchWork\Types\File_Tree_Diff::UNCHANGED;
					$node->first_child = $child;
				}

			}

			$node_a = $node_a->sibling;
			$node_b = $node_b->sibling;
		} else {
			if ( strcmp( $node_a->name, $node_b->name ) < 1 ) {
				// node_a has been deleted
				$node = new PatchWork\Types\File_Tree_Diff();
				$node->name = $node_a->name;
				$node->checksum = $node_a->checksum;
				$node->status = PatchWork\Types\File_Tree_Diff::CHANGE_REMOVED;

				// recurse for deleted node_a->first_child
				if ( $node_a->first_child ) {
					$child = patchwork_diff_file_trees( $node_a->first_child, null );
	
					if ( $child ) {
						$node->first_child = $child;
					}
				}

				$node_a = $node_a->sibling;
			} else {
				// node_b has been added
				$node = new PatchWork\Types\File_Tree_Diff();
				$node->name = $node_b->name;
				$node->checksum = $node_b->checksum;
				$node->status = PatchWork\Types\File_Tree_Diff::CHANGE_ADDED;

				// recurse for added node_b->first_child
				if ( $node_b->first_child ) {
					$child = patchwork_diff_file_trees( null, $node_b->first_child );
	
					if ( $child ) {
						$node->first_child = $child;
					}
				}

				$node_b = $node_b->sibling;
			}
		}

		if ( $node ) {
			if ( $diff ) {
				$last->sibling = $node;
			} else {
				$diff = $node;
			}

			$last = $node;
		}

	}

	return $diff;
}

function patchwork_sort_file_tree( $tree ) {
	$tree = patchwork_shallow_sort_file_tree( $tree );

	$node = $tree;
	while ( $node ) {
		if ( $node->first_child ) {
			$node->first_child = patchwork_sort_file_tree( $node->first_child );
		}

		$node = $node->sibling;
	}

	return $tree;
}

function patchwork_shallow_sort_file_tree( $tree ) {
	if ( ! $tree || ! $tree->sibling ) {
		return $tree;
	}

	$slow = $tree;
	$fast = $tree;

	while ( $fast->sibling && $fast->sibling->sibling ) {
		$slow = $slow->sibling;
		$fast = $fast->sibling->sibling;
	}

	$middle = $slow->sibling;
	$slow->sibling = null;

	$left = patchwork_shallow_sort_file_tree( $tree );
	$right = patchwork_shallow_sort_file_tree( $middle );

	$tree = patchwork_file_tree_sorted_merge( $left, $right );

	return $tree;
}

// function patchwork_file_tree_sorted_merge( $tree_a, $tree_b ) {
// 	$result = null;

// 	if ( ! $tree_a ) {
// 		return $tree_b;
// 	}

// 	if ( ! $tree_b ) {
// 		return $tree_a;
// 	}

// 	if ( strcmp( $tree_a->name, $tree_b->name ) < 1 ) {
// 		$result = $tree_a;
// 		$result->sibling = patchwork_file_tree_sorted_merge( $tree_a->sibling, $tree_b );
// 	} else {
// 		$result = $tree_b;
// 		$result->sibling = patchwork_file_tree_sorted_merge( $tree_a, $tree_b->sibling );
// 	}

// 	return $result;
// }

function patchwork_file_tree_sorted_merge( $tree_a, $tree_b ) {
	$dummy = new PatchWork\Types\File_Tree;
	$tail = $dummy;

	$dummy->sibling = null;

	while ( true ) {
		if ( ! $tree_a ) {
			$tail->sibling = $tree_b;
			break;
		}

		if ( ! $tree_b ) {
			$tail->sibling = $tree_a;
			break;
		}

		if ( strcmp( $tree_a->name, $tree_b->name ) < 1 ) {
			$temp = $tree_a;
			$tree_a = $temp->sibling;
			$temp->sibling = $tail->sibling;
			$tail->sibling = $temp;
		} else {
			$temp = $tree_b;
			$tree_b = $temp->sibling;
			$temp->sibling = $tail->sibling;
			$tail->sibling = $temp;
		}

		$tail = $tail->sibling;
	}

	return $dummy->sibling;
}

function patchwork_file_trees_equal( PatchWork\Types\File_Tree $tree_a = null, PatchWork\Types\File_Tree $tree_b = null ) {
	if ( ! $tree_a ) {
		if ( ! $tree_b ) {
			return true;
		}
		
		return false;
	}

	$node_a = $tree_a;
	while ( $node_a ) {
		$node_b = $tree_b;
		while ( $node_b ) {

			if ( $node_a->name == $node_b->name ) {
				if (
					$node_a->checksum != $node_b->checksum
					|| $node_a->first_child && ! $node_b->first_child
					|| $node_b->first_child && ! $node_a->first_child
				) {
					return false;
				}

				if ( $node_a->first_child ) {
					if ( ! patchwork_file_trees_equal( $node_a->first_child, $node_b->first_child ) ) {
						return false;
					}
				}

				$node_a = $node_a->sibling;
				continue 2;
			}

			$node_b = $node_b->sibling;
		}

		$node_a = $node_a->sibling;
	}

	return true;
}