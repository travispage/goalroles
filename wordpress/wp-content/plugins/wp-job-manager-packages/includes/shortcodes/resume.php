<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Shortcodes_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Shortcodes_Resume extends WPJM_Pack_Shortcodes {

	/**
	 * WPJM_Pack_Shortcodes_Resume constructor.
	 */
	public function __construct( $type ) {

		$this->type = $type;

		add_shortcode( 'resume_visibility_packages', array( $this, 'resume_visibility_packages' ) );
		add_shortcode( 'browse_resume_packages', array( $this, 'browse_resume_packages' ) );
		add_shortcode( 'contact_resume_packages', array( $this, 'contact_resume_packages' ) );
		add_shortcode( 'view_resume_packages', array( $this, 'view_resume_packages' ) );
		add_shortcode( 'view_name_resume_packages', array( $this, 'view_name_resume_packages' ) );

		// URL shortcodes
		add_shortcode( 'resume_packages_url', array( $this, 'any_package_url' ) );
		add_shortcode( 'browse_resume_packages_url', array( $this, 'browse_package_url' ) );
		add_shortcode( 'view_resume_packages_url', array( $this, 'view_package_url' ) );
		add_shortcode( 'view_name_resume_packages_url', array( $this, 'view_name_package_url' ) );
		add_shortcode( 'contact_resume_packages_url', array( $this, 'contact_package_url' ) );

		// Package Shortcodes
		add_shortcode( 'package_price', array( $this, 'package_price' ) );
		add_shortcode( 'view_resume_package_limit', array( $this, 'view_package_limit' ) );
		add_shortcode( 'view_name_package_limit', array( $this, 'view_name_package_limit' ) );
		add_shortcode( 'contact_package_limit', array( $this, 'contact_package_limit' ) );

		// My packages shortcode
		add_shortcode( 'resume_visibility_dashboard', array( $this, 'resume_visibility_dashboard' ) );
	}

	/**
	 * Resume Dashboard to show Resume Visibility Table
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	public function resume_visibility_dashboard( $atts = array(), $content = '' ) {
		$this->handler()->my_packages( true );
	}

	/**
	 * Return Browse Resume Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function browse_resume_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select Browse Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose a Browse Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'browse', 'browse_resume_packages' );
	}

	/**
	 * Return Resume Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function resume_visibility_packages( $atts = array(), $content = '' ){

		$type = ! empty( $_REQUEST['package_type'] ) ? esc_attr( $_REQUEST['package_type'] ) : 'any';

		return $this->get_form( $atts, $type, 'resume_visibility_packages' );
	}

	/**
	 * Return View Resume Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function view_resume_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select a View Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose a View Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'view', 'view_resume_packages' );
	}

	/**
	 * Return View Name Resume Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function view_name_resume_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select a View Name Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose a View Name Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'view_name', 'view_name_resume_packages' );
	}

	/**
	 * Return Contact Resume Packages Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function contact_resume_packages( $atts = array(), $content = '' ){

		$type_atts = array(
			'button_text' => __( 'Select Contact Package', 'wp-job-manager-packages' ),
			'header_text' => __( 'Choose Contact Package', 'wp-job-manager-packages' ),
		);

		// Merge shortcode atts with our custom ones
		$atts = array_merge( $type_atts, (array) $atts );

		return $this->get_form( $atts, 'contact', 'contact_resume_packages' );
	}

	/**
	 * Return View Name Package URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function view_name_package_url( $atts, $content = '' ){

		return $this->get_permalink( 'view_name', $atts, $content );
	}

	/**
	 * Return Contact Package URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function contact_package_url( $atts, $content = '' ){

		return $this->get_permalink( 'contact', $atts, $content );
	}

	/**
	 * Return Contact Package Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function contact_package_limit( $atts = array(), $content = '' ){

		return $this->type->packages->get_package_limit( 'contact' );
	}

	/**
	 * Return View Name Package Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function view_name_package_limit( $atts = array(), $content = '' ){

		return $this->type->packages->get_package_limit( 'view_name' );
	}
}
