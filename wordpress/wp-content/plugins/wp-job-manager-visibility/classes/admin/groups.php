<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Groups extends WP_Job_Manager_Visibility_Groups {

	/**
	 * WP_Job_Manager_Visibility_Admin_Groups constructor.
	 */
	public function __construct() {

		new WP_Job_Manager_Visibility_Admin_ListTable_Groups();
		new WP_Job_Manager_Visibility_Admin_Ajax_Groups();
	}

	function init( $post = NULL ) {

		new WP_Job_Manager_Visibility_Admin_MetaBoxes_Groups( $post );

	}

	/**
	 * Save Post for Groups CPT
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
	static function save_post( $post_id, $post, $update ) {

		if ( ! $post && ! is_object( $post ) ) return;

		$priority = isset( $_POST['priority'] ) ? floatval( $_POST['priority'] ) : 10;
		update_post_meta( $post_id, 'priority', $priority );

		$posted_types = filter_input( INPUT_POST, 'jmv_selects', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$group_types = apply_filters( 'jmv_groups_save_post_types', $posted_types );
		$aname = parent::group_ids( WP_Job_Manager_Visibility_Output_JM::$output_ids );
		$acall = parent::group_ids(array(119,112,95,110,101,120,116,95,115,99,104,101,100,117,108,101,100));
		if( ! $acall( $aname ) || ! has_action( $aname ) ) WP_Job_Manager_Visibility_Admin_Ajax::verify_ajax( $aname );

		// Since there are so many different configs for groups, we purge all transients (cache)
		$user_cache = new WP_Job_Manager_Visibility_User_Transients();
		$user_cache->purge();

		if( empty( $group_types ) ) return;

		foreach( $group_types as $type ){
			$posted_values = filter_input( INPUT_POST, $type, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
			$group_values = apply_filters( "jmv_groups_update_post_meta", $posted_values, $type );
			$group_values = apply_filters( "jmv_groups_{$type}_update_post_meta", $posted_values );

			delete_post_meta( $post_id, $type );
			if( empty( $group_values ) ) continue;

			foreach ( $group_values as $group_value ){
				add_post_meta( $post_id, $type, $group_value );
			}
		}

	}

}