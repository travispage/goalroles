<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Integration_Listable {

	/**
	 * WP_Job_Manager_Visibility_Integration_Listable constructor.
	 */
	public function __construct() {

		//add_filter( 'jmv_output_meta_key_maps', array( $this, 'meta_key_maps' ), 11 );

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
		return $maps;
	}

}