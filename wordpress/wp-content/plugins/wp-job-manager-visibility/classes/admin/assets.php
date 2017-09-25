<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Assets {

	/**
	 * WP_Job_Manager_Visibility_Admin_Assets constructor.
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( 'WP_Job_Manager_Visibility_Admin_Assets', 'maybe_deregister' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 999 );
		add_action( 'admin_enqueue_scripts', array($this, 'js'), 100 );

	}

	function js(){

		$support_ticket_url = 'https://plugins.smyl.es/support/new/';

		$translations = array(
			'error_submit_ticket'  => sprintf( __( 'If you continue receive this error, please submit a <a target="_blank" href="%s">support ticket</a>.', 'wp-job-manager-visibility' ), esc_url( $support_ticket_url ) ),
			'field_required'       => __( 'This field is required!', 'wp-job-manager-visibility' ),
			'yes'                  => __( 'Yes', 'wp-job-manager-visibility' ),
			'loading'              => __( 'Loading', 'wp-job-manager-visibility' ),
			'no'                   => __( 'No', 'wp-job-manager-visibility' ),
			'cancel'               => __( 'Cancel', 'wp-job-manager-visibility' ),
			'close'                => __( 'Close', 'wp-job-manager-visibility' ),
			'enable'               => __( 'Enable', 'wp-job-manager-visibility' ),
			'disable'              => __( 'Disable', 'wp-job-manager-visibility' ),
			'error'                => __( 'Error', 'wp-job-manager-visibility' ),
			'unknown_error'        => __( 'Uknown Error! Refresh the page and try again.', 'wp-job-manager-visibility' ),
			'success'              => __( 'Success', 'wp-job-manager-visibility' ),
			'ays_remove'           => __( 'Are you sure you want to remove this configuration?', 'wp-job-manager-visibility' ),
			'error_metakey_in_visible' => __( 'You can\'t add a meta key to hide if it\'s set as a visible field!', 'wp-job-manager-visibility' )
		);

		wp_localize_script( 'jmv-admin-js', 'jmrvlocale', $translations );
	}

	/**
	 * Maybe Deregister Chosen
	 *
	 * Applications addon plugin registers old version of Chosen.JS (1.1.0) on all
	 * admin pages, as such, we have to deregister and dequeue it to use a newer
	 * version which supports additional features.
	 *
	 *
	 * @since 1.4.2
	 *
	 * @param $hook
	 */
	public static function maybe_deregister( $hook ){

		if( array_key_exists( 'post_type', $_GET ) && ! empty( $_GET['post_type'] ) ){
			$post_type = $_GET['post_type'];
		} elseif( $hook === 'post.php' && array_key_exists( 'post', $_GET ) && ! empty( $_GET['post'] ) ){
			$post_type = get_post_type( $_GET['post'] );
		}

		if( isset( $post_type ) && ! empty( $post_type ) ){

			$vis_post_types = WP_Job_Manager_Visibility_CPT::get_post_types();

			if( in_array( $post_type, $vis_post_types, false ) ){

				if( wp_script_is( 'chosen', 'registered' ) ){
					wp_deregister_script( 'chosen' );
				}

				if( wp_script_is( 'chosen', 'enqueued' ) ){
					wp_dequeue_script( 'chosen' );
				}

				if( wp_style_is( 'chosen', 'enqueued' ) ){
					wp_dequeue_style( 'chosen' );
				}

				if( wp_style_is( 'chosen', 'registered' ) ){
					wp_deregister_style( 'chosen' );
				}

			}
		}

	}

	/**
	 * Register Admin CSS & JS
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function register(){

		$script_version = defined( 'WPJMV_DEBUG' ) ? filemtime( __FILE__ ) : NULL;

		if( ! wp_script_is( 'chosen', 'registered' ) ) {
			wp_register_script( 'chosen', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/chosen.jquery.js", array('jquery'), NULL );
		}

		if( ! wp_style_is( 'chosen', 'registered' ) ) {
			wp_register_style( 'chosen', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/css/chosen.min.css", array(), NULL );
		}

		wp_register_style( 'jmv-admin-css', $this->css_path('admin'), array( 'chosen' ), $script_version );
		wp_register_style( 'jmv-vendor-css', $this->css_path('vendor'), array(), $script_version );
		wp_register_script( 'jmv-vendor-js', $this->js_path('vendor'), array( 'jquery' ), $script_version, true );
		wp_register_script( 'jmv-admin-js', $this->js_path('admin'), array( 'jquery', 'chosen', 'jquery-ui-spinner', 'jmv-vendor-js' ), $script_version, true );
		//wp_register_script( 'jmv-default-js', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/single/default.js", array('jquery', 'chosen' ), $admin_js_time, TRUE );
		//wp_register_script( 'jmv-groups-js', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/single/groups.js", array( 'jquery', 'chosen', 'jquery-ui-spinner' ), $admin_js_time, true );

	}

	function js_path( $file ){

		$min            = defined( 'WPJMV_DEBUG' ) && WPJMV_DEBUG !== FALSE ? '' : '.min';
		$build_dir      = defined( 'WPJMV_DEBUG' ) && WPJMV_DEBUG !== FALSE ? '/build' : '';

		return JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js{$build_dir}/{$file}{$min}.js";
	}

	function css_path( $file ) {

		$min       = defined( 'WPJMV_DEBUG' ) && WPJMV_DEBUG !== FALSE ? '' : '.min';
		$build_dir = defined( 'WPJMV_DEBUG' ) && WPJMV_DEBUG !== FALSE ? '/build' : '';

		return JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/css{$build_dir}/{$file}{$min}.css";
	}

	/**
	 * Enqueue Admin CSS & JS
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $hook
	 */
	function enqueue( $hook ){
		global $post;

		$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();

		if ( $hook === 'resume_page_resume-manager-settings' ) {
			wp_enqueue_style( 'jmv-admin-css' );
			//wp_enqueue_script( 'jmv-settings-js' );
			wp_enqueue_style( 'jmv-vendor-css' );
			wp_enqueue_script( 'jmv-admin-js' );
		}

		if ( empty( $hook ) || ! ( $hook === 'post.php' || $hook === 'post-new.php' || $hook === 'edit.php') ) return;
		if ( ! is_object( $post ) || ! in_array( $post->post_type, $post_types )) return;

		wp_enqueue_style( 'jmv-admin-css' );
		wp_enqueue_style( 'jmv-vendor-css' );
		wp_enqueue_script( 'jmv-admin-js' );

	}
}