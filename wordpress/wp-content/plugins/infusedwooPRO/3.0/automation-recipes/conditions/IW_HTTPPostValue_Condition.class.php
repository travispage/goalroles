<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_HTTPPostValue_Condition extends IW_Automation_Condition {
	public $allow_multiple = true;
	
	function get_title() {
		return 'When HTTP Post Value is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger'
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		$post_key = isset($config['post_key']) ? $config['post_key'] : "";
		$op = isset($config['op']) ? $config['op'] : "equal";
		$post_value = isset($config['post_value']) ? $config['post_value'] : "";

		$html = 'Check Value of: <input style="position:relative; top: -1px; left: 2px;" type="text" name="post_key" placeholder="Enter HTTP Post Parameter" value="'.$post_key .'" /><br>';
		$html .= '<div class="iwar-minisection" style="margin-top: 10px;"><select name="op">
				<option value="equal"'.($op == 'equal' ? ' selected ' : '').'>is equal to</option>
				<option value="like"'.($op == 'like' ? ' selected ' : '').'>is equal to (case insensitive)</option>
				<option value="greater"'.($op == 'greater' ? ' selected ' : '').'>is greater than</option>
				<option value="less"'.($op == 'less' ? ' selected ' : '').'>is less than</option>
				<option value="contain"'.($op == 'contain' ? ' selected ' : '').'> contains</option>
				<option value="notequal"'.($op == 'notequal' ? ' selected ' : '').'>is not Equal to</option>
				<option value="startswith"'.($op == 'startswith' ? ' selected ' : '').'>starts with</option>
				<option value="endswith"'.($op == 'endswith' ? ' selected ' : '').'>ends with</option>
				</select>&nbsp; &nbsp; <input type="text" style="width: 42%" name="post_value" placeholder="Enter Value" value="'.$post_value.'" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';

		$html .= '</div>';

		return $html;
	}

	function validate_entry($conditions) {
		if(empty($conditions['post_value'])) {
			return "Please enter http post value";
		}
	}


	function test($config, $trigger) {
		$val1 = isset($_POST[$config['post_key']]) ? $_POST[$config['post_key']] : "";
		$op = $config['op'];
		$val2 = $trigger->merger->merge_text($config['post_value']);

		return $trigger->compare_val($val1, $op, $val2);
	}
}

iw_add_condition_class('IW_HTTPPostValue_Condition');