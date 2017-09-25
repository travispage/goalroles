<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Integration_Listify {

	/**
	 * WP_Job_Manager_Visibility_Integration_Listify constructor.
	 */
	public function __construct() {

		add_filter( 'jmv_output_meta_key_maps', array( $this, 'meta_key_maps' ), 11 );

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
	function meta_key_maps( $maps ){

		// Set thumbnail_id meta key to only featured_image, removing company_logo, to allow use of that meta key like normal field
		$maps['thumbnail_id'] = array( 'meta_keys' => array('featured_image') );

		return $maps;
	}

}