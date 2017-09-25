<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_MetaBoxes_Default extends WP_Job_Manager_Visibility_Admin_MetaBoxes {


	public function construct() {

		add_action( 'edit_form_after_title', array($this, 'title_user_dropdown' ) );

	}

	function init_meta_boxes(){

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'default', 'post_type' );

		$this->meta_boxes = apply_filters( 'jmv_default_metaboxes', array(
			'visible_fields' => array(
				'id'            => 'default_visible_fields',
				'title'         => __( 'Default Visible Fields', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'visible_fields_mb'),
				'screen'        => $this->post_type,
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			),
			'placeholders' => array(
				'id'            => 'default_visibilities',
				'title'         => __( 'Default Hidden Fields', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'placeholders_mb'),
				'screen'        => $this->post_type,
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			)
		) );

		return $this->meta_boxes;

	}

	function visible_fields_mb( $post, $metabox ){

		$this->post   = $post;
		$integration  = new WP_Job_Manager_Visibility_Integration();
		$this->fields = $integration->get_all_fields();

		$selected_fields = get_post_meta( $post->ID, 'visible_fields' );
		$disabled_fields = get_post_meta( $post->ID, 'placeholders', TRUE );
		$disabled_fields = is_array( $disabled_fields ) && ! empty( $disabled_fields ) ? array_keys( $disabled_fields ) : $disabled_fields;

		$this->include_view( 'metakeys', array(
			'multiple'            => TRUE,
			'select_placeholder'  => __( 'Select a Field to Show', 'wp-job-manager-visibility' ),
			'select_name'         => 'visible_fields',
			'select_label'        => 'visible',
			'existing_selections' => $selected_fields,
			'disable_selections'  =>  $disabled_fields,
			'fields'              => $this->fields
		), $post );
	}

	function title_user_dropdown( $post ) {

		//if( ! empty( $post->post_title ) ){
		//	$ug_title = WP_Job_Manager_Visibility_CPT::get_ug_label( $post->post_title );
		//	echo "<input id=\"jmv_user_id\" name=\"post_title\" value=\"{$post->post_title}\" type=\"hidden\">";
		//	echo "<h1>{$ug_title}</h1>";
		//	return;
		//}

		$this->post   = $post;
		$this->users  = WP_Job_Manager_Visibility_Users::get_users();
		$this->groups = WP_Job_Manager_Visibility_Groups::get_groups();

		$user_groups_existing = WP_Job_Manager_Visibility_Default::get_existing_ugs();

		$this->include_view( 'users', array(
			'select_name' => 'post_title',
			'existing_selections' => is_object( $post ) && isset( $post->post_title ) ? $post->post_title : 0,
			'disable_selections' => $user_groups_existing,
			'user_prepend'  => 'user-',
			'group_prepend' => 'group-',
		), $post );
	}

	function scripts(){

		wp_enqueue_script( 'jmv-default-js' );

	}

	function post_meta_debug( $post_meta ){

		if ( isset( $post_meta[ 'visible_fields' ] ) && isset( $post_meta[ 'visible_fields' ][ 0 ] ) ) {
			echo "<h3>Visible Fields:</h3>";
			$this->dump_array( maybe_unserialize( $post_meta[ 'visible_fields' ][ 0 ] ) );
			echo "<hr />";
		}

		if ( isset( $post_meta[ 'placeholders' ] ) && isset( $post_meta[ 'placeholders' ][ 0 ] ) ) {
			echo "<h3>Hidden Fields:</h3>";
			$this->dump_array( maybe_unserialize( $post_meta[ 'placeholders' ][ 0 ] ) );
			echo "<hr />";
		}

	}
}