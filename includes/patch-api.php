<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_apply_patch( PatchWork\Patch $patch, PatchWork\Writeable_Asset_Source $asset_source ) {
	if ( ! $patch->get_diffs() ) {
		throw new \RuntimeException( 'no diffs' );
		return false; // TODO: handle this better.
	}

	foreach ( $patch->get_diffs() as $diff ) {
		$ops = $diff->get_ops();

		if ( empty( $ops ) ) {
			continue;
		}

		// It's important that the ops are ordered by line number ascending.
		usort( $ops, function( $diff_a, $diff_b ) {
			return $diff_a->original_line_start - $diff_b->original_line_start;
		} );

		$file_exists = $asset_source->file_exists( $diff->file_path );

		if ( ! $file_exists && $diff->get_lines_added() > 0 ) {
			// This file has been added.
			$file = $asset_source->get_file( $diff->file_path, true );
		} elseif ( $file_exists && $diff->get_lines_added() < 1 && $diff->get_lines_deleted() > 0 ) {
			// This file has been deleted.
			$asset_source->delete_file( $diff->file_path );
			continue;
		} else {
			$file = $asset_source->get_file( $diff->file_path, false );
		}

		if ( ! is_resource( $file ) ) {
			throw new \RuntimeException( 'File couldn\'t be opened' ); // TODO: handle this better.
		}

		$memory = @fopen( 'php://temp', 'r+' );

		$original_line_number = 1;
		$patched_line_number = 1;
		while ( ( $op = array_shift( $ops ) ) != null ) {

			while (
				( ! $op->original_lines_effected || ( $op->original_line_start != $original_line_number ) )
				&& ( ! $op->patched_lines_effected || ( $op->patched_line_start != $patched_line_number ) )
				&& ( $line = fgets( $file ) ) !== false
			) {
				fwrite( $memory, $line );
				$original_line_number++;
				$patched_line_number++;
			}

			if ( $op->original_lines_effected ) {
				// Skip these lines from the original.
				for ( $i = 0; $i < $op->original_lines_effected; $i++ ) {
					fgets( $file );
				}

				$original_line_number += $op->original_lines_effected;
			}

			if ( $op->patched_lines_effected ) {
				fwrite( $memory, implode( '', $op->patched ) );
				$patched_line_number += $op->patched_lines_effected;
			}

		}

		// If the end of file was not reached, copy the rest of the original buffer to the patched buffer.
		if ( ! feof( $file ) ) {
			stream_copy_to_stream( $file, $memory );
		}

		ftruncate( $file, 0 );
		rewind( $file );
		rewind( $memory );

		stream_copy_to_stream( $memory, $file );

		fclose( $memory );
		fclose( $file );
	}
}

/**
 * For a patched asset_source, revert the files to their state before
 * they were patched.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Patch $patch
 * @param PatchWork\Asset_Source $asset_source
 * 
 * @return bool
 */
function patchwork_revert_patch( PatchWork\Patch $patch, PatchWork\Writeable_Asset_Source $asset_source ) {
	// Check that this patch is actually applied to this file.
	// patchwork_asset_is_patched( $asset, $patch );

	if ( ! $patch->get_diffs() ) {
		// TODO: make this better.
		throw new \RuntimeException( 'no diffs' );
	}

	foreach ( $patch->get_diffs() as $diff ) {
		$ops = $diff->get_ops();

		if ( empty( $ops ) ) {
			continue;
		}

		usort( $ops, function( $diff_a, $diff_b ) {
			return $diff_a->original_line_start - $diff_b->original_line_start;
		} );

		$file_exists = $asset_source->file_exists( $diff->file_path );

		if ( $file_exists && $diff->get_lines_added() > 0 && $diff->get_lines_deleted() < 1 ) {
			// This file was added by the patch.
			$asset_source->delete_file( $diff->file_path );
			continue;
		} elseif ( ! $file_exists && $diff->get_lines_added() < 1 && $diff->get_lines_deleted() > 0 ) {
			// This file was deleted by the patch.
			$file = $asset_source->get_file( $diff->file_path, true );
		} else {
			$file = $asset_source->get_file( $diff->file_path, false );
		}

		if ( ! is_resource( $file ) ) {
			throw new \RuntimeException( 'File couldn\'t be opened' ); // TODO: handle this better.
		}

		$memory = @fopen( 'php://temp', 'r+' );

		$original_line_number = 1;
		$patched_line_number = 1;
		while ( ( $op = array_shift( $ops ) ) != null ) {

			while (
				( ! $op->original_lines_effected || ( $op->original_line_start != $original_line_number ) )
				&& ( ! $op->patched_lines_effected || ( $op->patched_line_start != $patched_line_number ) )
				&& ( $line = fgets( $file ) ) !== false
			) {
				fwrite( $memory, $line );
				$original_line_number++;
				$patched_line_number++;
			}

			if ( $op->patched_lines_effected ) {
				// Skip these lines from the patched.
				for ( $i = 0; $i < $op->patched_lines_effected; $i++ ) {
					fgets( $file );
				}

				$patched_line_number += $op->patched_lines_effected;
			}

			if ( $op->original_lines_effected ) {
				fwrite( $memory, implode( '', $op->original ) );
				$original_line_number += $op->original_lines_effected;
			}

		}

		// If the end of file was not reached, copy the rest of the patched buffer to the original buffer.
		if ( ! feof( $file ) ) {
			stream_copy_to_stream( $file, $memory );
		}

		ftruncate( $file, 0 );
		rewind( $file );
		rewind( $memory );

		stream_copy_to_stream( $memory, $file );

		fclose( $memory );
		fclose( $file );
	}


}

