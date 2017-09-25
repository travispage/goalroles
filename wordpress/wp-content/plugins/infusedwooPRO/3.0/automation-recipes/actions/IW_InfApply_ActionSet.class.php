<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfRunActionSet_Action extends IW_Automation_Action {
	function get_title() {
		return "Run Action Set to Contact in Infusionsoft";
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

	function on_class_load() {
		add_action( 'wp_ajax_infusedwoo_data_src_infusion_actions', array($this,'infusedwoo_data_src_infusion_actions'),10 );
	}

	function display_html($config = array()) {
		$html = 'Select Actions to Apply to Contact<br>';
		$html .= '<input type="text" name="action" class="iwar-dynasearch" data-src="infusion_actions" placeholder="Start typing to add actions..." />';
		$html .= '<div class="action-contain dynasearch-contain">';
		
		if(isset($config['action-val']) && is_array($config['action-val'])) {
			foreach($config['action-val'] as $k => $val) {
				$label = isset($config['action-label'][$k]) ? $config['action-label'][$k] : 'Action ID # ' . $val;
				$html .= '<span class="action-item">';
				$html .= $label;
				$html .= '<input type="hidden" name="action-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="action-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div>';
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['action-val'])) {
			return "Please enter at least one action.";
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
					foreach($config['action-val'] as $action_id) {
						$iwpro->app->runAS($trigger->infusion_contact_id, (int) $action_id);
					}
				}
			} 
		} 
	}

	function infusedwoo_data_src_infusion_actions() {
		global $iwpro;

		if(!$iwpro->ia_app_connect()) return false;
		$actions = $iwpro->app->dsFind('ActionSequence',100,0,'TemplateName',"%{$_GET['term']}%", array('Id','TemplateName'));

		$result = array();
		foreach($actions as $action) {
			$result[] = array(
					'label' => $action['TemplateName'] . " [ {$action['Id']} ]",
					'value' => $action['Id'],
					'id' => $action['Id']
				);
		}

		echo json_encode($result);
		exit();

	}
}

iw_add_action_class('IW_InfRunActionSet_Action');