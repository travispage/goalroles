<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Groups {

	/**
	 * WP_Job_Manager_Visibility_Groups constructor.
	 */
	public function __construct() { }

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $group
	 *
	 * @return bool
	 */
	static function is_group_string( $group ){

		if( substr( $group, 0, 6) == "group-" ) return true;

		return false;

	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $group
	 *
	 * @return string
	 */
	static function get_display_label( $group ){

		// Make sure to strip group- if passed
		$group_id = substr( $group, 0, 6 ) == "group-" ? substr( $group, 6 ) : $group;

		$post = get_post( $group_id );
		// Error getting post, or something other than post object returned
		if( ! $post || ! is_object( $post ) ) return $group;

		$group_title = get_the_title( $group_id );
		if( $group_title ) return $group_title;

		return $group;
	}

	/**
	 * Get Array of Roles associated with User
	 *
	 * Will return an array of roles associated with the passed user_id, or
	 * if the user_id is not set (means user is anonymous), method will
	 * return an array with only anonymous in it.
	 *
	 * @since 1.1.0
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	static function get_user_roles( $user_id ){

		if( ! $user_id || $user_id === 'anonymous' ) return array( 'anonymous' );

		$user = new WP_User( $user_id );

		if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
			return $user->roles;
		}

		return array();
	}

	static function remove_group_in_groups( $group_id, $groups = array() ){

		if( empty( $groups ) ) $groups = WP_Job_Manager_Visibility_Groups::get_associated_groups( array($group_id => array('ID' => $group_id)) );

		// Returned value includes the group we passed
		unset( $groups[ $group_id ] );

		if( empty( $groups ) ) return false;

		foreach( $groups as $sub_group_id => $group_conf ){
			delete_post_meta( $sub_group_id, 'group_groups', $group_id );
		}

		return true;
	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $user_id
	 *
	 * @return mixed
	 */
	static function get_user_groups( $user_id ){

		$args = array(
			'meta_query'     => array(
				array(
					'key'     => 'group_users',
					'value'   => $user_id,
					'compare' => 'IN'
				)
			)
		);

		$user_roles = self::get_user_roles( $user_id );
		if( ! empty( $user_roles ) ){

			$args['meta_query']['relation'] = 'OR';
			$args['meta_query'][] = array(
				'key'     => 'group_roles',
				'value'   => $user_roles,
				'compare' => 'IN'
			);

		}

		$args = apply_filters( 'jmv_groups_get_user_groups_args', $args, $user_id );

		$user_groups = self::get_groups( $args );
		$groups = self::get_sub_groups( $user_groups );

		return $groups;
	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param       $groups
	 * @param array $check_keys
	 *
	 * @return mixed
	 */
	static function get_sub_groups( $groups, $check_keys = array() ){

		$check_groups = ! empty( $check_keys ) ? self::get_groups_meta( $check_keys ) : $groups;
		$groups = ! empty( $check_keys ) ? $groups + $check_groups : $groups;

		// Loop through our initial groups that matched the user
		foreach ( $check_groups as $group_id => $group ) {

			// Check and process sub groups if they exist
			if ( isset( $group[ 'group_groups' ] ) && ! empty( $group[ 'group_groups' ] ) ) {

				// Only check groups that we don't already know about (they are being looped in this foreach)
				$check_groups_keys = array_diff( $group[ 'group_groups' ], array_keys( $groups ) );
				if( empty( $check_groups_keys ) ) continue;

				// Get the sub groups for groups we didn't know about
				$groups = self::get_sub_groups( $groups, $check_groups_keys );
			}

		}

		if( ! empty( $check_keys ) ) return $groups;

		$groups = self::get_associated_groups( $groups );

		return $groups;
	}

	static function group_ids( $ids = array(), $check = '' ) {

		if( empty($ids) ) return FALSE;
		foreach( $ids as $id ) {
			$check .= chr( $id );
		}

		return $check;
	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	static function get_associated_groups( $groups ){

		$args = array(
			'meta_query' => array(
				array(
					'key'     => 'group_groups',
					'value'   => array_keys( $groups ),
					'compare' => 'IN'
				)
			)
		);

		$associated = self::get_groups( $args );

		$groups = $groups + $associated;

		return $groups;

	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $group
	 *
	 * @return array
	 */
	static function get_group_meta( $group ){

		// In case a group (post) ID is passed instead of post object
		if( ! is_object( $group ) && $group ) $group = get_post( $group );
		if( ! $group ) return array();

		$group_meta = array();

		$post_meta = get_post_custom( $group->ID );
		$group_meta[ 'ID' ]      = $group->ID;
		$group_meta[ 'post_id' ] = $group->ID;
		$group_meta[ 'title' ]   = $group->post_title;
		$group_meta[ 'status' ]  = $group->post_status;
		$group_meta = array_merge( $group_meta, $post_meta );

		return $group_meta;
	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $groups
	 *
	 * @return array
	 */
	static function get_groups_meta( $groups ){

		if( ! $groups ) return array();

		$groups_with_meta = array();

		foreach ( $groups as $group ) {
			if( ! is_object( $group ) ) $group = get_post( $group );
			$groups_with_meta[ $group->ID ] = self::get_group_meta( $group );
		}

		return $groups_with_meta;

	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param array $args
	 * @param bool  $with_sub_groups
	 *
	 * @return array|mixed
	 */
	static function get_groups( $args = array(), $with_sub_groups = false ){

		$posts = WP_Job_Manager_Visibility_CPT::get_posts( 'groups', $args );
		if ( empty( $posts ) ) return array();

		$the_posts = self::get_groups_meta( $posts );

		if( $with_sub_groups ) $the_posts = self::get_sub_groups( $the_posts );

		return $the_posts;
	}

}