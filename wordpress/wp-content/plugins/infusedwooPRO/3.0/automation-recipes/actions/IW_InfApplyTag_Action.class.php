<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfApplyTag_Action extends IW_Automation_Action {
	function get_title() {
		return "Apply Tag to Contact in Infusionsoft";
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
		add_action( 'wp_ajax_infusedwoo_data_src_infusion_tags', array($this,'infusedwoo_data_src_infusion_tags'),10 );
	}

	function display_html($config = array()) {
		$html = 'Select Tags to Apply to Contact<br>';
		$html .= '<input type="text" name="tag" class="iwar-dynasearch" data-src="infusion_tags" placeholder="Start typing to add tags..." />';
		$html .= '<div class="tag-contain dynasearch-contain">';
		
		if(isset($config['tag-val']) && is_array($config['tag-val'])) {
			foreach($config['tag-val'] as $k => $val) {
				$label = isset($config['tag-label'][$k]) ? $config['tag-label'][$k] : 'Tag ID # ' . $val;
				$html .= '<span class="tag-item">';
				$html .= $label;
				$html .= '<input type="hidden" name="tag-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="tag-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div>';
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['tag-val'])) {
			return "Please enter at least one tag.";
		}

	}

	function process($config, $trigger) {
		if(isset($trigger->user_email) && !empty($trigger->user_email)) {
			if(!isset($trigger->infusion_contact_id)) {
				$trigger->search_infusion_contact_id(true);
			}

			if(!empty($trigger->infusion_contact_id)) {
				global $iwpro;

				if($iwpro->ia_app_connect()) {
					foreach($config['tag-val'] as $tag_id) {
						$iwpro->app->grpAssign($trigger->infusion_contact_id, (int) $tag_id);
					}
				}
			} 
		} 
	}

	function infusedwoo_data_src_infusion_tags() {
		global $iwpro;

		if(!$iwpro->ia_app_connect()) return false;
		$tags = $iwpro->app->dsFind('ContactGroup',100,0,'GroupName',"%{$_GET['term']}%", array('Id','GroupName'));

		$result = array();
		foreach($tags as $tag) {
			$result[] = array(
					'label' => $tag['GroupName'] . " [ {$tag['Id']} ]",
					'value' => $tag['Id'],
					'id' => $tag['Id']
				);
		}

		echo json_encode($result);
		exit();

	}
}

iw_add_action_class('IW_InfApplyTag_Action');