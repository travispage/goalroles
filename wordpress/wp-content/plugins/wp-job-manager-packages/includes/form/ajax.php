<?php

if( ! defined( 'ABSPATH' ) ) exit;

class WPJM_Pack_Form_Ajax extends WPJM_Pack_Form {

	public function __construct( $type ) {

		$this->type = $type;
		add_action( 'wp', array( $this, 'form_handler' ) );

	}

}
