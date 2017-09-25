<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_MergeValueCheck_Condition extends IW_Automation_Condition {
	public $allow_multiple = true;
	
	function get_title() {
		return 'Check Merge Field Value ...';
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
				'IW_WooSubEvent_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function display_html($config = array()) {
		$merge_val = isset($config['merge_val']) ? $config['merge_val'] : "";
		$op = isset($config['op']) ? $config['op'] : "equal";
		$compare_value = isset($config['compare_value']) ? $config['compare_value'] : "";

		$html = 'Check Value of: <input style="position:relative; top: -1px; left: 2px;" type="text" name="merge_val" placeholder="Enter Merge Field" value="'.$merge_val .'" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i><br>';
		$html .= '<div class="iwar-minisection" style="margin-top: 10px;"><select name="op">
				<option value="equal"'.($op == 'equal' ? ' selected ' : '').'>is equal to</option>
				<option value="like"'.($op == 'like' ? ' selected ' : '').'>is equal to (case insensitive)</option>
				<option value="greater"'.($op == 'greater' ? ' selected ' : '').'>is greater than</option>
				<option value="less"'.($op == 'less' ? ' selected ' : '').'>is less than</option>
				<option value="contain"'.($op == 'contain' ? ' selected ' : '').'> contains</option>
				<option value="notequal"'.($op == 'notequal' ? ' selected ' : '').'>is not Equal to</option>
				<option value="startswith"'.($op == 'startswith' ? ' selected ' : '').'>starts with</option>
				<option value="endswith"'.($op == 'endswith' ? ' selected ' : '').'>ends with</option>
				<option value="equaldate"'.($op == 'equaldate' ? ' selected ' : '').'>is equal to (date)</option>
				<option value="greaterdate"'.($op == 'greaterdate' ? ' selected ' : '').'>is greater than (date)</option>
				<option value="lessdate"'.($op == 'lessdate' ? ' selected ' : '').'>is less than (date)</option>
				<option value="notempty"'.($op == 'notempty' ? ' selected ' : '').'>is not empty</option>
				<option value="empty"'.($op == 'empty' ? ' selected ' : '').'>is empty</option>
				</select>&nbsp; &nbsp; <input type="text" style="width: 42%" name="compare_value" placeholder="Enter Value" value="'.$compare_value.'" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';

		$html .= '</div>';

		return $html;
	}

	function validate_entry($conditions) {
		if(empty($conditions['merge_val'])) {
			return "Please enter merge field value to check";
		}
	}


	function test($config, $trigger) {
		$val1 = $trigger->merger->merge_text($config['merge_val']);
		$op = $config['op'];
		$val2 = $trigger->merger->merge_text($config['compare_value']);

		return $trigger->compare_val($val1, $op, $val2);
	}
}

iw_add_condition_class('IW_MergeValueCheck_Condition');