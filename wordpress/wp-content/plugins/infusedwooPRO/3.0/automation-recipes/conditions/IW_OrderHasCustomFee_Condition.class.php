<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderHasCustomFee_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If order contain custom fee ...';
	}

	function allowed_triggers() {
		return array(
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$fee_name = isset($config['fee_name']) ? $config['fee_name'] : '';
		$html = 'Fee Name&nbsp;&nbsp;';
		$html .= '<input type="text" name="fee_name" value="'.$fee_name.'" placeholder="Fee Name" />';
		$html .= '<hr><span style="font-size: 10pt;"><em>Leave fee name empty if this applies to all custom fees.</em></span>';
		return $html;
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		$order_id 	= $trigger->pass_vars[0];
		$order 		= new WC_Order($order_id);
		$order_fees = $order->get_fees();

		$fee_name = $config['fee_name'];

		if(is_array($order_fees) && count($order_fees) > 0) {
			if($fee_name == '') {
				return true;
			} else {
				foreach($order_fees as $f) {
					if($f['name'] == $fee_name) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
}

iw_add_condition_class('IW_OrderHasCustomFee_Condition');