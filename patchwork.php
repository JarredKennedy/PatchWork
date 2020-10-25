<?php
/**
 * @package     PatchWork
 * @author      Jarred Kennedy
 * @copyright   2018-2020 Jarred Kennedy
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: PatchWork
 * Plugin URI: https://{store-url}/patchwork
 * Description: Manage custom code changes as patches to keep them safe from updates and out of functions.php
 * Author: Jarred Kennedy
 * Version: 1.0.0-alpha
 * Requires PHP: 5.4
 * Text Domain: patchwork
 * Domain Path: /languages
 * License: MIT
 *
 * PatchWork is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PatchWork' ) ):

/**
 * PatchWork container class
 * 
 * @since 0.1.0
 */
final class PatchWork {

	/**
	 * @var PatchWork Singleton instance of PatchWork
	 */
	private static $instance;

	/**
	 * Instantiate the singleton instance
	 * 
	 * @since 0.1.0
	 * 
	 * @return PatchWork
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof PatchWork ) {
			self::$instance = new PatchWork;

			if ( self::$instance->check_environment() ) {
				self::$instance->bootstrap();
			}
		}

		return self::$instance;
	}

	/**
	 * Set constants, include files
	 * 
	 * @since 0.1.0
	 */
	public function bootstrap() {

		if ( ! defined( 'PATCHWORK_VERSION' ) ) {
			define( 'PATCHWORK_VERSION', '0.1.0' );
		}

		if ( ! defined( 'PATCHWORK_PATH' ) ) {
			define( 'PATCHWORK_PATH', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'PATCHWORK_URL' ) ) {
			define( 'PATCHWORK_URL', plugins_url( '', __FILE__ ) );
		}

		if ( ! defined( 'PATCHWORK_PLUGIN_FILE' ) ) {
			define( 'PATCHWORK_PLUGIN_FILE', __FILE__ );
		}

		if ( ! defined( 'PATCHWORK_PATCH_DIR' ) ) {
			define( 'PATCHWORK_PATCH_DIR', WP_CONTENT_DIR . '/patchwork' );
		}

		if ( ! defined( 'PATCHWORK_USE_PATCH_VERSION' ) ) {
			define( 'PATCHWORK_USE_PATCH_VERSION', 1 );
		}

		require_once PATCHWORK_PATH . 'autoload.php';

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		if ( function_exists( 'register_rest_route' ) ) {
			require_once PATCHWORK_PATH . 'includes/rest-api.php';
		} else {
			require_once PATCHWORK_PATH . 'includes/ajax-api.php';
		}

		require_once PATCHWORK_PATH . 'includes/admin/admin-menu.php';
		require_once PATCHWORK_PATH . 'includes/patchwork-utilities.php';
		require_once PATCHWORK_PATH . 'includes/patch-api.php';
		require_once PATCHWORK_PATH . 'includes/asset-api.php';
		require_once PATCHWORK_PATH . 'includes/scan-api.php';
	}

	/**
	 * Ensure the server environment meets the plugin requirements. Returns
	 * true indicating the plugin should initialize, or false if it shouldn't.
	 * 
	 * @since 0.1.0
	 * 
	 * @return bool
	 */
	public function check_environment() {
		if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
			add_action( 'admin_notices', function() {
				sprint( '<div class="notice notice-warning"><p>' . __( 'PatchWork requires PHP version 5.4 or higher', 'patchwork' ) . '</p></div>' );
			} );

			return false;
		}

		if ( ! wp_is_file_mod_allowed( 'file_patches:patchwork' ) ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-warning"><p>';
				_e( 'Your WordPress environment disallows making modifications to files. Check with your website administrator or hosting provider about the use of the DISALLOW_FILE_MODS constant.', 'patchwork' );
				echo '</p></div>';
			} );

			return false;
		}

		return true;
	}

	public function activate() {
		// Ensure the patches directory exists.
		$directory = ABSPATH . 'wp-content/patches';

		if ( ! file_exists( $directory ) || ! is_dir( $directory ) ) {
			@mkdir( $directory, 0644, false );
		}
	}

}

endif;

function patchwork() {
	return PatchWork::get_instance();
}

patchwork();
