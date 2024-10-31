<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       wearesmile.com
 * @since      1.0.0
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Smile_Courses
 * @subpackage Smile_Courses/includes
 * @author     We Are SMILE LTD <warren@wearesmile.com>
 */
class Smile_Courses {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smile_Courses_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = '1.0.1';
		$this->plugin_name = 'smile-courses';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smile_Courses_Loader. Orchestrates the hooks of the plugin.
	 * - Smile_Courses_I18n. Defines internationalization functionality.
	 * - Smile_Courses_Admin. Defines all hooks for the admin area.
	 * - Smile_Courses_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smile-courses-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smile-courses-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-smile-courses-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-smile-courses-public.php';

		$this->loader = new Smile_Courses_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smile_Courses_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smile_Courses_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Smile_Courses_Admin( $this->get_plugin_name(), $this->get_version() );
		$options = get_option( 'soc_options' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_admin_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_admin_scripts' );

		// Pre filter course modes.
		$this->loader->add_filter( 'transition_post_status', $plugin_admin, 'pre_filter_slug', 10, 3 );

		// Filter post titles.
		$this->loader->add_filter( 'save_post', $plugin_admin, 'prevent_empty_titles', 10, 3 );

		// Filter course mode slug on save.
		$this->loader->add_filter( 'editable_slug', $plugin_admin, 'edit_course_mode_slug', 2, 2 );

		// Courses.
		$this->loader->add_action( 'init', $plugin_admin, 'register_taxonomies', 11 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'menu_highlight' );
		$this->loader->add_action( 'init', $plugin_admin, 'add_rewrite_rules', 11 );
		// $this->loader->add_filter( 'post_type_link', $plugin_admin, 'soc_permalinks', 10, 3 );
		$this->loader->add_filter( 'preview_post_link', $plugin_admin, 'soc_preview_post_link', 10, 2 );

		if ( isset( $options['course_mode_search'] ) ) {
			$this->loader->add_action( 'wp_insert_post_data', $plugin_admin, 'oncourse_parent_mode_save_post', 10, 2 );
			$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'admin_post_mode_query', 10, 1 );
			$this->loader->add_filter( 'page_row_actions', $plugin_admin, 'course_remove_row_actions', 10, 1 );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'change_post_object_label' );
		} else {
			$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'admin_post_query', 10, 1 );
		}


		$this->loader->add_action( 'init', $plugin_admin, 'sp_register_post_types', 10, 3 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'sp_menu_page_removing', 10 );

		// Course Modes.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'course_type_remove_meta_box' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'course_type_add_meta_box' );

		// Courses Settings.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		$this->loader->add_filter( 'register_post_type_args', $plugin_admin, 'soc_alter_post_type_supports', 10, 2 );

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'courses_default_child_selector' );

		$this->loader->add_action( 'save_post', $plugin_admin, 'smile_save_courses_default_child_selector' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'pre_get_course_award' );
		// $this->loader->add_action( 'template_redirect', $plugin_admin, 'template_redirect_course_award' );

		// Archived post status.
		$this->loader->add_action( 'init', $plugin_admin, 'register_post_status' );
		$this->loader->add_action( 'admin_footer-post.php', $plugin_admin, 'append_post_status_list' );
		$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin, 'add_archived_status_to_quick_edit' );

		$this->loader->add_action( 'post_updated', $plugin_admin, 'set_course_modes_to_archived', 10, 3 );

		$this->loader->add_action( 'edit_form_after_title', $plugin_admin, 'output_plain_text_title', 10, 1 );

		$this->loader->add_action( 'current_screen', $plugin_admin, 'switch_off_cpt_support', 10, 1 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smile_Courses_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Custom action that allows us to get the default.
		$this->loader->add_filter( 'smile_oncourse_get_default_mode', $plugin_public, 'return_default_course_mode' );

		// Course
		$this->loader->add_action( 'template_redirect', $plugin_public, 'course_template_redirect', 50 ); // Redirect the course to the mode
		$this->loader->add_filter( 'wp_title', $plugin_public, 'course_mode_page_title' ); // Change the page title of the course more
//		$this->loader->add_filter( 'query_vars', $plugin_public, 'soc_query_vars' ); // Modify the query variables
		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'load_course_mode', 1, 1 ); // Load the course mode
//
//		// Course Mode
//		$this->loader->add_filter( 'template_include', $plugin_public, 'course_mode_template', 99 ); // Set the correct template
//
		// $this->loader->add_filter( 'admin_bar_menu', $plugin_public, 'add_toolbar_items', 100 ); // Add to admin bar
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Smile_Courses_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
