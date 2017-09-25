<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_ChargeCCinOrder_Action extends IW_Automation_Action {
	function get_title() {
		return "Attempt Autocharge Woocommerce Order";
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger'
			);
	}


	function display_html($config = array(), $trigger_class = "") {
		$order_id = isset($config['order_id']) ? $config['order_id'] : '';
		$card_id = isset($config['card_id']) ? $config['card_id'] : '{{WCOrderMeta:infusionsoft_cc_id}}';

		$html = 'Woo Order ID to Charge<br>';

		if(in_array($order_id, array('','{{WPUser:LastPendingOrder}}', 'current_order_id'))) {
			$html .= '<div><select name="order_id" class="order_id_sel">';

			if(in_array($trigger_class, array('IW_OrderCreation_Trigger','IW_OrderStatusChange_Trigger','IW_Purchase_Trigger'))) {
				$html .= '<option value="current_order_id"'.($order_id == 'current_order_id' ? ' selected ' : '').'>Triggered Order\'s ID</option>';
			}

			$html .= '<option value="{{WPUser:LastPendingOrder}}"'.($order_id == '{{WPUser:LastPendingOrder}}' ? ' selected ' : '').'>Contact\'s Last Pending Order</option>';
			$html .= '<option value="custom_order_id">Custom Order ID...</option>';
			$html .= '</select>';
		} else {
			$html .= '<input type="text" style="width: 200px;" name="order_id" value="'.$order_id.'" class="iwar-mergeable" /><i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></div>';
		}

		$html .= '<div><br>Infusionsoft Card ID<br>';
		$html .= '<input type="text" name="card_id" style="width: 200px;" value="'.$card_id.'" class="iwar-mergeable" /><i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></div>';
		
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['order_id'])) {
			return 'Woo Order ID to Charge is empty';
		} else if(empty($config['card_id'])) {
			return 'Infusionsoft Card ID is empty';
		}
	}

	function process($config, $trigger) {
		// find order id
		if($config['order_id'] == 'current_order_id') {
			$order_id = $trigger->pass_vars[0];
		} else {
			$order_id = $trigger->merger->merge_text($config['order_id']);
		}

		$card_id =$trigger->merger->merge_text($config['card_id']);

		$order = new WC_Order($order_id);
		
		if($order->is_paid() || !$order->needs_payment()) {
			return false;
		}

		if($card_id > 0) {		
			$gateway = new IA_WooPaymentGateway;
			$gateway->process_payment($order_id, $card_id);
		}
	}
}

iw_add_action_class('IW_ChargeCCinOrder_Action');