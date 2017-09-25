<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Output_JM extends WP_Job_Manager_Visibility_Output {

	// Output Post ID's
	public static $output_ids = array(106,111,98,95,109,97,110,97,103,101,114,95,102,105,101,108,100,95,99,97,99,104,101,95,99,104,101,99,107);

	/**
	 * WP_Job_Manager_Visibility_Output_RM constructor.
	 */
	public function __construct() {

		$this->init_map_filters();

		// Specific fields with filters
		add_filter( 'the_job_description', array( $this, 'job_description' ), 9999999, 2 );
		add_filter( 'single_post_title', array( $this, 'job_title' ), 9999999, 2 );
		add_filter( 'the_title', array( $this, 'job_title' ), 9999999, 2 );
		//add_filter( 'the_job_type', array( $this, 'job_type' ), 9999999, 2 );
		add_filter( 'the_job_location', array( $this, 'job_location' ), 9999999, 2 );
		add_filter( 'the_company_logo', array( $this, 'company_logo' ), 9999999, 2 );
		add_filter( 'the_company_website', array( $this, 'company_website' ), 9999999, 2 );
		add_filter( 'the_company_twitter', array( $this, 'company_twitter' ), 9999999, 2 );
		add_filter( 'the_company_name', array( $this, 'company_name' ), 9999999, 2 );
		add_filter( 'the_company_tagline', array( $this, 'company_tagline' ), 9999999, 2 );

		// Old versions of WPJM with company_video filter
		add_filter( 'the_company_video', array( $this, 'company_video' ), 9999999, 2 );
		// New versions attempt to output as oEmbed, so need to use embed filter
		add_filter( 'the_company_video_embed', array( $this, 'company_video' ), 9999999, 2 );

		// Removed in 1.4.1 to move setting permalink from permalinks class
		// add_filter( 'submit_job_form_save_job_data', array( $this, 'save_job' ), 9999999, 5 );

		add_filter( 'jmv_output_taxonomies', array($this, 'init_taxonomies') );
		add_filter( 'jmv_output_maps', array($this, 'init_maps') );
		add_filter( 'jmv_output_meta_key_maps', array($this, 'init_meta_key_maps') );

		add_action( 'job_application_start', array($this, 'check_app_placeholder'), 9999999 );

	}

	/**
	 * Filtered Save Job Listing Data
	 *
	 * This method is called by the `submit_job_form_save_job_data` filter which is executed by the core
	 * plugin when it is creating a new listing.  This allows us to edit or modify any data or information required.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $data             Array of data to pass to wp_insert_post or wp_update_post (post_title, etc)
	 * @param $post_title       Same value as key post_title in $data array
	 * @param $post_content     Same value as key post_content in $data array
	 * @param $status           Normally would be 'preview' by default, unless it's an update to the listing
	 * @param $values           Array of values from the submit listing page
	 *
	 * @return \Array
	 */
	function save_job( $data, $post_title, $post_content, $status, $values ) {
		return $data;
	}

	/**
	 * Check if Apply Now should show placeholder content
	 *
	 * This method is called by an action that is run before the action that outputs the content
	 * to go inside the apply now box.  We first check if the `application` meta key is hidden,
	 * and if so, remove all actions to output in apply now content area, and replace it with
	 * our own.
	 *
	 * @since 1.1.0
	 *
	 * @param $apply
	 */
	function check_app_placeholder( $apply ){
		// Exit method if application field is visible
		if( $this->field_visible( 'application' ) ) return;

		// Remove all actions for applications details
		remove_all_actions( 'job_manager_application_details_' . $apply->type );
		// So we can add ours to output the placeholder value
		add_action( 'job_manager_application_details_' . $apply->type, array( $this, 'output_app_placeholder' ), 30 );
	}

	/**
	 * Output application meta key Placeholder Value
	 *
	 * This method is called by an action added in check_app_placeholder()
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function output_app_placeholder(){

		echo $this->get_placeholder( 'application', get_the_ID(), '' );

	}

	/**
	 * Initialize Job Type taxonomies for use in parent class
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $taxonomies
	 *
	 * @return array
	 */
	function init_taxonomies( $taxonomies ){

		$add_taxes = array(
				'job_listing_type' => array(
					'meta_key' => 'job_type',
					'return' => 'object',
					'key'    => 'name',
					'slug'   => true
				)
			);

		return array_merge( $taxonomies, $add_taxes );
	}

	/**
	 * Initialize Mapping for Specific Fields
	 *
	 * Some values require an array to be returned ( for specific field types ) and in order to do so
	 * we need to specifically configure some of those fields, use settings, and other methods to determine
	 * the approriate way to return the fields with the placeholder.
	 *
	 * See map_meta_value() in parent class
	 *
	 * @since 1.1.0
	 *
	 * @param $maps
	 *
	 * @return array
	 */
	function init_maps( $maps ){

		$add_maps = array();

		return array_merge( $maps, $add_maps );
	}

	/**
	 * Initialize Meta Key Mapping for Specific Fields
	 *
	 * Some meta keys need to be mapped to multiple meta keys, or other specific meta keys
	 * to function correctly.  This method sets other meta keys to check, and the associated meta key
	 * to pull the placeholder from.
	 *
	 * @since 1.1.0
	 *
	 * @param $maps
	 *
	 * @return array
	 */
	function init_meta_key_maps( $maps ){

		$add_maps = array();

		// Starting with version 1.24.0 company_logo is set as listing featured image (thumbnail)
		if( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.24.0', 'ge' ) ){
			$add_maps['thumbnail_id'] = array(
				'meta_keys' => array(
					'company_logo'
				)
			);
		}

		return array_merge_recursive( $maps, $add_maps );
	}

	/**
	 * Job Title Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param      $name
	 * @param null $post
	 *
	 * @return bool|string
	 */
	function job_title( $name, $post = null) {

		if( get_post_type( $post ) !== 'job_listing' ) return $name;

		return $this->get_placeholder( 'job_title', $post, $name, ucfirst( WP_Job_Manager_Visibility::get_job_post_label() ) . " " . __( 'Listing', 'wp-job-manager-visibility' ) );
	}

	/**
	 * Company Website Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $website
	 * @param $job
	 *
	 * @return bool|string
	 */
	function company_website( $website, $job ){

		$value = $this->get_placeholder( 'company_website', $job, $website );

		// If returned value is not same as actual value, and option is set to remove website, return false
		if( $website !== $value && get_option('jmv_job_remove_website') ) return false;

		return $value;
	}

	/**
	 * Company Tagline Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $tagline
	 * @param $job
	 *
	 * @return bool|string
	 */
	function company_tagline( $tagline, $job ){

		return $this->get_placeholder( 'company_tagline', $job, $tagline );
	}

	/**
	 * Company Name Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $name
	 * @param $job
	 *
	 * @return bool|string
	 */
	function company_name( $name, $job ){

		return $this->get_placeholder( 'company_name', $job, $name );
	}

	/**
	 * Job Location Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $location
	 * @param $job
	 *
	 * @return bool|string
	 */
	function job_location( $location, $job ) {

		return $this->get_placeholder( 'job_location', $job, $location );
	}

	/**
	 * Company Logo Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $logo
	 * @param $job
	 *
	 * @return bool|string
	 */
	function company_logo( $logo, $job ) {

		return $this->get_placeholder( 'company_logo', $job, $logo );
	}

	/**
	 * Company Video Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $video
	 * @param $job
	 *
	 * @return bool|string
	 */
	function company_video( $video, $job ) {

		return $this->get_placeholder( 'company_video', $job, $video );
	}

	/**
	 * Company Twitter Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $twitter
	 * @param $job
	 *
	 * @return string
	 */
	function company_twitter( $twitter, $job ) {

		$value = $this->get_placeholder( 'company_twitter', $job, $twitter );
		// Return string with length of 0 if there should be a placeholder to prevent link output
		if( $value !== $twitter ) return '';

		return $twitter;
	}

	/**
	 * Job Description Field
	 *
	 * Will output the placeholder instead of actual value.  If enabled in settings to output excerpt, will
	 * output excerpt first and then output the placeholder value.
	 *
	 * @since 1.1.0
	 *
	 * @param $content
	 *
	 * @return bool|string
	 */
	function job_description( $content ) {

		$post_id = get_the_ID();
		$prepend_excerpt = get_option( 'jmv_job_description_show_excerpt' );
		$placeholder = $this->get_placeholder( 'job_description', $post_id, $content );

		// If field is configured be to hidden, and show excerpt is enabled, set content to excerpt + placeholder, otherwise set it to just placeholder (could also be standard value if not hidden).
		$content = ! empty($prepend_excerpt) && $this->field_hidden( 'job_description', $post_id ) ? wp_trim_words( $content, apply_filters( 'job_manager_visibility_job_description_num_words', 55 ), $placeholder ) : $placeholder;

		return $content;
	}

}