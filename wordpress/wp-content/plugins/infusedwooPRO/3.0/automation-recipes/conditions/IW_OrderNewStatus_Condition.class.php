<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderNewStatus_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If order\'s new status is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_OrderStatusChange_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$status = isset($config['status']) ? $config['status'] : '';

		$html = '<select name="status" autocomplete="off">';
		foreach(wc_get_order_statuses() as $k => $s) {
			$html .= '<option value="'.$k.'" '. ($status == $k ? 'selected' : '') .'>'.$s.'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		return ("wc-" . $trigger->pass_vars[2]) == $config['status'];		
	}
}

iw_add_condition_class('IW_OrderNewStatus_Condition');