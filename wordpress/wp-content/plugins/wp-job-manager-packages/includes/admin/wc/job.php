<?php


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_WC_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_WC_Job extends WPJM_Pack_Admin_WC {

	/**
	 * Get Meta Fields to Save to Product
	 *
	 * The fields must be unique, so best to put _job_ somewhere in them
	 * @see parent::save_product();
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_meta_fields(){

		// These need to include _SLUG to process correctly
		// ie ... _view_limit meta, needs to be set as _view_job_limit
		$fields = array(
			'_view_job_limit'           => 'int',
			'_apply_job_limit'          => 'int',
			'_allow_job_view'           => 'yesno',
			'_allow_job_view_rollover'  => 'yesno',
			'_allow_job_browse'         => 'yesno',
			'_allow_job_apply'          => 'yesno',
			'_allow_job_apply_rollover' => 'yesno',
			'_job_use_sd'               => 'yesno'
		);

		return $fields;

	}

	/**
	 * Get Shortcodes that can be used in Excerpt/Short Description
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_excerpt_shortcodes(){

		$shortcodes = array(
			'package_price' => array(
				'desc' => __( 'Output price (cost) of package (you MUST include this!)', 'wp-job-manager-packages' )
			),
			'view_job_package_limit' => array(
				'desc' => __( 'Output package limit set for View (will output number or unlimited based on config)', 'wp-job-manager-packages' )
			),
		    'apply_package_limit' => array(
				'desc' => sprintf( __( 'Output package limit set for %s (will output number or unlimited based on config)', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() )
		    ),
		);

		return apply_filters( 'job_manager_packages_wc_job_get_excerpt_shortcodes', $shortcodes );
	}
}
