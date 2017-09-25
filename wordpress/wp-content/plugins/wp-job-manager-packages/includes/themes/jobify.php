<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Themes_Jobify
 *
 * @45vSSfFGKBB6H5l6t+RJLgEE
 * @since 1.0.0
 *
 */
class WPJM_Pack_Themes_Jobify {

	/**
	 * WPJM_Pack_Themes_Jobify constructor.
	 */
	public function __construct(){

		add_action( 'wp_enqueue_scripts', array( $this, 'deregister' ), 11 );

		add_action( 'job_manager_packages_job_listings', array( $this, 'no_jobs_found_css' ) );
		add_filter( 'job_manager_packages_notice_single_job_listing_output_location', array( $this, 'notice_location' ) );

		//add_filter( 'job_manager_packages_resume_view_name_popup_data', array( $this, 'popup_customize' ) );

	}

	/**
	 * Add Custom Popover Configuration
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function popup_customize( $data ){
		$data['variation'] .= ' mini';

		return $data;
	}

	/**
	 * Return Custom Single Job Listing Notice Location
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $location
	 *
	 * @return string
	 */
	public function notice_location( $location ){
		return 'single_job_listing_info_before';
	}

	/**
	 * Handle Assets
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister(){
		// Deregister standard style to allow using Jobify styles
		//wp_deregister_style( 'jmpack-std-pkg-select' );
	}

	/**
	 * Output CSS for no_job_listings_found element
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function no_jobs_found_css(){
		echo '<style>.no_job_listings_found { padding: 0px !important; }</style>';
	}

}
