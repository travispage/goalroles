<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Users {

	/**
	 * WP_Job_Manager_Visibility_Users constructor.
	 */
	public function __construct() { }

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	static function get_users(){

		$users = get_users( array('fields' => array('display_name', 'ID', 'user_login', 'user_nicename', 'user_email')) );

		return $users;
	}

	/**
	 * Get users that already have default config
	 *
	 * This method is called when outputting the dropdown box to select users,
	 * so we can disable those fields to prevent users from duplicating config
	 * for the same user or group.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_users_with_config( $type = 'default' ) {

		$user_posts = WP_Job_Manager_Visibility_CPT::get_posts( $type );

		$users = array();

		foreach ( $user_posts as $post ) {
			$ug_string = $post->post_title;
			$users[] = isset( $config[ 'user_id' ] ) ? $config[ 'user_id' ] : $config[ 'user' ];
		}

		return $users;

	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $user
	 *
	 * @return bool
	 */
	static function is_user_string( $user ){

		if( substr( $user, 0, 5) == "user-" ) return true;

		return false;

	}

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $user
	 *
	 * @return string
	 */
	static function get_display_label( $user ){

		// Make sure to strip group- if passed
		$user_id = substr( $user, 0, 5 ) == "user-" ? substr( $user, 5 ) : $user;

		$user_data = get_userdata( $user_id );
		if ( $user_data && is_object( $user_data ) ) return $user_data->display_name;

		return $user;
	}
}