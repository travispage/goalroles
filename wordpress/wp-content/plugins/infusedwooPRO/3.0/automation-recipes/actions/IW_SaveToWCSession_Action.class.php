<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_SaveToWCSession_Action extends IW_Automation_Action {
	function get_title() {
		return "Save a value as session variable";
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_AddToCart_Trigger',
				'IW_Checkout_Trigger',
				'IW_PageVisit_Trigger',
				'IW_WishlistEvent_Trigger'
			);
	}

	function display_html($config = array(), $trigger_class = "") {
		$ofields = isset($config['ofields']) ? $config['ofields'] : array('');
		$ovalues = isset($config['ovalues']) ? $config['ovalues'] : array('');
		$html = '<div class="iwar_oinf_fields">';

		foreach($ofields as $i => $ofield) {
			$html .= '<div class="oinf_field">';
			$html .= '<input type="text" name="ofields[]" value="'.$ofields[$i].'" style="width: 45%" placeholder="Session Key" class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			$html .= '&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="ovalues[]" value="'.$ovalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	function validate_entry($config) {
		if(!is_array($config['ofields'])) return "Please ensure that all order fields are not empty";
		
		foreach($config['ofields'] as $k => $key) {
			if(empty($key)) return "Please ensure all order fields are not empty";
		}
	}

	function process($config, $trigger) {
		foreach($config['ofields'] as $k => $key) {
			$key_m = $trigger->merger->merge_text($key); 
			$val_m = $trigger->merger->merge_text($config['ovalues'][$k]);
			
			WC()->session->set("iwc_{$key_m}", $val_m);
		}
	}
}

iw_add_action_class('IW_SaveToWCSession_Action');