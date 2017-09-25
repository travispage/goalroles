<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Automation_Action {
	private $action_classes;

	public function add_action_class($action_class) {
		if(!is_array($this->action_classes)) {
			$this->action_classes = array();
		}

		$this->action_classes[] = $action_class;

		$new_action = new $action_class;
		
		if(method_exists($action_class, 'on_class_load')) {
			$new_action->on_class_load();
		}
	}

	public function get_icon() {
		return '<i class="fa fa-cog"></i>';
	}

	public function get_desc($config) {
		return $this->get_title($config);
	}

	public function get_available_actions($trigger_class = "") {
		$action_classes = is_array($this->action_classes) ? $this->action_classes : array();

		$actions = array();

		foreach($action_classes as $act) {
			$act_obj =  new $act;

			if(method_exists($act_obj, 'allowed_triggers')) {
				$allowed_triggers = $act_obj->allowed_triggers();
				$allowed_triggers = is_array($allowed_triggers) ? $allowed_triggers : array();

				if(empty($trigger_class) || in_array($trigger_class, $allowed_triggers)) {
					$actions[$act] = $act_obj;
				}
			} else {
				$actions[$act] = $act_obj;
			}
		}

		return $actions;
	}


}