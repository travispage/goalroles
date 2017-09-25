<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_TotalCartValue_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If Shopping Cart total value is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_CartAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$currency = get_woocommerce_currency_symbol();
		$type = isset($config['totaltype']) ? $config['totaltype'] : '';
		$compare = isset($config['compare']) ? $config['compare'] : '';
		$amount = isset($config['amount']) ? $config['amount'] : '';

		$html = '<select name="totaltype" style="width: 55%">';
		$html .= '	<option value="subtotal"'.($type == 'subtotal' ? ' selected' : '').'>Cart Subtotal</option>';
		$html .= '	<option value="subtotal-tax"'.($type == 'subtotal-tax' ? ' selected' : '').'>Cart Subtotal + Tax</option>';
		$html .= '</select>&nbsp;&nbsp;';

		$html .= '<select name="compare" style="width: 40%">';
		$html .= '	<option value="greater"'.($compare == 'greater' ? ' selected' : '').'>is greater than</option>';
		$html .= '	<option value="equal"'.($compare == 'equal' ? ' selected' : '').'>is equal to</option>';
		$html .= '	<option value="less"'.($compare == 'less' ? ' selected' : '').'>is less than</option>';
		$html .= '</select><br>';

		$html .= '<div style="padding: 10px 7px;">Enter Value in '. $currency .' <input type="text" name="amount" value="'.$amount.'" placeholder="0.00" style="width: 100px; position:relative; top: -2px;"/></div>';


		return $html;
	}

	function validate_entry($conditions) {
		if($conditions['amount'] == '') {
			return "Please enter an amount.";
		} else if(!is_numeric($conditions['amount'])) {
			return "Amount should be a numerical value.";
		}
	}


	function test($config, $trigger) {
		global $woocommerce;
		$cart = $woocommerce->session->get('cart');
		$product_id = $trigger->pass_vars[1];
		if(!empty($trigger->pass_vars[3])) $product_id = $trigger->pass_vars[3];
		$wc_product = wc_get_product($product_id);
		$trigger_class = get_class($trigger);

		if($trigger_class == 'IW_AddToCart_Trigger') {
			$total = $wc_product->get_price_including_tax();
			$tax = $total - $wc_product->get_price_excluding_tax();
		} else {
			$total = 0;
			$tax = 0;
		}
		
		
		foreach($cart as $item) {
			if(!empty($item['variation_id'])) {
				$prod_id = $item['variation_id'];
			} else {
				$prod_id = $item['product_id'];
			}

			$this_prod = wc_get_product($prod_id);

			$prod_price = $this_prod->get_price_including_tax();
			$total += $prod_price; 
			$tax += ($prod_price-$this_prod->get_price_excluding_tax());
		}

		$subtotal = $total - $tax;

		if($config['totaltype'] == 'subtotal') $tocompare = round((float) $subtotal, 2);
		else $tocompare = round((float) $total, 2);

		$amount = round((float) $config['amount'], 2);

		if($config['compare'] == 'greater') {
			if($tocompare > $amount) return true;
		} else if($config['compare'] == 'equal') {
			if($tocompare == $amount) return true;
		} else {
			if($tocompare < $amount) return true;
		}
		
		return false;
	}
}

iw_add_condition_class('IW_TotalCartValue_Condition');