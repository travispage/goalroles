<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_ListTable {

	protected $post_type;
	protected $new_columns;

	/**
	 * WP_Job_Manager_Visibility_Admin_ListTable constructor.
	 *
	 */
	public function __construct() {

		if( ! $this->post_type ) return;

		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'add_columns' ) );
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'column_values' ), 10, 2 );
		add_action( "admin_head-edit.php", array( $this, 'admin_edit_head' ), 10, 2 );
	}

	function get_user_group_display( $user_group ){

		if ( WP_Job_Manager_Visibility_Groups::is_group_string( $user_group ) ){

			return WP_Job_Manager_Visibility_Groups::get_display_label( $user_group );

		} elseif( WP_Job_Manager_Visibility_Users::is_user_string( $user_group ) ){

			return WP_Job_Manager_Visibility_Users::get_display_label( $user_group );

		}

		return $user_group;
	}

	function admin_edit_head(){

		$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();
		$post_type = isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : false;
		if( ! $post_type || ! in_array( $_GET['post_type'], $post_types ) ) return;

		if( method_exists( $this, 'title_column' ) ) add_filter( 'the_title', array( $this, 'title_column' ), 100, 2 );
?>
		<script type="text/javascript">
			jQuery(function($){ $( '.wp-list-table' ).removeClass('fixed'); });
		</script>
<?php

	}

	/**
	 * Add custom columns to list table
	 *
	 * Checks if $this->new_columns has all of the columns (keys) present in $columns
	 * and returns only $this->new_columns to allow for ordering of columns.  Otherwise
	 * method will merge arrays which puts all new columns at end of array.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	function add_columns( $columns ) {

		$diff = array_diff_key( $columns, $this->new_columns );
		if( empty( $diff ) ) return $this->new_columns;

		$columns = array_merge( $columns, $this->new_columns );

		return $columns;
	}

	function column_values( $column, $post_id ){
	}
}