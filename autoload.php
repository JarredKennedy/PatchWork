<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Setup the PatchWork autoloader
function patchwork_autoloader( $class ) {
	// Fully qualified name components
	$fqn_components = explode( '\\', $class );

	if ( ! empty( $fqn_components ) ) {
		if ( 'PatchWork' === $fqn_components[0] ) {
			$path = 'includes/';
			$fqn_count = count( $fqn_components );

			// Start at 1 because component 0 = 'PatchWork'
			for ( $i = 1; $i < $fqn_count; $i++ ) {
				$component = $fqn_components[$i];

				if ( $i === ($fqn_count - 1) ) {
					$component = 'class-' . $component . '.php';
				} else {
					$component .= '/';
				}

				$path .= strtolower( str_replace( '_', '-', $component ) );
			}

			$path = PATCHWORK_PATH . $path;

			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}
	}
}

spl_autoload_register( 'patchwork_autoloader' );
