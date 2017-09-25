<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Table {

	private $posts;

	/**
	 * WP_Job_Manager_Visibility_Admin_Table constructor.
	 *
	 * @param $post
	 */
	public function __construct( $post = null ) {

		$post_id = $post && is_object( $post ) ? $post->ID : $post;
		$this->posts = WP_Job_Manager_Visibility_Admin_Post::get( $post_id );

	}

	function getExistingUsers(){

		if( ! $this->posts ) $this->posts = WP_Job_Manager_Visibility_Admin_Post::get();

		$users = array();

		foreach( $this->posts as $post_key => $config ){
			$users[] = isset( $config[ 'user_id' ] ) ? $config[ 'user_id' ] : $config['user'];
		}

		return $users;

	}

	/**
	 * @return array
	 */
	public function getPosts() {

		return $this->posts;
	}

	function output_table(){

		include( 'views/table.php' );

	}

}