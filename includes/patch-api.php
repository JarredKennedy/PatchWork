<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_apply_patch( PatchWork\Patch $patch, PatchWork\Asset_Source $asset_source ) {
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

		$file = $asset_source->get_file( $diff->file_path );

		if ( ! is_resource( $file ) ) {
			return false; // TODO: handle this better.
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
function patchwork_revert_patch( PatchWork\Patch $patch, PatchWork\Asset_Source $asset_source ) {
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

		$file = $asset_source->get_file( $diff->file_path );

		if ( ! is_resource( $file ) ) {
			return false; // TODO: handle this better.
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