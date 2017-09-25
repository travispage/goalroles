<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderHasCoupon_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If customer used a coupon code for this order';
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

		return (sizeof( $order->get_used_coupons() ) > 0);
	}
}

iw_add_condition_class('IW_OrderHasCoupon_Condition');