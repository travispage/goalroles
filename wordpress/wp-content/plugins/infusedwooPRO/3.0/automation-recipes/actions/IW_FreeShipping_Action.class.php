<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_FreeShipping_Action extends IW_Automation_Action {
	function get_title() {
		return "Give Shipping discount to current user";
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
		add_filter('woocommerce_package_rates', array($this, 'apply_discount_shipping'));
	}

	function display_html($config = array()) {
		$discount = isset($config['discount']) ? $config['discount'] : '100%';
		$html = 'Discount Amount  &nbsp;&nbsp;<input type="text" name="discount" value="'.$discount.'" placeholder="" style="width: 150px; top: 0" />';
		$html.= '<br><i style="font-size: 10pt;">Amount without % will be treated as fixed amount. E.g. 100% for free shipping.</i>';
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['discount'])) {
			return 'Please enter a discount amount';
		} else {
			$check_nan = str_replace('%', '', $config['discount']);
			if(!is_numeric($check_nan))
				return 'Discount amount must be a number or a percent value.'; 
		}
	}

	function process($config, $trigger) {
		if(is_admin()) return false;
		if(!WC()->session) return false;

		WC()->session->set('iwar_free_shipping', $config['discount']);
		
	}

	function apply_discount_shipping($packages) {
		if(is_admin()) return $packages;
		if(!WC()->session) return $packages;

		$shipping_discount = WC()->session->get('iwar_free_shipping');

		$type = 'fixed';
		if(strpos($shipping_discount, '%') !== false) {
			$shipping_discount = str_replace('%', '', $shipping_discount);
			$type = 'percent';
		}

		if($shipping_discount > 0) {
			

			foreach($packages as $k => $rate) {
				$orig_cost = $packages[$k]->cost;
				if($type == 'fixed') {
					$new_cost = $orig_cost - $shipping_discount;
				} else {
					$new_cost = $orig_cost - $orig_cost*((float) $shipping_discount / 100.0);
				}

				if($new_cost < 0) $new_cost = 0;

				$packages[$k]->cost = $new_cost;
			}
		}

		return $packages;
	}
}

iw_add_action_class('IW_FreeShipping_Action');