/**
 * Verifies that the vendor of the patch is the same vendor of the asset
 * targeted by the patch. Returns true if the vendor is verified.
 * 
 * @since 1.0.0
 * 
 * @param PatchWork\Patch $patch The patch of which the vendor is being verified.
 * 
 * @return bool
 */
function patchwork_verify_patch_vendor( \PatchWork\Patch $patch ) {
	$verification = patchwork_get_verification();

	$verified = $verification->verify( $patch );

	/**
	 * Filters the verified status of a vendor for a patch.
	 * 
	 * @since 1.0.0
	 * 
	 * @param bool $verified True when the patch vendor has been verified.
	 * @param \PatchWork\Patch $patch The patch of which the vendor is being verified.
	 */
	return apply_filters( 'patchwork_verify_patch_vendor', $verified, $patch );
}

/**
 * Takes two asset sources, diff them, and return diffs.
 * 
 * @since 0.1.0
 * 
 * @param PatchWork\Types\File_Tree_Diff $file_tree_diff
 * @param PatchWork\Asset_Source $original_source
 * @param PatchWork\Asset_Source $patched_source
 * 
 * @return PatchWork\Diff[]
 */
function patchwork_diff_files( PatchWork\Types\File_Tree_Diff $changed_files, PatchWork\Asset_Source $original_source, PatchWork\Asset_Source $patched_source ) {
	$flat_tree = array();

	patchwork_walk_file_tree( $changed_files, function( $node, $path, $is_file ) use ( &$flat_tree ) {
		if ( $is_file ) {
			$flat_tree[] = array(
				$path . $node->name,
				$node->status
			);
		}
	} );

	$differ = new PatchWork\Difflib_Differ();

	$diffs = array();
	foreach ( $flat_tree as $file ) {
		list( $file_path, $status ) = $file;

		if ( $status === PatchWork\Types\File_Tree_Diff::CHANGE_ADDED ) {
			$diff = new PatchWork\Diff();
			$diff->file_path = $file_path;
			
			$op = new PatchWork\Types\Diff_OP();
			$op->original_line_start = 1;
			$op->original_lines_effected = 0;
			$op->patched_line_start = 1;

			$new_file = $patched_source->get_file( $file_path );

			while ( ($line = fgets( $new_file )) != null ) {
				$op->patched[] = $line;	
			}

			fclose( $new_file );

			$op->patched_lines_effected = count( $op->patched );

			$diff->add_op( $op );

			$diffs[] = $diff;
		} elseif ( $status === PatchWork\Types\File_Tree_Diff::CHANGE_REMOVED ) {
			$diff = new PatchWork\Diff();
			$diff->file_path = $file_path;
			
			$op = new PatchWork\Types\Diff_OP();
			$op->original_line_start = 1;
			$op->patched_line_start = 1;
			$op->patched_lines_effected = 0;

			$old_file = $original_source->get_file( $file_path );

			while ( ($line = fgets( $old_file )) !== false ) {
				$op->original[] = $line;	
			}

			fclose( $old_file );

			$op->original_lines_effected = count( $op->original );

			$diff->add_op( $op );

			$diffs[] = $diff;
		} elseif ( $status === PatchWork\Types\File_Tree_Diff::CHANGE_MODIFIED ) {
			$original_lines = array();
			$current_lines = array();

			$original_file = $original_source->get_file( $file_path );
			while ( ($line = fgets( $original_file )) !== false ) $original_lines[] = $line;
			fclose( $original_file );

			$current_file = $patched_source->get_file( $file_path );
			while ( ($line = fgets( $current_file )) !== false ) $current_lines[] = $line;
			fclose( $current_file );

			$diff = $differ->diff( $original_lines, $current_lines );
			$diff->file_path = $file_path;

			$diffs[] = $diff;
		}
	}

	return $diffs;
}

function patchwork_add_patch( $patch, $patch_hash, $patch_file ) {
	$patches = get_option( 'patchwork_patches', array() );

	$patch_header = $patch->get_header();
	$patch_name = $patch_header->name;

	if ( empty( $patch_name ) ) {
		$tai = $patch_header->target_asset_identifier;
		$asset = patchwork_get_asset( $tai );

		$patch_name = sprintf(
			__( '%s Patch #%s', 'patchwork' ),
			$asset->get_name(),
			substr( $patch_hash, 0, 12 )
		);
	}

	$patches[$patch_hash] = array(
		'status'	=> 'inactive',
		'file'		=> $patch_file,
		'name'		=> $patch_name
	);

	update_option( 'patchwork_patches', $patches );
}