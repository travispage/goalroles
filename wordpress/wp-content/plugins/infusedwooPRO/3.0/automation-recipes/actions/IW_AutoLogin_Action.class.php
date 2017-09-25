<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_AutoLogin_Action extends IW_Automation_Action {
	function get_title() {
		return "Autologin User to Wordpress";
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
		$user_login = isset($config['user_login']) ? $config['user_login'] : '';

		$html = 'User Login &nbsp;&nbsp;<input type="text" name="user_login" value="'.$user_login.'" placeholder="username" class="iwar-mergeable"  />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
	
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['user_login'])) return "Please enter User Login";
	}

	function process($config, $trigger) {
		if(!is_user_logged_in()) {
			$loginusername = $trigger->merger->merge_text($config['user_login']);
	        //get user's ID
	        $user = get_user_by('login', $loginusername);
	        if($user->ID > 0) {
		        $user_id = $user->ID;
		       	
		        if (!$user->has_cap('read_private_posts')) {
		                $user->add_cap('read_private_posts');
		        }                                                           
		        //login
		        wp_set_current_user($user_id, $loginusername);
		        wp_set_auth_cookie($user_id);
		        do_action('wp_login', $loginusername, $user);
	    	}
    	}
	}
}

iw_add_action_class('IW_AutoLogin_Action');