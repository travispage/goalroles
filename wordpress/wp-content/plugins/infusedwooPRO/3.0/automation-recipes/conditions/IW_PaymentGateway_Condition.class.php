<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_PaymentGateway_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'When payment gateway used is ...';
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
		$selected = isset($config['payment_gateway']) ? $config['payment_gateway'] : array();		
		$html = "Select Payment Gateway<br>";
		$html .= '<select name="payment_gateway[]" multiple autocomplete="off">';

		$wcpg = new WC_Payment_Gateways;
		$pgs = $wcpg->get_available_payment_gateways();

		foreach ($pgs as $pg) {
	        $html .= '<option value="';
  			$html .= $pg->id;
  			$html .= '"' . (in_array($pg->id, $selected) ? ' selected' : '');
  			$html .= '>' . $pg->title . " [ {$pg->id} ]";
  			$html .= "</option>";
		}

		$html .= '</select>';

		return $html;
	}

	function validate_entry($conditions) {
		if(empty($conditions['payment_gateway'])) {
			return "Please choose at least one Payment Gateway";
		}
	}


	function test($config, $trigger) {
		$order_id = $trigger->pass_vars[0];
		$order = new WC_Order($order_id);

		$payment_used = $order->payment_method;
		if(in_array($payment_used, $config['payment_gateway'])) {
			return true;
		} else {
			return false;
		}
	}
}

iw_add_condition_class('IW_PaymentGateway_Condition');