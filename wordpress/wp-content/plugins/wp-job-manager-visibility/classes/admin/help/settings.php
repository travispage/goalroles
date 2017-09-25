<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Help_Settings extends WP_Job_Manager_Visibility_Admin_Help {


	/**
	 * WP_Job_Manager_Visibility_Admin_Help_Settings constructor.
	 *
	 * @param $page
	 */
	public function __construct( $page ) {

		add_action( "load-{$page}", array( $this, 'init') );

	}

	function init_config(){

		$this->tabs = array(
			'debug' => array(
				'title' => __( 'Debug', 'wp-job-manager-visibility' )
			)
		);

	}

	function debug(){
?>
		<p><?php _e( 'Coming Soon...', 'wp-job-manager-visibility'); ?></p>
<?php
	}


}