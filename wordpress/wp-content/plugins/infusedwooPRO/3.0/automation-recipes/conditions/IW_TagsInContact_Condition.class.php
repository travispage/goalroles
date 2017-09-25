<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_TagsInContact_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If contact\'s tags in Infusionsoft ...';
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_UserAction_Trigger',
				'IW_WooSubEvent_Trigger',
				'IW_Checkout_Trigger'

			);
	}

	function on_class_load() {
		add_action('adm_automation_recipe_after', array($this, 'tag_dialog'));
		add_action('wp_ajax_iwar_add_new_tag', array($this,'iwar_add_new_tag'));
	}

	function iwar_add_new_tag() {
		if(is_admin()) {
			global $iwpro;
			if(!$iwpro->ia_app_connect()) {
				exit();
			}

			if($_POST['tag_category_id'] == 'new' && !empty($_POST['tag_category'])) {
				$tag_cat = $iwpro->app->dsAdd('ContactGroupCategory', array('CategoryName' => $_POST['tag_category']));
				$tag_category_id = (int) $tag_cat;
			} else {
				$tag_category_id = (int) $_POST['tag_category_id'];
			}
			$new_tag = array('GroupName' => $_POST['tag_name']);
			if($tag_category_id > 0) $new_tag['GroupCategoryId'] = $tag_category_id;

			$tag_id = (int) $iwpro->app->dsAdd('ContactGroup', $new_tag);

			if($tag_id > 0) {
				echo json_encode(array(
						'id' => $tag_id,
						'name' => $_POST['tag_name'] . " [ $tag_id ]"
					));

			}

			exit();
			
		}
	}

	function tag_dialog() {
		global $iwpro;
		?>
		<div id="tag_dialog" class="iw-jq-modal iw-tag-dialog-modal" title="Add New Infusionsoft Tag">
			<form>
			<table>
				<tr>
			      <td><label for="name">Tag Name</label></td>
			      <td><input type="text" name="tag_name" id="name" placeholder="Enter Tag Name..."></td>
			   
			    </tr>
			    <tr>
			      <td><label for="email">Tag Category</label></td>
			      <td>
			      	<select name="tag_category_id">
			      		<option value="0">(No Category)</option>
			      		<?php if($iwpro->ia_app_connect()) { 
			      				$tag_cats = $iwpro->app->dsFind('ContactGroupCategory', 1000,0, 'Id', '%', array('Id','CategoryName'));

			      				foreach($tag_cats as $cat) {
			      					echo '<option value="'.$cat['Id'].'">'.$cat['CategoryName'].'</option>';
			      				}
			      			?>

			      		<?php } ?>
			      		<option value="new">(Add New Category)</option>

			      	</select>
			      	<input type="text" name="tag_category" id="category" style="display:none" placeholder="Type to Search Category"></td>
			     </tr>
			 </table>
			</form>
		</div>
		<script>
			jQuery(document).ready(function() {
				tag_dialog = jQuery("#tag_dialog").dialog({
				  autoOpen: false,
			      height: 220,
			      width: 430,
			      modal: true,
			      buttons: {
			      	Cancel: function() {
			          tag_dialog.dialog( "close" );
			        },
			        "Add Tag": proc_infusion_add_tag,
			        
			      },
			      close: function() {
			        jQuery("#tag_dialog form")[0].reset();
			        jQuery('[name=tag_category]').hide();
					jQuery('[name=tag_category_id]').show();

			      }
			    });
			});

			jQuery("[name=tag_category_id]").change(function() {
				if(jQuery(this).val() == 'new') {
					jQuery(this).hide();
					jQuery('[name=tag_category]').show();
				}
			});

			infusion_add_new_tag = function(item, $el) {
				last_tag_dialog_el = $el;
				last_tag_dialog_itm = item;
				jQuery("#tag_dialog [name=tag_name]").val(item.value);
				tag_dialog.dialog('open');
			};

			proc_infusion_add_tag = function() {
				var tag_name = jQuery("#tag_dialog [name=tag_name]").val();
				var cat_id = jQuery("#tag_dialog [name=tag_category_id]").val();
				var cat_name = jQuery("#tag_dialog [name=tag_category]").val();
				iwar['infusion_tags_cache'] = {}; 

				if(tag_name == "") {
					jQuery("#tag_dialog [name=tag_name]").addClass('ui-state-error');
				} else {
					swal({title: "Adding tag...",   text: "Please wait while we add the new tag", showConfirmButton: false });
					jQuery.post(ajaxurl+"?action=iwar_add_new_tag", {tag_name: tag_name, tag_category_id: cat_id, tag_category: cat_name}, function(data) {
						if(data.id) {
								var search_name = last_tag_dialog_el.attr('name');
								var append_new = '<span class="'+search_name+'-item">';
								append_new += data.name;
								append_new += '<input type="hidden" name="'+search_name+'-val[]" value="'+data.id+'" />';
								append_new += '<input type="hidden" name="'+search_name+'-label[]" value="'+data.name+'" />';
								append_new += '<i class="fa fa-times-circle"></i>';
								append_new += '</span>';
								swal.close();
								last_tag_dialog_el.parent().find("."+search_name+"-contain").append(append_new)
						} else {
							 swal("Error", "There was an error when adding tag. Please try again later or add tag manually inside infusionsoft.", "error");
						}
					}, 'json');
					tag_dialog.dialog( "close" );
				}
			}
		</script>
		<?php
	}

	function display_html($config = array()) {
		$tagselect = isset($config['tagselect']) ? $config['tagselect'] : '';
		$html = '<select name="tagselect" style="width: 100%;">';
		$html .= '<option value="some"'.($tagselect == 'some' || empty($tagselect) ? ' selected' : '' ).'>has one or more of these tags ...</option>';
		$html .= '<option value="all"'.($tagselect == 'all' ? ' selected' : '' ).'>has all these tags ...</option>';
		$html .= '<option value="dont"'.($tagselect == 'dont' ? ' selected' : '' ).'>don\'t have one of these tags ...</option>';
		$html .= '<option value="none"'.($tagselect == 'none' ? ' selected' : '' ).'>don\'t have all of these tags ...</option>';
		$html .= '</select><div style="padding: 10px;">';
		$html .= '<input type="text" name="tag" class="iwar-dynasearch" data-src="infusion_tags" placeholder="Start typing to add tags..." style="width: 80%" />';
		$html .= '<div class="tag-contain dynasearch-contain">';
		
		if(isset($config['tag-val']) && is_array($config['tag-val'])) {
			foreach($config['tag-val'] as $k => $val) {
				$label = isset($config['tag-label'][$k]) ? $config['tag-label'][$k] : 'Tag ID # ' . $val;
				$html .= '<span class="tag-item">';
				$html .= $label;
				$html .= '<input type="hidden" name="tag-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="tag-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div></div>';
		return $html;
	}

	function validate_entry($conditions) {
		if(empty($conditions['tag-val'])) {
			return "Please enter at least one tag.";
		}
	}


	function test($config, $trigger) {
		if(!isset($trigger->infusion_contact_id)) {
			$trigger->search_infusion_contact_id();
		}

		if(!empty($trigger->infusion_contact_id)) {
			if(!isset($trigger->contact_tags)) {
				$contact_tags = $this->get_contact_tags($trigger->infusion_contact_id);
				$trigger->contact_tags = $contact_tags;
			} else {
				$contact_tags = $trigger->contact_tags;
			}
		}

		$tags_to_check = $config['tag-val'];
		//print_r($tags_to_check);
		//print_r($contact_tags);
		//exit();
		if($config['tagselect'] == 'some') {
			foreach($tags_to_check as $tid) {
				if(in_array($tid, $contact_tags)) {
					return true;
				}
			}

			return false;
		} else if($config['tagselect'] == 'all') {
			foreach($tags_to_check as $tid) {
				if(!in_array($tid, $contact_tags)) {
					return false;
				}
			}

			return true;
		} else if($config['tagselect'] == 'dont') {
			foreach($tags_to_check as $tid) {
				if(!in_array($tid, $contact_tags)) {
					return true;
				}
			}

			return false;
		} else {
			foreach($tags_to_check as $tid) {
				if(in_array($tid, $contact_tags)) {
					return false;
				}
			}

			return true;
		}
	}

	function get_contact_tags($cid) {
		global $iwpro;

		if($iwpro->ia_app_connect()) {
			$contact = $iwpro->app->dsLoad('Contact',$cid, array('Groups'));
			if(isset($contact['Groups'])) {
				$tags = explode(",", $contact['Groups']);
				return is_array($tags) ? $tags : array();
			} else {
				return array();
			}
		} else {
			return array();
		}
	}
}

iw_add_condition_class('IW_TagsInContact_Condition');