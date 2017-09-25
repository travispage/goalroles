<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OrderStatusChange_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	
	function trigger_when() {
		add_action('woocommerce_order_status_changed', array($this,'trigger'), 10, 3);
	}

	public function get_desc() {
		return 'when an order in woocommerce changes status';
	}


	function get_title() {
		return 'Order Status Change Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-flag"></i>';
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
}

iw_add_trigger_class('IW_OrderStatusChange_Trigger');