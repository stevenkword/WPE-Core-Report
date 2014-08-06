<?php
/**
 ** Paris Muse Releases
 ** Version 1.0.0
 **/
class WPE_Core_Report_Releases {

	// Version
	const VERSION            = '1.0.0';
	const VERSION_OPTION     = 'wpe_core_report_releases_version';
	const REVISION           = '19700101';

	// Post Types
	const POST_TYPE_SLUG     = 'releases';
	const POST_TYPE_NAME     = 'Releases';
	const POST_TYPE_SINGULAR = 'Release';
	const POST_TYPE_CAP      = 'post';

	/* Taxonomy */
	const TAXONOMY_SLUG      = 'version';
	const TAXONOMY_NAME      = 'Versions';
	const TAXONOMY_SINGULAR  = 'Version';

	// Metadata Manager
	const METADATA_GROUP     = 'wpe-core-report-releases-metadata';

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
		// Version Check
		if( $version = get_option( self::VERSION_OPTION, false ) ) {
			$this->version = $version;
		} else {
			$this->version = self::VERSION;
			add_option( self::VERSION_OPTION, $this->version );
		}

		// Initilization
		add_action( 'init', array( $this, 'action_init_check_version' ) );
		add_action( 'init', array( $this, 'action_init_register_post_types' ) );
		add_action( 'init', array( $this, 'action_init_register_taxonomies' ) );

		// Admin Menu Icon
		add_action( 'admin_head', array( $this, 'action_admin_head') ); // Admin menu icon

		// Custom metadata manager
		add_action( 'admin_init', array( $this, 'action_admin_init_add_metabox' ) );

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
			'taxonomies'      => array( self::TAXONOMY_SLUG ),
		) );
	}

	/**
	 * [action_admin_head description]
	 * @return [type] [description]
	 */
	public function action_admin_head() {
		?>
		<style type="text/css">
		#adminmenu .menu-icon-releases div.wp-menu-image:before {
			content: "\f319";
		}
		</style>
		<?php
	}

	/**
	 * Register the taxonomy
	 *
	 * @uses add_action()
	 * @return null
	 */
	public function action_init_register_taxonomies() {

		// Versions
		register_taxonomy( self::TAXONOMY_SLUG, array( self::POST_TYPE_SLUG ) , array(
			'labels' => array(
				'name'              => __( self::TAXONOMY_NAME ),
				'singular_name'     => __( self::TAXONOMY_SINGULAR ),
				'search_items'      => __( 'Search ' . self::TAXONOMY_NAME ),
				'all_items'         => __( 'All ' . self::TAXONOMY_NAME ),
				'parent_item'       => __( 'Parent ' . self::TAXONOMY_SINGULAR ),
				'parent_item_colon' => __( 'Parent ' . self::TAXONOMY_SINGULAR . ':' ),
				'edit_item'         => __( 'Edit ' . self::TAXONOMY_SINGULAR ),
				'update_item'       => __( 'Update ' . self::TAXONOMY_SINGULAR ),
				'add_new_item'      => __( 'Add New ' . self::TAXONOMY_SINGULAR ),
				'new_item_name'     => __( 'New ' . self::TAXONOMY_SINGULAR. ' Name' ),
				'menu_name'         => __( self::TAXONOMY_NAME ),
				'view_item'         => __( 'View ' . self::TAXONOMY_SINGULAR )
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
		) );
	}


	/**
	 * Add a new metabox using the Custom Metadata Manager plugin
	 *
	 * @action custom_metadata_manager_init_metadata
	 */
	public function action_admin_init_add_metabox() {

		if ( function_exists( 'x_add_metadata_field' ) && function_exists( 'x_add_metadata_group' ) ) {

			// Release Details
			x_add_metadata_group( self::METADATA_GROUP, self::POST_TYPE_SLUG, array(
				'label' => 'Release Details'
			) );

				x_add_metadata_field( 'wpe_core_report_release_reservation_link', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Reservation Link',
					'field_type' => 'text',
					'description' => '',
					'multiple' => false
				) );

				x_add_metadata_field( 'wpe_core_report_release_about_wpe-core-reportum', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'About Museum',
					'field_type' => 'wysiwyg',
					'description' => 'A brief description of the wpe-core-reportum where this release takes place.',
					'multiple' => false
				) );

				x_add_metadata_field( 'wpe_core_report_release_video_embed', self::POST_TYPE_SLUG, array(
					'group' => self::METADATA_GROUP,
					'label' => 'Video',
					'field_type' => 'textarea',
					'description' => 'Video Embed Code',
					'multiple' => false
				) );
		}
	}

	/**
	 * [action_template_redirect description]
	 * @return [type] [description]
	 */
	function action_template_redirect() {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ 'attraction' ] ) || isset( $wp_query->query_vars[ 'version' ] ) ) {
			$template = locate_template( 'archive-releases.php' );
		}

		// Load em if you've got em
		if( ! empty( $template ) ) {
			include( $template );
			exit;
		}

		return;
	}



} // Class
WPE_Core_Report_Releases::instance();