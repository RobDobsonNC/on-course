<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              wearesmile.com
 * @since             1.0.0
 * @package           Smile_Courses
 *
 * @wordpress-plugin
 * Plugin Name:       OnCourse
 * Plugin URI:        wearesmile.com
 * Description:       This plugin provides the foundations to create complex course listings on educational websites created by We Are SMILE. Speeding up development time, allowing developers to focus on the features that matter.
 * Version:           1.1.4
 * Author:            SMILE
 * Author URI:        wearesmile.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smile-courses
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smile-courses-activator.php
 */
function activate_smile_courses() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smile-courses-activator.php';
	Smile_Courses_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smile-courses-deactivator.php
 */
function deactivate_smile_courses() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smile-courses-deactivator.php';
	Smile_Courses_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smile_courses' );
register_deactivation_hook( __FILE__, 'deactivate_smile_courses' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smile-courses.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smile_courses() {

	$plugin = new Smile_Courses();
	$plugin->run();

}
run_smile_courses();
