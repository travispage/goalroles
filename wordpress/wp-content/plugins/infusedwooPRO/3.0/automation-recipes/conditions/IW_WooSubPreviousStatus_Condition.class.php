<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WooSubPreviousStatus_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If the subscription changes status ...';
	}

	function allowed_triggers() {
		return array(
				'IW_WooSubEvent_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$status1 = isset($config['status1']) ? $config['status1'] : '';
		$status2 = isset($config['status2']) ? $config['status2'] : '';

		$html = '<table><tr><td>From</td><td> <select name="status1" autocomplete="off"><option value="">Any Status</option>';
		foreach(wcs_get_subscription_statuses() as $k => $s) {
			$html .= '<option value="'.$k.'" '. ($status1 == $k ? 'selected' : '') .'>'.$s.'</option>';
		}
		$html .= '</select></td></tr>';
		$html .= '<tr><td>To</td><td><select name="status2" autocomplete="off"><option value="">Any Status</option>';
		foreach(wcs_get_subscription_statuses() as $k => $s) {
			$html .= '<option value="'.$k.'" '. ($status2 == $k ? 'selected' : '') .'>'.$s.'</option>';
		}
		$html .= '</select></td></tr></table>';
		return $html;
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		if($trigger->sub_event == 'status_changed') {
			return ((empty($config['status1']) || $config['status1'] == $trigger->pass_vars[1]) && (empty($config['status2']) || $config['status2'] == $trigger->pass_vars[2]));
		} else {
			return false;
		}
	}
}

iw_add_condition_class('IW_WooSubPreviousStatus_Condition');