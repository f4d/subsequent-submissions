<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://rezon8.net/
 * @since             1.0.0
 * @package           Subsequent_Submissions
 *
 * @wordpress-plugin
 * Plugin Name:       Subsequent Submissions
 * Plugin URI:        http://rezon8.net/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            David Powers
 * Author URI:        http://rezon8.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subsequent-submissions
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-subsequent-submissions-activator.php
 */
function activate_subsequent_submissions() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subsequent-submissions-activator.php';
	Subsequent_Submissions_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subsequent-submissions-deactivator.php
 */
function deactivate_subsequent_submissions() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subsequent-submissions-deactivator.php';
	Subsequent_Submissions_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_subsequent_submissions' );
register_deactivation_hook( __FILE__, 'deactivate_subsequent_submissions' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-subsequent-submissions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_subsequent_submissions() {

	$plugin = new Subsequent_Submissions();
	$plugin->run();

}
run_subsequent_submissions();
