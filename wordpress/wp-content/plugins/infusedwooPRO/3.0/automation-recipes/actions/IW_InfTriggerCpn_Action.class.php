<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfTriggerCpn_Action extends IW_Automation_Action {
	function get_title() {
		return "Trigger a Campaign API Goal in Infusionsoft";
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_WooSubEvent_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function display_html($config = array()) {
		global $iwpro;
		$integration = isset($config['integration']) ? $config['integration'] : $iwpro->machine_name;
		$callname = isset($config['callname']) ? $config['callname'] : base_convert(time(), 10, 36);
		$html = '<span style="font-size: 10pt">Enter the API Goal Integration and Call Name. <a href="http://infusedaddons.com/guides/infusedwoo/using-api-goals-in-infusionsoft/" target="_blank">Click here to learn more about API goals.</a></span>';
		$html .= '<table style="margin-top: 5px;">';
		$html .= '<tr>';
		$html .= '<td>Integration </td>';
		$html .= '<td><input name="integration" type="text" value="'.$integration.'" placeholder="Integration Name" style="width: 200px; position: relative; top: -1px; left: 2px;"/> </td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>Call Name </td>';
		$html .= '<td><input name="callname" type="text" value="'.$callname.'" placeholder="Call Name" style="width: 200px; position: relative; top: -1px; left: 2px;"/> </td>';
		$html .= '</tr>';
		$html .= '</table>';

		return $html;
	}

	function validate_entry($config) {
		if(empty($config['integration'])) {
			return "Please enter integration name.";
		} else if(empty($config['callname'])) {
			return "Please enter call name.";
		} else if(!ctype_alnum($config['callname']) && !(strpos($config['callname'], '{{') !== false)) {
			return "Call name must only contain alphanumeric characters";
		}

	}

	function process($config, $trigger) {
		if(isset($trigger->user_email) && !empty($trigger->user_email)) {
			if(!isset($trigger->infusion_contact_id)) {
				$trigger->search_infusion_contact_id();
			}

			if(!empty($trigger->infusion_contact_id)) {
				global $iwpro;

				if($iwpro->ia_app_connect()) {
					$integration = $trigger->merger->merge_text($config['integration']);
					$callname = $trigger->merger->merge_text($config['callname']);

					$iwpro->app->achieveGoal($integration, $callname, $trigger->infusion_contact_id);
				}
			} 
		} 
	}
}

iw_add_action_class('IW_InfTriggerCpn_Action');