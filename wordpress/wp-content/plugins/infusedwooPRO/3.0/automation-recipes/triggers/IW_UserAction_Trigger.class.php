<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_UserAction_Trigger extends IW_Automation_Trigger {
	public $support_caching = true;
	
	function trigger_when() {
		add_action('woocommerce_before_checkout_form', array($this,'trigger_reached_checkout'), 10, 0);
		add_action('woocommerce_checkout_process', array($this,'pressed_checkout_button'),10,0);
		add_action('woocommerce_cart_is_empty', array($this,'cart_emptied'));
		add_action('wp_login', array($this,'wp_login'),10,2);
		add_action('user_register', array($this,'user_register'));
		add_action('wp_logout', array($this,'wp_logout'));
	}

	public function get_desc() {
		return 'when a specfic action is made by customer in wordpress';
	}


	function get_title() {
		return 'User Action Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-bolt" style="position:relative; left: 6px;"></i>';
	}

	function get_contact_email() {
		if($this->event == 'wp_login') {
			$user = $this->pass_vars[1];
			return $user->user_email;
		} else {
			if(is_user_logged_in()) {
				$user = wp_get_current_user();
				$email = get_user_meta($user->ID, 'billing_email', true);
				if(empty($email)) $email = $user->user_email;

				return $email;
			} else if(isset($_POST['billing_email'])) {
				return $_POST['billing_email'];
			} else return "";
		}
	}


	function trigger_reached_checkout() {
		if(is_user_logged_in()) {
			$pass_vars = func_get_args();
			$this->pass_vars = $pass_vars;
			$this->event = 'reachedcheckout';

			$this->trigger();
		}
	}

	function pressed_checkout_button() {
		if(!is_user_logged_in()) {
			$pass_vars = func_get_args();
			$this->pass_vars = $pass_vars;
			$this->event = 'reachedcheckout';

			$this->trigger();
		}
	}

	function cart_emptied() {
		$pass_vars = func_get_args();
		$this->pass_vars = $pass_vars;
		$this->event = 'cartemptied';

		$this->trigger();
	}

	function wp_login() {
		$pass_vars = func_get_args();
		$this->pass_vars = $pass_vars;
		$this->event = 'wp_login';

		$this->trigger();
	}

	function user_register() {
		$pass_vars = func_get_args();
		$this->pass_vars = $pass_vars;
		$this->event = 'user_register';

		$this->trigger();
	}

	function wp_logout() {
		$pass_vars = func_get_args();
		$this->pass_vars = $pass_vars;
		$this->event = 'wp_logout';

		$this->trigger();
	}





	function get_log_details() {
		return "Event " . $this->event;
	}
}

//iw_add_trigger_class('IW_UserAction_Trigger');