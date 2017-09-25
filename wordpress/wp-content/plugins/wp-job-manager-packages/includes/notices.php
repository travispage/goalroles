<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Notices
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Notices {

	/**
	 * WPJM_Pack_Notices constructor.
	 */
	public function __construct() {

		// If WooCommerce not available, use our own internal notices, otherwise use WooCommerce
		if( ! function_exists( 'wc_add_notice' ) ){
			add_action( 'get_template_part_content', array( $this, 'check' ), 1, 2 );
		}

		$job_notice_location = apply_filters( 'job_manager_packages_notice_single_job_listing_output_location', 'single_job_listing_start' );
		$resume_notice_location = apply_filters( 'job_manager_packages_notice_single_resume_listing_output_location', 'single_resume_start' );


		if( ! empty( $job_notice_location ) ){
			add_action( $job_notice_location, array( $this, 'check_notice_output' ) );
		}

		if( ! empty( $resume_notice_location ) ){
			add_action( $resume_notice_location, array( $this, 'check_notice_output' ) );
		}
	}

	/**
	 * Check Notice Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function check_notice_output(){

		if( function_exists( 'wc_print_notices' ) ){
			wc_print_notices();
			return;
		}

		$this->check_internal();
	}

	/**
	 * Check Internal Notice Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $part
	 * @param $name
	 */
	public function check( $part, $name ){

		if( $name !== 'page' || function_exists( 'wc_add_notice' ) ){
			return;
		}

		$this->check_internal();
	}

	/**
	 * Check Internal Notices
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function check_internal(){

		$user_id      = get_current_user_id();
		$user_notices = get_transient( "jmpack_notice_{$user_id}" );

		if( $user_notices === FALSE ){
			return;
		}

		if( $user_notices instanceof WPJM_Pack_Notice ){
			$user_notices->show();
			$user_notices->remove();
		}

	}
}
