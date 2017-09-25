<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WishlistStockChanges_Condition extends IW_Automation_Condition {
	public $allow_multiple = true; 

	function get_title() {
		return 'Wishlist item inventory stock value changed ...';
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
		$num = isset($config['num']) ? $config['num'] : 1;

		$html = '<select name="type" class="full-select wptype-sel" style="width: 100%" autocomplete="off">
			<option value="new_stock"'.($type == 'new_stock' ? ' selected' : '').'>and new stock value</option>
			<option value="new_minus_q"'.($type == 'new_minus_q' ? ' selected' : '').'>and (new stock value &ndash; item quantity)</option>
			<option value="new_minus_old"'.($type == 'new_minus_old' ? ' selected' : '').'>and (new stock value &ndash; old stock value)</option>
			</select>';

		$html .= '<div class="iwar-minisection minisection-wooproducts">';
		$html .= '<select name="op" style="width: 50%">';
		$html .= '<option value="less"'.($op == 'less' ? ' selected' : '').'>is less than</option>';
		$html .= '<option value="equal"'.($op == 'equal' ? ' selected' : '').'>is equal to</option>';
		$html .= '<option value="greater"'.($op == 'greater' ? ' selected' : '').'>is greater than</option>';
		$html .= '</select>';
		$html .= '&nbsp;<input type="number" name="num" placeholder="1" style="width: 40%" value="'.$num.'" />';
		$html .= '</div>';

		return $html;

	}

	function validate_entry($conditions) {
		if(!is_numeric($conditions['num'])) {
			return 'Value should be a number';
		} 

	}


	function test($config, $trigger) {
		if($trigger->pass_vars[0] != 'change_stock') return false;

		$value_to_compare = $trigger->pass_vars[7];
		if($config['type'] == 'new_minus_q') $value_to_compare = $value_to_compare - $trigger->pass_vars[3];
		else if($config['type'] == 'new_minus_old') $value_to_compare = $value_to_compare - $trigger->pass_vars[6];

		if($config['op'] == 'less') {
			return $value_to_compare < ((int) $config['num']);
		} else if($config['op'] == 'greater') {
			return $value_to_compare > ((int) $config['num']);
		} else {
			return $value_to_compare == ((int) $config['num']);
		}

		return false;
	}
}

iw_add_condition_class('IW_WishlistStockChanges_Condition');