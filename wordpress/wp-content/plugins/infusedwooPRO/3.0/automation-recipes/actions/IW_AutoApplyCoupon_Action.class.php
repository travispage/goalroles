<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_AutoApplyCoupon_Action extends IW_Automation_Action {
	function get_title() {
		return "Auto-apply coupon code to customer's cart";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger',
				'IW_HttpPost_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function display_html($config = array()) {
		$coupon_code = isset($config['coupon']) ? $config['coupon'] : '';
		$html = 'Coupon Code  &nbsp;&nbsp;<input type="text" name="coupon" value="'.$coupon_code.'" placeholder="" class="iwar-mergeable" style="width: 250px; top: 0" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['coupon'])) {
			return 'Please enter a coupon code';
		} 	
	}

	function process($config, $trigger) {
		$this->config = $config;
		$this->trigger = $trigger; 
		add_action( 'woocommerce_before_calculate_totals', array($this, 'apply_coupon'), 10, 0 );
	}

	function apply_coupon() {
		$coupon_code = $this->trigger->merger->merge_text($this->config['coupon']);
		$the_coupon = new WC_Coupon( $coupon_code );

		if(!WC()->cart->has_discount($coupon_code) && $the_coupon->is_valid()) {
			WC()->cart->add_discount($coupon_code);
		}
	}
}

iw_add_action_class('IW_AutoApplyCoupon_Action');