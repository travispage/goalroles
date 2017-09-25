<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderCreation_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	
	function trigger_when() {
		add_action('woocommerce_checkout_update_order_meta', array($this,'trigger'), 10, 1);
	}

	public function get_desc() {
		return 'when an order is created in woocommerce (paid or unpaid)';
	}


	function get_title() {
		return 'Order Creation Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-shopping-basket" style="left: 1px;"></i>';
	}

	function get_contact_email() {
		$order_id = $this->pass_vars[0];

		$this->log_details = "Woo Order ID # " . $order_id;

		$wc_order = new WC_Order( $order_id );

		if(method_exists($wc_order, 'get_billing_email')) {
			return $wc_order->get_billing_email();
		} else {
			return $wc_order->billing_email;
		}
		
	}

	function get_log_details() {
		return $this->log_details;
	}

	function get_user_cookie_ip() {
		$order_id = $this->pass_vars[0];

		$cookie = get_post_meta( $order_id, 'iwar_saved_cookie', true );
		$ip = get_post_meta( $order_id, 'iwar_saved_ip', true );

		if(!empty($cookie)) {
			$this->user_cookie = $cookie;
			$this->user_ip = $ip;
		}

	}

	function save_user_cookie_to_order($order_id) {
		$cookie = get_post_meta( $order_id, 'iwar_saved_cookie', true );

		if(!is_admin() && empty($cookie)) {
			$wc_order = new WC_Order($order_id);
			$this->user_email = $wc_order->billing_email;

			$this->identify_user();
			update_post_meta( $order_id, 'iwar_saved_cookie', $this->user_cookie);
			update_post_meta( $order_id, 'iwar_saved_ip', $this->user_ip);
		}
	}
}

iw_add_trigger_class('IW_OrderCreation_Trigger');