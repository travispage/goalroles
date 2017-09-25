<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderRequiresShipping_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If order requires shipping ...';
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
		$shipping = new WC_Shipping;
		$ship_methods = $shipping->load_shipping_methods();
		$ship_method = isset($config['method']) ? $config['method'] : '';

		$html = 'and shipping method selected is <br>';
		$html .= '<select name="method">';
		$html .= '<option value="">(Any shipping method)</option>';
		foreach($ship_methods as $k => $method) {
			$html .= '<option value="'.$k.'" '. ($ship_method == $k ? 'selected' : '') .'>'.$method->method_title.'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		$order_id = $trigger->pass_vars[0];
		$order = new WC_Order($order_id);
		$order_shipping = $order->get_shipping_methods();

		$ship_method = $config['method'];
		$shippable = is_array($order_shipping) && count($order_shipping) > 0;

		if($shippable) {
			if($ship_method == '') {
				return true;
			} else {
				return $order->has_shipping_method($ship_method);
			}
		}

		return false;
	}
}

iw_add_condition_class('IW_OrderRequiresShipping_Condition');