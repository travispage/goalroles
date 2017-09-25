<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Help {

	public $tabs;
	public $post_type;
	public $screens;

	/**
	 * WP_Job_Manager_Visibility_Admin_Help constructor.
	 *
	 */
	public function __construct() {

		add_action( "load-edit.php", array( $this, 'post_list' ) );
		add_action( 'load-post-new.php', array( $this, 'post_new' ) );
		add_action( 'load-post.php', array( $this, 'post_edit' ) );

	}

	/**
	 * Initialize Tabs and Tab Sidebars
	 *
	 * This method gets called by the magic method for any CPT pages, or called
	 * specifically by a class that extends this class (ie settings page) in order
	 * to initialize and add tabs and tab sidebars.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param string $page_type
	 */
	function init( $page_type = '' ) {

		if( ! $this->check_post_type() ) return;

		$screen = get_current_screen();

		if( ! $this->tabs ) $this->init_config();
		if( ! empty( $page_type ) ) $page_type = "_{$page_type}";

		foreach ( $this->tabs as $tab => $conf ) {

			$args = array(
				'id'    => "jmv_{$tab}{$page_type}",
				'title' => $conf[ 'title' ],
			);

			// Check if specific method for page tab exists, otherwise use standard tab method
			$callback_method = method_exists( $this, "{$tab}{$page_type}" ) ? "{$tab}{$page_type}" : $tab;
			// Set callback to specific page method, or regular tab method (if exists), otherwise set to false ( will use 'content' arg )
			$args[ 'callback' ] = method_exists( $this, $callback_method ) ? array( $this, "output_{$callback_method}" ) : FALSE;
			// Add help tab with arguments
			$screen->add_help_tab( $args );

			// Check if specific method for page exists for sidebar, if not use standard sidebar tab method
			$sidebar_method = method_exists( $this, "sidebar_{$tab}{$page_type}" ) ? "{$tab}{$page_type}" : $tab;
			// Check if regular or page specific method exists, if not, use the sidebar_all method instead
			$sidebar_method = method_exists( $this, "sidebar_{$sidebar_method}" ) ? "sidebar_{$sidebar_method}" : "sidebar_all";
			// Get the sidebar content (or false if no content)
			$sidebar_content = $this->$sidebar_method();
			// Set the sidebar if there is content
			if( $sidebar_content ) $screen->set_help_sidebar( $sidebar_content );

		}

	}

	/**
	 * Magic Method for output_ sidebar_ and post_ method calls
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $method_name
	 * @param $args
	 *
	 * @return mixed|void|\WP_Job_Manager_Visibility_Admin_Ajax
	 */
	public function __call( $method_name, $args ) {

		if ( preg_match( '/(?P<action>(output|sidebar|post)+)_(?P<variable>\w+)/', $method_name, $matches ) ) {
			$variable = strtolower( $matches[ 'variable' ] );
			switch ( $matches[ 'action' ] ) {
				case 'output':
					if( ! $this->check_post_type() || ! method_exists( $this, $variable ) ) return false;
					return $this->$variable();

				case 'sidebar':
					$sidebar_method = $variable === "all" ? "sidebar" : "sidebar_{$variable}";
					if ( ! $this->check_post_type() || ! method_exists( $this, $sidebar_method ) ) return false;
					ob_start();
					$this->$sidebar_method();
					$sidebar_content = ob_get_clean();
					return $sidebar_content;

				case 'post':
					if ( ! $this->screens ) $this->init_config();
					if ( ! $this->check_post_type() || empty( $this->screens[ $variable ] ) ) return;
					$this->init( $variable );
					break;

				case 'default':
					error_log( 'Method ' . $method_name . ' not exists' );
			}
		}
	}

	/**
	 * Check Post Type against $this->post_type
	 *
	 * This method should be ran from action for edit.php, post.php, and post-new.php pages to check
	 * in $_GET['post_type'] or $_GET['post'] to see if they match $this->post_type.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	function check_post_type(){

		// Check for post type in $_GET
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] === $this->post_type ) return true;
		// Check for post in $_GET (probably post.php page)
		if ( isset( $_GET[ 'post' ] ) && get_post_type( $_GET['post'] ) === $this->post_type ) return TRUE;

		return false;
	}

	/**
	 * Default Sidebar Output
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function sidebar() {
		?>
		<p><a href="https://plugins.smyl.es" target="_blank">sMyles Plugins</a></p>
		<p><a href="https://plugins.smyl.es/docs-kb/" target="_blank">Documentation</a></p>
		<?php
	}

}