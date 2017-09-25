<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_WC_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC_Job extends WPJM_Pack_WC {

	/**
	 * Initialize/Construct
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function init(){

		add_action( 'woocommerce_job_visibility_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		add_filter( 'job_manager_packages_my_packages_job_table_classes', array( $this, 'table_classes' ), 10, 2 );
	}

	/**
	 * Init Configuration
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function init_config(){

		$configuration = array(
			'post_type'         => 'job_listing',
			'label_fallback'    => __( 'Job', 'wp-job-manager-packages' ),
			'slug'              => 'job',
			// If changed, must be updated in view-job-package.php
			'product_type'      => 'job_visibility_package',
			'sub_type' => 'job_visibility_subscription'
		);

		// Set object variable values
		foreach( $configuration as $key => $config ) {
			$this->$key = $config;
		}

		return $configuration;
	}

	/**
	 * Init and Return Admin Instance
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $config
	 *
	 * @return \WPJM_Pack_Admin_WC_Job
	 */
	public function admin( $config ){

		$this->type->wca = new WPJM_Pack_Admin_WC_Job( $this->type, $config );
		return $this->type->wca;
	}
}