<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Integration_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Integration_Job extends WPJM_Pack_Integration {

	/**
	 * Construct Hooks
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function hooks(){

		// Browse
		add_filter( 'job_manager_get_listings', array( $this, 'get_listings_query_args' ), 9999999, 2 );
		add_filter( 'job_manager_output_jobs_defaults', array( $this, 'check_show_filters' ), 9999999, 2 );

		// Apply/Contact
		add_action( 'job_application_start', array( $this, 'check_apply' ), 9999999 );

		// Our template actions for output
		add_action( 'job_manager_packages_single_job_listing', array( $this, 'single_job_listing' ) );
		add_action( 'job_manager_packages_job_listings', array( $this, 'job_listings' ) );
		add_action( 'job_manager_packages_job_listings_ajax', array( $this, 'job_listings' ) );

		// Our template actions for apply output
		add_action( 'job_manager_packages_job_application_url', array( $this, 'apply_now' ) );
		add_action( 'job_manager_packages_job_application_email', array( $this, 'apply_now' ) );
		add_action( 'job_manager_packages_job_application_form', array( $this, 'apply_now' ) );
	}

	/**
	 * Check Apply Now
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $method
	 */
	public function check_apply( $method ){

		// No need to go further if not enabled or user has permissions
		if( $this->user_can( 'apply' ) ){
			return;
		}

		if( isset( $method->type ) ){

			// Remove all other actions
			remove_all_actions( 'job_manager_application_details_' . $method->type );
			// Add ours to output placeholder
			add_action( 'job_manager_application_details_' . $method->type , array( $this, 'apply_now' ) );

		} else {

			// Type undefined, removed both URL and email actions
			remove_all_actions( 'job_manager_application_details_url' );
			remove_all_actions( 'job_manager_application_details_email' );
			// Add ours for both
			add_action( 'job_manager_application_details_url', array( $this, 'apply_now' ) );
			add_action( 'job_manager_application_details_email', array( $this, 'apply_now' ) );

		}

	}

	/**
	 * Check if Listings filters should show
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function check_show_filters( $args ){

		if( ! $this->user_can( 'browse' ) ){
			// Hide filters (return TRUE through filter to still show filters)
			$args['show_filters'] = apply_filters( 'job_manager_packages_cant_browse_show_filters', FALSE, $args );
		}

		return $args;
	}

	/**
	 * Query Args Filter
	 *
	 * This method filters the query args to short circuit when required (to return 0 results)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return mixed
	 */
	public function get_listings_query_args( $query_args, $args ){

		// Short circuit get posts through args
		if( ! $this->user_can( 'browse' ) ){
			$query_args['post__in'] = array( 0 );
			$this->force_no_results = TRUE;
		}

		return $query_args;
	}

	/**
	 * Filter core WordPress Template Include
	 *
	 * This method is required as the only filter available in core WordPress is this one, and as such, we have to
	 * filter on template files as early as possible, to return our own template file, as theme templates always
	 * take priority (this method overrides that).
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $full_template_path
	 *
	 * @return string
	 */
	public function template_include( $full_template_path ){

		$template = basename( $full_template_path );

		switch ( $template ) {

			/**
			 * Filter single-job_listing.php template file
			 *
			 * We have to filter this template file and return our own (if required), otherwise theme template files would be loaded
			 * instead, and chances are (99%) that this would bypass any integration we have to show our own template file.
			 */
			case 'single-job_listing.php':
				if( ! $this->user_can( 'view' ) ){
					// This template file uses a standard structure, but instead of calling core WordPress get_template_part() which
					// does not have a filter, it uses our custom get_job_manager_packages_template_part() to allow filtering as needed.
					$full_template_path = $this->locate_template( 'single-job_listing' );
				}

				break;

			default:
				break;
		}

		return $full_template_path;
	}

	/**
	 * Filter Core WP Job Manager Templates
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return string
	 */
	public function filter_template( $template, $template_name, $template_path ){

		if( $template_path === 'jm_packages' ){
			return $template;
		}

		switch ( $template_name ) {

			// Single job listing template file
			case 'content-single-job_listing.php':
				if( ! $this->user_can( 'view' ) ){
					// Load out custom template
					$template = $this->locate_template( 'content-single-job_listing' );
				}

				break;

			// No jobs found template file
			case 'content-no-jobs-found.php':

				/**
				 * Check if forced no jobs found
				 *
				 * To output our own template for the job listings page, we force the get listings function to return
				 * zero results, so we can then return our own custom template through the "no results found" template.
				 */
				if( $this->force_no_results && ! $this->user_can('browse' ) ){
					$template = $this->locate_template( 'job_listings' );
				}

				break;

			// No jobs found template file
			case 'application-form.php':

				if( ! $this->user_can( 'apply' ) ){
					$template = $this->locate_template( 'application-form' );
				}

				break;

			// Job Application
			case 'job-application.php':

				if( ! $this->user_can( 'apply' ) ){
					$check_template = $this->locate_template( 'job-application' );
					// If template exists, use, otherwise set back to passed $template (in case there is a theme template, but no standard one)
					$template = $check_template && file_exists( $check_template ) ? $check_template : $template;
				}

				break;

			default:
				break;
		}

		return $template;
	}

	/**
	 * Browse Job Listings Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function job_listings(){

		$default = sprintf( __( 'Please <a href="%s">select a package</a> to browse listings.', 'wp-job-manager-packages' ), '[browse_job_packages_url]' );

		$this->output_placeholder( 'browse', $default );
	}

	/**
	 * Single Job Listing Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function single_job_listing(){

		$default = sprintf( __( 'Please <a href="%s">select a package</a> to view this listing\'s details.', 'wp-job-manager-packages' ), '[view_job_packages_url]' );

		$this->output_placeholder( 'view', $default );
	}

	/**
	 * Apply Now Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function apply_now(){

		$default = sprintf( __( 'Please <a href="%1$s">select a package</a> to %2$s this listing.', 'wp-job-manager-packages' ), '[apply_job_packages_url]',  $this->type->packages->get_package_type( 'apply', 'verb', true ) );

		$this->output_placeholder( 'apply', $default );

	}

	/**
	 * Check if View requires packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_view_package(){

		return get_option( 'job_manager_job_visibility_require_package_view', FALSE );

	}

	/**
	 * Check if Browse requires packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_browse_package(){

		return get_option( 'job_manager_job_visibility_require_package_browse', FALSE );

	}

	/**
	 * Check if Apply requires packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_apply_package(){

		return get_option( 'job_manager_job_visibility_require_package_apply', FALSE );

	}
}
