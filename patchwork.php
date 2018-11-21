<?php
/**
 * @package     PatchWork
 * @author      Jarred Kennedy
 * @copyright   2018 Jarred Kennedy
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: PatchWork
 * Plugin URI: https://wordpress.org/plugins/patchwork#hopefully
 * Description: Manage custom code changes as patches to keep them safe from updates and out of functions.php
 * Author: Jarred Kennedy
 * Author URI: https://jarredkennedy.com/open-source
 * Version: 0.1.0
 * Requires PHP: 5.4
 * Text Domain: patchwork
 * Domain Path: /languages
 *
 * PatchWork is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * PatchWork is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PatchWork.  If not, see <https://www.gnu.org/licenses/>.
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
	private $instance;

	/**
	 * @var PatchWork\Assets Exposes functions for managing  
	 */
	private $assets;

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
			define( 'PATCHWORK_PATCH_DIR', untrailingslashit( wp_upload_dir() ) . '/patchwork' );
		}

		if ( ! defined( 'PATCHWORK_TEXT_DOMAIN' ) ) {
			define( 'PATCHWORK_TEXT_DOMAIN', 'patchwork' );
		}

	}

	/**
	 * Ensure the server environment meets the plugin requirements
	 * 
	 * @since 0.1.0
	 * 
	 * @return bool
	 */
	public function check_environment() {
		if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
			add_action( 'admin_notices', function() {
				sprint( '<div class="notice notice-warning"><p>' . __( 'PatchWork requires PHP version 5.4 or higher', PATCHWORK_TEXT_DOMAIN ) . '</p></div>' );
			} );

			return false;
		}

		return true;
	}

}

endif;

function patchwork() {
	return PatchWork::get_instance();
}