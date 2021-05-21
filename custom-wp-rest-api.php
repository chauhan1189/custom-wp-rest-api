<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             99999999
 * @package           Cstom WP Rest API
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Wp Rest-API
 * Plugin URI:        ../custom-wp-rest-api-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           99999999
 * Author:            Shalini Bhardwaj
 * Author URI:        /
 * License:           GPL-2.0+
 * Text Domain:       custom-wp-rest-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 99999999 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'Custom_wp_rest_api_VERSION', '99999999' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-wp-rest-api-activator.php
 */
function activate_Custom_wp_rest_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-wp-rest-api-activator.php';
	Custom_wp_rest_api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-wp-rest-api-deactivator.php
 */
function deactivate_Custom_wp_rest_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-wp-rest-api-deactivator.php';
	Custom_wp_rest_api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Custom_wp_rest_api' );
register_deactivation_hook( __FILE__, 'deactivate_Custom_wp_rest_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-wp-rest-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    99999999
 */
function run_Custom_wp_rest_api() {

	$plugin = new Custom_wp_rest_api();
	$plugin->run();

}
run_Custom_wp_rest_api();

/***************************Defining some constants***************************/

define('SITE_URL', get_site_url());
define( 'POSTS_PER_PAGE', 10 );
define( 'DIR_NAME', dirname(__FILE__) );
/*******************************************************************************/
/************************Blog Posts end points start here***********************/
/*******************************************************************************/

if ( post_type_exists( 'post' ) ){
	require_once( DIR_NAME . '/endpoints/wp-posts-endpoints.php' );

}

if ( post_type_exists( 'product' ) ){
	add_action( 'woocommerce_loaded', 'action_woocommerce_loaded', 10, 1 );

	function action_woocommerce_loaded(){

		require_once( DIR_NAME . '/endpoints/wp-woocommerce-endpoints.php' );

	}
}