<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_HttpPost_Action extends IW_Automation_Action {
	function get_title() {
		return "Send HTTP Post to Another Server";
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_WooSubEvent_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		add_action( 'adm_automation_recipe_after', array($this, 'inf_httpfield_script'));
	}


	function inf_httpfield_script() {
		?>
		<script>
		jQuery("body").on("click", ".httpp_field_add", function() {
			var $fieldarea = jQuery(this).parent().children(".iwar_httpp_fields");
			var htm = '<div class="httpp_field"><input type="text" name="hfields[]" style="width: 45%" placeholder="Field Name" />';
			htm += '&nbsp;&nbsp;&nbsp;<input type="text" name="hvalues[]" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable" />';
			htm += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			htm += '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" title="Remove Field" aria-hidden="true"></i></div>';
			$fieldarea.append(htm);
			return false;
		});

		</script>
		<?php
	}

	function display_html($config = array()) {
		$hfields = isset($config['hfields']) ? $config['hfields'] : array('');
		$hvalues = isset($config['hvalues']) ? $config['hvalues'] : array('');
		$hurl = isset($config['hurl']) ? $config['hurl'] : '';

		$html = 'POST URL &nbsp;&nbsp;<input type="text" name="hurl" value="'.$hurl.'" placeholder="http://" />';
		$html .= '<hr><div class="iwar_httpp_fields">';

		foreach($hfields as $i => $hfield) {
			$html .= '<div class="httpp_field"><input type="text" name="hfields[]" value="'.$hfields[$i].'" style="width: 45%" placeholder="Field Name" />';
			$html .= '&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="hvalues[]" value="'.$hvalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div><br>';
		$html .= '<a href="#" class="httpp_field_add">Add more fields ...</a>';
	
		return $html;
	}

	function validate_entry($config) {
		if(empty($config['hurl'])) return "Please enter HTTP POST URL";
		if(strpos($config['hurl'], 'http') === 'false') return "URL must begin with http: or https:";

		if(!is_array($config['hfields'])) return "Please ensure that all http fields are not empty";
		
		foreach($config['hfields'] as $k => $key) {
			if(empty($key)) return "Please ensure all fields are not empty";
		}
	}

	function process($config, $trigger) {
		$hfields = isset($config['hfields']) ? $config['hfields'] : array();
		$hvalues = isset($config['hvalues']) ? $config['hvalues'] : array();

		$ch = curl_init();
		//url-ify the data for the POST
		foreach($hfields as $key=>$field) { 
			$field 	= $trigger->merger->merge_text($field);
			$val 	= $trigger->merger->merge_text($hvalues[$key]);
			$fields_string .= $field.'='.$val.'&';
		}
		rtrim($fields_string, '&');


		$url = $config['hurl'];


		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($hfields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		curl_close($ch);
		do_action( 'iwar_httppost_action', $result, $url, $fields_string );
		return $result;
	}
}

iw_add_action_class('IW_HttpPost_Action');