<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Resume {

	/**
	 * WPJM_Pack_Admin_Resume constructor.
	 *
	 * @param $type WPJM_Pack_Resume
	 */
	public function __construct( $type ){
		$this->settings = new WPJM_Pack_Admin_Settings_Resume( $type, $this );
	}
}
