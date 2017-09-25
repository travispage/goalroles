<?php
/**
 * Plugin Name: WP Job Manager - Visibility
 * Plugin URI:  http://plugins.smyl.es/wp-job-manager-visibility
 * Description: Set fields as visible or hidden (with placeholders) for WP Job Manager fields using custom groups or user configurations.
 * Author:      Myles McNamara
 * Author URI:  http://smyl.es
 * Version:     1.4.2
 * Tested up to: 4.7
 * Requires at least: 4.1
 * Domain Path: /languages
 * Text Domain: job-manager-visibility
 * Last Updated: @@timestamp
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'sMyles_JMV_Update' ) ) require_once( 'includes/smyles-update/class-smyles-update.php' );

// Includes any custom or compatibility functions
require_once( 'includes/functions.php' );

Class WP_Job_Manager_Visibility extends sMyles_JMV_Update {

	const PLUGIN_SLUG = 'wp-job-manager-visibility';
	const PROD_ID = 'WP Job Manager - Visibility';
	const VERSION = '1.4.2';

	protected static $instance;
	/**
	 * @var WP_Job_Manager_Visibility_Integration
	 */
	protected $integration;
	protected $plugin_slug;
	protected $plugin_file;

	function __construct() {

		$this->plugin_product_id = self::PROD_ID;
		$this->plugin_version    = self::VERSION;
		$this->plugin_slug       = self::PLUGIN_SLUG;
		$this->plugin_file       = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );

		// PHP < 5.4 Compatibility
		require_once( 'includes/compatibility.php' );

		if ( ! defined( 'JOB_MANAGER_VISIBILITY_VERSION' ) ) define( 'JOB_MANAGER_VISIBILITY_VERSION', WP_Job_Manager_Visibility::VERSION );
		if ( ! defined( 'JOB_MANAGER_VISIBILITY_PROD_ID' ) ) define( 'JOB_MANAGER_VISIBILITY_PROD_ID', WP_Job_Manager_Visibility::PROD_ID );
		if ( ! defined( 'JOB_MANAGER_VISIBILITY_PLUGIN_DIR' ) ) define( 'JOB_MANAGER_VISIBILITY_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		if ( ! defined( 'JOB_MANAGER_VISIBILITY_PLUGIN_URL' ) ) define( 'JOB_MANAGER_VISIBILITY_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		register_activation_hook( __FILE__, array($this, 'plugin_activated') );
		register_deactivation_hook( __FILE__, array($this, 'plugin_deactivated') );

		new WP_Job_Manager_Visibility_Install();

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 50 );
		add_action( 'plugins_loaded', array($this, 'load_translations') );
		add_filter( 'cron_schedules', array($this, 'add_weekly') );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 4 );
		//add_action( "in_plugin_update_message-{$this->plugin_file}", array( $this, 'update_message' ), 20, 2 );

		if( class_exists( 'WP_Resume_Manager' ) && version_compare( RESUME_MANAGER_VERSION, '1.11.0', '<' ) ){
			// Disable Resume integration
			update_option( 'jmv_enable_resume_manager_integration', 0 );
			// Add notice for resumes
			add_filter( 'admin_notices', array( $this, 'resumes_update_required' ) );
		}

		if ( is_admin() ) $this->init_updates( __FILE__ );

	}

	/**
	 * WPJM Resumes Outdated Notice
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function resumes_update_required(){

		?>
		<div class="update-nag">
		<?php _e( 'WP Job Manager Visibility requires WP Job Manager Resumes 1.13.0 and above. Please upgrade as soon as possible!  Resume integration will be disabled until you upgrade to 1.13.0 or above.', 'wp-job-manager-visibility' ); ?>
		</div><?php

	}

	function update_message( $plugin_data, $r ){

		// $remote_notice = wp_remote_get( "https://plugins.smyl.es/update-notice/jmv/" );

		$output = 'hi';

		return print $output;
	}

	/**
	 * @param $plugin_meta
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $status
	 *
	 * @return array
	 */
	public function add_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if ( $this->plugin_slug . '/' . $this->plugin_slug . '.php' == $plugin_file ) {
			//$plugin_meta[ ] = sprintf( '<a href="%s">%s</a>', __( 'http://wordpress.org/plugins/' . $this->plugin_slug, $this->plugin_slug ), __( 'Wordpress', $this->plugin_slug ) );
			$plugin_meta[] = '<a target="_blank" href="https://www.transifex.com/projects/p/' . $this->plugin_slug . '/">' . __( 'Translate', 'wp-job-manager-visibility' ) . '</a>';
		}

		return $plugin_meta;
	}

	/**
	 * Class Loader After Plugins Loaded
	 *
	 * Initializes new admin class instance if Job Manager and Resume Manager
	 * classes are defined, and user is an administrator.
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function plugins_loaded() {

		if ( class_exists( 'WP_Job_Manager' ) ) new WP_Job_Manager_Visibility_Admin();

		$this->integration = new WP_Job_Manager_Visibility_Integration( TRUE );
		new WP_Job_Manager_Visibility_Permalinks();

	}

	/**
	 * WP_Job_Manager_Visibility_Integration
	 *
	 *
	 * @since 1.4.0
	 *
	 * @return WP_Job_Manager_Visibility_Integration
	 */
	function integration(){
		return $this->integration;
	}

	/**
	 * Load Plugin Translations from Languages Directory
	 *
	 * @since 1.1.0
	 *
	 */
	function load_translations() {

		load_plugin_textdomain( 'wp-job-manager-visibility', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Job_Manager_Visibility
	 */
	public static function get_instance() {

		if ( null == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Add a weekly option to cron jobs
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	function add_weekly( $schedules ) {

		// add a 'weekly' schedule to the existing set
		$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => __( 'Once Weekly', 'wp-job-manager-visibility' )
		);

		return $schedules;
	}

	/**
	 * Set option when plugin gets activated
	 *
	 *
	 * @since 1.0
	 *
	 */
	function plugin_activated() {
		add_option( 'job_manager_visibility_activated', 'true' );
		wp_schedule_event( time() + 60, 'weekly', 'job_manager_field_cache_check' );
	}

	/**
	 * Ran when plugin is deactivated
	 *
	 *
	 * @since 1.0
	 *
	 */
	function plugin_deactivated() {
		wp_clear_scheduled_hook( 'job_manager_field_cache_check' );
	}

	/**
	 * Get Job Listing Post Type Label
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return string|void
	 */
	public static function get_job_post_label() {

		$job_obj      = get_post_type_object( 'job_listing' );
		$job_singular = is_object( $job_obj ) ? $job_obj->labels->singular_name : __( 'Job', 'wp-job-manager-visibility' );
		if ( ! $job_singular ) $job_singular = __( 'Job', 'wp-job-manager-visibility' );

		return $job_singular;
	}

	/**
	 * Class Autoloader Method
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $class
	 */
	public static function autoload( $class ) {

		// Exit autoload if being called by a class other than ours
		if ( FALSE === strpos( $class, 'WP_Job_Manager_Visibility' ) ) return;

		$class_file = str_replace( 'WP_Job_Manager_Visibility_', '', $class );
		$file_array = array_map( 'strtolower', explode( '_', $class_file ) );

		$dirs = 0;
		$file = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/classes/';

		while ( $dirs ++ < count( $file_array ) ) {
			$file .= '/' . $file_array[ $dirs - 1 ];
		}

		$file .= '.php';

		if ( ! file_exists( $file ) || $class === 'WP_Job_Manager_Visibility' ) {
			return;
		}

		include $file;

	}

}

spl_autoload_register( array('WP_Job_Manager_Visibility', 'autoload') );

WP_Job_Manager_Visibility::get_instance();