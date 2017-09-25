<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Resume {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;
	/**
	 * @var \WPJM_Pack_WC_Resume
	 */
	public $wc;
	/**
	 * @var \WPJM_Pack_WC_Subscriptions_Resume
	 */
	public $wc_sub;
	/**
	 * @var \WPJM_Pack_Admin_WC_Resume
	 */
	public $wca;
	/**
	 * @var \WPJM_Pack_WC_Resume
	 */
	public $handler;
	/**
	 * @var \WPJM_Pack_Admin_Resume
	 */
	public $admin;
	/**
	 * @var \WPJM_Pack_Integration_Resume
	 */
	public $integration;
	/**
	 * @var \WPJM_Pack_Packages_Resume
	 */
	public $packages;
	/**
	 * @var string Standard product slug
	 */
	public $package_type = 'resume_visibility_package';
	/**
	 * @var string Subscription slug
	 */
	public $sub_type = 'resume_visibility_subscription';
	/**
	 * @var string Post Type
	 */
	public $post_type = 'resume';
	/**
	 * @var string Post type slug
	 */
	public $slug = 'resume';

	/**
	 * WPJM_Pack_Resume constructor.
	 *
	 * @param $core
	 */
	public function __construct( $core ){

		$this->core = $core;

		if( is_admin() ){
			$this->admin = new WPJM_Pack_Admin_Resume( $this );
		}

		// WooCommerce Integration
		if( class_exists( 'WooCommerce' ) ){

			// WooCommerce WC_Product_Resume_Visibility_Package
			$core::include_file( 'wc/wcp-resume-visibility-package' );

			// Initialize frontend and admin (this will init and set $this->wca if admin)
			$this->wc = new WPJM_Pack_WC_Resume( $this );

			if( class_exists( 'WC_Subscriptions' ) ){
				$core::include_file( 'wc/wcp-resume-visibility-subscription' );
				$this->wc_sub = new WPJM_Pack_WC_Subscriptions_Resume( $this );
			}

			$this->handler = $this->wc;
		}

		$this->form        = new WPJM_Pack_Form( $this );
		$this->packages    = new WPJM_Pack_Packages_Resume( $this );
		$this->integration = new WPJM_Pack_Integration_Resume( $this );
		$this->shortcodes  = new WPJM_Pack_Shortcodes_Resume( $this );
	}

	/**
	 * @return \WP_Job_Manager_Packages
	 */
	public function core(){

		return $this->core;
	}

	/**
	 * @return \WPJM_Pack_WC_Resume
	 */
	public function wc(){

		return $this->wc;
	}

	/**
	 * @return \WPJM_Pack_Admin_WC_Resume
	 */
	public function wca(){

		return $this->wca;
	}

	/**
	 * @return \WPJM_Pack_Admin_Resume
	 */
	public function admin(){

		return $this->admin;
	}

	/**
	 * @return \WPJM_Pack_Integration_Resume
	 */
	public function integration(){

		return $this->integration;
	}
}
