<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_MetaBoxes_Groups extends WP_Job_Manager_Visibility_Admin_MetaBoxes {

	/**
	 *
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	public function construct() {

		add_filter( 'jmv_view_users_args', array( $this, 'users_args' ), 10, 2 );

	}

	function init_meta_boxes(){

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'groups', 'post_type' );

		$this->meta_boxes = apply_filters( 'jmv_groups_metaboxes', array(
			'users'  => array(
				'id'            => 'users_mb',
				'title'         => __( 'Users', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'users_mb'),
				'screen'        => $this->post_type,
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			),
			'groups' => array(
				'id'            => 'groups_mb',
				'title'         => __( 'Groups', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'groups_mb'),
				'screen'        => $this->post_type,
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			),
			'roles' => array(
				'id'            => 'roles_mb',
				'title'         => __( 'Roles', 'wp-job-manager-visibility' ),
				'callback'      => array( $this, 'roles_mb' ),
				'screen'        => $this->post_type,
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => NULL
			),
			'priority' => array(
				'id'            => 'priority_mb',
				'title'         => __( 'Group Priority', 'wp-job-manager-visibility' ),
				'callback'      => array($this, 'priority_mb'),
				'screen'        => $this->post_type,
				'context'       => 'side',
				'priority'      => 'high',
				'callback_args' => NULL
			),
		) );

		return $this->meta_boxes;

	}

	function priority_mb( $post, $metabox ) {

		$this->post  = $post;

		$priority = get_post_meta( $post->ID, 'priority', TRUE );

		$this->include_view( 'priority', array(
			'priority' => $priority
		), $post );

	}

	function roles_mb( $post, $metabox ){

		$this->post   = $post;
		$this->roles  = WP_Job_Manager_Visibility_Roles::get_roles();

		$selected_roles = get_post_meta( $post->ID, 'group_roles' );

		$this->include_view( 'roles', array(
			'multiple'            => TRUE,
			'select_placeholder'  => __( 'Select a Role', 'wp-job-manager-visibility' ),
			'select_name'         => 'group_roles',
			'existing_selections' => $selected_roles,
		), $post );

	}

	/**
	 * Output Users MetaBox
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post
	 * @param $metabox
	 */
	function users_mb( $post, $metabox ){

		$this->post   = $post;
		$this->users  = WP_Job_Manager_Visibility_Users::get_users();

		$selected_users = get_post_meta( $post->ID, 'group_users' );

		$this->include_view( 'users', array(
			'exclude_groups' => true,
			'multiple'  => true,
			'select_placeholder' => __( 'Select a User', 'wp-job-manager-visibility' ),
			'select_name' => 'group_users',
			'existing_selections' => $selected_users
		), $post );
	}

	/**
	 * Output Groups MetaBox
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post
	 * @param $metabox
	 */
	function groups_mb( $post, $metabox ){

		$this->post  = $post;
		$this->groups = WP_Job_Manager_Visibility_Groups::get_groups();

		$selected_groups = get_post_meta( $post->ID, 'group_groups' );

		$this->include_view( 'users', array(
			'exclude_users' => TRUE,
			'multiple' => true,
			'select_placeholder' => __( 'Select a Group', 'wp-job-manager-visibility' ),
			'select_name' => 'group_groups',
			'existing_selections' => $selected_groups,
			'disable_selections' => $post->ID
		), $post );
	}

	/**
	 * Set/Filter Users View Args
	 *
	 * Filter is called before displaying users view to provide arguments available
	 * in the users view file.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $args
	 * @param $post
	 *
	 * @return mixed
	 */
	function users_args( $args, $post ){

		//$args['select_name'] = 'post_title';
		//$args['single_selected'] = is_object( $post ) && isset( $post->post_title ) ? $post->post_title : 0;
		return $args;

	}

	/**
	 * Enqueue Group MetaBox Scripts
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function scripts(){

		wp_enqueue_script( 'jmv-groups-js' );

	}

	function post_meta_debug( $post_meta ) {

		foreach( $this->meta_boxes as $type => $config ){

			if ( isset( $post_meta[ "group_{$type}" ] ) && isset( $post_meta[ "group_{$type}" ][ 0 ] ) ) {
				echo "<h3>" . ucfirst( $type ) . "</h3>";
				$this->dump_array( maybe_unserialize( $post_meta[ "group_{$type}" ][ 0 ] ) );
				echo "<hr />";
			}

		}

	}

}