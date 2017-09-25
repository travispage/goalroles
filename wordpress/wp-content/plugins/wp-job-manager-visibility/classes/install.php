<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Install extends WP_Job_Manager_Visibility_CPT {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'check' ) );
		parent::__construct();
	}

	/**
	 * Check if should include install file
	 *
	 * @since 1.1.0
	 *
	 */
	public function check() {

		$current_version  = get_option( 'job_manager_visibility_version' );
		$plugin_activated = get_option( 'job_manager_visibility_activated' );

		if ( $plugin_activated || ! $current_version || version_compare( JOB_MANAGER_VISIBILITY_VERSION, $current_version, '>' ) ) {
			// Remove option if was set on plugin activation
			if ( $plugin_activated ) delete_option( 'job_manager_visibility_activated' );
			if( ! $current_version ) update_option( 'job_manager_visibility_version', JOB_MANAGER_VISIBILITY_VERSION );

			$this->init_user_roles();
			WP_Job_Manager_Visibility_Roles::add_anonymous();
			$this->add_default_groups( TRUE );
		}

	}

	/**
	 * Init user roles
	 *
	 * @access public
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function init_user_roles() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {

			if ( empty( $this->capabilities ) ) $this->init_capabilities();

			foreach ( $this->capabilities as $type => $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}

		}
	}

	/**
	 * Add Default Groups
	 *
	 * This method will add the Anonymous, Candidates, and Employers default groups
	 * which is called on install/update if 0 groups exists, or when executed through
	 * settings button under Setup tab.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param bool $check_empty     Whether to check if there are 0 groups before adding groups
	 *
	 * @return array|bool
	 */
	public static function add_default_groups( $check_empty = FALSE ){

		$post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'groups', 'post_type' );

		if( $check_empty ){
			$count = wp_count_posts( $post_type );
			if( isset( $count->publish ) && (int) $count->publish > 0 ) return false;
		}

		$added = false;
		$groups = array(
			'all'       => __( 'All', 'wp-job-manager-visibility' ),
			'anonymous' => __( 'Anonymous', 'wp-job-manager-visibility' ),
			'candidate' => __( 'Candidates', 'wp-job-manager-visibility' ),
			'employer' => __( 'Employers', 'wp-job-manager-visibility' ),
			'administrator' => __( 'Administrators', 'wp-job-manager-visibility' ),
		);

		foreach( $groups as $role => $title ){

			// If first returned post is published and has same title, skip it
			if( $existing_post = get_page_by_title( $title, OBJECT, $post_type ) ) {
				if( isset( $existing_post->post_status ) && $existing_post->post_status === 'publish' ) continue;
			}

			$post_args = array(
				'post_title'   => $title,
				'post_type'    => $post_type,
				'post_content' => '',
				'post_status'  => 'publish',
			);

			$post_id = wp_insert_post( $post_args );

			if( is_numeric( $post_id ) ) {

				if( $role === 'all' ){

					$allroles = WP_Job_Manager_Visibility_Roles::get_roles();
					if( empty( $allroles ) ) continue;

					foreach( $allroles as $single_role => $role_display ) {
						add_post_meta( $post_id, 'group_roles', $single_role );
					}

				} else {

					add_post_meta( $post_id, 'group_roles', $role );

				}

				update_post_meta( $post_id, 'priority', 10 );
			}

			$added[] = $title;
		}

		return $added;
	}
}