<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_HttpPost_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	public $merge_handlers = array(
			'HTTPPost' => array('HTTP Post Value', 'merge_handler_post_value')
		);
	
	function trigger_when() {
		if(isset($_GET['iwar_http_post'])) {
			add_action('init', array($this,'trigger'), 10, 0);
		}
	}

	public function get_desc() {
		return 'when HTTP POST is made. Ping URL will appear after saving ';
	}


	function get_title() {
		return 'HTTP POST Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-share"></i>';
	}

	function custom_condition($recipe_id, $configs) {
		return ($_GET['iwar_http_post'] == $recipe_id);
	}

	function admin_public_note($recipe_id, $configs) {
		if(isset($recipe_id) && $recipe_id > 0) {
			$html = "Send HTTP POSTs to this URL";
			$html .= '<div class="iwar-http-post">'. site_url('wp-load.php?iwar_http_post=' . $recipe_id) . '</div>';
			return $html;
		}
	}

	function get_contact_email() {
		$email = "";
		$email_src = array('Email','email','EMAIL','inf_field_Email','Contact0Email','email_address');
		foreach($email_src as $k) {
			if(isset($_POST[$k])) {
				$email = $_POST[$k];
				break;
			}
		}

		if(empty($email)) {
			foreach($email_src as $k) {
				if(isset($_GET[$k])) {
					$email = $_GET[$k];
					break;
				}
			}
		}


		return $email;
	}

	function get_log_details() {
		return print_r($_POST, 1);
	}

	function merge_fields() {
		return array('HTTPPost' => array());
	}

	function merge_handler_post_value($key) {
		return isset($_POST[$key]) ? $_POST[$key] : "";
	}
}

iw_add_trigger_class('IW_HttpPost_Trigger');
