<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Job {

	/**
	 * WPJM_Pack_Admin_Job constructor.
	 *
	 * @param $type WPJM_Pack_Job
	 */
	public function __construct( $type ){
		$this->settings = new WPJM_Pack_Admin_Settings_Job( $type, $this );
	}
}
