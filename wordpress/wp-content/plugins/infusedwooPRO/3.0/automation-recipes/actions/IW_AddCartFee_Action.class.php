<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_AddCartFee_Action extends IW_Automation_Action {
	function get_title() {
		return "Add Custom Fee to User's Cart";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger',
				'IW_HttpPost_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		add_action('template_redirect', array($this,'reset_session_vars'),1,0);
		add_action('woocommerce_cart_calculate_fees', array($this, 'apply_custom_fees'),10,1);
	}

	function display_html($config = array()) {
		$fee = isset($config['fee']) ? $config['fee'] : '5%';
		$taxable = isset($config['taxable']) ? $config['taxable'] : 'off';
		$fee_desc = isset($config['fee_desc']) ? $config['fee_desc'] : 'Custom Fee';

		$html = '<table><tr><td>Fee Name</td><td><input type="text" name="fee_desc" value="'.$fee_desc.'" placeholder="Custom Fee" style="width: 190px; top: 0" /></td></tr>';
		$html .= '<tr><td>Fee Amount</td><td><input type="text" name="fee" value="'.$fee.'" placeholder="" style="width: 90px; top: 0" />';
		$html .= '<span style="margin-left: 15px;"><input type="hidden" name="taxable" value="off" /><input type="checkbox" name="taxable" '.($taxable == 'on' ? 'checked' : '').' /> Taxable</span></td></tr>';
		$html.= '</table><i style="font-size: 10pt;">Amount without % will calculate the percent of the cart subtotal.</i>';
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['fee_desc'])) {
			return 'Please enter a fee name';
		}

		if(empty($config['fee'])) {
			return 'Please enter a fee amount';
		} else {
			$check_nan = str_replace('%', '', $config['fee']);
			if(!is_numeric($check_nan))
				return 'Fee amount must be a number or a percent value.'; 
		}
	}

	function process($config, $trigger) {
		$cart_fees = isset($trigger->cart_fees) ? $trigger->cart_fees : array();
		$cart_fees[] = $config;
		$trigger->cart_fees = $cart_fees;
		
		WC()->session->set('iwar_cart_fees', $cart_fees);
	}

	function apply_custom_fees($cart) {
		if(is_cart() || is_checkout()) {
			$cart_fees = WC()->session->get('iwar_cart_fees');

			if(is_array($cart_fees)) foreach($cart_fees as $config) {
				$fee = $config['fee'];
				$taxable = $config['taxable'] == 'on';

				$type = 'fixed';
				if(strpos($fee, '%') !== false) {
					$fee = str_replace('%', '', $fee);
					$type = 'percent';
				}

				if($type == 'fixed') {
					$apply_fee = $fee;
				} else {
					$subtotal = $cart->subtotal_ex_tax;
					$apply_fee = $subtotal*((float) $fee / 100.0);
				}				

				WC()->cart->add_fee($config['fee_desc'], $apply_fee, $taxable);
			}
		}
	}

	function reset_session_vars() {
		WC()->session->set('iwar_cart_fees', null);
	}
}

iw_add_action_class('IW_AddCartFee_Action');