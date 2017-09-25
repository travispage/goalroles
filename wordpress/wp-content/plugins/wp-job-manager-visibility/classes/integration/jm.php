<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';
}

if ( ! class_exists( 'WP_Job_Manager_Form_Submit_Job' ) ){

	// As of WPJM 1.25.2 and newer, the job_manager_multi_job_type() function is called in the init_fields() method, if for some reason
	// that function is not available, we have to manually load the files, and remove the core method that includes them to prevent fatal PHP errors.
	if ( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.25.2', 'ge' ) && ! function_exists( 'job_manager_multi_job_type' ) ) {
		remove_class_filter( 'after_setup_theme', 'WP_Job_Manager', 'include_template_functions', 11 );
		require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-functions.php';
		require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-template.php';
	}

	require_once JOB_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-form-submit-job.php';
}

class WP_Job_Manager_Visibility_Integration_JM extends WP_Job_Manager_Form_Submit_Job {


	/**
	 * Override parent class construct
	 */
	public function __construct() {

	}

	/**
	 * Get all job (and company) fields
	 *
	 * To get all the job fields we have to extend the submit job class, set the fields
	 * to null, re-initialize the fields, and then use the abstract method to return them.
	 *
	 * @since @@since
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_all_fields( $type = 'job' ) {

		$this->fields = NULL;
		$this->init_fields();
		$fields = $this->get_fields( $type );
		return $fields;
	}
}