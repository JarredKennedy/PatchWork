<?php

function pretty_print_tree( PatchWork\Types\File_Tree $tree, $depth = 0 ) {
	$status_text = [ 'Unchanged', 'Added', 'Removed', 'Modified' ];

	$node = $tree;
	while ( $node ) {
		echo str_repeat( "\t", $depth );

		if ( $node->checksum > 0 ) {
			echo "+ ";
			echo $node->name;
			if ( property_exists( $node, 'status' ) ) {

			}
		} else {
			echo $node->name . "/";
		}

		if ( property_exists( $node, 'status' ) ) {
			printf( ' (%s)', $status_text[$node->status] );
		}

		echo PHP_EOL;

		if ( $node->first_child ) {
			pretty_print_tree( $node->first_child, $depth + 1 );
		}

		$node = $node->sibling;
	};

}