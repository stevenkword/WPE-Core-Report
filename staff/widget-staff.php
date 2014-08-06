<?php
/**
 ** Paris Muse Staff Widget
 ** Version 1.0.0
 **/
class Muse_Staff_Widget extends WP_Widget {

	const VERSION            = '1.0.0';
	const VERSION_OPTION     = 'wpe_core_report_staff_widget_version';
	const REVISION           = '20140521';

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
	 * Add actions and filters
	 *
	 * @uses add_action, add_filter
	 * @since 1.0.0
	 */
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wp_version;
		// Version Check Prerequisite
		if( $version = get_option( self::VERSION_OPTION, false ) ) {
			$this->version = $version;
		} else {
			$this->version = self::VERSION;
			add_option( self::VERSION_OPTION, $this->version );
		}

		parent::__construct( false, 'Paris Muse - Featured Guide' );
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
	 * Outputs the content of the widget
	 *
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {
		// Require ID
		if( 0 >= intval( $instance['staff_id'] ) ) {
			return;
		}

		$staff_member = get_post( $instance['staff_id'] );

		$title = apply_filters( 'widget_title', $staff_member->post_title );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		//echo $staff_member->post_content;
		if( function_exists( 'get_metadata' ) && $bio = get_metadata( 'post', $instance['staff_id'], 'wpe_core_report_staff_bio', true ) ) {
			echo $bio;
		}

		echo get_the_post_thumbnail( $instance['staff_id'], 'medium' );

		echo '<p><a href="' . get_permalink( $instance['staff_id'] ) . '">Read More</a></p>';

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @since 1.0.0
	 */
 	public function form( $instance ) {
		global $post;
		$staff = get_posts( array(
			'posts_per_page' => 100, // If this gets much larger we would want to do an AJAX autocomplete
			'post_type'      => 'staff',
			'fields'         => 'ids'
		) );

		// outputs the options form on admin
		if ( isset( $instance[ 'staff_id' ] ) ) {
			$title = get_the_title( $instance[ 'staff_id' ] );
		} else {
			$title = '';
		}
		?>
		<p>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="hidden" value="<?php echo esc_attr( $title ); ?>">
			<label for="<?php echo $this->get_field_id( 'staff_id' ); ?>"><?php _e( 'Select a staff member:' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'staff_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'staff_id' ) ); ?>">
				<?php
				foreach ( $staff as $staff_id ) {
					?>
					<option value="<?php echo esc_attr( $staff_id ); ?>" <?php selected( $staff_id, isset( $instance['staff_id'] ) ? $instance['staff_id'] : '' ); ?>><?php echo esc_html( get_the_title( $staff_id ) ); ?></option>
					<?php
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Processes widget options to be saved
	 *
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['staff_id'] = ( ! empty( $new_instance['staff_id'] ) ) ? strip_tags( $new_instance['staff_id'] ) : '';
		return $instance;
	}

} // Class
Muse_Staff_Widget::instance();