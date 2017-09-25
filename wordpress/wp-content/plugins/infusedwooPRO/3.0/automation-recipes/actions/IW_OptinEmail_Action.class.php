<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_OptinEmail_Action extends IW_Automation_Action {
	function get_title() {
		return "Mark email address as marketable in Infusionsoft";
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_Checkout_Trigger',
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_WooSubEvent_Trigger'
			);
	}

	function display_html($config = array()) {
		$email = isset($config['email']) ? $config['email'] : '';
		$reason = isset($config['reason']) ? $config['reason'] : '';

		$html = 'Email Address &nbsp;&nbsp;<input type="text" name="email" value="'.$email.'" placeholder="" class="iwar-mergeable"  />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i><br>';
		$html .= '<div style="height:10px; width: 100%;"></div>Reason / Source of contact<br><textarea name="reason" style="width: 80%; height: 50px;">'.$reason.'</textarea>';
		
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['email'])) return "Please enter email address";
		if(empty($config['reason'])) return "Please enter reason / source of contact";

	}

	function process($config, $trigger) {
		global $iwpro;
		
		if($iwpro->ia_app_connect()) {
			$email = $trigger->merger->merge_text($config['email']);
			$iwpro->app->optIn($email, $config['reason']);
    	}
	}
}

iw_add_action_class('IW_OptinEmail_Action');