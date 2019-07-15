<?php

namespace PatchWork\Admin;

class Menu {

	/**
	 * Initialize the PatchWork admin menu.
	 * 
	 * @since 0.1.0
	 */
	public static function init() {
		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
	}

	/**
	 * Register the PatchWork admin menu
	 * 
	 * @since 0.1.0
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'PatchWork', 'patchwork' ),
			__( 'PatchWork', 'patchwork' ),
			'manage_options',
			'patchwork',
			[ self::class, 'display_patches_page' ],
			null,
			42
		);
	}

	public static function display_patches_page() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'PatchWork', 'patchwork' ); ?></h1>
			<hr class="wp-header-end">
			<div id="patchwork-app">
				<ul>
					<li>List</li>
					<li>Of</li>
					<li>Patches</li>
					<li>I</li>
					<li>Guess</li>
				</ul>			
			</div>
		</div>
		<?php
	}

}