<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_AddToCart_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	
	function trigger_when() {
		add_action('woocommerce_add_to_cart', array($this,'trigger'), 10, 6);
	}

	public function get_desc() {
		return 'when customer adds item to cart';
	}


	function get_title() {
		return 'Add to Cart Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-cart-plus"></i>';
	}

	function get_contact_email() {
		if(is_user_logged_in()) {
			$user = wp_get_current_user();
			$email = get_user_meta($user->ID, 'billing_email', true);
			if(empty($email)) $email = $user->user_email;

			return $email;
		} else return "";
	}

	function get_log_details() {
		$product_id = $this->pass_vars[1];
		$var_id = $this->pass_vars[3];
		if(!empty($var_id)) {
			return "Added Variation # " . $var_id;
		} else {
			return "Added Product # " . $product_id;
		}
	}
}

iw_add_trigger_class('IW_AddToCart_Trigger');