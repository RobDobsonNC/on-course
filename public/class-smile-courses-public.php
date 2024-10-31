<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       wearesmile.com
 * @since      1.0.0
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/public
 * @author     We Are SMILE LTD <warren@wearesmile.com>
 */
class Smile_Courses_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smile_Courses_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smile_Courses_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( ! isset( get_option( 'soc_options' )['styles_disabled'] ) ) :
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smile-courses-public.min.css', array(), $this->version, 'all' );
		endif;

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smile_Courses_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smile_Courses_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( ! isset( get_option( 'soc_options' )['js_disabled'] ) ) :
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/min/smile-courses-public.min.js', array( 'jquery' ), $this->version, false );
		endif;

	}

	/**
	 * Expose parent course to a course mode single.
	 *
	 * @since 1.0.0
	 */
	public function get_the_course( $course_mode_id = null ) {
		if ( ! post_type_exists( 'course_mode' ) ) {
			return;
		}
		global $post;
		static $course_post = null;
		if ( null !== $course_mode_id ) :
			if ( empty( $course_post ) ) :
				$course_mode_post = get_post( $course_mode_id );
				$course_post      = get_post( $course_mode_post->post_parent );
			endif;
		elseif ( 'course_mode' === get_post_type() && ! empty( $post ) ) :
			if ( empty( $course_post ) ) :
				$course_post = get_post( $post->post_parent );
			endif;
		endif;
		return $course_post;
	}

	/**
	 * Assign the course mode template to that of a course.
	 */
	public function course_mode_template( $template ) {
		if ( post_type_exists( 'course_mode' ) ) {
			if ( 'course_mode' === get_post_type() && is_single() ) :
				$new_template = locate_template( array( 'single-course.php' ) );
				if ( '' != $new_template ) :
					return $new_template;
				endif;
			endif;
		}
		return $template;
	}

	/**
	 * Helper function to echo out a course title.
	 */
	public function the_course_title() {
		echo $this->get_the_course_title();
	}

	/**
	 * Retrieve and return the current course title.
	 */
	public function get_the_course_title() {
		global $course_post;
		return $course_post->post_title;
	}

	/**
	 * Modify the current course mode title to include the parent course title.
	 */
	public function course_mode_page_title( $data ) {
		if ( post_type_exists( 'course_mode' ) ) {
			global $post, $course_post;
			if ( isset( $course_post ) ) :
				return $course_post->post_title . ' - ' . $data;
			endif;
		}
		return $data;
	}

	/**
	 * Modify the query vars.
	 */
	public function soc_query_vars( $vars ) {
		if ( post_type_exists( 'course_mode' ) ) {
			$vars[] = '_course';
		}
		return $vars;
	}

	/**
	 * Load the course mode when on a fake course mode page.
	 */
	public function load_course_mode( $query ) {
		if ( is_admin() ) {
			return $query;
		}
		if ( ! post_type_exists( 'course_mode' ) ) {
			return $query;
		}
		if ( $query->is_main_query() && 'course_mode' === $query->get( 'post_type' ) ) :
			$course_slug = get_query_var( '_course' );
			$args        = array(
				'name'        => $course_slug,
				'post_type'   => 'course',
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$course      = current( get_posts( $args ) );
			if ( $course && $course_slug === $course->post_name ) :
				$query->set( 'post_parent', $course->ID );
				$query->set( 'posts_per_page', 1 );
			else :
				$query->set( 'is_404', true );
			endif;

		endif;
		return $query;
	}

	/**
	 * Get the first published post as the defaualt instead.
	 */
	public function get_first_published( $parent_id ) {

		$args = array(
			'post_parent' => $parent_id,
			'post_type'   => 'course_mode',
			'numberposts' => 1,
			'post_status' => [ 'publish', 'sbc_pending' ],
		);

		$args        = apply_filters( 'smile_oc_filter_course_mode_redirect_args', $args, $wp_query );
		$course_mode = get_children( $args );
		$course_mode = current( $course_mode );

		if ( $course_mode ) :
			return $course_mode;
		endif;
	}

	/**
	 * Get the post object of the default course mode.
	 */
	public function return_default_course_mode( $post_id ) {

		global $wp_query;

		if ( isset( get_option( 'soc_options' )['enable_default_course_modes'] ) && get_post_meta( $post_id, 'smile_default_course_mode', true ) ) :

			$default_mode = get_posts(
				array(
					'post_parent'    => $post_id,
					'post_type'      => 'course_mode',
					'post_status'    => array( 'publish' ),
					'exact'          => true,
					'posts_per_page' => 1,
					'name'           => get_post_meta( $post_id, 'smile_default_course_mode', true ),
				)
			);

			if ( empty( $default_mode ) ) :
				$default_mode = $this->get_first_published( $post_id );
			endif;
		else :

			$default_mode = $this->get_first_published( $post_id );

		endif;

		return $default_mode;
	}

	/**
	 * If is single and is course/course mode and $course_post is undefined
	 */
	public function course_template_redirect() {
		global $wp_query;

		$options = get_option( 'soc_options' );

		if ( isset( $options['disable_course_redirect'] ) && 'true' === $options['disable_course_redirect'] ) :
			return;
		endif;

		if ( isset( $_GET['preview'] ) || is_preview() ) :
			return;
		endif;

		if ( ! post_type_exists( 'course_mode' ) ) :
			return;
		endif;

		if ( isset( $wp_query->query['s'] ) || $wp_query->is_search ) :
			return;
		endif;

		if ( ! is_admin() && ! $wp_query->is_archive && ! is_404() && isset( $wp_query->query['post_type'] ) && 'course' === $wp_query->query['post_type'] ) :
			$redirect_to = false;
			if ( isset( get_option( 'soc_options' )['enable_default_course_modes'] ) && get_post_meta( get_the_ID(), 'smile_default_course_mode', true ) && ! empty( get_posts(
				array(
					'post_parent'    => get_the_ID(),
					'post_type'      => 'course_mode',
					'post_status'    => 'publish',
					'exact'          => true,
					'posts_per_page' => 1,
					'name'           => get_post_meta( get_the_ID(), 'smile_default_course_mode', true ),
				)
			) ) ) :
				global $post;
				$default_course_mode = get_post_meta( get_the_id(), 'smile_default_course_mode', true );
				$redirect_to = home_url( '/course/' . $post->post_name . '/' . $default_course_mode  );
				if ( isset( $_GET['oncourse_debug'] ) && 'true' === $_GET['oncourse_debug'] ) :
					echo '[course_template_redirect]<pre>';
					var_dump( $redirect_to );
					die;
				endif;
			else :
				$args        = array(
					'post_parent' => $wp_query->post->ID,
					'post_type'   => 'course_mode',
					'numberposts' => 1,
					'post_status' => [ 'publish', 'sbc_publish' ],
				);

				$args = apply_filters( 'smile_oc_filter_course_mode_redirect_args', $args, $wp_query );
				$course_mode = get_children( $args );
				// var_dump( $args );die;
				$course_mode = current( $course_mode );
				if ( $course_mode ) :
					if ( isset( $_GET['oncourse_debug'] ) && 'true' === $_GET['oncourse_debug'] ) :
						echo '[course_template_redirect]<pre>';
						var_dump( $course_mode );
						die;
					endif;
					$redirect_to = get_the_permalink( $course_mode->ID );
				endif;
			endif;
			if ( $redirect_to ) :
				wp_redirect( $redirect_to );
				exit;
			else :
				if ( 'archived' === get_post_status( get_the_ID() ) ) :
					$wp_query->set_404();
					status_header( 404 );
					return;
				endif;
			endif;
		// check if is a 404 error, and it's on the mode custom post type
		elseif ( is_404() && isset( $wp_query->query['post_type'] ) && 'course_mode' === $wp_query->query['post_type'] ) :

			if ( ! isset( $wp_query->query['course_mode'] ) || empty( $wp_query->query['course_mode'] ) || null === $wp_query->query['course_mode'] ) {
				if ( absint( $wp_query->query_vars['post_parent'] ) ) :

					/**
					 * Get a published course_mode with
					 * the same parent & redirect to it.
					 */
					$args = array(
						'post_parent' => $wp_query->query_vars['post_parent'],
						'post_type'   => 'course_mode',
						'post_status' => 'publish',
						'numberposts' => 1,
					);

					// Allow it to be filtered on a theme by theme basis.
					$filtered_args = apply_filters( 'archived_course_mode_redirect', $args );
					$course_modes  = get_posts( $filtered_args );

					if ( $course_modes ) :
						wp_safe_redirect( get_permalink( $course_modes[0]->ID ) );
						exit;
					endif;
				endif;
				if ( isset( $_GET['oncourse_debug'] ) && 'true' === $_GET['oncourse_debug'] ) :
					echo '404';
					die;
				endif;
				$wp_query->set_404();
				status_header( 404 );
				return;
			}
			$args   = array(
				'name'        => $wp_query->query['course_mode'],
				'post_type'   => 'course',
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$course = get_posts( $args );
			$course = current( $course );
			if ( $course ) :
				$args        = array(
					'post_parent' => $course->ID,
					'post_type'   => 'course_mode',
					'numberposts' => 1,
					'post_status' => 'publish',
				);
				$course_mode = get_children( $args );
				$course_mode = current( $course_mode );
				if ( $course_mode ) :
					if ( isset( $_GET['oncourse_debug'] ) && 'true' === $_GET['oncourse_debug'] ) :
						var_dump( $course_mode );
						die;
					endif;
					wp_redirect( get_the_permalink( $course_mode->ID ) );
					exit;
				else :
					if ( isset( $_GET['oncourse_debug'] ) && 'true' === $_GET['oncourse_debug'] ) :
						echo '404';
						die;
					endif;
					$wp_query->set_404();
					status_header( 404 );
				endif;
			endif;
		endif;
	}

	/**
	 * Add course mode to the admin toolbar.
	 */
	public function add_toolbar_items( $admin_bar ) {
		if ( ! post_type_exists( 'course_mode' ) ) {
			return;
		}
		if ( is_singular( 'course_mode' ) && ! is_admin() ) {
			$admin_bar->add_menu(
				array(
					'id'    => 'edit-course-mode',
					'title' => 'Edit Course Mode',
					'href'  => esc_url( home_url( '/wp-admin/post.php?post=' . get_the_ID() . '&action=edit' ) ),
					'meta'  => array(
						'title' => __( 'Edit Course Mode' ),
						'class' => 'edit-course-mode',
					),
				)
			);
		}
	}
}
