<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_MetaBoxes extends WP_Job_Manager_Visibility_Admin_Views {

	public $post_type;
	public $meta_boxes;

	/**
	 * WP_Job_Manager_Visibility_Admin_MetaBoxes constructor.
	 */
	public function __construct() {

		$this->construct();
		$this->init_meta_boxes();
		$this->add_meta_boxes();

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 999 );

	}

	function add_meta_boxes(){

		$this->add_debug_meta_box();

		if( ! $this->meta_boxes ) $this->init_meta_boxes();

		foreach ( $this->meta_boxes as $mb => $conf ) {
			// Go to next if callback method doesn't exist
			if( ! method_exists( $conf['callback'][0], $conf['callback'][1] ) ) continue;
			add_meta_box( $conf[ 'id' ], $conf[ 'title' ], $conf[ 'callback' ], $conf[ 'screen' ], $conf[ 'context' ], $conf[ 'priority' ], $conf[ 'callback_args' ] );
		}
	}

	function add_debug_meta_box() {

		if ( ! get_option( 'jmv_enable_post_debug' ) ) return FALSE;
		add_meta_box( 'visibilities_debug', __( 'Debug Information', 'wp-job-manager-visibility' ), array( $this, 'debug_output' ), $this->post_type, 'normal', 'low' );

	}

	function scripts(){

	}

	/**
	 * Construct Placeholder
	 *
	 * This is just a construct placeholder that should be overriden by
	 * any classes that extend this class.  All this does is eliminate
	 * the need to call parent::__construct() in class that extends
	 * this one.
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function construct() {
		// Not used, only a placeholder, see method doc for details
	}
}