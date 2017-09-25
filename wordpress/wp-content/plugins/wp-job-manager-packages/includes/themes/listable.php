<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Themes_Listable
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Themes_Listable {

	/**
	 * @var array $remove   Settings to remove from Visibility settings
	 */
	private static $remove = array(
		'job_manager_job_visibility_require_package_view_ph',
		'job_manager_job_visibility_require_package_browse_ph',
	);

	/**
	 * @var array $redirect_cbs Redirect checkboxes to always return TRUE on
	 */
	private static $redirect_cbs = array(
		'job_manager_job_visibility_require_package_view_redirect',
		'job_manager_job_visibility_require_package_browse_redirect',
	);

	/**
	 * WPJM_Pack_Themes_Listable constructor.
	 */
	public function __construct(){

		add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister' ), 11 );
		add_filter( 'job_manager_settings', array( $this, 'jm_settings' ), 99999 );

		add_filter( 'woocommerce_before_template_part', array( $this, 'notice_dismiss' ), 10, 4 );

		// Force all Listable types to enable Redirect (due to Listable style and format issues)
		foreach( self::$redirect_cbs as $redirect_cb ){
			add_filter( "pre_option_{$redirect_cb}", array( $this, 'return_true' ) );
		}
	}

	/**
	 * Return TRUE
	 *
	 * Used to be backwards compatible with versions of PHP that do
	 * not support anonymous functions.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function return_true(){
		return true;
	}

	/**
	 * Enqueue JS to dismiss WC notice if one of ours
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name
	 * @param $template_path
	 * @param $located
	 * @param $args
	 */
	public function notice_dismiss( $template_name, $template_path, $located, $args ){

		if( ! array_key_exists( 'messages', $args ) || strpos( $template_name, 'notices/' ) === FALSE ){
			return;
		}

		foreach( (array) $args['messages'] as $message ){

			if( strpos( $message, 'jmpack-woonotice' ) !== FALSE ){
				wp_enqueue_script( 'jmpack-listable' );
				return;
			}

		}

	}

	/**
	 * Customize Settings Required for Listable
	 *
	 * Listable uses a bunch of different hacks and styles that make it very difficult to integrate
	 * displaying package selection on listings page, etc. Because of this, we removed the placeholder
	 * setting/feature, and enable the redirect setting, as well as disable users from disabling the
	 * setting.  Maybe in a later release I will work out problems with Listable, but for now, this
	 * is how it has to be.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function jm_settings( $settings ){

		$vis_settings = $settings['job_visibility'][1];

		foreach( (array) $vis_settings as $vis_index => $vis_setting ){

			// Remove placeholders
			if( in_array( $vis_setting['name'], self::$remove, false ) ){
				unset( $settings['job_visibility'][1][$vis_index] );
			}

			// Set redirect enabled and disable allowing users to change it
			if( in_array( $vis_setting['name'], self::$redirect_cbs, false ) ){
				$settings['job_visibility'][1][$vis_index]['std'] = 1;

				// Existing attributes
				$attributes = ! empty( $settings['job_visibility'][1][$vis_index]['attributes'] ) ? (array) $settings['job_visibility'][1][$vis_index]['attributes'] : array();
				// Attributes to add
				$add_attributes = array( 'checked' => 'checked', 'class' => 'wpjmpack-redirect-disable-uncheck', 'disabled' => 'disabled' );

				$settings['job_visibility'][1][$vis_index]['attributes'] = array_merge( $attributes, $add_attributes );
			}


		}

		return $settings;
	}

	/**
	 * Register Styles
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function register(){
		wp_register_script( 'jmpack-listable', WP_Job_Manager_Packages::url( 'assets/js/listable.js' ), array( 'jquery' ), FALSE, TRUE );
		wp_register_style( 'jmpack-listable-ps', WP_Job_Manager_Packages::url( 'assets/css/listable-ps.css' ), array(), FALSE );
	}

	/**
	 * Handle Assets
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister(){
		// Deregister standard style to allow using Listable styles
		wp_deregister_style( 'jmpack-std-pkg-select' );
	}

}
