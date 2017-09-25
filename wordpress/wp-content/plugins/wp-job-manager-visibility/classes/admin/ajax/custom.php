<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Ajax_Custom extends WP_Job_Manager_Visibility_Admin_Ajax {

	private $post_id;
	private $meta_key;
	private $placeholders;

	function init_values(){

		$this->post_id = $this->get_post_id( FILTER_SANITIZE_NUMBER_INT );
		if ( ! $this->post_id ) throw new Exception( __( 'Unable to determine post ID!', 'wp-job-manager-visibility' ) );

		// Get existing post meta config
		$this->placeholders = get_post_meta( $this->post_id, 'placeholders', TRUE );
		$this->meta_key     = $this->get_meta_key();

		if ( ! $this->meta_key ) throw new Exception( __( 'A meta key (field) is required!', 'wp-job-manager-visibility' ) );

	}

	function get_table( $post_id = null ) {

		$post_id = $this->get_post_id( FILTER_SANITIZE_NUMBER_INT, $post_id );
		$post = get_post( $post_id );

		ob_start();
		$views = new WP_Job_Manager_Visibility_Admin_Views();
		$views->placeholder_table( $post );
		$this->table = ob_get_clean();

		return $this->table;
	}

	function add(){

		$this->init_values();

		// Set array to new value
		$this->placeholders[ $this->meta_key ] = array(
			'meta_key'    => $this->meta_key,
			'placeholder' => $this->get_placeholder( FILTER_SANITIZE_FULL_SPECIAL_CHARS )
		);
		// Update post meta
		$result = update_post_meta( $this->post_id, 'placeholders', $this->placeholders );
		// Failure if false ( true = updated, integer = new post meta created )
		if( ! $result ) throw new Exception( __( 'Error updating default configuration placeholder post meta!', 'wp-job-manager-visibility' ) );

		$this->message = __( 'Default configuration updated successfully!', 'wp-job-manager-visibility' );

		$this->get_table();

		return $this->message;
	}

	function remove(){

		$this->init_values();

		if( isset( $this->placeholders[ $this->meta_key ] ) ) unset( $this->placeholders[ $this->meta_key ] );
		$result = update_post_meta( $this->post_id, 'placeholders', $this->placeholders );
		if ( ! $result ) throw new Exception( __( 'Error removing configuration!', 'wp-job-manager-visibility' ) );
		$this->message = __( 'Default configuration removed successfully!', 'wp-job-manager-visibility' );

		$this->get_table();

		return $this->message;
	}
}