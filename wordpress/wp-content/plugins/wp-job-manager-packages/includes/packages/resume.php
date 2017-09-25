<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Packages_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Packages_Resume extends WPJM_Pack_Packages {

	/**
	 * Get Values to Insert in Meta
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $package
	 * @param array $values
	 *
	 * @return array
	 */
	public function get_insert_array( $package, array $values = array() ){

		$add_vals = array(
			'allow_browse' => $package->allow_resume_browse == 'yes' ? 1 : 0,
			'allow_view' => $package->allow_resume_view == 'yes' ? 1 : 0,
			'allow_view_name' => $package->allow_resume_view_name == 'yes' ? 1 : 0,
			'allow_contact' => $package->allow_resume_contact == 'yes' ? 1 : 0,
			'view_limit' => $package->view_resume_limit,
			'view_name_limit' => $package->view_name_resume_limit,
			'contact_limit' => $package->contact_resume_limit,
			'package_type'     =>  'resume'
		);

		return array_merge( $values, $add_vals );
	}

	/**
	 * Get Package Types Configs
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function get_package_types(){

		$types = array(
			'browse' => array(
				'limit' => FALSE,
				'color' => get_option( 'job_manager_resume_visibility_sui_browse_color', 'grey' ),
				'icon'  => 'browser',
				'label' => __( 'Browse', 'wp-job-manager-packages' )
			),
			'view'   => array(
				'limit' => TRUE,
				'color' => get_option( 'job_manager_resume_visibility_sui_view_color', 'teal' ),
				'icon'  => 'unhide',
				'label' => __( 'View', 'wp-job-manager-packages' )
			),
			'view_name'   => array(
				'limit' => TRUE,
				'color' => get_option( 'job_manager_resume_visibility_sui_view_name_color', 'olive' ),
				'icon'  => 'unhide',
				'label' => __( 'View Name', 'wp-job-manager-packages' )
			),
			'contact'  => array(
				'limit' => TRUE,
				'color' => get_option( 'job_manager_resume_visibility_sui_contact_color', 'blue' ),
				'icon'  => 'send',
				'label' => __( 'Contact', 'wp-job-manager-packages' )
			)
		);

		return apply_filters( 'job_manager_packages_resume_get_package_types', $types );
	}

	/**
	 * Output Selection Summary
	 *
	 * This method will output a custom summary of what is included in the package.  This method
	 * will only be called if the package does not have setting enabled to use short description.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $product \WC_Product_Resume_Visibility_Package
	 */
	public function output_selection_summary( $product ){

		$multiple = FALSE;
		printf( __( '%s to ', 'wp-job-manager-packages' ), $product->get_price_html() );

		if( $product->allow_enabled( 'view' ) ){
			$view_limit = $product->get_limit( 'view' );
			printf( _n( 'view %s single listing', 'view %s single listings', $view_limit, 'wp-job-manager-packages' ) . ' ', $view_limit ? $view_limit : __( 'unlimited', 'wp-job-manager-packages' ) );
			$multiple = TRUE;
		}

		if( $product->allow_enabled( 'view_name' ) ){
			$view_name_limit = $product->get_limit( 'view_name' );

			if( $multiple ){
				echo apply_filters( 'job_manager_packages_resume_package_selection_contact_comma', ', ' );
			}

			printf( _n( 'view %s single listing name', 'view %s single listing names', $view_name_limit, 'wp-job-manager-packages' ) . ' ', $view_name_limit ? $view_name_limit : __( 'unlimited', 'wp-job-manager-packages' ) );
			$multiple = TRUE;
		}

		if( $product->allow_enabled( 'contact' ) ){

			$contact_limit = $product->get_limit( 'contact' );
			$contact_verb  = $this->get_package_type( 'contact', 'verb', true );

			if( $multiple ){
				echo apply_filters( 'job_manager_packages_resume_package_selection_contact_comma', ', ' );
			}
			printf( _n( '%s %s listing', '%s %s listings', $contact_limit, 'wp-job-manager-packages' ) . ' ', $contact_verb, $contact_limit ? $contact_limit : __( 'unlimited', 'wp-job-manager-packages' ) );
			$multiple = TRUE;
		}

		if( $product->allow_enabled( 'browse' ) ){
			if( $multiple ){
				echo apply_filters( 'job_manager_packages_job_package_selection_browse_and', __( ', and ', 'wp-job-manager-packages' ) );
			}
			_e( 'browse all listings', 'wp-job-manager-packages' );
		}

	}
}
