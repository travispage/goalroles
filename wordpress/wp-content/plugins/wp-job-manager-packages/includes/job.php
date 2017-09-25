<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Job {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;
	/**
	 * @var \WPJM_Pack_WC_Job
	 */
	public $wc;
	/**
	 * @var \WPJM_Pack_WC_Subscriptions_Job
	 */
	public $wc_sub;
	/**
	 * @var \WPJM_Pack_Admin_WC_Job
	 */
	public $wca;
	/**
	 * @var \WPJM_Pack_WC_Job
	 */
	public $handler;
	/**
	 * @var \WPJM_Pack_Admin_Job
	 */
	public $admin;
	/**
	 * @var \WPJM_Pack_Integration_Job
	 */
	public $integration;
	/**
	 * @var \WPJM_Pack_Packages_Job
	 */
	public $packages;
	/**
	 * @var string
	 */
	public $package_type = 'job_visibility_package';
	/**
	 * @var string
	 */
	public $sub_type = 'job_visibility_subscription';
	/**
	 * @var string
	 */
	public $post_type = 'job_listing';
	/**
	 * @var string
	 */
	public $slug = 'job';

	/**
	 * WPJM_Pack_Job constructor.
	 *
	 * @param $core \WP_Job_Manager_Packages
	 */
	public function __construct( $core ) {

		$this->core = $core;

		if( is_admin() ){
			$this->admin = new WPJM_Pack_Admin_Job( $this );
		}

		// WooCommerce Integration
		if( class_exists( 'WooCommerce' ) ){
			// WooCommerce WC_Product_Job_Visibility_Package
			$core::include_file( 'wc/wcp-job-visibility-package' );

			// Initialize frontend and admin (this will init and set $this->wca if admin)
			$this->wc = new WPJM_Pack_WC_Job( $this );

			if( class_exists( 'WC_Subscriptions' ) ){
				$core::include_file( 'wc/wcp-job-visibility-subscription' );
				$this->wc_sub = new WPJM_Pack_WC_Subscriptions_Job( $this );
			}

			$this->handler = $this->wc;
		}

		$this->form = new WPJM_Pack_Form( $this );
		$this->packages = new WPJM_Pack_Packages_Job( $this );
		$this->integration = new WPJM_Pack_Integration_Job( $this );
		$this->shortcodes = new WPJM_Pack_Shortcodes_Job( $this );
	}

	/**
	 * @return \WP_Job_Manager_Packages
	 */
	public function core(){

		return $this->core;
	}

	/**
	 * @return \WPJM_Pack_WC_Job
	 */
	public function wc(){

		return $this->wc;
	}

	/**
	 * @return \WPJM_Pack_Admin_WC_Job
	 */
	public function wca(){

		return $this->wca;
	}

	/**
	 * @return \WPJM_Pack_Admin_Job
	 */
	public function admin(){

		return $this->admin;
	}

	/**
	 * @return \WPJM_Pack_Integration_Job
	 */
	public function integration(){

		return $this->integration;
	}
}
