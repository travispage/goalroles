<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Shortcodes
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Shortcodes {

	/**
	 * @var \WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	protected $type;

	/**
	 * @var
	 */
	protected $form;

	/**
	 * Get Page Specific Permalink
	 *
	 * This method calls the core WP Job Manager (and Resumes) function to return the
	 * permalink of the page set in the configuration.  This allows support for Polylang
	 * and integration for future core updates.
	 *
	 * @since 1.0.0
	 *
	 * @param        $type
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function get_permalink( $type = 'any', $atts = array(), $content = '' ){

		$permalink = '#';

		$slug = $this->type->slug;
		// Function should be job_manager_get_permalink or resume_manager_get_permalink
		$function = "{$slug}_manager_get_permalink";

		if( function_exists( $function ) ){

			if( $type !== 'any' ){
				// First try our package type specific
				$permalink = $function( "{$type}_{$slug}_packages" );
			}

			// If that's not set or not valid page, use standard one
			if( empty( $permalink ) ){
				$permalink = $function( "{$slug}_visibility_packages" );

				// Add type to query args if we're not using package type specific pages
				if( $type !== 'any' ){
					$permalink = add_query_arg( array( 'package_type' => esc_attr( $type ) ), $permalink );
				}

			}
		}

		// Add specific packages show (if passed in shortcode args) to query args
		if( ! empty( $atts['packages'] ) ){
			$specific_packages = str_replace( ' ', '', $atts['packages'] );
			$permalink = add_query_arg( array( 'packages' => esc_attr( $specific_packages ) ), $permalink );
		}

		// Add listing_id query arg
		if( ! empty( $atts['listing_id'] ) ){
			$permalink = add_query_arg( array( 'listing_id' => absint( $atts['listing_id'] ) ), $permalink );
		} elseif( $listing_id = get_the_ID() ) {
			// If no listing_id passed in shortcode args, try to get it based on current page
			$permalink = add_query_arg( array( 'listing_id' => absint( $listing_id ) ), $permalink );
		}

		return $permalink;
	}

	/**
	 * Get Form Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $atts
	 * @param $type
	 * @param $shortcode
	 *
	 * @return string
	 */
	public function get_form( $atts, $type, $shortcode ){

		$atts = shortcode_atts(
			array(
				'listing_id'  => ! empty( $_REQUEST['listing_id'] ) ? absint( $_REQUEST['listing_id'] ) : false,
				'packages'    => array(),
				'button_text' => __( 'Select Package', 'wp-job-manager-packages' ),
				'header_text' => __( 'Choose a Package', 'wp-job-manager-packages' ),
				'placeholder' => $this->type->form->placeholder
			), $atts, $shortcode
		);

		if( empty( $atts['listing_id'] ) ){

			$qo = get_queried_object();

			// Check queried object if Post Type (means probably an archive page, which doesn't have a post/page ID)
			if( $qo && $qo instanceof WP_Post_Type ){
				// Set redirect atts value to redirect back to this page
				$atts['redirect'] = get_post_type_archive_link( $qo->name );
			} else {
				// Otherwise set listing_id to ID in the loop
				$atts['listing_id'] = get_the_ID();
			}

		}

		$specific_packages = ! empty( $atts['packages'] ) ? explode( ',', $atts['packages'] ) : array();

		$packages = $this->handler()->get_packages( $specific_packages, $type );

		$user = $this->type->packages->user;
		$user_packages = $user::get_useable( get_current_user_id(), $this->type->post_type, $type );

		$form = new WPJM_Pack_Form( $this->type, $type );

		return $form->get( $atts, $packages, $user_packages );
	}

	/**
	 * Package Handler Class Object
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return \WPJM_Pack_WC_Job
	 */
	public function handler(){
		return $this->type->handler;
	}

	/**
	 * Return Package Price
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function package_price( $atts = array(), $content = '' ){
		return $this->type->packages->get_package_price_html();
	}

	/**
	 * Return View Package Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function view_package_limit( $atts = array(), $content = '' ){
		return $this->type->packages->get_package_limit( 'view' );
	}

	/**
	 * Any Packages URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function any_package_url( $atts, $content = '' ){
		return $this->get_permalink( 'any', $atts, $content );
	}

	/**
	 * Return View Package URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function view_package_url( $atts, $content = '' ){
		return $this->get_permalink( 'view', $atts, $content );
	}

	/**
	 * Return Browse Package URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function browse_package_url( $atts, $content = '' ){
		return $this->get_permalink( 'browse', $atts, $content );
	}

}
