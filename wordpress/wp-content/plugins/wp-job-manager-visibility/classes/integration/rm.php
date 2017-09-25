<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Manager_Form' ) )
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );

if ( ! class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) )
	require_once( RESUME_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-resume-manager-form-submit-resume.php' );


class WP_Job_Manager_Visibility_Integration_RM extends WP_Resume_Manager_Form_Submit_Resume {

	/**
	 * Override parent class construct
	 */
	public function __construct() {

	}
	/**
	 * Get all resume fields
	 *
	 * To get all the resume fields we have to extend the submit resume class, set the fields
	 * to null, re-initialize the fields, and then use the abstract method to return them.
	 *
	 * @since @@since
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_all_fields( $type = 'resume_fields' ) {

		$this->fields = null;
		$this->init_fields();

		return $this->get_fields( $type );
	}

}