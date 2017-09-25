<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Checkout_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;

	function trigger_when() {
		if(is_user_logged_in()) {
			add_action('template_redirect', array($this,'checkout_visit'), 9);
		} else {
			add_action('infusedwoo_order_process', array($this, 'checkout_trigger'),10,2);
		}
	}

	public function get_desc() {
		return 'when customer is about to checkout';
	}


	function get_title() {
		return 'Attempts to Checkout Trigger';
	}

	function get_icon() {
		return '<i class="fa fa-credit-card"></i>';
	}

	function get_contact_email() {
		if(is_user_logged_in()) {
			$user = wp_get_current_user();
			$email = get_user_meta($user->ID, 'billing_email', true);
			if(empty($email)) $email = $user->user_email;

			return $email;
		} else if(isset($_POST['billing_email'])) {
			return $_POST['billing_email'];
		} else {
			return WC()->session->get('session_email');
		}
	}

	function get_log_details() {
		return "";
	}

	function checkout_visit() {
		if(is_checkout()) {
			$cart =& WC()->cart;
			$contents = $cart->get_cart();

			if(is_array($contents) && count($contents) > 0) {
				$this->trigger();
			}
		}
	}

	function checkout_trigger($contact_id, $contact_email) {
		$this->user_email = $contact_email;
		$this->trigger();
	}
}

iw_add_trigger_class('IW_Checkout_Trigger');