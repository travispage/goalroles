<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderNoShipping_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If order doesn\'t require shipping ...';
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
		$html = '';
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

		return !$shippable;
	}
}

iw_add_condition_class('IW_OrderNoShipping_Condition');