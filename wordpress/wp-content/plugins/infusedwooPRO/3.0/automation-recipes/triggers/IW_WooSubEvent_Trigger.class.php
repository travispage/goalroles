<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WooSubEvent_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	
	function trigger_when() {
		add_action('woocommerce_scheduled_subscription_payment', array($this,'trigger_scheduled_subscription_payment'), 10, 1);
		add_action('woocommerce_subscription_renewal_payment_complete', array($this,'trigger_renewal_payment_complete'), 10, 1);
		add_action('woocommerce_subscription_payment_failed', array($this,'trigger_payment_failed'), 10, 1);
		add_action('woocommerce_subscription_payment_complete', array($this,'trigger_payment_complete'), 10, 1);
		add_action('woocommerce_subscription_renewal_payment_failed', array($this,'trigger_renewal_payment_failed'), 10, 1);
		add_action('woocommerce_subscription_status_changed', array($this,'trigger_status_changed'), 10, 3);

		add_action('woocommerce_scheduled_subscription_trial_end', array($this,'trigger_trial_end'), 10, 1);
		add_action('woocommerce_scheduled_subscription_end_of_prepaid_term', array($this,'trigger_end_of_prepaid_term'), 10, 1);
		
	}

	public function get_desc() {
		return 'when an event happens related to a Woo Subscription';
	}


	function get_title() {
		return 'Woocommerce Subscription Event Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-refresh" style="left: 2px; position: relative;"></i>';
	}

	function get_contact_email() {
		$s_key = $this->pass_vars[1];

		if(is_int($s_key)) {
			$subscription = new WC_Subscriptions( $s_key );
		} else {
			$subscription = $s_key;
		}
		
		$this->log_details = "Subscription # " . $subscription->id;
		return $subscription->billing_email;
	}

	function get_log_details() {
		return $this->log_details;
	}

	function get_user_cookie_ip() {

	}

	function __call($method, $args) {
		if(strpos($method, 'trigger_') !== false) {
			$this->pass_vars = $args;
			$this->sub_event = str_replace('trigger_', '', $method);

			$this->trigger();
		}
	}
}

iw_add_trigger_class('IW_WooSubEvent_Trigger');