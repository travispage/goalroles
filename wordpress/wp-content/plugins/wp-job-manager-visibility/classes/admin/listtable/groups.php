<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_ListTable_Groups extends WP_Job_Manager_Visibility_Admin_ListTable {


	/**
	 * WP_Job_Manager_Visibility_Admin_ListTable_Groups constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'groups', 'post_type' );

		$this->new_columns = apply_filters( 'jmv_groups_listtable_columns', array(
			'cb'    => "<input type=\"checkbox\" />",
			'title' => __( 'Title', 'wp-job-manager-visibility' ),
			'priority' => __( 'Priority', 'wp-job-manager-visibility' ),
			'users' => __( 'Users', 'wp-job-manager-visibility' ),
			'groups' => __( 'Groups', 'wp-job-manager-visibility' ),
			'roles' => __( 'Roles', 'wp-job-manager-visibility' ),
			'date' => __( 'Last Updated', 'wp-job-manager-visibility' ),
			'actions' => __( 'Actions', 'wp-job-manager-visibility' )
		));

			add_action( 'before_delete_post', array( $this, 'trash_post' ) );
			add_action( 'wp_trash_post', array( $this, 'trash_post' ) );

		parent::__construct();

	}

	/**
	 * Prompt for Group Removal
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post_id
	 */
	function trash_post( $post_id ){

		$post_type = get_post_type( $post_id );

		if( $post_type !== "visibility_groups" ) return;
		// Get the group config if there is one
		$group_config = WP_Job_Manager_Visibility_Default::get_group( $post_id );
		$groups_with_group = WP_Job_Manager_Visibility_Groups::get_associated_groups( array( $post_id => array( 'ID' => $post_id ) ) );

		if( empty( $group_config ) ) return;

		// User confirmed removal of group
		if( isset( $_GET['remove_group'] ) ) {
			WP_Job_Manager_Visibility_Groups::remove_group_in_groups( $post_id, $groups_with_group );
			wp_delete_post( $group_config['ID'], true );
			$user_cache = new WP_Job_Manager_Visibility_User_Transients();
			$user_cache->purge();
			return;
		}

		$group_title = WP_Job_Manager_Visibility_CPT::get_ug_label( $group_config['title'] );

		$message = "<p>" . sprintf(__( 'Are you sure you want to remove the <strong>%s</strong> group?', 'wp-job-manager-visibility' ), $group_title ) . "</p>";
		$message .= "<p><em>" . __( 'This will remove any configurations you have created for this group!', 'wp-job-manager-visibility' ) . "</em></p>";

		if( ! empty( $groups_with_group ) ){
			$message .= "<p>" . __( 'There were configurations also found with this group in them, and if you remove this group it will be removed from those group configurations.', 'wp-job-manager-visibility' ) . "</p>";
		}

		$remove_url = esc_url( add_query_arg( array( 'remove_group' => true ) ) );

		$message .= "<a href=\"{$remove_url}\" class=\"button button-primary\">" . __( "Yes, remove the group!", 'wp-job-manager-visibility' ) . "</a>";

		wp_die( $message, __( 'Remove Group?', 'wp-job-manager-visibility' ), array( 'back_link' => true ) );
	}

	/**
	 * Output column values
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $column
	 * @param $post_id
	 */
	function column_values( $column, $post_id ) {

		if ( ! array_key_exists( $column, $this->new_columns ) ) return;
		do_action( 'jmv_groups_listtable_before_column_values', $column, $post_id );

		switch( $column ){

			case 'priority':
				echo get_post_meta( $post_id, 'priority', TRUE );
				break;

			case 'users':
				$users = get_post_meta( $post_id, 'group_users' );
				if( is_array( $users ) && ! empty( $users ) ){
					echo implode( ", ", array_map( "WP_Job_Manager_Visibility_Users::get_display_label", $users ) );
				}
				break;

			case 'groups':
				$groups = get_post_meta( $post_id, 'group_groups' );
				if ( is_array( $groups ) && ! empty( $groups ) ) {
					echo implode( ", ", array_map( "WP_Job_Manager_Visibility_Groups::get_display_label", $groups ) );
				}
				break;

			case 'roles':
				$roles = get_post_meta( $post_id, 'group_roles' );
				if ( is_array( $roles ) && ! empty( $roles ) ) {
					echo implode( ", ", array_map( "WP_Job_Manager_Visibility_Roles::get_display_label", $roles ) );
				}
				break;

			case 'actions':

				?>
					<div class="button-group">
						<a href="<?php echo get_edit_post_link( $post_id ); ?>" class="button"><i class="fa fa-pencil fa-lg"></i> <?php _e('Edit', 'wp-job-manager-visibility'); ?></a>
						<a href="<?php echo get_delete_post_link( $post_id ); ?>" class="button"><i class="fa fa-trash-o fa-lg" style="color: #a23333; font-weight: bold;"></i> <?php _e('Delete', 'wp-job-manager-visibility'); ?></a>
					</div>
				<?php
				break;
		}

		do_action( 'jmv_groups_listtable_after_column_values', $column, $post_id );

	}

}