<?php

namespace PatchWork\Admin;

class Menu {

	public static $menu_hook;

	/**
	 * Initialize the PatchWork admin menu.
	 * 
	 * @since 0.1.0
	 */
	public static function init() {
		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'include_assets' ], 20 );
	}

	/**
	 * Register the PatchWork admin menu
	 * 
	 * @since 0.1.0
	 */
	public static function register_menu() {
		self::$menu_hook = add_management_page(
			__( 'PatchWork', 'patchwork' ),
			__( 'PatchWork', 'patchwork' ),
			'manage_options',
			'patchwork',
			[ self::class, 'display_patches_page' ],
			1
		);
	}

	public static function display_patches_page() {
		?>
		<div class="wrap" id="patchwork-wrap">
		</div>
		<?php
	}

	public static function include_assets() {
		$screen = get_current_screen();

		if ( $screen && $screen->id == self::$menu_hook ) {
			wp_localize_script( 'patchwork-admin', 'patchwork', array(
				'pw_url'	=> PATCHWORK_URL
			) );

			wp_enqueue_script( 'patchwork-admin' );
			wp_enqueue_style( 'patchwork-admin' );
		}
	}

}