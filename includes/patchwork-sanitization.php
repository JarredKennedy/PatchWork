<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes a target asset identifier (TAI).
 * 
 * @since 0.1.0
 * 
 * @param string $raw_tai The string to sanitize into a valid TAI.
 * @param WP_Error $error Any errors occuring in this function will be attached to this WP_Error instance.
 * 
 * @return string The sanitized TAI.
 */
function patchwork_sanitize_target_asset_id( $raw_tai, $error = null ) {
	$asset_type = '';
	$asset_slug = '';
	$asset_version = '';

	$tai_components = explode( ':', $raw_tai );
	$num_components = count( $tai_components );

	if ( $error && $num_components !== 3 ) {
		$error->add(
			'pw_sanitize_tai_error',
			sprintf( __( 'Expected target asset identifier to have three components, %d found', 'patchwork' ), $num_components ),
			$raw_tai
		);
	}

	$asset_types = _patchwork_get_asset_types();

	if ( in_array( $tai_components[0], $asset_types ) ) {
		$asset_type = $tai_components[0];
	} elseif ( $error ) {
		$error->add(
			'pw_sanitize_tai_error',
			sprintf( __( 'Expected asset type to be one of %s but got %s', 'patchwork' ), implode( ', ', $asset_types ), $tai_components[0] ),
			$raw_tai
		);
	}

	if ( $num_components > 1 && ! empty( $tai_components[1] ) ) {
		$asset_slug = sanitize_title( $tai_components[1] );
	} elseif ( $error ) {
		$error->add(
			'pw_sanitize_tai_error',
			__( 'Asset slug component is missing', 'patchwork' ),
			$raw_tai
		);
	}

	if ( $num_components > 2 && ! empty( $tai_components[2] ) ) {
		$asset_version = $tai_components[2];
	} elseif ( $error ) {
		$error->add(
			'pw_sanitize_tai_error',
			__( 'Asset version component is missing', 'patchwork' ),
			$raw_tai
		);
	}

	return $asset_type . ':' . $asset_slug . ':' . $asset_version;
}