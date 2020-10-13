<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function patchwork_apply_patch( PatchWork\Patch $patch, PatchWork\Asset_Source $asset_source ) {
	if ( ! $patch->get_diffs() ) {
		throw new \RuntimeException( 'no diffs' );
		return false; // handle this better.
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
			return false; // handle this better.
		}

		$memory = @fopen( 'php://temp', 'r+' );

		$next_op = current( $ops );

		$original_line_number = 1;
		$patched_line_number = 1;
		
		$line = fgets( $file );
		// Check that there is a line to be written, or there is an op that effects the current line in the patched file.
		while ( $line || ( $next_op && $next_op->patched_line_start == $patched_line_number ) ) {

			if (
				$next_op
				&& $patched_line_number == $next_op->patched_line_start
				&&  $next_op->op === PatchWork\Types\Diff_OP::OP_ADD
			) {
				if ( ! $line ) {
					fwrite( $memory, "\n" );
				}

				fwrite( $memory, $next_op->patched . "\n" );

				$patched_line_number += $next_op->patched_lines_effected;

				$next_op = next( $ops );
			} elseif (
				$next_op
				&& $original_line_number == $next_op->original_line_start
				&& $next_op->op === PatchWork\Types\Diff_OP::OP_DELETE
			) {
				$original_line_number += $next_op->original_lines_effected;

				for ( $i = 0; $i < $next_op->original_lines_effected; $i++ ) {
					$line = fgets( $file );
				}

				$next_op = next( $ops );
			} elseif (
				$next_op
				&& $original_line_number == $next_op->original_line_start
				&& $next_op->op === PatchWork\Types\Diff_OP::OP_CHANGE
			) {
				fwrite( $memory, $next_op->patched . "\n" );

				$original_line_number += $next_op->original_lines_effected;
				$patched_line_number += $next_op->patched_lines_effected;

				for ( $i = 0; $i < $next_op->original_lines_effected; $i++ ) {
					$line = fgets( $file );
				}

				$next_op = next( $ops );
			} else {
				fwrite( $memory, $line );
				$original_line_number++;
				$patched_line_number++;

				$line = fgets( $file );
			}

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
 * targetted by the patch. Returns true if the vendor is verified.
 * 
 * @since 1.0.0
 * 
 * @param PatchWork\Patch $patch The patch of which the vendor is being verified.
 * 
 * @return bool
 */
function patchwork_verify_patch_vendor( \PatchWork\Patch $patch ) {
	$verification = _patchwork_get_verification();

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