<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_LoggedIn_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If user is logged-in in Wordpress';
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_HttpPost_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$html = '';
		return $html;
	}

	function validate_entry($conditions) {
		
	}


	function test($config, $trigger) {
		return !is_admin() && is_user_logged_in();
	}
}

iw_add_condition_class('IW_LoggedIn_Condition');