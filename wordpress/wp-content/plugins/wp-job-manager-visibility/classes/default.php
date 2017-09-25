<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Default {

	/**
	 * WP_Job_Manager_Visibility_Default constructor.
	 */
	public function __construct() {



	}

	/**
	 * Get Single User Default Config
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	static function get_user( $user_id ) {

		$args = array(
			'meta_query' => array(
				array(
					'key'     => 'user_id',
					'value'   => "user-{$user_id}",
					'compare' => 'IN'
				)
			)
		);

		$user_conf = WP_Job_Manager_Visibility_CPT::get_posts( 'default', $args );

		if( ! empty( $user_conf ) ) {
			$post_meta = get_post_custom( $user_conf[0]->ID );
			$user_conf = array_merge( (array) $user_conf[0], $post_meta );
		}

		return $user_conf;
	}

	/**
	 * Get a Sorted Array of Default Groups based on User ID
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $user_id
	 *
	 * @return array|mixed
	 */
	static function get_user_groups( $user_id ){

		$user_groups = WP_Job_Manager_Visibility_Groups::get_user_groups( $user_id );
		$group_conf = WP_Job_Manager_Visibility_Default::get_group( $user_groups );

		foreach( $group_conf as $conf_id => $conf ){
			// Get user_id key and remove group- tag to get just the ID
			$group_id = str_replace( 'group-', '', $conf['user_id'][0] );
			// Set the priority to the group configured priority, or 10 if there isn't one
			$group_conf[ $conf_id ]['priority'] = array( isset( $user_groups[$group_id]['priority'][0] ) ? $user_groups[$group_id]['priority'][0] : 10 );
			// Unserialize placeholders if it exists
			$group_conf[ $conf_id ]['placeholders'] = isset( $conf[ 'placeholders' ][ 0 ] ) ? maybe_unserialize( $conf[ 'placeholders' ][ 0 ] ) : array();
		}

		uasort( $group_conf, array( "WP_Job_Manager_Visibility_Default", "usort_float") );

		return $group_conf;
	}

	/**
	 * Get Group(s) Default Configs
	 *
	 * You can pass a single post ID to get the group's config, or an array of post ID's to get configs from
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $group_ids
	 *
	 * @return array|mixed
	 */
	static function get_group( $group_ids ) {

		if ( empty( $group_ids ) ) return array();

		$meta_value = ! is_array( $group_ids ) ? "group-{$group_ids}" : array();

		if( is_array( $group_ids ) && ! empty( $group_ids ) ){
			foreach( array_keys( $group_ids ) as $group_id ){
				$meta_value[] = "group-{$group_id}";
			}
		}

		$args = array(
			'meta_query' => array(
				array(
					'key'     => 'user_id',
					'value'   => $meta_value,
					'compare' => 'IN'
				)
			)
		);

		$group_conf = self::get_configs( $args );

		if( ! is_array( $group_ids ) ) $group_conf = array_pop( $group_conf );

		return $group_conf;
	}

	/**
	 * Reorder decimal integers lowest to highest
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	static function usort_float( $a, $b ) {

		$result = $a['priority'][0] < $b['priority'][0] ? - 1 : ( $a['priority'][0] === $b['priority'][0] ? 0 : 1 );

		return $result;

	}

	/**
	 * Get All Default Configs
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	static function get_configs( $args = array() ) {

		$posts = WP_Job_Manager_Visibility_CPT::get_posts( 'default', $args );

		if ( empty( $posts ) ) return array();

		$the_posts = array();

		foreach ( $posts as $post ) {

			$post_meta = get_post_custom( $post->ID );

			$the_posts[ $post->ID ][ 'ID' ]      = $post->ID;
			$the_posts[ $post->ID ][ 'post_id' ] = $post->ID;
			$the_posts[ $post->ID ][ 'title' ]   = $post->post_title;
			$the_posts[ $post->ID ][ 'status' ]  = $post->post_status;

			$the_posts[ $post->ID ] = array_merge( $the_posts[ $post->ID ], $post_meta );
		}

		return $the_posts;

	}

	/**
	 * Get existing Users and Groups with Default Configs
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	public static function get_existing_ugs(){

		$ug_posts = WP_Job_Manager_Visibility_CPT::get_posts( 'default' );

		$ugs = array();

		foreach ( $ug_posts as $post ) {
			$user_id_meta = get_post_meta( $post->ID, 'user_id', true );
			$ug_title = $post->post_title;
			$ugs[] = ! $user_id_meta && $ug_title ? $ug_title : $user_id_meta;
		}

		return $ugs;

	}

}