<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Views {

	public $groups;
	public $users;
	public $post;
	public $fields;
	public $roles;

	function placeholder_table( $post ){

		$this->post = $post;
		$this->include_view( 'ph-table', array(), $post );

	}

	function placeholders_mb( $post, $metabox ) {

		$this->post = $post;
		$integration = new WP_Job_Manager_Visibility_Integration();
		$this->fields = $integration->get_all_fields();
		$this->include_view( 'placeholders' );
	}

	function include_view( $view, $args = array(), $post = null ){

		$file_path = JOB_MANAGER_VISIBILITY_PLUGIN_DIR . "/classes/admin/views/{$view}.php";
		$arguments = apply_filters( "jmv_view_{$view}_args", $args, $post );

		extract( $arguments, EXTR_SKIP );

		if( file_exists( $file_path ) ) include( $file_path );

	}


	function debug_output( $post, $metabox ) {

		$post_meta = get_post_meta( $post->ID );

		if( method_exists( $this, 'post_meta_debug' ) ) call_user_func( array( $this, 'post_meta_debug' ), $post_meta );

		echo "<h3>Post Meta:</h3>";
		$this->dump_array( $post_meta );

		echo "<hr /><h3>Post:</h3>";
		$this->dump_array( $post );

		echo "<hr /><h3>Metabox:</h3>";
		$this->dump_array( $metabox );
	}

	/**
	 * Dumps/Echo out array data with print_r or var_dump if xdebug installed
	 *
	 * Will check if xdebug is installed and if so will use standard var_dump,
	 * otherwise will use print_r inside <pre> tags to give formatted output.
	 *
	 * @since 1.1.0
	 *
	 * @param $array_data
	 */
	function dump_array( $array_data ) {

		if ( ! $array_data ) {
			_e( 'No array data found!', 'wp-job-manager-visibility' );

			return;
		}
		if ( ! function_exists( 'xdebug_get_code_coverage' ) ) {
			echo "<pre>";
			print_r( $array_data );
			echo "</pre>";

			return;
		}
		var_dump( $array_data );
	}
}