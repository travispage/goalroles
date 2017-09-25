<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Redirect_Action extends IW_Automation_Action {
	function get_title() {
		return "Redirect User to Another Page";
	}

	function on_class_load() {
		add_action('template_redirect', array($this,'check_for_redir'),11,0);
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger'
			);
	}

	function display_html($config = array()) {
		$url = isset($config['url']) ? $config['url'] : '';

		$html = 'URL &nbsp;&nbsp;<input type="text" name="url" value="'.$url.'" placeholder="http://" class="iwar-mergeable"  />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
	
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['url'])) {
			return 'Please enter URL';
		}
	}

	function process($config, $trigger) {
		$url = $trigger->merger->merge_text($config['url']);

		if(get_class($trigger) == 'IW_HttpPost_Trigger') {
			wp_redirect($url);
		} else {
			WC()->session->set('iwar_redir', $url);
		}
	}

	function check_for_redir() {
		$url = WC()->session->get('iwar_redir');
		if(!empty($url)) {
			WC()->session->set('iwar_redir', null);
			wp_redirect($url);
			exit();
		}
	}
}

iw_add_action_class('IW_Redirect_Action');