<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_PageViewTime_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If total time spent on the page / post is ...';
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

		if(!is_user_logged_in()) {
			global $woocommerce;
			$woocommerce->session->set_customer_session_cookie(true);
		}


		if(isset($post->ID) || isset($product->ID)) {
			$pg_id = isset($post->ID) ? $post->ID : $product->ID;
			$first_pg_visit = WC()->session->get('iw_first_pg_visit_' . $pg_id);
			if(empty($first_pg_visit)) {
				$first_pg_visit = time();
				WC()->session->set("iw_first_pg_visit_{$pg_id}" , $first_pg_visit);
			}
		} else {
			$first_pg_visit = 0;
		}

		$rem_time = ($first_pg_visit + $config['minutes']*60) - time();

		if($rem_time <= 0 && $first_pg_visit > 0) {
			return true;
		} else {
			$this->config = $config;
			add_filter( 'iw_page_triggers', array($this, 'script_postpone'), 10, 1 );
		}
	}

	function script_postpone($val) {
		$val[] = array('trigger_type' => 'page_visit', 'wait_time' => $this->config['minutes']*60);
		return $val;
	}
}

iw_add_condition_class('IW_PageViewTime_Condition');