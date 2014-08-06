<?php
/**
 ** Paris Muse Staff
 ** Version 1.0.0
 **/
class Muse_Staff {

	// Version
	const VERSION            = '1.0.0';
	const VERSION_OPTION     = 'wpe_core_report_staff_version';
	const REVISION           = '19700101';

	// Post Types
	const POST_TYPE_SLUG     = 'staff';
	const POST_TYPE_NAME     = 'Staff';
	const POST_TYPE_SINGULAR = 'Staff';
	const POST_TYPE_CAP      = 'post';

	// Metadata Manager
	const METADATA_GROUP     = 'wpe-core-report-staff-metadata';

	private $version         = false;

	private static $instance = false;

	/**
	 * Implement singleton
	 *
	 * @uses self::setup
	 * @return self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Clone
	 *
	 * @since 1.0.0
	 */
	private function __clone() { }

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		global $wp_version;
		// Version Check Prerequisite
		if( $version = get_option( self::VERSION_OPTION, false ) ) {
			$this->version = $version;
		} else {
			$this->version = self::VERSION;
			add_option( self::VERSION_OPTION, $this->version );
		}

		// Load Widgets
		self::load_widgets();

		// Initilization
		add_action( 'init', array( $this, 'action_init_check_version' ) );
		add_action( 'init', array( $this, 'action_init_register_post_types' ) );

