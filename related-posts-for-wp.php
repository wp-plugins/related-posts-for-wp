<?php
/*
	Plugin Name: Related Posts for WordPress
	Plugin URI: http://www.barrykooij.com/
	Description: Related Posts for WordPress, related posts that perform!
	Version: 1.1.0
	Author: Barry Kooij
	Author URI: http://www.barrykooij.com/
	License: GPL v3
	 
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	 
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	 
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class RP4WP {

	private static $instance = null;

	const VERSION = '1.1.0';

	/**
	 * @var RP4WP_Settings
	 */
	public $settings = null;

	/**
	 * Singleton get method
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return RP4WP
	 */
	public static function get() {
		if(null == self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the plugin file
	 *
	 * @access public
	 * @static
	 * @return String
	 */
	public static function get_plugin_file() {
		return __FILE__;
	}

	/**
	 * A static method that will setup the autoloader
	 */
	private static function setup_autoloader() {
		require_once( plugin_dir_path( self::get_plugin_file() ) . '/classes/class-autoloader.php' );
		$autoloader = new RP4WP_Autoloader( plugin_dir_path( self::get_plugin_file() ) . 'classes/' );
		spl_autoload_register( array( $autoloader, 'load' ) );
	}

	/**
	 * This method runs on plugin activation
	 */
	public static function activation() {

		// Setup autoloader
		self::setup_autoloader();

		// Run the installer
		$installer = new RP4WP_Installer();
		$installer->install();

		// Redirect to installation wizard
		add_site_option( RP4WP_Constants::OPTION_DO_INSTALL, true );
	}

	/**
	 * The constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {

		// Setup the autolaoder
		self::setup_autoloader();

		// Check if we need to run the installer
		if ( get_site_option( RP4WP_Constants::OPTION_DO_INSTALL, false ) ) {

			// Delete site option
			delete_site_option( RP4WP_Constants::OPTION_DO_INSTALL );

			// Redirect to installation wizard
			wp_redirect( admin_url() . '?page=rp4wp_install', 301 );
			exit;
		}

		// Setup settings
		if ( is_admin() ) {
			$this->settings = new RP4WP_Settings();
		}

		// Filters
		$manager_filter = new RP4WP_Manager_Filter( plugin_dir_path( __FILE__ ) . 'classes/filters/' );
		$manager_filter->load_filters();

		// Hooks
		$manager_hook = new RP4WP_Manager_Hook( plugin_dir_path( __FILE__ ) . 'classes/hooks/' );
		$manager_hook->load_hooks();
	}

}

function RP4WP() {
	return RP4WP::get();
}

function __rp4wp_main() {
	RP4WP();
}

// Create object - Plugin init
add_action( 'plugins_loaded', '__rp4wp_main' );

// Activation hook
register_activation_hook( __FILE__, array( 'RP4WP', 'activation' ) );