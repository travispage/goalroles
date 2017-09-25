<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Automation_Trigger {
	protected $name;
	protected $icon;
	private $trigger_classes;
	public $pass_vars;
	public $is_advanced = false;

	public function trigger() {
		if(!isset($this->pass_vars) || !is_array($this->pass_vars)) {
			$pass_vars = func_get_args();
			$this->pass_vars = $pass_vars;
		}
		$this->identify_user();

		$args = array(
			 'posts_per_page' 	=> -1,
			 'post_type' 		=> 'iw_automation_recipe',
			 'post_status' 		=> 'iw-enabled',
			 'meta_key'			=> 'iw_trigger_class',
			 'meta_value'		=> get_class($this)
		);
		$recipes_unfiltered = get_posts( $args );

		foreach($recipes_unfiltered as $recipe) {
			$recipe_id = $recipe->ID;

			$configs 	= get_post_meta( $recipe_id, 'iw_recipe_config',true );
			$conditions	= isset($configs['conditions']) ? $configs['conditions'] : array();

			$proceed = true;

			if(method_exists($this, 'custom_condition')) {
				if(!$this->custom_condition($recipe_id, $configs)) $proceed = false;
			}

			if($proceed) {
				$this->merger = new IW_Automation_MergeFields($this, $configs);
				$this->merger->early_fetch();

				foreach($conditions as $k => $cond) {
					if(class_exists($cond['condition'])) {
						$test_condition = new $cond['condition'];
						$conf = isset($cond['config']) ? $cond['config'] : array();
						$test_condition->recipe_id_proc = $recipe_id;
						$test_condition->sequence_id = $k;
						if(!$test_condition->test($conf, $this)) {
							$proceed = false;
							break;
						}
					}
				}
			}

			if($proceed) {
				// process actions
				$this->run_action($recipe_id);

				// log stats
				$stat_logging_override = get_post_meta( $recipe_id, 'iwar_logging_override',true );
				if(!(isset($stat_logging_override) && $stat_logging_override == 'disabled')) {
					if(method_exists($this, 'get_log_details')) {
						$details = $this->get_log_details();
					} else {
						$details = "";
					}
					$this->log_stats($recipe_id, $details);
				}

				update_post_meta( $recipe_id, 'iwar_last_triggered', time() );
			}

		}

	}

	public function get_icon() {
		return '<i class="fa fa-bolt"></i>';
	}

	public function get_desc() {
		return $this->get_title();
	}

	public function identify_user() {
		if(isset($this->user_email) && !empty($this->user_email)) {
			$email = $this->user_email;
		} else {
			$email = method_exists($this, 'get_contact_email') ? $this->get_contact_email() : "";
		}

		$this->user_email = apply_filters( 'iwar_get_user_email', $email, $this );
		$this->user_extras = apply_filters( 'iwar_user_extras', array(), $this);

		do_action('iwar_identify_user');

		$ip = $_SERVER['REMOTE_ADDR'];
		$this->user_cookie = "";
		$this->user_ip = $ip;
		
	}

	public function log_stats($recipe_id, $details="") {
		$stat_disabled = get_post_meta( $recipe_id, 'disable_stats', true );
		if($stat_disabled) return false;

		$time = current_time('mysql');
		
		$log = array(
				'cookie' => isset($this->user_cookie) ? $this->user_cookie : "",
				'IP'	=> isset($this->user_ip) ? $this->user_ip : "",
				'email' => isset($this->user_email) ? $this->user_email : "",
				'remarks' => $details
			);

		$data = array(
		    'comment_post_ID' => $recipe_id,
		    'comment_author' => 'InfusedWoo',
		    'comment_author_email' => 'infusedwoo@localhost',
		    'comment_author_url' => '',
		    'comment_content' => serialize($log),
		    'comment_type' => 'iw_recipe_stats',
		    'comment_parent' => 0,
		    'user_id' => 0,
		    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
		    'comment_agent' => 'InfusedWoo',
		    'comment_date' => $time,
		    'comment_approved' => 1,
		);

		wp_insert_comment($data);
	}

	public function add_trigger_class($trigger_class) {
		if(!is_array($this->trigger_classes)) {
			$this->trigger_classes = array();
		}

		$this->trigger_classes[] = $trigger_class;

		$new_trigger = new $trigger_class;
		$new_trigger->trigger_when();
	}

	public function get_available_triggers() {
		$trigger_classes = is_array($this->trigger_classes) ? $this->trigger_classes : array();

		$triggers = array();

		foreach($trigger_classes as $trigger) {
			$triggers[$trigger] = new $trigger;
		}

		return $triggers;
	}

	public function search_infusion_contact_id($add_contact = false) {
		if(!isset($this->user_email)) $this->identify_user();

		if(!empty($this->user_email)) {
			global $iwpro;
			if($iwpro->ia_app_connect()) {
				$contact = $iwpro->app->dsFind('Contact',1,0,'Email',$this->user_email, array('Id'));
				if(isset($contact[0]['Id'])) {
					$this->infusion_contact_id = (int) $contact[0]['Id'];
				} else if($add_contact) {
					$this->infusion_contact_id = $iwpro->app->dsAdd('Contact',array('Email' => $this->user_email));
				}
				return $this->infusion_contact_id;
			} else {
				$this->infusion_contact_id = 0;
				return 0;
			}
		} else {
			$this->infusion_contact_id = 0;
			return 0;
		}
	}

	public function search_wp_user_id() {
		if(!isset($this->user_email)) $this->identify_user();

		if(!empty($this->user_email)) {
			$user = get_user_by('email', $this->user_email);
			if(isset($user->ID)) {
				$user_id = (int) $user->ID;
			} else {
				$users = get_users(array( 
				        'meta_key'     => 'billing_email',
				        'meta_value'   => $this->user_email,
				        'fields' 	   => 'ids'
				));

				$user_id = isset($users[0]) ? $users[0] : 0;
			}

			if($user_id > 0) {
				$this->wp_user_id = $user_id;
				return $user_id;
			} else {
				$this->wp_user_id = 0;
				return 0;
			}
		} else {
			$this->wp_user_id = 0;
			return 0;
		}
	}

	public function run_action($recipe_id) {
		$configs = get_post_meta( $recipe_id, 'iw_recipe_config',true );
		$actions = isset($configs['actions']) ? $configs['actions'] : array();
		$this->recipe_id_proc = $recipe_id;

		$default_email = $this->user_email; 
		$manual = $this;

		foreach($actions as $k => $action) {
			if(class_exists($action['action'])) {
				$to_process = new $action['action'];
				$action_config = isset($action['config']) ? $action['config'] : array();

				if(!empty($action_config['_override_email'])) {
					if(!(isset($manual) && $manual->user_email == $action_config['_override_email'])) {
						$copy_trigger = get_post_meta( $recipe_id, 'iw_trigger_class', true);
						$manual = new $copy_trigger;
						$manual->merger = new IW_Automation_MergeFields($manual, array());
						$manual->pass_vars = $this->pass_vars;
						$manual->user_email = $this->merger->merge_text($action_config['_override_email']);
					}

					$to_process->recipe_id_proc = $recipe_id;
					$to_process->sequence_id = $k;
					$process_permit = apply_filters( 'iwar_process_action', true, $to_process, $manual );
					if($process_permit) $to_process->process($action_config, $manual);
				} else {
					$to_process->recipe_id_proc = $recipe_id;
					$to_process->sequence_id = $k;

					$process_permit = apply_filters( 'iwar_process_action', true, $to_process, $this );

					if($process_permit) $to_process->process($action_config, $this);
				}

				
			}
		}
	}

	public function compare_val($val1, $op, $val2) {
		$check = false;

		if($op == 'equal') 
			$check = ($val1 == $val2);
		else if($op == 'like') 
			$check = (strtolower($val1) == strtolower($val2));
		else if($op == 'greater')
			$check = ($val1 > $val2);
		else if($op == 'less')
			$check = ($val1 < $val2);
		else if($op == 'contain')
			$check = (strpos($val1, $val2) !== false);
		else if($op == 'notequal')
			$check = ($val1 != $val2);
		else if($op == 'endswith') 
			$check = ($val2 === "" || (($temp = strlen($val1) - strlen($val2)) >= 0 && strpos($val1, $val2, $temp) !== false));
		else if($op == 'startswith')
			$check = ($val2 === "" || strrpos($val1, $val2, -strlen($val1)) !== false);
		else if($op == 'equaldate') {
			$date1 = date("Y-m-d", strtotime($val1));
			$date2 = date("Y-m-d", strtotime($val2));

			$check = ($date1 == $date2);
		} else if($op == 'greaterdate') {
			$ts1 = strtotime($val1);
			$ts2 = strtotime($val2);

			$check = ($ts1 > $ts2);
		} else if($op == 'lessdate') {
			$ts1 = strtotime($val1);
			$ts2 = strtotime($val2);

			$check = ($ts1 < $ts2);
		} else if($op == 'empty') {
			$check = empty($val1);
		} else if($op == 'notempty') {
			$check = !empty($val1);
		}


		return $check;
	}

}

