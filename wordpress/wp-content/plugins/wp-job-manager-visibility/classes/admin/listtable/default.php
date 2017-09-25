<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_ListTable_Default extends WP_Job_Manager_Visibility_Admin_ListTable {


	/**
	 * WP_Job_Manager_Visibility_Admin_ListTable_Default constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'default', 'post_type' );

		$this->new_columns = array(
			'cb'    => "<input type=\"checkbox\" />",
			'title'  => __( 'Title', 'wp-job-manager-visibility' ),
			'type'   => '<i class="fa fa-lg fa-street-view"></i>',
			'priority'  => __( 'Priority', 'wp-job-manager-visibility' ),
			'hidden_fields' => __( 'Hidden Fields', 'wp-job-manager-visibility' ),
			'visible_fields' => __( 'Visible Fields', 'wp-job-manager-visibility' ),
			'date'     => __( 'Last Updated', 'wp-job-manager-visibility' ),
			'actions' => __( 'Actions', 'wp-job-manager-visibility' )
		);

		parent::__construct();

	}

	function title_column( $title, $post_id ) {
		if( $_GET['post_type'] !== 'default_visibilities' ) return $title;

		return $this->get_user_group_display( $title );
	}

	function column_values( $column, $post_id ) {

		if ( ! array_key_exists( $column, $this->new_columns ) ) return;


		switch( $column ){

			case 'priority':
				$user_id = get_post_meta( $post_id, 'user_id', TRUE );
				if ( WP_Job_Manager_Visibility_Groups::is_group_string( $user_id ) ) {
					$group_id = str_replace( "group-", "", $user_id );
					$priority = get_post_meta( $group_id, 'priority', true );
				}
				if ( WP_Job_Manager_Visibility_Users::is_user_string( $user_id ) ) {
					$priority = strtolower( __( 'n/a', 'wp-job-manager-visibility' ) );
				}
				echo $priority;
				break;

			case 'type':
				$user_id = get_post_meta( $post_id, 'user_id', TRUE );
				if( WP_Job_Manager_Visibility_Groups::is_group_string( $user_id ) ) {
					$group_id = str_replace( "group-", "", $user_id );
					$group_title = $group_id ? get_the_title( $group_id ) . ' ' : '';
					$icon = "users";
					$title = $group_title . __( 'Group', 'wp-job-manager-visibility' );
				}
				if( WP_Job_Manager_Visibility_Users::is_user_string( $user_id ) ) {
					$icon = 'user';
					$auser_id = str_replace( "user-", "", $user_id );
					$the_user = $auser_id ? get_user_by( 'id', $auser_id ) . ' ' : '';
					$user_title = is_object( $the_user ) ? $the_user->display_name . ' ': '';
					$title = $user_title . __( 'User', 'wp-job-manager-visibility' );
				}
				echo "<i class=\"fa fa-lg fa-{$icon}\" title=\"{$title}\"></i>";
				break;

			case 'hidden_fields':
				$placeholders = get_post_meta( $post_id, 'placeholders', TRUE );
				if( is_array( $placeholders ) && ! empty( $placeholders ) ){
					foreach( $placeholders as $placeholder => $config ){
						$title = html_entity_decode( $config['placeholder'] );
						$title = strip_tags( $title );
						echo "<span class=\"jmv-list-fields-box-hide jmv-list-fields-box\" title=\"{$title}\">{$placeholder}</span>";
					}
				}
				break;

			case 'visible_fields':
				$visible_fields = get_post_meta( $post_id, 'visible_fields' );
				if( is_array( $visible_fields ) && ! empty( $visible_fields ) ){
					foreach( $visible_fields as $field ){
						echo "<span class=\"jmv-list-fields-box-show jmv-list-fields-box\">{$field}</span>";
					}
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

	}

}