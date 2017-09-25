<?php


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_WC_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_WC_Resume extends WPJM_Pack_Admin_WC {

	/**
	 * Get Meta Fields to Save to Product
	 *
	 * The fields must be unique, so best to put _resume_ somewhere in them
	 * @see parent::save_product();
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_meta_fields(){

		$fields = array(
			'_view_resume_limit'               => 'int',
			'_view_name_resume_limit'          => 'int',
			'_contact_resume_limit'            => 'int',
			'_allow_resume_view'               => 'yesno',
			'_allow_resume_view_rollover'      => 'yesno',
			'_allow_resume_browse'             => 'yesno',
			'_allow_resume_contact'            => 'yesno',
			'_allow_resume_contact_rollover'   => 'yesno',
			'_allow_resume_view_name'          => 'yesno',
			'_allow_resume_view_name_rollover' => 'yesno',
			'_resume_use_sd'                   => 'yesno'
		);

		return $fields;

	}

	/**
	 * Get Shortcodes that can be used in Excerpt/Short Description
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function get_excerpt_shortcodes(){

		$shortcodes = array(
			'package_price' => array(
				'desc' => __( 'Output price (cost) of package (you MUST include this!)', 'wp-job-manager-packages' )
			),
			'view_resume_package_limit'  => array(
				'desc' => __( 'Output package limit set for View (will output number or unlimited based on config)', 'wp-job-manager-packages' )
			),
			'view_name_package_limit'  => array(
				'desc' => __( 'Output package limit set for View Name (will output number or unlimited based on config)', 'wp-job-manager-packages' )
			),
			'contact_package_limit' => array(
				'desc' => __( 'Output package limit set for %s (will output number or unlimited based on config)', 'wp-job-manager-packages' ),
			),
		);

		return apply_filters( 'job_manager_packages_wc_resume_get_excerpt_shortcodes', $shortcodes );
	}
}
