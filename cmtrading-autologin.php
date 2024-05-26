<?php
/**
 * Plugin Name: Cmtrading Autologin
 * Plugin URI: https://rgbcode.com/
 * Description: Plugin for automatic login to the webtrader page (based on Panda data).
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
define( 'CM_AUTOLOGIN_CORE_VERSION', '1.0.0' );
define( 'CM_AUTOLOGIN_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CM_AUTOLOGIN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CM_AUTOLOGIN_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );

require_once CM_AUTOLOGIN_PLUGIN_PATH . 'includes/class-autoloader.php';

/**
 * The code that runs during plugin activation.
 */
function activate_risco_group_core(): void {
	new \cm\classes\core\Activator();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_risco_group_core(): void {
	new \cm\classes\core\Dectivator();
}

register_activation_hook( __FILE__, 'activate_risco_group_core' );
register_deactivation_hook( __FILE__, 'deactivate_risco_group_core' );
