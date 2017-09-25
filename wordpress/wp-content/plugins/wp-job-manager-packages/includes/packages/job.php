<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Packages_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Packages_Job extends WPJM_Pack_Packages {

	/**
	 * Get Package Values to Insert
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
			'allow_apply' => $package->allow_job_apply == 'yes' ? 1 : 0,
			'allow_browse' => $package->allow_job_browse == 'yes' ? 1 : 0,
			'allow_view' => $package->allow_job_view == 'yes' ? 1 : 0,
			'view_limit' => $package->view_job_limit,
			'apply_limit' => $package->apply_job_limit,
			'package_type'     =>  'job_listing'
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
				'limit' => false,
			    'color' => get_option( 'job_manager_job_visibility_sui_browse_color', 'grey' ),
			    'icon' => 'browser',
			    'label' => __( 'Browse', 'wp-job-manager-packages' )
			),
			'view' => array(
				'limit' => true,
			    'color' => get_option( 'job_manager_job_visibility_sui_view_color', 'teal' ),
				'icon' => 'unhide',
				'label' => __( 'View', 'wp-job-manager-packages' )
			),
		    'apply' => array(
		    	'limit' => true,
			    'color' => get_option( 'job_manager_job_visibility_sui_apply_color', 'blue' ),
			    'icon' => 'send',
			    'label' => job_manager_packages_get_apply_label()
		    ),
		);

		// Set apply verb (if apply is used)
		if( strtolower( $types['apply']['label'] ) === 'apply' ){
			$types['apply']['verb'] = __( 'apply to', 'wp-job-manager-packages' );
		}

		return apply_filters( 'job_manager_packages_job_get_package_types', $types );
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
	 * @param $product \WC_Product_Job_Visibility_Package
	 */
	public function output_selection_summary( $product ){

		$multiple = FALSE;
		printf( __( '%s to ', 'wp-job-manager-packages' ), $product->get_price_html() );

		if( $product->allow_enabled( 'view' ) ){
			$view_limit = $product->get_limit( 'view' );
			printf( _n( 'view %s single listing', 'view %s single listings', $view_limit, 'wp-job-manager-packages' ) . ' ', $view_limit ? $view_limit : __( 'unlimited', 'wp-job-manager-packages' ) );
			$multiple = TRUE;
		}

		if( $product->allow_enabled( 'apply' ) ){

			$apply_limit = $product->get_limit( 'apply' );
			$apply_verb  = $this->get_package_type( 'apply', 'verb', true );

			if( $multiple ){
				echo apply_filters( 'job_manager_packages_job_package_selection_apply_comma', ', ' );
			}
			printf( _n( '%s %s listing', '%s %s listings', $apply_limit, 'wp-job-manager-packages' ) . ' ', $apply_verb, $apply_limit ? $apply_limit : __( 'unlimited', 'wp-job-manager-packages' ) );
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
