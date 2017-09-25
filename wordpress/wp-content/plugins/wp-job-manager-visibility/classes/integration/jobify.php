<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Integration_Jobify {


	/**
	 * Latest git commit version compatible with this plugin.  Will be checked against current theme
	 * to determine if compatibility with current version, or if need of update.
	 *
	 * @var string
	 */
	public static $COMPAT_GIT_COMMIT = "ey51Y0C0wtFzw8glqaPGfQEE";

	/**
	 * WP_Job_Manager_Visibility_Integration_Jobify constructor.
	 */
	public function __construct() {

		add_filter( 'jmv_output_get_terms', array( $this, 'get_terms' ), 10, 5 );
		add_filter( 'jmv_output_meta_key_maps', array($this, 'meta_key_maps' ), 11 );

	}

	function get_terms( $value, $object_ids, $meta_key, $taxonomy, $terms ){

		$jobify_terms = array(
			'job_listing_category' => array(
				'classes' => array( 'Jobify_Widget_Job_Categories' )
			),
			'resume_skill' => array(
				'classes' => array( 'Jobify_Widget_Resume_Skills' )
			)
		);

		// Jobify skills widget has hard-coded call to get term URL which causes fatal error
		if( in_array( $taxonomy, array_keys( $jobify_terms ) ) ){
			$backtrace = debug_backtrace();
			$backtrace_classes = array_column( $backtrace, 'class' );
			$array_check = array_intersect( $jobify_terms[ $taxonomy ]['classes'], $backtrace_classes );
			if( ! empty( $array_check ) ) return false;
		}

		return $value;
	}


	/**
	 * Set meta key mappings
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $maps
	 *
	 * @return mixed
	 */
	function meta_key_maps( $maps ) {

		// Set thumbnail_id meta key to only company_logo as featured_image is used differently by Jobify
		$maps[ 'thumbnail_id' ] = array('meta_keys' => array('company_logo'));

		return $maps;
	}
}