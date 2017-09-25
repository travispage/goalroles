<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_ListTable_Custom extends WP_Job_Manager_Visibility_Admin_ListTable {


	/**
	 * WP_Job_Manager_Visibility_Admin_ListTable_Default constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'default', 'post_type' );
		$this->new_columns = array(
			'fields' => __( 'Fields', 'wp-job-manager-visibility' ),
			'field_count' => __( 'Total Fields', 'wp-job-manager-visibility' ),
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
		$placeholders = get_post_meta( $post_id, 'placeholders', TRUE );

		switch( $column ){

			case 'fields':
				if( is_array( $placeholders ) && ! empty( $placeholders ) ){
					foreach( $placeholders as $placeholder => $config ){
						echo "<span class=\"jmv-list-fields-box-hide jmv-list-fields-box\">{$placeholder}</span>";
					}
				}
				break;

			case 'field_count':
				echo count( $placeholders );
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