<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_UserLeavesPage_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If customer navigates away from page.';
	}

	function allowed_triggers() {
		return array(
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
		if (isset($_GET['iwar_hold']) && $_GET['iwar_hold'] == 1 && $_GET['trig'] == 'leavepage') {
			return true;
		} else {
			add_filter( 'iw_page_triggers', array($this, 'script_postpone'), 10, 1 );
			return false;
		}
	}

	function script_postpone($val) {
		$val[] = array('trigger_type' => 'leavepage');
		return $val;
	}
}

iw_add_condition_class('IW_UserLeavesPage_Condition');