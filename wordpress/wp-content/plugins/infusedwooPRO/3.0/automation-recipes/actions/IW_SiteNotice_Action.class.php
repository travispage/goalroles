<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_SiteNotice_Action extends IW_Automation_Action {
	function get_title() {
		return "Display site notice to user";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger'			
			);
	}

	function display_html($config = array()) {
		$notice = isset($config['notice']) ? $config['notice'] : '';

		$html = '<hr>Show Notice : <br> <input type="text" name="notice" value="'.$notice.'" placeholder="Enter Site Notice to show" class="iwar-mergeable" style="width: 380px; margin-top: 5px;" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
	
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['notice'])) return "Please enter site notice message.";
	}

	function process($config, $trigger) {
		$this->notice = $trigger->merger->merge_text($config['notice']);
		add_action( 'wp_footer', array($this, 'show_notice'), 10, 0 );
		add_filter( 'iw_page_live_actions', array($this,'show_notice_live'), 10, 1 );
		add_filter('iwar_live_ran', array($this, 'iwar_live_ran'), 10, 1);
	}

	function show_notice_live($fcns) {
		$notice = '<p class="demo_store infusedwoo_custom_notice"><span><b>'.$this->notice.'</b></span></p>';
		$notice = json_encode($notice);
		$fcns[] = array(
				'id'	=> $this->recipe_id_proc . '-' . $this->sequence_id,
				'to_eval' => 1,
				'eval' => 'if(jQuery(".infusedwoo_custom_notice").length <= 0) {jQuery(".iwar_footer_control").append('.$notice.')}'
			);

		return $fcns;
	}

	function show_notice() {
		echo '<p class="demo_store infusedwoo_custom_notice"><span><b>'.$this->notice.'</b></span></p>';
	}

	function iwar_live_ran($ran) {
		$ran[] = $this->recipe_id_proc . '-' . $this->sequence_id;
		return $ran;
	}
}

iw_add_action_class('IW_SiteNotice_Action');