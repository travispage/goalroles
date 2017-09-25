<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Visibility_Admin
 *
 * @since @@version
 *
 */
class WP_Job_Manager_Visibility_Admin {

	/**
	 * WP_Job_Manager_Visibility_Admin constructor.
	 */
	public function __construct() {

		//if( get_option('job_manager_visibility_enabled') ){
		//	//new WP_Job_Manager_Visibility_Admin_WritePanels();
		//	//new WP_Job_Manager_Visibility_Admin_Ajax();
		//}

		new WP_Job_Manager_Visibility_Admin_Assets();
		new WP_Job_Manager_Visibility_Admin_Default();
		//new WP_Job_Manager_Visibility_Admin_Custom();
		new WP_Job_Manager_Visibility_Admin_Groups();

		if( get_option( 'jmv_disable_heartbeat' ) ) add_action( 'init', array($this, 'death_to_heartbeat'), 1 );
		add_filter( 'sanitize_option_jmv_disable_postlock', array($this, 'death_to_postlock'), 1 );

		add_action( 'admin_notices', array( $this, 'packages_plugin' ) );
		add_action( 'admin_init', array( $this, 'packages_plugin_check' ) );
	}

	/**
	 * Check for Packages Plugin Notice Dismiss
	 *
	 *
	 * @since @@version
	 *
	 */
	function packages_plugin_check(){

		if( isset( $_GET['jmv_hide_packages_plugin_notice'] ) ){
			update_option( 'jmv_packages_plugin_notice_disabled', true );
			remove_query_arg( 'jmv_hide_packages_plugin_notice' );
		}

	}

	/**
	 * Output Notice for Packages Plugin
	 *
	 *
	 * @since @@version
	 *
	 */
	function packages_plugin(){
		if( ! get_option( 'jmv_packages_plugin_notice_disabled', false ) ){
			$notice = __( 'Thanks for upgrading/installing WP Job Manager Visibility!', 'wp-job-manager-visibility' );
			$notice .= '  ' . sprintf( __( 'Did you know this plugin (WP Job Manager Visibility) can be extended using the <a href="%1$s" target="_blank">WP Job Manager Packages</a> plugin?<br/> <br />The packages plugin allows you to configure groups based on Visibility Packages or WooCommerce Paid Listings packages, and TONS of other features!<br /><strong><a href="%2$s">Click here for more information (and 15 percent off coupon code)</a></strong>', 'wp-job-manager-visibility' ), 'https://plugins.smyl.es/wp-job-manager-packages/', admin_url( 'edit.php?post_type=default_visibilities&page=visibility_settings#settings-packages_promo' ) );
			echo '<div class="updated notice"><p>' . $notice . '</p><p><a href="?jmv_hide_packages_plugin_notice">' . __( 'Dismiss', 'wp-job-manager-visibility' ) . '</a></p></div>';
		}
	}

	/**
	 * Set disabled_post_lock on CPT
	 *
	 * Based on settings this will either set, or remove disabled_post_lock on
	 * this plugins custom post types.  To do this we hook into the sanitize
	 * filter to only execute when the option has been changed.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	function death_to_postlock( $value ){

		// Null means option was deselected, 1 means postlock disable was checked
		// 0 means add_option was called by settings class to add option
		if( $value === null || $value === 1 ){
			$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();
			$post_type_support_method = $value === 1 ? 'add_post_type_support' : 'remove_post_type_support';
			foreach ( $post_types as $post_type ) {
				$post_type_support_method( $post_type, 'disabled_post_lock' );
			}
		}

		return $value;
	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since 1.1.0
	 *
	 */
	function death_to_heartbeat() {
		if ( $this->is_plugin_page() ) {
			if( wp_script_is( 'heartbeat', 'registered' ) || wp_script_is( 'heartbeat', 'enqueued' ) ){
				wp_deregister_script( 'heartbeat' );
			}
		}
	}

	/**
	 * Check if current page is one of plugin pages
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	function is_plugin_page() {

		global $pagenow;

		$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();
		$post_types[] = 'job_listing';
		$post_types[] = 'resume';

		$current_post_type = ( isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : '' );
		if ( in_array( $current_post_type, $post_types ) ) return TRUE;

		return FALSE;
	}

}