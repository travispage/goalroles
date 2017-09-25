<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WooSubTrialEnded_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If the subscription\'s trial period ended';
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
		return $trigger->sub_event == 'trigger_trial_end';
	}
}

iw_add_condition_class('IW_WooSubTrialEnded_Condition');