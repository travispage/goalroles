<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_SetGIFTimer_Action extends IW_Automation_Action {
	function get_title() {
		return "Set GIF Timer Date/Time";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger',
				'IW_HttpPost_Trigger',
				'IW_UserAction_Trigger'
			);
	}

	function display_html($config = array()) {
		$datetime = isset($config['datetime']) ? $config['datetime'] : '';

		$html = 'Enter Date / Time (Y-m-d H:i:s) &nbsp;&nbsp;<input type="text" name="datetime" value="'.$datetime.'" placeholder="e.g. '.date('Y-m-d', time()+24*3600).'" class="iwar-mergeable"  />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
	
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['datetime'])) {
			return 'Please enter a value';
		}
	}

	function process($config, $trigger) {
		$time = $trigger->merger->merge_text($config['datetime']);

		// session update
		$trigger->iw_timer_gif = $time;
		$timer_ses = WC()->session->get('iw_timer_gif');
		$from_now = strtotime($timer_ses) - time();

		if(empty($timer_ses) || $from_now <= 0) {
			WC()->session->set('iw_timer_gif', $time);
		}


	}
}

iw_add_action_class('IW_SetGIFTimer_Action');