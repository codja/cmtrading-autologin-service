<?php
/**
 * Plugin Name: Cmtrading Autologin
 * Plugin URI: https://rgbcode.com/
 * Description: Plugin for automatic login to the webtrader.
 * Author: rgbcode
 * Author URI: https://rgbcode.com/
 * Version: 1.0.0
 * Text Domain: cmtrading-autologin
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		exit();
	}
}

/**
 * Currently plugin version.
 */
define( 'CMAS_AUTOLOGIN_CORE_VERSION', '1.0.0' );
define( 'CMAS_AUTOLOGIN_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CMAS_AUTOLOGIN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CMAS_AUTOLOGIN_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );

require_once CMAS_AUTOLOGIN_PLUGIN_PATH . 'includes/class-autoloader.php';

/**
 * The code that runs during plugin activation.
 */
function activate_cmas_autologin(): void {
	new \cmas\classes\core\Activator();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cmas_autologin(): void {
	new \cmas\classes\core\Deactivator();
}

register_activation_hook( __FILE__, 'activate_cmas_autologin' );
register_deactivation_hook( __FILE__, 'deactivate_cmas_autologin' );
