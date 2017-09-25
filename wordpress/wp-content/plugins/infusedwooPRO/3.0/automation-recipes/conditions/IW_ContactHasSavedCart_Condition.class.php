<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_ContactHasSavedCart_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If contact has saved cart in woocommerce';
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_Checkout_Trigger',
				'IW_PageVisit_Trigger'
			);
	}

	function display_html($config = array()) {
		$html = '';
		return $html;
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		$user_email = $trigger->user_email;

		$cart_info = ia_retrieve_cart($user_email);
		$saved_cart = unserialize($cart_info['cartcontent']);

		if(is_array($saved_cart) && count($saved_cart) > 0) {
			return true;
		} else {
			return false;
		}
	}
}

iw_add_condition_class('IW_ContactHasSavedCart_Condition');