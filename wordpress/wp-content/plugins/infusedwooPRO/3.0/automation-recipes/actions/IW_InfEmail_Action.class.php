<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfEmail_Action extends IW_Automation_Action {
	function get_title() {
		return "Send Email (using Infusionsoft Emailer)";
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
		add_action( 'wp_ajax_infusedwoo_data_src_infusion_emtemplates', array($this,'infusedwoo_data_src_infusion_emtemplates'),10, 0 );
	}

	function display_html($config = array()) {
		$html = 'Select Email Templates to send<br>';
		$html .= '<input type="text" name="emtemplate" class="iwar-dynasearch" data-src="infusion_emtemplates" placeholder="Start typing to find email templates..." />';
		$html .= '<div class="emtemplate-contain dynasearch-contain">';
		
		if(isset($config['emtemplate-val']) && is_array($config['emtemplate-val'])) {
			foreach($config['emtemplate-val'] as $k => $val) {
				$label = isset($config['emtemplate-label'][$k]) ? $config['emtemplate-label'][$k] : 'Email ID # ' . $val;
				$html .= '<span class="tag-item">';
				$html .= $label;
				$html .= '<input type="hidden" name="emtemplate-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="emtemplate-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div>';
		return $html;

	}

	function validate_entry($config) {
		if(empty($config['emtemplate-val'])) {
			return "Please enter at least one email template.";
		}
	}

	function process($config, $trigger) {
		if(isset($trigger->user_email) && !empty($trigger->user_email)) {
			if(!isset($trigger->infusion_contact_id)) {
				$trigger->search_infusion_contact_id();
			}

			global $iwpro;
			$template_ids = $config['emtemplate-val'];

			if(!$iwpro->ia_app_connect()) {
				return false;
			}

			// for each template, retrieve content:
			if(is_array($template_ids)) foreach($template_ids as $template_id) {
				$template = $iwpro->app->getEmailTemplate($template_id);

				$body = $trigger->merger->merge_text($template['htmlBody']);
				$subject = $trigger->merger->merge_text($template['subject']);

				// merge content:
				$iwpro->app->sendEmail(
					array($trigger->infusion_contact_id),
					$template['fromAddress'],
					"~Contact.Email~", 
					$template['ccAddress'],
					$template['bccAddress'],
					"HTML",
					$subject, 
					$body, 
					strip_tags($body)
				);

				$iwpro->app->attachEmail(
					$trigger->infusion_contact_id, 
					$template['fromAddress'], 
					$template['fromAddress'], 
					"~Contact.Email~", 
					$template['ccAddress'],
                    $template['bccAddress'], 
                    'HTML', 
                    $subject, 
                    $body, 
                    strip_tags($body),
                    '', 
                    date("Y-m-d"), 
                    date("Y-m-d"), 
                    1
                 );
			}
		}
	}

	function infusedwoo_data_src_infusion_emtemplates() {
		global $iwpro;

		if(!$iwpro->ia_app_connect()) return false;
		$emails = $iwpro->app->dsQuery('Template',100,0,
			array(
				'PieceTitle' => "%{$_GET['term']}%",
				'PieceType'	=> 'Email'
			), 
			array('Id','PieceTitle'));

		$result = array();
		foreach($emails as $email) {
			$result[] = array(
					'label' => $email['PieceTitle'] . " [ {$email['Id']} ]",
					'value' => $email['Id'],
					'id' => $email['Id']
				);
		}

		echo json_encode($result);
		exit();

	}

}

iw_add_action_class('IW_InfEmail_Action');