<?php
/**
 * Fired during plugin activation
 *
 * @link       wearesmile.com
 * @since      1.0.0
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smile_Courses
 * @subpackage Smile_Courses/includes
 * @author     We Are SMILE LTD <warren@wearesmile.com>
 */
class Smile_Courses_Activator {

	/**
	 * Set up the plugin defaults on activation.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if ( ! get_option( 'soc_options' ) ) {
			$option_defaults = array(
				'enable_course_modes' => true,
				'default_taxonomies' => array(
					'subject_area',
					'course_type', 
					'course_award',
				),
			);
			add_option( 'soc_options', $option_defaults );
		}
	}

}
