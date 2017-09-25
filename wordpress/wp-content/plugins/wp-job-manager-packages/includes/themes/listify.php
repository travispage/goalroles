<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Themes_Listify
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Themes_Listify {

	/**
	 * WPJM_Pack_Listify constructor.
	 */
	public function __construct(){

		add_action( 'job_manager_packages_get_apply_label', array( $this, 'apply_label' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister' ), 11 );

	}

	/**
	 * Deregister Assets
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister(){

		// Deregister standard style to allow using Listify styles
		wp_deregister_style( 'jmpack-std-pkg-select' );
	}

	/**
	 * Return Custom Apply Label
	 *
	 * Listify uses the "apply" as contact, and as such, we need to return Contact instead of Apply
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $label
	 *
	 * @return string|void
	 */
	public function apply_label( $label ){
		return __( 'Contact', 'wp-job-manager-packages' );
	}
}
