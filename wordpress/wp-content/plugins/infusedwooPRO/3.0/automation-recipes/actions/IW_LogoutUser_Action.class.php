<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_LogoutUser_Action extends IW_Automation_Action {
	function get_title() {
		return "Logout User from Wordpress";
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function display_html($config = array()) {
		$html = '';
	
		return $html;
	}

	function validate_entry($config) {
	}

	function process($config, $trigger) {
		if(is_user_logged_in()) {
			wp_logout();
		}
	}
}

iw_add_action_class('IW_LogoutUser_Action');