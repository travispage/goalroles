<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfUpdateContact_Action extends IW_Automation_Action {
	function get_title() {
		return "Update / Add Contact Record Field in Infusionsoft";
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
		add_action( 'adm_automation_recipe_after', array($this, 'inf_cfield_script'));
	}

	function get_contact_fields() {
		global $iwpro; 
		$merge_fields = array(
				"Email"	=> "Email Address",
				"FirstName" => "First Name",
				"MiddleName" => "Middle Name",
				"LastName" => "Last Name",
				"Nickname" => "Nickname",
				"Suffix" => "Suffix",
				"Phone1" => "Phone",
				"Company" => "Company",
				'Password' => 'Password',
				"StreetAddress1" => "Street Address",
				"StreetAddress2" => "Street Address 2",
				"City" => "City",
				"State" => "State",
				"Country" => "Country",
				"PostalCode" => "Postal Code",
				"ZipFour1" => "ZipFour",
				"ContactNotes" => "Contact Notes",
				"Leadsource" => "Leadsource",
				"Address2Street1" => "Shipping Street Address",
				"Address2Street2" => "Shipping Street Address 2",
				"City2" => "Shipping City",
				"State2" => "Shipping State",
				"Country2" => "Shipping Country",
				"PostalCode2" => "Shipping Postal Code",
				"ZipFour2" => "Shipping ZipFour",
				"Address3Street1" => "Optional Street Address",
				"Address3Street2" => "Optional Street Address 2",
				"City3" => "Optional City",
				"State3" => "Optional State",
				"Country3" => "Optional Country",
				"PostalCode3" => "Optional Postal Code",
				"ZipFour3" => "Optional ZipFour",
				"Language" => "Language",
				"TimeZone" => "TimeZone",
				"Facebook" => "Facebook",
				"Twitter"  => "Twitter",
				"LinkedIn" => 'LinkedIn',
				"Anniversary" => "Anniversary",
				"Birthday" => "Birthday",
				"AssistantName" => "Assistant Name",
				"AssistantPhone" => "Assistant Phone",
				"EmailAddress2" => "Email Address 2",
				"EmailAddress3" => "Email Address 3",
				"Phone2" => "Phone 2",
				"Phone3" => "Phone 3",
				"Fax1" => "Fax",
				"Fax2" => "Fax 2",
				"JobTitle" => "Job Title",
				"SpouseName" => "Spouse Name",
				"Title" => "Title",
				"Website" => "Website"
			);

		if($iwpro->ia_app_connect()) {
			$custfields = $iwpro->app->dsFind("DataFormField", 200,0, "FormId", -1, array("Name","Label","DataType"));
			if(is_array($custfields) && count($custfields) > 0) {
				foreach($custfields as $custfield) {
					$merge_fields["_" . $custfield["Name"]] = "Custom Field: " . $custfield["Label"];
				}
			}
		}

		return $merge_fields;
	}

	function inf_cfield_script() {
		$merge_fields = $this->get_contact_fields();

		?>
		<script>
		var iwar_contact_fields = <?php echo json_encode($merge_fields) ?>;

		jQuery("body").on("click", ".inf_cfield_add", function() {
			var $fieldarea = jQuery(this).parent().children(".iwar_cinf_fields");
			var htm = '<div class="cinf_field"><select name="cfields[]" style="width: 45%">';
			htm += '<option value="">Select Contact Field...</option>';

			for(fld in iwar_contact_fields) {
				htm += '<option value="'+fld+'">'+iwar_contact_fields[fld]+'</option>';
			}
			htm += '</select>&nbsp;&nbsp;&nbsp;<input type="text" name="cvalues[]" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable" />';
			htm += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			htm += '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" title="Remove Field" aria-hidden="true"></i></div>';
			$fieldarea.append(htm);
			return false;
		});

		jQuery("body").on("click",".fa-minus-circle", function() {
			jQuery(this).parent().remove();
		});

		</script>
		<?php
	}

	function display_html($config = array()) {
		$cfields = isset($config['cfields']) ? $config['cfields'] : array('');
		$cvalues = isset($config['cvalues']) ? $config['cvalues'] : array('');
		$add_contact = isset($config['add_contact']) ? $config['add_contact'] : 'on';

		$fields = $this->get_contact_fields();

		$html = '<div class="iwar_cinf_fields">';

		foreach($cfields as $i => $cfield) {
			$html .= '<div class="cinf_field"><select name="cfields[]" style="width: 45%">';
			$html .= '<option value="">Select Contact Field...</option>';
			foreach($fields as $k => $field) {
				$html .= '<option value="'.$k.'"'.($k == $cfields[$i] ? ' selected ' : "").'>'.$field.'</option>';
			}
			$html .= '</select>&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="cvalues[]" value="'.$cvalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<a href="#" class="inf_cfield_add">Add more fields ...</a>';
		$html .= '<hr>';
		$html .= '<input type="hidden" name="add_contact" value="off" />';
		$html .= '<input value="on" autocomplete="off" type="checkbox" name="add_contact"'.($add_contact == 'on' ? ' checked ' : '').' /> <span style="font-size: 10pt">Add new contact if contact record doesn\'t exist.</span>';

		return $html;
	}

	function validate_entry($config) {
		if(!is_array($config['cfields'])) return "Please ensure that all contact fields are not empty";
		
		foreach($config['cfields'] as $val) {
			if(empty($val)) return "Please ensure all contact fields are not empty";
		}
	}

	function process($config, $trigger) {
		if(isset($trigger->user_email) && !empty($trigger->user_email)) {
			global $iwpro;
			if(!isset($trigger->infusion_contact_id)) {
				$trigger->search_infusion_contact_id();
			}

			$add_contact = isset($config['add_contact']) ? $config['add_contact'] : 'on';

			$contactinfo = array();
			$socialaccount = array();

			foreach($config['cfields'] as $k => $field) {
				if(in_array($field, array('Facebook','Twitter','LinkedIn'))) {
					$socialaccount[$field] = $trigger->merger->merge_text($config['cvalues'][$k]);
				} else {
					$contactinfo[$field] = $trigger->merger->merge_text($config['cvalues'][$k]); 
				}
			}

			if(!empty($trigger->infusion_contact_id)) {
				$cid = $trigger->infusion_contact_id;
				if($iwpro->ia_app_connect()) {
					$iwpro->app->dsUpdate('Contact', $cid, $contactinfo);
				}
			} else if($add_contact == 'on') {
				if($iwpro->ia_app_connect()) {
					if(!isset($contactinfo['Email'])) {
						$contactinfo['Email'] = $trigger->user_email;
					}
					$cid = (int) $iwpro->app->dsAdd('Contact', $contactinfo);
				}
			}

			if(count($socialaccount) > 0) {
				foreach($socialaccount as $k => $v) {
					$info = $iwpro->app->dsQuery('SocialAccount', 1, 0, array('ContactId' => $cid, 'AccountType' => $k), array('Id','AccountName'));

					if(isset($info[0]['Id']) && $info[0]['AccountName'] != $v) {
						$iwpro->app->dsUpdate('SocialAccount', $info[0]['Id'], array('AccountName' => $v));
					} else {
						$iwpro->app->dsAdd('SocialAccount', array(
								'AccountType' => $k,
								'AccountName' => $v,
								'ContactId'  => $cid
							));
					}
				}
			} 
		} 
	}
}

iw_add_action_class('IW_InfUpdateContact_Action');