		// Redirects and Rewrites
		add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );
		add_action( 'template_redirect', array( $this, 'action_init_redirects' ), 1 ); // Must be after taxonomies are registered
		add_action( 'init', array( $this, 'action_init_register_rewrites' ), 2 );

		add_filter( 'query_vars', array( $this, 'filter_query_vars_add_vars' ) );
		add_filter( 'pre_get_posts', array( $this, 'filter_pre_get_posts' ) );

		// Admin Menu Icon
		add_action( 'admin_head', array( $this, 'action_admin_head') ); // Admin menu icon

		// Custom metadata manager
		add_action( 'admin_init', array( $this, 'action_admin_init_add_metabox' ) );

		// Register Widgets
		add_action( 'widgets_init', array( $this, 'action_widgets_init_register_widgets' ) );
	}

	/**
	 * [load_features description]
	 * @return [type] [description]
	 */
	private function load_widgets() {
		/* Staff Widget */
		require get_template_directory() . '/features/staff/widget-staff.php';
	}

	public function action_widgets_init_register_widgets() {
		register_widget( 'Muse_Staff_Widget' );
	}

	/**
	 * Version Check
	 *
	 * @since 1.0.0
	 */
	function action_init_check_version() {
		// Check if the version has changed and if so perform the necessary actions
		if ( ! isset( $this->version ) || $this->version <  self::VERSION ) {
			// Do version upgrade tasks here
			update_option( self::VERSION_OPTION, self::VERSION );
		}
	}

	/**
	 * Register custom post type(s)
	 *
	 * @uses register_post_type
	 * @since 1.0.0
	 * @return null
	 */
	public function action_init_register_post_types() {
		// Register the post type
		register_post_type( self::POST_TYPE_SLUG, array(
			'labels' => array(
				'name'          => __( self::POST_TYPE_NAME ),
				'singular_name' => __( self::POST_TYPE_SINGULAR ),
				'add_new_item'  => __( 'Add New ' . self::POST_TYPE_SINGULAR ),
				'edit_item'     => __( 'Edit ' . self::POST_TYPE_SINGULAR ),
				'new_item'      => __( 'New ' . self::POST_TYPE_SINGULAR ),
				'view_item'     => __( 'View ' . self::POST_TYPE_SINGULAR ),
				'search_items'  => __( 'Search' . self::POST_TYPE_NAME ),
			),
			'menu_icon'       => '',
			'public'          => true,
			'capability_type' => self::POST_TYPE_CAP,
			'has_archive'     => true,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'hierarchical'    => true,
			'supports'        => array( 'title', 'editor', 'thumbnail' ),
		) );
	}

	/**
	 * [action_admin_head description]
	 * @return [type] [description]
	 */
	public function action_admin_head() {
		?>
		<style type="text/css">
		#adminmenu .menu-icon-staff div.wp-menu-image:before {
			content: "\f338";
		}
		</style>
		<?php
	}

	/**
	 * Add a new metabox using the Custom Metadata Manager plugin
	 *
	 * @action custom_metadata_manager_init_metadata
	 */
	public function action_admin_init_add_metabox() {

		if ( function_exists( 'x_add_metadata_field' ) && function_exists( 'x_add_metadata_group' ) ) {

			// Staff Details
			x_add_metadata_group( self::METADATA_GROUP, self::POST_TYPE_SLUG, array(
				'label' => 'Staff Details'
			) );

				x_add_metadata_field( 'wpe_core_report_staff_title', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Title',
					'field_type' => 'text',
					'description' => 'Speciality / Position',
					'multiple' => false
				) );

				x_add_metadata_field( 'wpe_core_report_staff_bio', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Widget Bio',
					'field_type' => 'wysiwyg',
					'description' => 'A few words about this staff member.',
					'multiple' => false
				) );

				x_add_metadata_field( 'wpe_core_report_staff_management', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Management',
					'field_type' => 'checkbox',
					'description' => 'Is this staff member part of management?',
					'multiple' => false
				) );

				x_add_metadata_field( 'wpe_core_report_staff_guide', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Guide',
					'field_type' => 'checkbox',
					'description' => 'Is this staff member a guide?',
					'multiple' => false,
				) );
		}
	}

	/**
	 * [action_init_redirects description]
	 * @return [type] [description]
	 */
	function action_init_redirects(){

		global $wp_query;

		if( is_admin() || ! is_main_query() || self::POST_TYPE_SLUG != $wp_query->query_vars[ 'post_type' ] ) {
			return;
		}

		$order_var = ( isset( $wp_query->query_vars['order'] ) ) ? ( $wp_query->query_vars['order'] ) : false;
		$order_get = ( isset( $_GET['order'] ) ) ? ( $_GET['order'] ) : false;

		$staff_type = ( isset( $_GET['staff-type'] ) ) ? ( $_GET['staff-type'] ) : false;
		if( empty( $staff_type ) ) {
			$staff_type = ( isset( $wp_query->query_vars['staff-type'] ) ) ? ( $wp_query->query_vars['staff-type'] ) : false;
		}
		$staff_type_url = ( ! empty( $staff_type ) ) ? 'staff/' . $staff_type . '/' : 'staff/';

		// Make pretty links
		if( 'Z-A' == $order_get ) {
			$redirect = home_url( $staff_type_url . 'Z-A/' );
		} elseif( 'A-Z' == $order_get ) {
			$redirect = home_url( $staff_type_url . 'A-Z/' );
		}

		// And do it
		if( isset( $redirect ) ) {
			wp_redirect( $redirect );
			exit(0);
		}
	}

	/**
	 * [action_init_register_rewrites description]
	 * @return [type] [description]
	 */
	function action_init_register_rewrites() {

		// Guides
		add_rewrite_rule('^staff/guides/(A-Z)/?','index.php?order=ASC&post_type=staff&orderby=name&staff-type=guides','top');
		add_rewrite_rule('^staff/guides/(Z-A)/?','index.php?order=DESC&post_type=staff&orderby=name&staff-type=guides','top');
		add_rewrite_rule('^staff/guides/?','index.php?order=ASC&post_type=staff&orderby=name&staff-type=guides','top'); // Default is ASC

		// Management
		add_rewrite_rule('^staff/management/(A-Z)/?','index.php?order=ASC&post_type=staff&orderby=name&staff-type=management','top');
		add_rewrite_rule('^staff/management/(Z-A)/?','index.php?order=DESC&post_type=staff&orderby=name&staff-type=management','top');
		add_rewrite_rule('^staff/management/?','index.php?order=ASC&post_type=staff&orderby=name&staff-type=management','top'); // Default is ASC

		// All
		add_rewrite_rule('^staff/(A-Z)/?','index.php?order=ASC&post_type=staff&orderby=name','top');
		add_rewrite_rule('^staff/(Z-A)/?','index.php?order=DESC&post_type=staff&orderby=name','top');

	}

	/**
	 * If we are looking at staff, default to show order by name ASC
	 *
	 * [filter_pre_get_posts description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	function filter_pre_get_posts( $query ) {
		if( is_admin() || ! $query->is_main_query() || ! isset( $query->query_vars[ 'post_type' ] ) || self::POST_TYPE_SLUG != $query->query_vars[ 'post_type' ] ) {
			return $query;
		}

		// Sort Staff alphabetically by default
		if( ! $query->get( 'order' ) ) {
			$query->set( 'order', 'ASC' );
		}
		$query->set( 'orderby', 'name' );

		// Show only guides or management?
		$staff_type = $query->get( 'staff-type' );
		if( 'guides' == $staff_type ) {
			$query->set( 'meta_query', array( array(
				'key' => 'wpe_core_report_staff_guide',
				'value' => 'on',
			) ) );
		} elseif( 'management' == $staff_type ) {
			$query->set( 'meta_query', array( array(
				'key' => 'wpe_core_report_staff_management',
				'value' => 'on'
			) ) );
		}

		return $query;
	}

	/**
	 * [filter_query_vars_add_vars description]
	 * @param  [type] $vars [description]
	 * @return [type]       [description]
	 */
	function filter_query_vars_add_vars( $vars ) {
		$vars[] = 'staff-type';
		return $vars;
	}

	/**
	 * [action_template_redirect description]
	 * @return [type] [description]
	 */
	function action_template_redirect() {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ 'orderby' ] ) && self::POST_TYPE_SLUG != $wp_query->query_vars[ 'post_type' ] ) {
			$template = locate_template( 'archive-staff.php' );
		}

		// Load em if you've got em
		if( ! empty( $template ) ) {
			include( $template );
			exit;
		}
		return;
	}

	/**
	 * Render filters
	 *
	 * @return [type] [description]
	 */
	public static function render_filters() {
		global $wp_query;

		$staff_type = ( isset( $_GET['staff-type'] ) ) ? ( $_GET['staff-type'] ) : false;
		if( empty( $staff_type ) ) {
			$staff_type = ( isset( $wp_query->query_vars['staff-type'] ) ) ? ( $wp_query->query_vars['staff-type'] ) : false;
		}

		if( $wp_query->is_post_type_archive( array( 'staff' ) ) ) { ?>
			<form id="staff-filter-form" action="<?php echo home_url( 'staff/' );?>">
				<select id="sort-order" name="order">
					<option <?php selected( $wp_query->query_vars['order'], 'ASC' ); ?>>A-Z</option>
					<option <?php selected( $wp_query->query_vars['order'], 'DESC' ); ?>>Z-A</option>
				</select>
				<input type="hidden" name="staff-type" value="<?php echo $staff_type; ?>"/>
				</form>
				<script type="text/javascript">
				jQuery( document ).ready( function( $ ) {
					$('#sort-order').change(function() {
						$('#staff-filter-form').submit();
					});
				});
				</script>
			<?php
		}
	}

} // Class
Muse_Staff::instance();
