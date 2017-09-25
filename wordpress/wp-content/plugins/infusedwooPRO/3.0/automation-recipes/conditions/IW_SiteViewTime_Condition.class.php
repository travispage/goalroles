<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_SiteViewTime_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If total time spent on the website is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger'
			);
	}

	function display_html($config = array()) {
		$minutes = isset($config['minutes']) ? $config['minutes'] : '';
		$html = 'Time in minutes&nbsp;&nbsp;';
		$html .= '<input type="text" name="minutes" value="'.$minutes.'" placeholder="" style="width: 100px;" />';
		return $html;
	}

	function validate_entry($conditions) {
		if($conditions['minutes'] <= 0) {
			return 'Please enter a positive value';
		}
	}


	function test($config, $trigger) {
		global $post;
		global $product;

		$first_site_visit = WC()->session->get('iw_first_site_visit');
		if(empty($first_site_visit)) {
			$first_site_visit = time();
			WC()->session->set('iw_first_site_visit', $first_site_visit);
		}

		$rem_time = ($first_site_visit + $config['minutes']*60) - time();

		if($rem_time <= 0 && $first_pg_visit > 0) {
			return true;
		} else {
			$this->config = $config;
			add_filter( 'iw_page_triggers', array($this, 'script_postpone'), 10, 1 );
			return false;
		}
	}

	function script_postpone($val) {
		$val[] = array('trigger_type' => 'site_visit', 'wait_time' => $this->config['minutes']*60);
		return $val;
	}
}

iw_add_condition_class('IW_SiteViewTime_Condition');