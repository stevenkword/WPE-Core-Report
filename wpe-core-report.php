<?php
/*
Plugin Name: WPE Core Report Base Plugin
Plugin URI:
Description: Meh
Version: 1.0.0
Author: Steven Word
Author URI: http://wpengine.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class WPE_Core_Report_Loader {

	// Version
	const VERSION                     = '1.0.0';
	const VERSION_OPTION              = 'wpe_core_report_loader_version';
	const REVISION                    = '20140804'; //yyyymmdd
	const TEXT_DOMAIN                 = '';

	protected static $current_version = false;
	private static $instance          = false;

	public $admin_notices             = array();
	public $plugin_dependencies       = array();

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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		global $wp_version;
		// Version Check
		if( $version = get_option( self::VERSION_OPTION, false ) ) {
			self::$current_version = $version;
		} else {
			self::$current_version = self::VERSION;
			add_option( self::VERSION_OPTION, self::$current_version );
		}

		// Load Features
		self::load_features();

		// Theme Activation
		add_action( 'after_switch_theme', array( $this, 'activate' ) );

		// Check for plugin dependencies
		add_action( 'after_setup_theme', array( $this, 'register_plugin_dependencies' ) );
		add_action( 'admin_init', array( $this, 'check_plugin_dependencies' ) );
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );

		// Perform updates if necessary
		add_action( 'init', array( $this, 'action_init_check_version' ) );

		// Posts 2 Posts ( Many to Many relationships ). Sets up connections between custom post types
		add_action( 'p2p_init', array( $this, 'action_p2p_init_register_connections' ) );
	}

	/**
	 * Clone
	 *
	 * @since 1.0.0
	 */
	private function __clone() { }

	/**
	 * [load_features description]
	 * @return [type] [description]
	 */
	private function load_features() {

		/* Major Release Versions */
		require plugin_dir_path( __FILE__ ) . '/releases/class-releases.php';
	}

	/**
	 * On plugin/theme activation
	 *
	 * @uses flush_rewrite_rules()
	 * @since 1.0.0
	 * @return null
	 */
	public function activate() {

		if( class_exists( 'WPE_Core_Report_Releases' ) ) {
			WPE_Core_Report_Releases::action_init_register_post_types();
			WPE_Core_Report_Releases::action_init_register_taxonomies();
		}

		flush_rewrite_rules();
	}

	/**
	 * On plugin deactivation
	 *
	 * @uses flush_rewrite_rules()
	 * @since 1.0.0
	 * @return null
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Version Check
	 *
	 *
	 *
	 * @since 1.0.0
	 */
	public function action_init_check_version() {
		// Check if the version has changed and if so perform the necessary actions
		if ( ! isset( self::$version ) || self::$version < self::VERSION ) {

			// Perform updates if necessary
			// e.g. if( '2.0.0' > $this->version ) {
			//	do_the_things();
			// }

			// Update the version information in the database
			update_option( self::VERSION_OPTION, self::VERSION );
		}
	}

	/**
	 * Sets up Posts 2 Posts connections between custom post types
	 *
	 * @return [type] [description]
	 */
	public function action_p2p_init_register_connections() {

		if( function_exists( 'p2p_register_connection_type' ) ) {
			// Connect Staff to Releases
			if( class_exists( 'WPE_Core_Report_Releases' ) ) {
				p2p_register_connection_type( array(
					'name' => 'posts_to_releases',
					'from' => 'posts',
					'to'   => WPE_Core_Report_Releases::POST_TYPE_SLUG
				) );
			}

		}

	}

	/**
	 ** DEPENDENCY MANAGEMENT EXPIRIMENTATION
	 **/

	/**
	 * Define feature dependencies
	 *
	 * [register_plugin_dependencies description]
	 * @return [type] [description]
	 */
	public function register_plugin_dependencies() {

		/*
		$active_plugins = get_option( 'active_plugins', array() );
		var_dump( $active_plugins );
		 */

		// Posts 2 Posts
		$plugin_dependencies[] = array(
			'slug'        => 'posts-to-posts',
			'name'        => 'Posts 2 Posts',
			'path'        => 'posts-to-posts/posts-to-posts.php',
			'description' => 'Release Guide Connections'
		);

		// Custom Metadata Manager
		$plugin_dependencies[] = array(
			'slug'        => 'custom-metadata',
			'name'        => 'Custom Metadata Manager',
			'path'        => 'custom-metadata/custom_metadata.php',
			'description' => 'Staff Details'
		);

		// Revolution Slider
		$plugin_dependencies[] = array(
			'slug'        => 'revslider',
			'name'        => 'Revolution Slider',
			'path'        => 'revslider/revslider.php',
			'description' => 'Slideshow'
		);

		// Contact Form
		$plugin_dependencies[] = array(
			'slug'        => 'gravityforms',
			'name'        => 'Gravity Forms',
			'path'        => 'gravityforms/gravityforms.php',
			'description' => 'Contact Form'
		);

		$this->plugin_dependencies = $plugin_dependencies;
	}

	/**
	 * [check_plugin_dependencies description]
	 * @return [type] [description]
	 */
	public function check_plugin_dependencies() {

		foreach( $this->plugin_dependencies as $dependency ) {

			if( ! isset( $dependency[ 'path' ] ) || ! is_plugin_active( $dependency[ 'path' ] ) ) {
				$plugin_install_url   = admin_url( 'plugin-install.php?tab=search&s=' . $dependency[ 'slug' ] );
				$plugin_install_link  = '<a href="' . esc_url( $plugin_install_url ) . '">Install</a>';

				$plugin_activate_url  = admin_url( 'plugins.php?plugin_status=inactive#' . $dependency[ 'slug' ] );
				$plugin_activate_link = '<a href="' . esc_url( $plugin_activate_url ) . '">Activate</a>';

				$this->admin_notices[] = __( 'The <em>' . $dependency['description'] . '</em> feature is dependent on the <em>' . $dependency['name'] . '</em> plugin. Please ' . $plugin_install_link . ' and ' . $plugin_activate_link . '.', self::TEXT_DOMAIN );
			}
		}
	}

	/**
	 * Display the admin notices for missing dependencies
	 *
	 * [action_admin_notices description]
	 * @return [type] [description]
	 */
	public function action_admin_notices() {
		if( 0 < count( $this->admin_notices ) ) {
			echo '<div class="error">';
			foreach( $this->admin_notices as $notice ) {
				echo "<p>$notice</p>";
			}
			echo '</div>';
		}
	}


} // Class
WPE_Core_Report_Loader::instance();

// On Plugin Activation
register_activation_hook( __FILE__, array( 'WPE_Core_Report_Loader', 'activate' ) );

// On Plugin DeActivation
register_deactivation_hook( __FILE__, array( 'WPE_Core_Report_Loader', 'deactivate' ) );