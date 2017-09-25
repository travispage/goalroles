<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Automation_Condition {
	private $condition_classes;

	public function add_condition_class($condition_class) {
		if(!is_array($this->condition_classes)) {
			$this->condition_classes = array();
		}

		$this->condition_classes[] = $condition_class;

		$new_condition = new $condition_class;
		
		if(method_exists($new_condition, 'on_class_load')) {
			$new_condition->on_class_load();
		}
	}

	public function get_available_conditions($trigger_class = "") {
		$condition_classes = is_array($this->condition_classes) ? $this->condition_classes : array();

		$conditions = array();
		foreach($condition_classes as $cond) {
			$cond_obj =  new $cond;

			if(method_exists($cond_obj, 'allowed_triggers')) {
				$allowed_triggers = $cond_obj->allowed_triggers();
				$allowed_triggers = is_array($allowed_triggers) ? $allowed_triggers : array();

				if(empty($trigger_class) || in_array($trigger_class, $allowed_triggers)) {
					$conditions[$cond] = $cond_obj;
				}
			} else {
				$conditions[$cond] = $cond_obj;
			}
		}

		return $conditions;
	}
}