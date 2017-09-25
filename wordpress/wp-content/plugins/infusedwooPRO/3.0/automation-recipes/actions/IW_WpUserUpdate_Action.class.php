<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WpUserUpdate_Action extends IW_Automation_Action {
	function get_title() {
		return "Update / Add User Record Field in Wordpress";
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
		add_action( 'adm_automation_recipe_after', array($this, 'wpuser_cfield_script'));
	}

	function get_contact_fields() {
		global $iwpro; 
		$merge_fields = array(
				'user_nicename' => 'User Nice Name',
				'user_email' => 'User Email',
				'user_pass' => 'Password (*required for new user)',
				'display_name' => 'Display Name',
				'nickname' => 'Nickname',
				'first_name' => 'First Name',
				'last_name' => 'Last Name',
				'description' => 'Description',
				'user_login' => 'Username (*only for adding new user)',
				'ID' => 'User ID (*only for forcing user update)',
				'role' => 'User Role'

			);

		return $merge_fields;
	}

	function wpuser_cfield_script() {
		$merge_fields = $this->get_contact_fields();

		?>
		<script>
		var wp_user_fields = <?php echo json_encode($merge_fields) ?>;

		jQuery("body").on("click", ".wp_ufield_add", function() {
			var $fieldarea = jQuery(this).parent().children(".wp_user_fields");
			var htm = '<div class="wpu_field"><select class="iwar_ufield" name="ufields[]" style="width: 45%">';
			htm += '<option value="">Select User Field...</option>';

			for(fld in wp_user_fields) {
				htm += '<option value="'+fld+'">'+wp_user_fields[fld]+'</option>';
			}
			htm += '<option value="meta">Custom User Meta ...</option>';
			htm += '</select>&nbsp;&nbsp;&nbsp;<input type="text" name="uvalues[]" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable" />';
			htm += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			htm += '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" title="Remove Field" aria-hidden="true"></i></div>';
			$fieldarea.append(htm);
			return false;
		});

		jQuery("body").on('change','.iwar_ufield', function() {
			if(jQuery(this).val() == 'meta') {
				jQuery(this).parent().prepend('<input type="text" name="ufields[]" style="width: 45%" placeholder="Enter User Meta Key..." />')
				jQuery(this).remove();
			}
		});

		</script>
		<?php
	}

	function display_html($config = array()) {
		$ufields = isset($config['ufields']) ? $config['ufields'] : array('');
		$uvalues = isset($config['uvalues']) ? $config['uvalues'] : array('');
		$add_contact = isset($config['add_contact']) ? $config['add_contact'] : 'on';

		$fields = $this->get_contact_fields();

		$html = '<div class="wp_user_fields">';

		foreach($ufields as $i => $ufield) {
			$html .= '<div class="wpu_field">';
			if($ufields[$i] == '' || in_array($ufields[$i], array_keys($fields))) {
				$html .= '<select class="iwar_ufield" name="ufields[]" style="width: 45%">';
				$html .= '<option value="">Select User Field...</option>';
				foreach($fields as $k => $field) {
					$html .= '<option value="'.$k.'"'.($k == $ufields[$i] ? ' selected ' : "").'>'.$field.'</option>';
				}
				$html .= '<option value="meta">Custom User Meta ...</option>';
				$html .= '</select>';
			} else {
				$html .= '<input type="text" name="ufields[]" value="'.$ufields[$i].'" style="width: 45%" placeholder="Enter User Meta Key..." />';
			}
			$html .= '&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="uvalues[]" value="'.$uvalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<a href="#" class="wp_ufield_add">Add more fields ...</a>';
		$html .= '<hr>';
		$html .= '<input type="hidden" name="add_contact" value="off" />';
		$html .= '<input value="on" autocomplete="off" type="checkbox" name="add_contact"'.($add_contact == 'on' ? ' checked ' : '').' /> <span style="font-size: 10pt">Add new user if user record doesn\'t exist.</span>';

		return $html;
	}

	function validate_entry($config) {
		if(!is_array($config['ufields'])) return "Please ensure that all contact fields are not empty";
		
		foreach($config['ufields'] as $val) {
			if(empty($val)) return "Please ensure all contact fields are not empty";
		}
	}

	function process($config, $trigger) {
		$user_id = 0;
		$merge_fields = $this->get_contact_fields();
		$user_login = '';
		$user_fields = array();
		$meta_fields = array();
		$add_contact = isset($config['add_contact']) ? $config['add_contact'] : 'on';


		// Arrange Fields
		foreach($config['ufields'] as $k => $v) {
			if($v == 'ID') {
				$user_id = (int) $trigger->merger->merge_text($config['uvalues'][$k]);
			} else if($v == 'user_login') {
				$user_login = $trigger->merger->merge_text($config['uvalues'][$k]);
				$user_fields[$v] = $trigger->merger->merge_text($config['uvalues'][$k]);
			} else if(in_array($v, array_keys($merge_fields))) {
				$user_fields[$v] = $trigger->merger->merge_text($config['uvalues'][$k]);
			} else {
				$meta_fields[$v] = $trigger->merger->merge_text($config['uvalues'][$k]);
			}
		}

		if($user_id == 0) {
			if(isset($trigger->user_email) && !empty($trigger->user_email)) {
				$user = get_user_by('email', $trigger->user_email);
				if(isset($user->ID) && $user->ID > 0) $user_id = $user->ID;
				else {
					$users = get_users(array('meta_key' => 'billing_email', 'meta_value' => $trigger->user_email,  'number' => 1, 'fields' => 'ids'));
					$user_id = (int) $users[0];
				}
			}
		}

		if($user_id > 0) {
			$user_fields['ID'] = $user_id;
			wp_update_user($user_fields);

			if(isset($user_fields['role'])) {
				$wp_user_object = new WP_User($user_id);
				$wp_user_object->set_role($user_fields['role']);
			}
		} else if(isset($user_fields['user_login']) && isset($user_fields['user_pass']) && $add_contact == 'on') {
			$user_id = wp_insert_user($user_fields);
		}

		if($user_id > 0) {
			foreach($meta_fields as $k => $v) {
				update_user_meta( $user_id, $k, $v );
			}
		}
	}
}

iw_add_action_class('IW_WpUserUpdate_Action');