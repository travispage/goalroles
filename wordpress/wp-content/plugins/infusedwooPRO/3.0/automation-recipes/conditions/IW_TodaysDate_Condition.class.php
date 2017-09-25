<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_TodaysDate_Condition extends IW_Automation_Condition {
	public $allow_multiple = true;

	function get_title() {
		return 'If current date is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_UserAction_Trigger',
				'IW_WooSubEvent_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$date_compare = isset($config['date_compare']) ? $config['date_compare'] : date("m/d/Y");
		$op = isset($config['op']) ? $config['op'] : 'equaldate';

		$html = '<div class="iwar-minisection" style="margin-top: 3px;"><select name="op">
				<option value="equaldate"'.($op == 'equaldate' ? ' selected ' : '').'>is equal to</option>
				<option value="greaterdate"'.($op == 'greaterdate' ? ' selected ' : '').'>is greater than</option>
				<option value="lessdate"'.($op == 'lessdate' ? ' selected ' : '').'>is less than</option>
				</select>&nbsp; &nbsp; 
				<input type="text" style="width: 32%" name="date_compare" placeholder="mm/dd/yyyy" value="'.$date_compare.'" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';

		$html .= '</div>';
		return $html;
	}

	function validate_entry($conditions) {
		if(empty($conditions['date_compare'])) {
			return "Please enter date value.";
		}
	}


	function test($config, $trigger) {
		$val1 = date("Y-m-d H:i:s");
		$op = $config['op'];
		$val2 = $trigger->merger->merge_text($config['date_compare']);

		return $trigger->compare_val($val1, $op, $val2);
	}
}

iw_add_condition_class('IW_TodaysDate_Condition');