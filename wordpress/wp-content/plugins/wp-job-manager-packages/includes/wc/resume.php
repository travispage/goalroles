<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_WC_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC_Resume extends WPJM_Pack_WC {

	/**
	 * Init/Construct
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function init(){

		add_action( 'woocommerce_resume_visibility_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_filter( 'job_manager_packages_my_packages_resume_table_classes', array( $this, 'table_classes' ), 10, 2 );

	}

	/**
	 * Init Config
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function init_config(){

		$configuration = array(
			'post_type'         => 'resume',
			'label_fallback'    => __( 'Resume', 'wp-job-manager-packages' ),
			'slug'              => 'resume',
			// If changed, must be updated in view-resume-package.php
			'product_type'      => 'resume_visibility_package',
			'sub_type' => 'resume_visibility_subscription'
		);

		// Set object variable values
		foreach( $configuration as $key => $config ) {
			$this->$key = $config;
		}

		return $configuration;
	}

	/**
	 * Init and Return Admin Class Instance
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $config
	 *
	 * @return \WPJM_Pack_Admin_WC_Resume
	 */
	public function admin( $config ){

		$this->type->wca = new WPJM_Pack_Admin_WC_Resume( $this->type, $config );

		return $this->type->wca;
	}
}