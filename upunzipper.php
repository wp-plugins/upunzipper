<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://ravidhu.com
 * @since             1.0.0
 * @package           Upunzipper
 *
 * @wordpress-plugin
 * Plugin Name:       Upunzipper
 * Plugin URI:        http://ravidhu.com/upunzipper
 * Description:       This plugin allow you extract and add files in the media library from a zip file.
 * Version:           1.0.0
 * Author:            Ravidhu Dissanayake
 * Author URI:        http://ravidhu.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       upunzipper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-upunzipper-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-upunzipper-activator.php';
	Upunzipper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-upunzipper-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-upunzipper-deactivator.php';
	Upunzipper_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-upunzipper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Upunzipper();
	$plugin->run();

}
run_plugin_name();
