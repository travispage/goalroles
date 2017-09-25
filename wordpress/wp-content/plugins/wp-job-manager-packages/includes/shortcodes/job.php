<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Shortcodes_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Shortcodes_Job extends WPJM_Pack_Shortcodes {

	/**
	 * WPJM_Pack_Shortcodes_Job constructor.
	 *
	 * @param $type \WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public function __construct( $type ) {

		$this->type = $type;

		//add_shortcode( 'job_manager_packages', array( $this, 'job_visibility_packages' ) );

		add_shortcode( 'job_visibility_packages', array( $this, 'job_visibility_packages' ) );
		add_shortcode( 'browse_job_packages', array( $this, 'browse_job_packages' ) );
		add_shortcode( 'apply_job_packages', array( $this, 'apply_job_packages' ) );
		add_shortcode( 'view_job_packages', array( $this, 'view_job_packages' ) );

		// URL shortcodes
		add_shortcode( 'job_packages_url', array( $this, 'any_package_url' ) );
		add_shortcode( 'browse_job_packages_url', array( $this, 'browse_package_url' ) );
		add_shortcode( 'view_job_packages_url', array( $this, 'view_package_url' ) );
		add_shortcode( 'apply_job_packages_url', array( $this, 'apply_package_url' ) );

		// Package Shortcodes
		add_shortcode( 'package_price', array( $this, 'package_price' ) );
		add_shortcode( 'view_job_package_limit', array( $this, 'view_package_limit' ) );
		add_shortcode( 'apply_package_limit', array( $this, 'apply_package_limit' ) );

		// My packages shortcode
		add_shortcode( 'job_visibility_dashboard', array( $this, 'job_visibility_dashboard' ) );

	}

	/**
	 * Dashboard to show Job Visibility Table
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	public function job_visibility_dashboard( $atts = array(), $content = '' ){

		$this->handler()->my_packages( true );

	}

	/**
	 * Return Apply Package Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function apply_package_limit( $atts = array(), $content = '' ){
		return $this->type->packages->get_package_limit( 'apply' );
	}

	/**
	 * Return Browse Job Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function browse_job_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select Browse Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose a Browse Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'browse', 'browse_job_packages' );
	}

	/**
	 * Return Job Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function job_visibility_packages( $atts = array(), $content = '' ){

		$type = ! empty( $_REQUEST['package_type'] ) ? esc_attr( $_REQUEST['package_type'] ) : 'any';

		return $this->get_form( $atts, $type, 'job_visibility_packages' );
	}

	/**
	 * Return View Job Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function view_job_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select a View Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose a View Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'view', 'view_job_packages' );
	}

	/**
	 * Return Apply Job Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function apply_job_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => sprintf( __( 'Select %s Package', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() ),
			'header_text' => sprintf( __( 'Choose %s Package', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'apply', 'apply_job_packages' );
	}

	/**
	 * Return Apply Package URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function apply_package_url( $atts, $content = '' ){

		return $this->get_permalink( 'apply', $atts, $content );
	}
}
