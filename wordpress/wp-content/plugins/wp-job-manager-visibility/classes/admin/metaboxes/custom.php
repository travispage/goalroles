<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_MetaBoxes_Custom extends WP_Job_Manager_Visibility_Admin_MetaBoxes {


	public function construct() {

		add_action( 'edit_form_after_title', array( $this, 'user_dropdown' ) );

	}

	function init_meta_boxes() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'custom', 'post_type' );

		$this->meta_boxes = apply_filters( 'jmv_custom_metaboxes', array(
			'job' => array(
				'id'            => 'job_visibility',
				'title'         => __( 'Custom Visibilities', 'wp-job-manager-visibility' ),
				'callback'      => array( $this, 'job_mb' ),
				'screen'        => 'job_listing',
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			),
			'job' => array(
				'id'            => 'job_visibility',
				'title'         => __( 'Custom Visibilities', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'job_mb'),
				'screen'        => 'job_listing',
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			)
		) );

		return $this->meta_boxes;

	}

	function scripts(){

		wp_enqueue_script( 'jmv-custom-js' );

	}

	function job_mb( $post, $metabox ) {

		$job      = new WP_Resume_Manager_Visibility_Integration_JM();
		$this->fields = $job->get_all_fields();
		$this->users = WP_Job_Manager_Visibility_Users::get_users();
		$this->groups = WP_Job_Manager_Visibility_Groups::get_groups();

		include_once( 'views/metabox.php' );

		$this->include_view( 'metabox', array(
			'select_name'         => 'post_title',
			'existing_selections' => is_object( $post ) && isset( $post->post_title ) ? $post->post_title : 0,
			'user_prepend'        => 'user-',
			'group_prepend'       => 'group-'
		), $post );
	}

}