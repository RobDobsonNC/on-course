<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       wearesmile.com
 * @since      1.0.0
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smile_Courses
 * @subpackage Smile_Courses/admin
 * @author     We Are SMILE LTD <warren@wearesmile.com>
 */
class Smile_Courses_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smile-courses-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_scripts() {
		$view_type = $_GET['oncourse_type'] ?? '';
		if ( $this->course_modes_enabled() && 'modes_view' === $view_type ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smile-courses-admin.js', array( 'jquery' ), $this->version, false );

			if ( isset( $_GET['course_parent'] ) && $_GET['course_parent'] ) {
	
				$search_url = admin_url( 'edit.php?post_type=course' );
	
				wp_localize_script( $this->plugin_name, 'data_obj', array(
						'parent_url' => esc_url( get_edit_post_link( $_GET['course_parent'] ) ),
						'parent_id' => $_GET['course_parent'],
						'search_url' => esc_url( $search_url ),
					)
				);
			}
		}
	}

	/**
	 * Filter the course mode slug on save.
	 */
	public function edit_course_mode_slug( $post_name, $post ) {

		if ( 'course' === $post->post_type && isset( $_GET['post_parent'] ) && (int) $post->ID === (int) $_GET['post_parent'] ) {

			$post_name = '';
		}

		return $post_name;
	}

	/**
	 * Stop empty post saving.
	 */
	public function prevent_empty_titles( $post_id, $post, $is_update ) {

		if ( 'course_mode' === $post->post_type && $post->post_parent < 1 ) {
			if ( isset( $_GET['post_parent'] ) && is_numeric( $_GET['post_parent'] ) ) {
				$parent_post_type = get_post_type( $_GET['post_parent'] );
				if ( 'course' === $parent_post_type ) {
					$parent = sanitize_text_field( $_GET['post_parent'] );

					$args = array(
						'ID'          => $post->ID,
						'post_parent' => $parent,
					);

					wp_update_post( $args );
				}
			}
		}
		return $post_id;
	}

	/**
	 * Save Post.
	 */

	/**
	 * Filter the course mode slug before.
	 */
	public function pre_filter_slug( $old_status, $new_status, $post ) {

		if ( 'course_mode' === $post->post_type ) {
			if ( $old_status !== $new_status && 'draft' === $old_status ) {

				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$post_slug = sanitize_title_with_dashes( $_POST['post_title'] );
					wp_update_post(
						array(
							'ID'        => $post->ID,
							'post_name' => $post_slug,
						)
					);
				}
			}
		}
	}

	/**
	 * Register the taxonomies.
	 */
	public function register_taxonomies() {

		$taxonomies = array();

		/**
		 * Conditionally register the default taxonomies.
		 */
		$soc_taxonomies = get_option( 'soc_options' );
		if ( isset( $soc_taxonomies['default_taxonomies'] ) && ! empty( $soc_taxonomies['default_taxonomies'] ) && is_array( $soc_taxonomies['default_taxonomies'] ) ) {
			$soc_taxonomies = $soc_taxonomies['default_taxonomies'];
		
			if ( in_array( 'subject_area', $soc_taxonomies ) ) {
				$taxonomies[] = array(
					'the_tax'   => 'subject_area',
					'single'    => 'Subject Area',
					'plural'    => 'Subject Areas',
					'post_type' => array( 'course' ),
					'args'      => array(
						'rewrite'      => array(
							'slug'       => 'subject-area',
							'with_front' => false,
						),
						'capabilities' => array(
							'manage_terms' => 'manage_terms_subject_area',
							'edit_terms'   => 'edit_terms_subject_area',
							'delete_terms' => 'delete_terms_subject_area',
							'assign_terms' => 'assign_terms_subject_area',
						),
					),
				);
			}
			
			if ( isset( $soc_taxonomies ) && is_array( $soc_taxonomies ) && in_array( 'course_type', $soc_taxonomies ) ) {
				$taxonomies[] = array(
					'the_tax'   => 'course_type',
					'single'    => 'Course Type',
					'plural'    => 'Course Types',
					'post_type' => array( 'course_mode', 'course' ), // View in menu
					'args'      => array(
						'capabilities' => array(
							'manage_terms' => 'manage_terms_course_type',
							'edit_terms'   => 'edit_terms_course_type',
							'delete_terms' => 'delete_terms_course_type',
							'assign_terms' => 'assign_terms_course_type',
						),
					),
				);
			}

			if ( isset( $soc_taxonomies ) && is_array( $soc_taxonomies ) && in_array( 'course_award', $soc_taxonomies ) ) {
				$taxonomies[] = array(
					'the_tax'   => 'course_award',
					'single'    => 'Award',
					'plural'    => 'Awards',
					'post_type' => array( 'course_mode', 'course' ), // View in menu
					'args'      => array(
						'rewrite'      => array(
							'slug'       => 'study-level',
							'with_front' => false,
						),
						'capabilities' => array(
							'manage_terms' => 'manage_terms_course_award',
							'edit_terms'   => 'edit_terms_course_award',
							'delete_terms' => 'delete_terms_course_award',
							'assign_terms' => 'assign_terms_course_award',
						),
					),
				);
			}
		}

		$taxonomies = apply_filters( 'oncourse_taxonomy_args', $taxonomies );

		foreach ( $taxonomies as $taxonomy ) {
			$the_tax   = $taxonomy['the_tax'];
			$single    = $taxonomy['single'];
			$plural    = $taxonomy['plural'];
			$post_type = $taxonomy['post_type'];
			$labels    = array(
				'name'                  => _x( $plural, 'Taxonomy general name' ),
				'singular_name'         => _x( $single, 'Taxonomy singular name' ),
				'search_items'          => __( 'Search ' . $plural ),
				'popular_items'         => __( 'Popular ' . $plural ),
				'all_items'             => __( 'All ' . $plural ),
				'parent_item'           => __( 'Parent ' . $single ),
				'parent_item_colon'     => __( 'Parent ' . $single ),
				'edit_item'             => __( 'Edit ' . $single ),
				'update_item'           => __( 'Update ' . $single ),
				'add_new_item'          => __( 'Add New ' . $single ),
				'new_item_name'         => __( 'New ' . $single ),
				'add_or_remove_items'   => __( 'Add or remove ' . $plural ),
				'choose_from_most_used' => __( 'Choose from most used ' . $plural ),
				'menu_name'             => __( $plural ),
			);
			$args      = array(
				'labels'            => $labels,
				'public'            => true,
				'show_in_nav_menus' => true,
				'hierarchical'      => true,
				'show_tagcloud'     => true,
				'show_ui'           => true,
				'rewrite'           => true,
				'query_var'         => true,
				'show_admin_column' => true,
			);
			if ( ! empty( $taxonomy['args'] ) ) :
				foreach ( $taxonomy['args'] as $arg => $value ) :
					$args[ $arg ] = $value;
				endforeach;
			endif;
			register_taxonomy( $the_tax, $post_type, $args );
		}
		/**
		 * Setup default course types for taxonomy.
		 */
		$term = wp_insert_term( 'Full Time', 'course_type', array( 'slug' => 'full-time' ) );
		$term = wp_insert_term( 'Part Time', 'course_type', array( 'slug' => 'part-time' ) );

		$term = wp_insert_term( 'Undergraduate', 'course_award', array( 'slug' => 'undergraduate' ) );
		$term = wp_insert_term( 'Postgraduate', 'course_award', array( 'slug' => 'postgraduate' ) );
	}

	/**
	 * Add course and course mode rewrite rules.
	 */
	public function add_rewrite_rules() {
		if ( post_type_exists( 'course_mode' ) ) {
			add_rewrite_tag( '%_course%', '([^&]+)' );
	// add_permastruct( 'course_mode', '/course/%course%/%mode%', [
	// 'slug'          => 'course',
	// 'with_front'    => false,
	// 'pages'         => false,
	// 'feeds'         => false,
	// 'ep_mask'       => 0,
	// 'feed'          => 0
	// ] );
			add_rewrite_rule(
				'course/([^/]*)/([^/]*)/?',
				'index.php?post_type=course_mode&_course=$matches[1]&name=$matches[2]',
				'top'
			);
		}
		add_rewrite_rule(
			'course/([^/]*)/?',
			'index.php?post_type=course&name=$matches[1]',
			'top'
		);
	}

	/**
	 * Add course mode permalink.
	 *
	 * @param string $permalink The permalink to modify.
	 * @param object $post The post.
	 */
	public function soc_permalinks( $permalink, $post ) {
		if ( post_type_exists( 'course_mode' ) ) {
			if ( 'course_mode' === get_post_type( $post ) ) {
	// $course = get_post( $post->post_parent );
	// if ( $course ) :
	// $permalink = str_replace( '%mode%', $course->post_name, $permalink );

					$permalink = str_replace( 'course_mode', 'course', $permalink );
	// endif;
			}
		}
		return $permalink;
	}

	/**
	 * Hightlight admin menu item for courses, on course mode edit page.
	 */
	public function menu_highlight() {
		if ( post_type_exists( 'course_mode' ) ) {
			global $parent_file, $submenu_file, $post_type;
			if ( 'course_mode' === $post_type ) :
				$parent_file  = 'edit.php?post_type=course';
				$submenu_file = 'edit.php?post_type=course';
			endif;
		}
	}

	/**
	 * Update the post parent when saved.
	 */
	public function oncourse_parent_mode_save_post( $data, $post_arr ) {

		if ( ! $this->course_modes_enabled() ) {
			return;
		}

		if ( isset( $_GET['post_parent'] ) && $_GET['post_parent'] ) {
			$data['post_parent'] = $_GET['post_parent'];
		}

		return $data;
	}

	/**
	 * Rename "Course Modes" to course parent
	 */
	public function change_post_object_label() {

		global $wp_post_types;

		if ( ! $this->course_modes_enabled() ) {
			return;
		}

		$parent_id = $_GET['course_parent'] ?? '';
		$view_type = $_GET['oncourse_type'] ?? '';
		$parent = get_post( $parent_id );

		if ( isset( $wp_post_types['course_mode'] ) && $parent && 'modes_view' === $view_type ) :
			$labels                     = $wp_post_types['course_mode']->labels;
			$labels->name               = $parent->post_title;
		endif;
	}

	public function course_modes_enabled() {
		$options = get_option( 'soc_options' );

		$enabled = apply_filters( 'enable_course_modes_search', true );

		if ( ! $enabled ) {
			return false;
		}

		// Only register course mode as a posttype if enabled in the settings.
		if ( isset( $options['enable_course_modes'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Remove the view action for course parents
	 */
	public function course_remove_row_actions( $actions ) {
		if ( get_post_type() === 'course' && $this->course_modes_enabled() ) {
			unset( $actions['edit'] );
			
			$children = get_posts(
				array(
					'post_parent' => get_the_ID(),
					'post_status' => get_post_stati(),
					'post_type'   => 'course_mode',
				)
			 );
			$count = count( $children );
			$admin_url = admin_url( 'edit.php?post_type=course_mode&course_parent=' . get_the_ID() . '&oncourse_type=modes_view' );
			$actions = array_merge( array(
				'course_modes' => sprintf( '<a href="%s">%s (%s)</a>',
					esc_url( $admin_url ), 
					'Course Modes',
					(string) $count
				)
			), $actions );
		}

		return $actions;
	}

	/**
	 * Show child course modes on the course admin listing.
	 *
	 * @param array $query The query array.
	 */
	public function admin_post_mode_query( $query ) {
 
		if ( ! $this->course_modes_enabled() ) {
			return;
		}

		$cpt = $_GET['post_type'] ?? '';
		$parent_id = $_GET['course_parent'] ?? '';
		$view_type = $_GET['oncourse_type'] ?? '';

		if ( 'course_mode' === $cpt && $parent_id && 'modes_view' === $view_type ) {
			$query->set( 'post_parent', $parent_id );
		}

		return $query;
	}

	/**
	 * Show child course modes on the course admin listing.
	 *
	 * @param array $query The query array.
	 */
	public function admin_post_query( $query ) {
		if ( post_type_exists( 'course_mode' ) ) {
			if ( ! is_admin() ) { // Do nothing if not is admin.
				return $query;
			}
			if ( ! function_exists( 'get_current_screen' ) ) {
				return $query;
			}
			$scr = get_current_screen();
			if ( empty( $scr ) || ! ( 'edit' === $scr->base && 'course' === $scr->post_type ) ) { // If in the right admin screen.
				return $query;
			}
			$query->set( 'post_type', array( 'course', 'course_mode' ) ); // Force query to get both 'request' and 'quote' cpt.
			add_filter(
				 'edit_posts_per_page', function( $perpage ) {
				global $post_type;
				if ( is_array( $post_type ) && in_array( 'course', $post_type ) ) { // Force global $post_type to be = 'course' if is an array.
						$post_type = 'course';
				}
				return $perpage; // We don't want to affect $perpage value.
			}
				);
		}
		return $query;
	}
	/**
	 * Create metabox on course and course mode.
	 */
	public function add_meta_boxes() {
		global $post;
		if ( post_type_exists( 'course_mode' ) ) {

			add_meta_box( 'mode-child', 'Course', array( $this, 'mode_attributes_meta_box' ), 'course_mode', 'side', 'high' );

			if ( isset( $post ) && 'auto-draft' !== $post->post_status ) :
				add_meta_box( 'mode-parent', 'Course Modes', array( $this, 'course_attributes_meta_box' ), 'course', 'side', 'high' );
			endif;
		}
	}

	/**
	 * Add course posts to meta box of courses.
	 *
	 * @param object $post The post.
	 */
	public function course_attributes_meta_box( $post ) {
		$post_id = $post->ID;
		$args    = array(
			'post_parent'    => $post->ID,
			'posts_per_page' => -1,
			'post_type'      => 'course_mode',
			'post_status'    => array( 'any', 'archived' ),
		);

		$the_query = new WP_Query( $args );
		// The Loop
		if ( $the_query->have_posts() ) :
		?>
		<table class="wp-list-table widefat fixed striped pages meta-inline-table">
			<tbody id="the-list">
				<?php
				while ( $the_query->have_posts() ) :
$the_query->the_post();
?>
					<tr>
						<td>
							<strong>
								<a href="
								<?php
											echo esc_url( get_edit_post_link() );
										 ?>
										 " class="row-title" aria-label="Edit “<?php the_title(); ?>”"><?php the_title(); ?></a>
							</strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( get_edit_post_link() ); ?>" aria-label="Edit “<?php the_title(); ?>”">Edit</a> | </span>
								<span class="trash"><a href="<?php echo esc_url( get_delete_post_link() ); ?>" class="submitdelete" aria-label="Move “<?php the_title(); ?>” to the Bin">Bin</a> | </span>
								<span class="view"><a href="<?php the_permalink(); ?>" rel="bookmark" aria-label="View “<?php the_title(); ?>”">View</a></span>
							</div>
						</td>
					</tr>
				<?php
					endwhile;
					wp_reset_query();
					wp_reset_postdata();
				?>
			</tbody>
		</table>
		<?php else : ?>
			<p><?php _e( 'There have been no course modes created for this course.' ); ?></p>
		<?php endif; ?>
		<footer class="meta-footer-area">
			<a href="
			<?php
					echo esc_url(
						add_query_arg(
							array(
								'post_type'   => 'course_mode',
								'post_parent' => $post->ID,
							),
							admin_url( 'post-new.php' )
						)
					);
					?>
					" class="button-primary">Add New Course Mode</a>
		</footer>
		<?php
		$args = array(
			'p'         => $post_id,
			'post_type' => 'course',
		);

		$main_query = new WP_Query( $args );
		// The Loop
		if ( $main_query->have_posts() ) :
			while ( $main_query->have_posts() ) :
$main_query->the_post();
			endwhile;
		endif;
		wp_reset_query();
		wp_reset_postdata();
	}

	/**
	 * Add course posts to meta box of course mode.
	 *
	 * @param object $post The post.
	 */
	public function mode_attributes_meta_box( $post ) {
		if ( post_type_exists( 'course_mode' ) ) {
			global $pagenow;
			$post_id = $post->ID;
			if ( in_array( $pagenow, array( 'post-new.php' ) ) && empty( $_GET['post_parent'] ) ) :
				echo '<p>Select the course this course mode is for.</p>';
				wp_dropdown_pages(
					array(
						'post_type'        => 'course',
						'depth'            => 1,
						'name'             => 'parent_id',
						'show_option_none' => false,
						'sort_column'      => 'menu_order, post_title',
						'echo'             => 1,
					)
				);
			elseif ( in_array( $pagenow, array( 'post.php' ) ) || ( in_array( $pagenow, array( 'post-new.php' ) ) && isset( $_GET['post_parent'] ) ) ) :
				if ( isset( $_GET['post_parent'] ) ) :
					$post_parent_id = $_GET['post_parent'];
				else :
					$post_parent_id = $post->post_parent;
				endif;
				$args = array(
					'p'         => $post_parent_id,
					'post_type' => 'course',
					'post_status' => array( 'any', 'archived' ),
				);

				$copy_course_data = apply_filters( 'soc_copy_parent_course_data', true );

				$parent_query = $copy_course_data ? new WP_Query( $args ) : get_posts( $args );
				$parent_id    = false;
				// The Loop
				if ( $parent_query ) :
				?>
				<table class="wp-list-table widefat fixed striped pages meta-inline-table
				<?php
				if ( $post_parent_id ) :
						echo ' no_footer';
	endif;
	?>
	">
					<tbody id="the-list">
						<?php

						if ( $copy_course_data ) :
							if ( $parent_query->have_posts() ) :
								while ( $parent_query->have_posts() ) :
									$parent_query->the_post();

									$parent_id = get_the_ID();
									$this->output_metabox_markup( $parent_id );
								endwhile;
							endif;
						else :
							foreach ( $parent_query as $item ) :
								$parent_id = $item->ID;
								$this->output_metabox_markup( $parent_id );
							endforeach;
						endif;

						wp_reset_query();
						wp_reset_postdata();
						?>
					</tbody>
				</table>
				<?php endif; ?>
				<?php if ( $parent_id ) : ?>
					<footer class="meta-footer-area">
						<a href="
						<?php
								echo esc_url(
									add_query_arg(
										array(
											'post_type'   => 'course_mode',
											'post_parent' => $parent_id,
										),
										admin_url( 'post-new.php' )
									)
								);
								?>
								" class="button-primary">Add New Course Mode</a>
					</footer>
				<?php
				endif;
				$args = array(
					'p'         => $post_id,
					'post_type' => 'course_mode',
				);

				$main_query = new WP_Query( $args );
				// The Loop
				if ( $main_query->have_posts() ) :
					while ( $main_query->have_posts() ) :
	$main_query->the_post();
					endwhile;
				endif;
				wp_reset_query();
				wp_reset_postdata();
			endif;
		}
	}

	/**
	 * Output markup for list of courses.
	 */
	public function output_metabox_markup( $the_id ) {
		?>
		<tr>
			<td>
				<strong>
					<a href="
					<?php
								echo esc_url( get_edit_post_link( $the_id ) );
								?>
								" class="row-title" aria-label="Edit “<?php echo esc_attr( get_the_title( $the_id ) ); ?>”"><?php echo esc_html( get_the_title( $the_id ) ); ?></a>
				</strong>
				<div class="row-actions">
					<span class="edit"><a href="<?php echo esc_url( get_edit_post_link( $the_id ) ); ?>" aria-label="Edit “<?php echo esc_attr( get_the_title( $the_id ) ); ?>”">Edit</a> | </span>
					<span class="trash"><a href="<?php echo esc_url( get_delete_post_link( $the_id ) ); ?>" class="submitdelete" aria-label="Move “<?php echo esc_attr( get_the_title( $the_id ) ); ?>” to the Bin">Bin</a> | </span>
					<span class="view"><a href="<?php echo esc_url( get_the_permalink( $the_id ) ); ?>" rel="bookmark" aria-label="View “<?php echo esc_attr( get_the_title( $the_id ) ); ?>”">View</a></span>
				</div>
				<input type="hidden" name="parent_id" value="<?php echo esc_attr( $the_id ); ?>">
			</td>
		</tr>
		<?php
	}

	/**
	 * Use radio inputs instead of checkboxes for term checklists in specified taxonomies.
	 */
	public function course_type_remove_meta_box() {
		if ( post_type_exists( 'course_mode' ) && taxonomy_exists( 'course_type' ) ) {
			remove_meta_box( 'course_typediv', 'course_mode', 'side' );
		}
	}

	/**
	 * Add new taxonomy meta box to course mode edit page.
	 */
	public function course_type_add_meta_box() {
		if ( post_type_exists( 'course_mode' ) && taxonomy_exists( 'course_type' ) ) {
			add_meta_box( 'course_type_radio', 'Course Type', array( $this, 'course_type_metabox' ), 'course_mode', 'side', 'core' );
		}
	}

	/**
	 * Populate new taxonomy meta box to course mode edit page.
	 */
	public function course_type_metabox( $post ) {
		if ( ! post_type_exists( 'course_mode' ) ) {
			return;
		}
		// Get taxonomy and terms
		$taxonomy = 'course_type';

		// Set up the taxonomy object and get terms
		$tax   = get_taxonomy( $taxonomy );
		$terms = get_terms( $taxonomy, array( 'hide_empty' => 0 ) );

		// Name of the form
		$name = 'tax_input[' . $taxonomy . ']';

		// Get current and popular terms
		$popular   = get_terms(
			 $taxonomy, array(
				 'orderby'      => 'count',
				 'order'        => 'DESC',
				 'number'       => 10,
				 'hierarchical' => false,
			 )
			);
		$postterms = get_the_terms( $post->ID, $taxonomy );
		$current   = ( $postterms ? array_pop( $postterms ) : false );
		$current   = ( $current ? $current->term_id : 0 );
		?>

		<div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">

			<!-- Display taxonomy terms -->
			<ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy; ?> categorychecklist form-no-clear">
				<?php
				foreach ( $terms as $term ) :
					$id = $taxonomy . '-' . $term->term_id;
					echo "<li id='$id'><label class='selectit'>"
						. "<input type='radio' id='in-$id' name='{$name}[]'"
						. checked( $current, $term->term_id, false )
						. "value='$term->term_id' />$term->name<br />"
						. '</label></li>';
				endforeach;
				?>
		   </ul>

		</div>
		<?php
	}

	/**
	 * Include bartender post type registration in OnCourse
	 */
	public function sp_register_post_types() {
		if ( function_exists( 'sb_register_post_types' ) ) :

			// Register the course post type.
			if ( file_exists( get_stylesheet_directory() . '/bartender-json/on-course/course.json' ) ) :

				// Check the child theme.
				sb_register_post_types( get_stylesheet_directory() . '/bartender-json/on-course/course.json' );

			elseif ( file_exists( get_template_directory() . '/bartender-json/on-course/course.json' ) ) :

				// Check the parent theme.
				sb_register_post_types( get_template_directory() . '/bartender-json/on-course/course.json' );

			else :

				// Pull from the plugin.
				sb_register_post_types( realpath( __DIR__ . '/..' ) . '/bartender-json/post-types.json' );
			endif;

			$options = get_option( 'soc_options' );

			// Only register course mode as a posttype if enabled in the settings.
			if ( isset( $options['enable_course_modes'] ) ) {

				// Register the course mode post type.
				if ( file_exists( get_stylesheet_directory() . '/bartender-json/on-course/course-modes.json' ) ) :

					// Check the child theme.
					sb_register_post_types( get_stylesheet_directory() . '/bartender-json/on-course/course-modes.json' );

				elseif ( file_exists( get_template_directory() . '/bartender-json/on-course/course-modes.json' ) ) :

					// Check the parent theme.
					sb_register_post_types( get_template_directory() . '/bartender-json/on-course/course-modes.json' );

				else :
					// Pull from the plugin.
					sb_register_post_types( realpath( __DIR__ . '/..' ) . '/bartender-json/course-mode-post-type.json' );
				endif;
			}

		endif;
	}

	/**
	 * Fake remove title cpt support.
	 */
	public function switch_off_cpt_support( $screen ) {

		if ( is_admin() ) {
			$options = get_option( 'soc_options' );

			if ( is_array( $options ) && isset( $options['default_course_supports'] ) &&  ! in_array( 'title', $options['default_course_supports'], true ) ) {

				if ( 'post' === $screen->base && 'course' === $screen->post_type ) {
					remove_post_type_support( 'course', 'title' );
				}
			}

			if ( is_array( $options ) && isset( $options['default_course_supports'] )  && ! in_array( 'title', $options['default_course_mode_supports'], true ) ) {

				if ( 'course_mode' === $screen->base && 'course_mode' === $screen->post_type ) {
					remove_post_type_support( 'course_mode', 'title' );
				}
			}
		}
	}

	/**
	 * Override the post type supports via the backend settings.
	 */
	public function soc_alter_post_type_supports( $args, $post_type ) {
		$soc_course_supports = get_option( 'soc_options' );

	    switch ( $post_type ) {
	    	case 'course':
	    		if ( isset( $soc_course_supports['default_course_supports'] ) ) {
					$args['supports'] = array_merge( $soc_course_supports['default_course_supports'], array( 'title' ) );
	    		} else {
	    			$args['supports'] = array('');
				}

	    		break;
	    	
	    	case 'course_mode':
	    		if ( isset( $soc_course_supports['default_course_mode_supports'] ) ) {
					$args['supports'] = array_merge( $soc_course_supports['default_course_mode_supports'], array( 'title' ) );
	    		} else {
	    			$args['supports'] = array('');
	    		}
	     		break;
	    }
	    return $args;
	}

	/**
	 * Initialise the options page.
	 */
	public function add_settings_menu() {
		// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function )
		add_options_page( 'OnCourse', 'OnCourse', 'manage_options', 'oncourse', array( $this, 'init_oncourse_options_page' ) );
	}

	/**
	 * Output the page markup.
	 */
	public function init_oncourse_options_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Smile Courses' ); ?></h2>

		<form method="post" action="options.php">
		<?php
			settings_fields( 'soc-general-settings-group' );
			do_settings_sections( 'soc_general_settings_section' );
			submit_button( 'Save Settings', 'secondary', 'soc_save_general_settings_btn' );
			?>
		</form>

		<?php do_action( 'soc_additional_settings_section' ); ?>
	</div>
	<?php
	}

	/**
	 * Register the archived post status.
	 */
	public function register_post_status() {
		register_post_status(
			'archived',
			array(
				'label'                     => _x( 'Archived', 'course' ),
				'public'                    => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'exclude_from_search'       => true,
				'publicly_queryable'        => false,
				'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>' ),
			)
		);
	}

	/**
	 * Add to post status select on the
	 * single post page for courses & course_modes.
	 */
	public function append_post_status_list() {

		global $post;
		$complete = '';
		$label    = '';
		$post_id  = sanitize_text_field( $_GET['post'] );

		if ( absint( $post_id ) ) :
			$current_post = get_post( $post_id );
			if ( 'course' === $current_post->post_type || 'course_mode' === $current_post->post_type ) :
				if ( 'archived' === $current_post->post_status ) :
					$complete = ' selected';
					printf(
						'<script>
							let label = document.getElementById( "post-status-display" );
							label.innerHTML = "Archived";
						</script>'
					);
				endif;

				printf(
					'<script>
						let dropdown = document.getElementById( "post_status" );
						if ( ! dropdown.querySelector( "option[value=archived]" ) ) {
							dropdown.insertAdjacentHTML( "beforeend", "<option value=\"archived\" ' . esc_attr( $complete ) . '>Archived</option>" );
						}
					</script>'
				);

			endif;
		endif;
	}

	/**
	 * Output an un-ediable course title.
	 */
	public function output_plain_text_title( $post ) {

		if ( in_array( get_post_type(), array( 'course', 'course_mode' ), true ) ) {
			global $post_type_object;
			$viewable = is_post_type_viewable( $post->post_type );
			if ( ! post_type_supports( $post->post_type, 'title' ) ) {
		?>

			<div id="titlediv">
				<div id="titlewrap">
					<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $title_placeholder; ?></label>
					<input type="text" size="30" value="<?php echo esc_attr( $post->post_title ); ?>" id="title" spellcheck="true" autocomplete="off" disabled />
				</div>
				<div class="inside">
				<?php
				if ( $viewable ) :
					$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html( $post->ID ) : '';

					// As of 4.4, the Get Shortlink button is hidden by default.
					if ( has_filter( 'pre_get_shortlink' ) || has_filter( 'get_shortlink' ) ) {
						$shortlink = wp_get_shortlink( $post->ID, 'post' );

						if ( ! empty( $shortlink ) && $shortlink !== $permalink && home_url( '?page_id=' . $post->ID ) !== $permalink ) {
							$sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr( $shortlink ) . '" />' .
								'<button type="button" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val());">' .
								__( 'Get Shortlink' ) .
								'</button>';
						}
					}

					if ( $post_type_object->public
						&& ! ( 'pending' === get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) )
					) {
						$has_sample_permalink = $sample_permalink_html && 'auto-draft' !== $post->post_status;
					?>
						<div id="edit-slug-box" class="hide-if-no-js">
								<?php
								if ( $has_sample_permalink ) {
									echo $sample_permalink_html;
								}
								?>
						</div>
					<?php
					}
				endif;
				?>
				</div>
				<?php
				wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
				?>
			</div>
		<?php
			}
		}
	}

	/**
	 * If the user has updated a course post status to archived,
	 * then archive the course_modes as well.
	 */
	public function set_course_modes_to_archived( $post_ID, $post_after, $post_before ) {

		if ( 'course' === $post_after->post_type ) :

			if ( 'archived' === $post_after->post_status ) :

				// Query the course_mode children.
				$args = array(
					'post_type'      => 'course_mode',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'post_parent'    => $post_after->ID,
				);

				$course_mode_children = get_posts( $args );

				// Loop the children and update the status to archived.
				foreach ( $course_mode_children as $key => $course_mode ) {
					wp_update_post(
						array(
							'ID'          => $course_mode->ID,
							'post_status' => 'archived',
						)
					);
				}

			endif;
		endif;

		return $post_ID;
	}

	/**
	 * Add post status to the dropdown on the
	 * course & course mode backend archive.
	 */
	public function add_archived_status_to_quick_edit() {

		printf(
			'<script>
				let dropdown = document.querySelector( "select[name=\"_status\"]" );
				dropdown.insertAdjacentHTML( "beforeend", "<option value=\"archived\">Archived</option>" );
			</script>'
		);
	}

	/**
	 * Register all of the settings to the page.
	 */
	public function register_settings() {

		// add_settings_section( $id, $title, $callback, $page )
		add_settings_section(
			'soc_general_settings_section',
			'General Settings',
			array( $this, 'print_general_settings_section_info' ),
			'soc_general_settings_section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'oncourse_enable_course_modes',
			'Enable Course Modes',
			array( $this, 'soc_enable_course_modes_field' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'oncourse_enable_default_course_modes',
			'Enable Default Course Modes',
			array( $this, 'soc_enable_default_course_modes_field' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'oncourse_toggle_default_taxonomies',
			'Set Default Course / Course Mode Taxonomies',
			array( $this, 'soc_toggle_default_taxonomies' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'oncourse_toggle_course_supports',
			'Set Course Supports',
			array( $this, 'soc_toggle_course_supports' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'oncourse_toggle_course_mode_supports',
			'Set Course Mode Supports',
			array( $this, 'soc_toggle_course_mode_supports' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);


		// register_setting( $option_group, $option_name, $sanitize_callback )
		register_setting( 'soc-general-settings-group', 'soc_options', '' );

		// add_settings_section( $id, $title, $callback, $page )
		add_settings_section(
			'scc_sync_courses_section',
			'Sync Courses',
			array( $this, 'print_additional_settings_section_info' ),
			'scc_sync_section'
		);

		add_settings_field(
			'soc_disable_course_redirect',
			'Disable course redirect',
			array( $this, 'soc_disable_course_redirect' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'soc_style_toggle',
			'Disable Plugin Styles',
			array( $this, 'soc_style_toggle' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'soc_js_toggle',
			'Disable Plugin Js',
			array( $this, 'soc_js_toggle' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);

		add_settings_field(
			'course_mode_search',
			'Course Mode Search',
			array( $this, 'course_mode_search' ),
			'soc_general_settings_section',
			'soc_general_settings_section'
		);
	}

	/**
	 * Description for the general settings section.
	 */
	public function print_general_settings_section_info() {
		$main_settings_description = apply_filters( 'soc_general_settings_description', '' );
		_e( $main_settings_description, 'soc' );
	}

	/**
	 * Display the api url field.
	 */
	public function soc_enable_course_modes_field() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[enable_course_modes]" <?php checked( isset( $options['enable_course_modes'] ) ); ?> value="true" />
		<?php
	}

	/**
	 * Display the api url field.
	 */
	public function soc_enable_default_course_modes_field() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[enable_default_course_modes]" <?php checked( isset( $options['enable_default_course_modes'] ) ); ?> value="true" />
		<?php
	}

	/**
	 * Toggle the style enqueue
	 */
	public function soc_style_toggle() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[styles_disabled]" <?php checked( isset( $options['styles_disabled'] ) ); ?> value="true" />
		<?php
	}

	/**
	 * Toggle the style enqueue
	 */
	public function soc_disable_course_redirect() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[disable_course_redirect]" <?php checked( isset( $options['disable_course_redirect'] ) ); ?> value="true" />
		<?php
	}

	/**
	 * Toggle the js enqueue
	 */
	public function soc_js_toggle() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[js_disabled]" <?php checked( isset( $options['js_disabled'] ) ); ?> value="true" />
		<?php
	}

	/**
	 * Toggle the js enqueue
	 */
	public function course_mode_search() {
		$options = get_option( 'soc_options' );
		?>
		<input type="checkbox" name="soc_options[course_mode_search]" <?php checked( isset( $options['course_mode_search'] ) ); ?> value="true" />
		<?php
	}

	public function soc_toggle_default_taxonomies() {
		$soc_taxonomies = get_option( 'soc_options' );
		if ( isset( $soc_taxonomies['default_taxonomies'] ) && is_array( $soc_taxonomies['default_taxonomies'] ) ) {
			$soc_taxonomies = $soc_taxonomies['default_taxonomies'];
			$subject_area = in_array( 'subject_area', $soc_taxonomies )  ? 'checked' : '';
			$course_type = in_array( 'course_type', $soc_taxonomies ) ? 'checked' : '';
			$course_award = in_array( 'course_award', $soc_taxonomies ) ? 'checked' : '';
		} else {
			$subject_area = '';
			$course_type = '';
			$course_award = '';
		}
		

		echo '<ul><li><label for="soc_options_default_taxonomies_subject_area">'
			.'<input ' . $subject_area . ' type="checkbox" name="soc_options[default_taxonomies][]" id="soc_options_default_taxonomies_subject_area" value="subject_area" />Subject Areas</label></li>'

			.'<li><label for="soc_options_default_taxonomies_course_type">'
			.'<input ' . $course_type . ' type="checkbox" value="course_type" name="soc_options[default_taxonomies][]" id="soc_options_default_taxonomies_course_type" />Course Types</label></li>'

			.'<li><label for="soc_options_default_taxonomies_course_award">'
			.'<input ' . $course_award . ' type="checkbox" value="course_award" name="soc_options[default_taxonomies][]"  id="soc_options_default_taxonomies_course_award" />Course Awards</label></li></ul>';
	}

	public function soc_toggle_course_supports() {

		$soc_course_supports = get_option( 'soc_options' );
		if ( isset( $soc_course_supports['default_course_supports'] ) && 
			 is_array( $soc_course_supports['default_course_supports'] ) ) {

			$soc_course_supports = $soc_course_supports['default_course_supports'];
			$title = in_array( 'title', $soc_course_supports )  ? 'checked' : '';
			$editor = in_array( 'editor', $soc_course_supports )  ? 'checked' : '';
			$author = in_array( 'author', $soc_course_supports ) ? 'checked' : '';
			$thumbnail = in_array( 'thumbnail', $soc_course_supports ) ? 'checked' : '';
			$excerpt = in_array( 'excerpt', $soc_course_supports ) ? 'checked' : '';
			$trackbacks = in_array( 'trackbacks', $soc_course_supports ) ? 'checked' : '';
			$custom_fields = in_array( 'custom-fields', $soc_course_supports ) ? 'checked' : '';
			$comments = in_array( 'comments', $soc_course_supports ) ? 'checked' : '';
			$revisions = in_array( 'revisions', $soc_course_supports ) ? 'checked' : '';
			$page_attributes = in_array( 'page-attributes', $soc_course_supports ) ? 'checked' : '';
			$post_formats = in_array( 'post-formats', $soc_course_supports ) ? 'checked' : '';

		} else {

			$title           = '';
			$editor          = '';
			$author          = '';
			$thumbnail       = '';
			$excerpt         = '';
			$trackbacks      = '';
			$custom_fields   = '';
			$comments        = '';
			$revisions       = '';
			$page_attributes = '';
			$post_formats    = '';

		}

		echo '<ul><li><label for="soc_options_default_course_supports_title">'
			.'<input ' . $title . ' type="checkbox" name="soc_options[default_course_supports][]" id="soc_options_default_course_supports_title" value="title" />Title</label></li>'

			.'<li><label for="soc_options_default_course_supports_editor">'
			.'<input ' . $editor . ' type="checkbox" value="editor" name="soc_options[default_course_supports][]" id="soc_options_default_course_supports_editor" />Wysiwyg Editor</label></li>'

			.'<li><label for="soc_options_default_course_supports_author">'
			.'<input ' . $author . ' type="checkbox" value="author" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_author" />Author</label></li>'

			.'<li><label for="soc_options_default_course_supports_thumbnail">'
			.'<input ' . $thumbnail . ' type="checkbox" value="thumbnail" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_thumbnail" />Thumbnail</label></li>'

			.'<li><label for="soc_options_default_course_excerpt">'
			.'<input ' . $excerpt . ' type="checkbox" value="excerpt" name="soc_options[default_course_supports][]"  id="soc_options_default_course_excerpt" />Excerpt</label></li>'

			.'<li><label for="soc_options_default_course_trackbacks">'
			.'<input ' . $trackbacks . ' type="checkbox" value="trackbacks" name="soc_options[default_course_supports][]"  id="soc_options_default_course_trackbacks" />Trackbacks</label></li>'
			
			.'<li><label for="soc_options_default_course_supports_custom_fields">'
			.'<input ' . $custom_fields . ' type="checkbox" value="custom-fields" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_custom_fields" />Custom Fields</label></li>'

			.'<li><label for="soc_options_default_course_supports_comments">'
			.'<input ' . $comments . ' type="checkbox" value="comments" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_comments" />Comments</label></li>'

			.'<li><label for="soc_options_default_course_supports_revisions">'
			.'<input ' . $revisions . ' type="checkbox" value="revisions" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_revisions" />Revisions</label></li>'

			.'<li><label for="soc_options_default_course_supports_page_attributes">'
			.'<input ' . $page_attributes . ' type="checkbox" value="page-attributes" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_page_attributes" />Page Attributes</label></li>'

			.'<li><label for="soc_options_default_course_supports_post_formats">'
			.'<input ' . $post_formats . ' type="checkbox" value="post-formats" name="soc_options[default_course_supports][]"  id="soc_options_default_course_supports_post_formats" />Post Formats</label></li></ul>';		
	}

	public function soc_toggle_course_mode_supports() {

		$soc_course_mode_supports = get_option( 'soc_options' );
		if ( isset( $soc_course_mode_supports['default_course_mode_supports'] ) && 
			 is_array( $soc_course_mode_supports['default_course_mode_supports'] ) ) {

			$soc_course_mode_supports = $soc_course_mode_supports['default_course_mode_supports'];
			$title = in_array( 'title', $soc_course_mode_supports )  ? 'checked' : '';
			$editor = in_array( 'editor', $soc_course_mode_supports )  ? 'checked' : '';
			$author = in_array( 'author', $soc_course_mode_supports ) ? 'checked' : '';
			$thumbnail = in_array( 'thumbnail', $soc_course_mode_supports ) ? 'checked' : '';
			$excerpt = in_array( 'excerpt', $soc_course_mode_supports ) ? 'checked' : '';
			$trackbacks = in_array( 'trackbacks', $soc_course_mode_supports ) ? 'checked' : '';
			$custom_fields = in_array( 'custom_fields', $soc_course_mode_supports ) ? 'checked' : '';
			$comments = in_array( 'comments', $soc_course_mode_supports ) ? 'checked' : '';
			$revisions = in_array( 'revisions', $soc_course_mode_supports ) ? 'checked' : '';
			$page_attributes = in_array( 'page_attributes', $soc_course_mode_supports ) ? 'checked' : '';
			$post_formats = in_array( 'post_formats', $soc_course_mode_supports ) ? 'checked' : '';

		} else {
			
			$title           = '';
			$editor          = '';
			$author          = '';
			$thumbnail       = '';
			$excerpt         = '';
			$trackbacks      = '';
			$custom_fields   = '';
			$comments        = '';
			$revisions       = '';
			$page_attributes = '';
			$post_formats    = '';

		}

		echo '<ul><li><label for="soc_options_default_course_mode_supports_title">'
			.'<input ' . $title . ' type="checkbox" name="soc_options[default_course_mode_supports][]" id="soc_options_default_course_mode_supports_title" value="title" />Title</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_editor">'
			.'<input ' . $editor . ' type="checkbox" value="editor" name="soc_options[default_course_mode_supports][]" id="soc_options_default_course_mode_supports_editor" />Wysiwyg Editor</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_author">'
			.'<input ' . $author . ' type="checkbox" value="author" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_author" />Author</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_thumbnail">'
			.'<input ' . $thumbnail . ' type="checkbox" value="thumbnail" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_thumbnail" />Thumbnail</label></li>'

			.'<li><label for="soc_options_default_course_mode_excerpt">'
			.'<input ' . $excerpt . ' type="checkbox" value="excerpt" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_excerpt" />Excerpt</label></li>'

			.'<li><label for="soc_options_default_course_mode_trackbacks">'
			.'<input ' . $trackbacks . ' type="checkbox" value="trackbacks" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_trackbacks" />Trackbacks</label></li>'
			
			.'<li><label for="soc_options_default_course_mode_supports_custom_fields">'
			.'<input ' . $custom_fields . ' type="checkbox" value="custom-fields" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_custom_fields" />Custom Fields</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_comments">'
			.'<input ' . $comments . ' type="checkbox" value="comments" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_comments" />Comments</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_revisions">'
			.'<input ' . $revisions . ' type="checkbox" value="revisions" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_revisions" />Revisions</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_page_attributes">'
			.'<input ' . $page_attributes . ' type="checkbox" value="page-attributes" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_page_attributes" />Page Attributes</label></li>'

			.'<li><label for="soc_options_default_course_mode_supports_post_formats">'
			.'<input ' . $post_formats . ' type="checkbox" value="post-formats" name="soc_options[default_course_mode_supports][]"  id="soc_options_default_course_mode_supports_post_formats" />Post Formats</label></li></ul>';		
	}

	/**
	 * Fix for show in menu bug
	 */
	public function sp_menu_page_removing() {
		remove_menu_page( 'edit.php?post_type=course_mode' );
	}
	
	/**
	 * Replace course mode preview link
	 */
	public function soc_preview_post_link( $preview_link, $post ) {
		if ( 'course_mode'  === $post->post_type ) :
			$preview_link = str_replace( 'course&', 'course_mode&', $preview_link );
		endif;
		return $preview_link;
	}
	
	/**
	 * Add a select to courses single.
	 */
	public function courses_default_child_selector() {
		global $post;
		if ( 'auto-draft' !== $post->post_status ) :
			$children = get_children($post->ID);
			$args     = array(
				'posts_per_page' => -1,
				'post_type'      => 'course_mode',
				'post_parent'    => get_the_id(),
			);
			$children = get_posts( $args );
			if ( ! empty( $children ) ) :
				add_meta_box(
					'courses_default_child_selector',
					__( 'Default Course Mode' ),
					array( $this, 'smile_courses_default_child_selector_callback' ),
					'course',
					'side',
					'high'
				);
			endif;
		endif;
	}

	/**
	 * Get post meta in a callback
	 *
	 * @param WP_Post $post    The current post.
	 * @param array   $metabox With metabox id, title, callback, and args elements.
	 */
	public function smile_courses_default_child_selector_callback() {

		$args     = array(
			'posts_per_page' => -1,
			'post_type'      => 'course_mode',
			'post_parent'    => get_the_id(),
		);
		$children = get_posts( $args );

		$selected = get_post_meta( get_the_id(), 'smile_default_course_mode', true );

		echo '<select name="smile_default_course_mode">';

		foreach ( $children as $key => $child ) :
			if ( $selected ) {
				if ( $selected === $child->post_name ) :
					echo '<option type="radio" id="smile_default_course_mode_' . $key . '"  value="' . $child->post_name . '" selected />' . $child->post_title . '</option>';
				else :
					echo '<option type="radio" id="smile_default_course_mode_' . $key . '"  value="' . $child->post_name . '" />' . $child->post_title . '</option>';
				endif;
			} else {
				if ( 0 === $key ) :
					echo '<option type="radio" id="smile_default_course_mode_' . $key . '"  value="' . $child->post_name . '" selected />' . $child->post_title . '</option>';
				else :
					echo '<option type="radio" id="smile_default_course_mode_' . $key . '"  value="' . $child->post_name . '" />' . $child->post_title . '</option>';
				endif;
			}
		endforeach;

		echo '</select>';

		wp_nonce_field( 'smile_default_course_mode_nonce', 'default_course_mode_nonce' );

	}

	/**
	 * Save the course default course
	 */
	public function smile_save_courses_default_child_selector( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['default_course_mode_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['default_course_mode_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'smile_default_course_mode_nonce' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// Check the user's permissions.
		if ( 'course' !== $_POST['post_type'] ) {
			return $post_id;
		}

		// Sanitize the user input.
		$data = sanitize_text_field( $_POST['smile_default_course_mode'] );

		// Update the meta field.
		update_post_meta( $post_id, 'smile_default_course_mode', $data );
	}

	/**
	 * Check if the parent has children.
	 */
	function has_children( $post_id = false ) {

		if ( $post_id ) {
			$posts = get_posts( array( 'post_type' => 'course_mode', 'post_parent' => $post_id ) );

			if ( $posts ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get posts by course award
	 */
	public function pre_get_course_award( $query ) {
		$ignore_pre_course_award = false;
		$ignore_pre_course_award = apply_filters( 'oncouse_ignore_pre_course_award', $ignore_pre_course_award );

		if ( $ignore_pre_course_award ) {
			return;
		}
			
		if ( $query->is_main_query() && $query->is_tax( 'course_award' ) && $query->is_archive() ) {

			$args = array(
				'post_type'      => 'course_mode',
				'posts_per_page' => '-1',
				'tax_query'      => array(
					array(
						'taxonomy' => 'course_award',
						'terms'    => $query->queried_object->slug,
						'field'    => 'slug',
					),
				),
			);

			$posts   = get_posts( $args );
			$parents = array();
			foreach ( $posts as $key => $post ) :
				array_push( $parents, wp_get_post_parent_id( $post ) );
			endforeach;

			$course_args = array(
				'post_type'      => 'course',
				'posts_per_page' => '-1',
				'tax_query'      => array(
					array(
						'taxonomy' => 'course_award',
						'terms'    => $query->queried_object->slug,
						'field'    => 'slug',
					),
				),
			);

			$posts = get_posts( $course_args );
			foreach ( $posts as $key => $post ) :
				if ( $this->has_children( $post->ID ) ) :
					array_push( $parents, $post->ID );
				endif;
			endforeach;

			$query->set( 'smile_course_award', $query->queried_object->slug );

			// unset( $query->query_vars['course_award'] );
			// unset( $query->query_vars['post_type'] );
			// unset( $query->tax_query );
			// unset( $query->query['course_award'] );
			// unset( $query->queried_object );

			$query->set( 'post__in', $parents );
			// $query->set( 'post_type', 'course' );

		}

	}

	/**
	 * Redirect from a course award taxonomy
	 */
	function template_redirect_course_award() {

		global $wp_query;
		$template = $wp_query->query_vars;

		if ( array_key_exists( 'smile_course_award', $template ) && ! empty( $template['smile_course_award'] ) ) {
			locate_template( 'taxonomy-course_award.php', true );
			exit();
		}
	}

}
