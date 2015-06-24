<?php
/*
Plugin Name: Customizer Removal Options Panel
Plugin URI: https://github.com/parallelus/customizer-remove-all-parts
Description: Completely removes the WordPress Customizer from loading in your install.
Version: 1.0.0
Author: Jesse Petersen, Andy Wilkerson
Author URI: http://pmgllc.github.io/customizer-removal-options-panel
Text Domain: wp-crop
Domain Path: /languages

Copyright 2015 Jesse Petersen, Andy Wilkerson.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

// Exit if accessed directly
if ( __FILE__ == $_SERVER['SCRIPT_FILENAME'] ) { exit; }


if (!class_exists('Customizer_Removal_Options')) :
class Customizer_Removal_Options {

	/**
	 * @var Customizer_Removal_Options
	 */
	private static $instance;


	/**
	 * Main Instance
	 *
	 * Allows only one instance of Customizer_Removal_Options in memory.
	 *
	 * @static
	 * @staticvar array $instance
	 * @return Big mama, Customizer_Removal_Options
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Customizer_Removal_Options ) ) {

			// Start your engines!
			self::$instance = new Customizer_Removal_Options;

			// Load the structures to trigger initially
			add_action( 'plugins_loaded', array( self::$instance, 'load_languages' ) );
			add_action( 'init', array( self::$instance, 'init' ), 10 ); // was priority 5
			add_action( 'admin_init', array( self::$instance, 'admin_init' ), 10 ); // was priority 5

		}
		return self::$instance;
	}

	/**
	 * Run all plugin stuff on init.
	 *
	 * @return void
	 */
	public function init() {

		// Remove customize capability
		add_filter( 'map_meta_cap', array( self::$instance, 'filter_to_remove_customize_capability'), 10, 4 );
	}

	/**
	 * Run all of our plugin stuff on admin init.
	 *
	 * @return void
	 */
	public function admin_init() {

		// Drop some customizer actions
		remove_action( 'plugins_loaded', '_wp_customize_include', 10);
		remove_action( 'admin_enqueue_scripts', '_wp_customize_loader_settings', 10);

		// Manually overrid Customizer behaviors
		add_action( 'load-customize.php', array( self::$instance, 'override_load_customizer_action') );
	}

	/**
	 * Load our language files
	 *
	 * @access public
	 * @return void
	 */
	public function load_languages() {
		// Set textdomain string
		$textdomain = 'wp-crop';

		// The 'plugin_locale' filter is also used by default in load_plugin_textdomain()
		$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

		// Set filter for WordPress languages directory
		$wp_languages_dir = apply_filters( 'crop_wp_languages_dir',	WP_LANG_DIR . '/wp-crop/' . $textdomain . '-' . $locale . '.mo' );

		// Translations: First, look in WordPress' "languages" folder
		load_textdomain( $textdomain, $wp_languages_dir );

		// Translations: Next, look in plugin's "languages" folder (default)
		$plugin_dir = basename( dirname( __FILE__ ) );
		$languages_dir = apply_filters( 'crop_languages_dir', $plugin_dir . '/languages' );
		load_plugin_textdomain( $textdomain, FALSE, $languages_dir );
	}

	/**
	 * Remove customize capability
	 *
	 * This needs to be in public so the admin bar link for 'customize' is hidden.
	 */
	public function filter_to_remove_customize_capability( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
		if ($cap == 'customize') {
			return array('nope'); // thanks @ScreenfeedFr, http://bit.ly/1KbIdPg
		}

		return $caps;
	}

	/**
	 * Manually overriding specific Customizer behaviors
	 */
	public function override_load_customizer_action() {
		// If accessed directly
		wp_die( __( 'The Customizer is currently disabled.', 'wp-crop' ) );
	}

} // End Class
endif;

/**
* The main function. Use like a global variable, except no need to declare the global.
*
* @return object The one true Customizer_Removal_Options Instance
*/
function Customizer_Removal_Options() {
	return Customizer_Removal_Options::instance();
}

// GO!
Customizer_Removal_Options();