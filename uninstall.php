<?php
/**
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean up unused meta, options, etc. in the database.
 *
 * @package WordPress Plugin Template/Uninstall
 */

// If plugin is not being uninstalled, exit (do nothing).
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Do something here if plugin is being uninstalled.
$acf_options_list = [
	'cmas_autologin_enable',
	'cmas_matching_lang_list',
	'cmas_autologin_ip_black_list',
];

if ( function_exists( 'delete_field' ) ) {
	foreach ( $acf_options_list as $option_name ) {
		delete_field( $option_name, 'option' );
	}
}
