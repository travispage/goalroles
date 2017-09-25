<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WishlistPriceChanges_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'Wishlist item price has been changed ...';
	}

	function allowed_triggers() {
		return array(
				'IW_WishlistEvent_Trigger'			
				);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$type = isset($config['type']) ? $config['type'] : '';		
		$op = isset($config['op']) ? $config['op'] : '';
		$num = isset($config['num']) ? $config['num'] : "0.00";

		$html = '<select name="type" class="full-select wptype-sel" style="width: 100%" autocomplete="off">
			<option value="new_minus_old"'.($type == 'new_minus_old' ? ' selected' : '').'>and (new price &ndash; old price)</option>
			<option value="percent_dec"'.($type == 'percent_dec' ? ' selected' : '').'>and % percent price decrease ... </option>
			<option value="percent_inc"'.($type == 'percent_inc' ? ' selected' : '').'>and % percent price increase ... </option>
			<option value="new_price"'.($type == 'new_price' ? ' selected' : '').'>and new price</option>
			</select>';

		$html .= '<div class="iwar-minisection minisection-wooproducts">';
		$html .= '<select name="op" style="width: 50%">';
		$html .= '<option value="less"'.($op == 'less' ? ' selected' : '').'>is less than</option>';
		$html .= '<option value="equal"'.($op == 'equal' ? ' selected' : '').'>is equal to</option>';
		$html .= '<option value="greater"'.($op == 'greater' ? ' selected' : '').'>is greater than</option>';
		$html .= '</select>';
		$html .= '&nbsp;<input type="text" name="num" placeholder="0.00" style="width: 40%" value="'.$num.'" />';
		$html .= '</div>';

		return $html;

	}

	function validate_entry($conditions) {
		if(!is_numeric($conditions['num'])) {
			return 'Value should be a number';
		}

	}


	function test($config, $trigger) {
		if($trigger->pass_vars[0] != 'change_price') return false;
		$new_val = (float) $trigger->pass_vars[5];
		$old_val = (float)  $trigger->pass_vars[4];
 
		if($config['type'] == 'new_minus_old') {
			$value_to_compare =  $new_val -  $old_val;
		} else if($config['type'] == 'new_price') {
			$value_to_compare =  $new_val;
		} else if($config['type'] == 'percent_dec') {
			$value_to_compare = (($old_val-$new_val)*100) / $old_val;
		} else {
			$value_to_compare = (($new_val-$old_val)*100) / $old_val;
		}

		if($config['op'] == 'less') {
			return $value_to_compare < ((float) $config['num']);
		} else if($config['op'] == 'greater') {
			return $value_to_compare > ((float) $config['num']);
		} else {
			return $value_to_compare == ((float) $config['num']);
		}

		return false;
	}
}

iw_add_condition_class('IW_WishlistPriceChanges_Condition');