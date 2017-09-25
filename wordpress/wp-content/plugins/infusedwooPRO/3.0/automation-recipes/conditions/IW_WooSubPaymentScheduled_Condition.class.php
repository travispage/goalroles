<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WooSubPaymentScheduled_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If a subscription payment has been scheduled';
	}

	function allowed_triggers() {
		return array(
				'IW_WooSubEvent_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {

	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		return $trigger->sub_event == 'scheduled_subscription_payment';
	}
}

iw_add_condition_class('IW_WooSubPaymentScheduled_Condition');