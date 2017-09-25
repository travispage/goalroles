var iwar = {};
iwar.selected_conds = [];

jQuery(document).ready(function() {
	jQuery(".recipe-control-more").hide();
	jQuery('.custom-date input[type=text]').datepicker({
        dateFormat : 'mm/dd/yy'
    });

	if(jQuery("[name='iwar-trigger']").val() != '') {
		var iwar_trigger = jQuery("[name='iwar-trigger']").val();
		iwar.recipe_id = jQuery('[name=recipe_id]').val();
		
		preload_conditions(iwar_trigger);
		preload_actions(iwar_trigger);		
	} 

	jQuery(".trigger-display").click(function(){
		var iwar_trigger = jQuery(this).attr('value');
		preload_conditions(iwar_trigger);
		preload_actions(iwar_trigger);
		jQuery("[name='iwar-trigger']").val(iwar_trigger);

		jQuery(this).addClass('selected_trigger');
		jQuery(".trigger-display").hide();
		jQuery(".more-triggers-show").hide();
		jQuery('.selected_trigger').show();
		jQuery(".trigger-select-info").hide();

		jQuery(".iw-ar-proceed").show();
		jQuery(".select-trigger-edit").show();
	});

	jQuery(".select-trigger-edit").click(function() {
		swal({   
			title: "Warning",   
			text: "You will lose all settings if the trigger is changed. Proceed?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Yes, proceed",   closeOnConfirm: false }, function(){ iw_change_trigger(); swal.close(); });
	});

	

	jQuery("body").on('click','.iwar-add-condition', function() {
		var new_cond_html = iwar_available_conditions_html();

		jQuery(".iwar-conditions").append(new_cond_html);
		refresh_iwar_conditions();
	});

	jQuery("body").on('click','.iwar-add-action', function() {
		var new_act_html = iwar_available_actions_html();

		jQuery(".iwar-actions").append(new_act_html);
	});


	jQuery("body").on('click', ".autome-remove", function() {
		jQuery(this).parent().remove();
		refresh_iwar_conditions();
	});

	jQuery("body").on('change','.iwar_condition',function(e) {
		var ind = jQuery(this).index()-1;
		last_val = iwar.selected_conds[ind];
		var cond = jQuery(this).val();
		var filter_conds = [];
		for(var i = 0; i <  iwar.selected_conds.length; i++) {
			if(i != ind) filter_conds.push(iwar.selected_conds[i]);
		}

		if(jQuery.inArray(cond, filter_conds) > -1 && iwar.available_conditions[cond].allow_multiple != 1) {
			swal('Existing condition found','Please remove first the other existing instance with the same condition.','error');
			jQuery(this).val(last_val);
		} else {
			jQuery(this).closest(".autome-condition").find(".iwar-cond-disphtml-area").html(iwar.available_conditions[cond].html);
		}

		refresh_iwar_conditions();
	});

	jQuery("body").on('change','.iwar_action',function(e) {
		var ind = jQuery(this).index()-1;
		var act = jQuery(this).val();
		var filter_conds = [];
			jQuery(this).closest(".autome-action").find(".iwar-act-disphtml-area").html(iwar.available_actions[act].html + iwar_action_override_html());
		});

	jQuery(".iwar-save-recipe").click(function(){
		iwar_save_recipe();
	});

	jQuery(".cont-more").click(function() {
		jQuery(this).parent().children(".recipe-control-more").toggle();
	});

	jQuery("body").on('click','.iwar-delete-recipe', function(e){
		e.preventDefault(); 
		iwar.temp_url = jQuery(this).attr('href');

		swal({   
			title: "Warning",   
			text: "Are you sure you want to delete this recipe?",   
			type: "warning",   
			showCancelButton: true,   
			confirmButtonColor: "#DD6B55",   
			confirmButtonText: "Yes, proceed",   
			closeOnConfirm: false 
		}, function(){ 
			location.href = iwar.temp_url;
		});
	});

	jQuery("body").on('click','.iwar-clear-stats', function(e){
		e.preventDefault(); 
		iwar.temp_url = jQuery(this).attr('href');

		swal({   
			title: "Warning",   
			text: "This action will remove all stats on this recipe. Proceed?",   
			type: "warning",   
			showCancelButton: true,   
			confirmButtonColor: "#DD6B55",   
			confirmButtonText: "Yes, proceed",   
			closeOnConfirm: false 
		}, function(){ 
			location.href = iwar.temp_url;
		});
	});

	// Search functionality
	jQuery("#search_recipes").keyup(function() {
		var search_keyword = jQuery(this).val();
		jQuery(".iw-recipe-item").show();
		jQuery(".iw_search_empty").hide();
		if(search_keyword == '') return true;

		search_keyword = search_keyword.toLowerCase();

		var $title_els = jQuery(".recipe-title");

		var num_hidden = 0;
		for(var i = 0; i < $title_els.length; i++) {
			var this_title = jQuery($title_els[i]).html();
			this_title = this_title.toLowerCase();

			if(this_title.indexOf(search_keyword) == -1) {
				jQuery($title_els[i]).closest('.iw-recipe-item').hide();
				num_hidden++;
			}
		}

		if(num_hidden == $title_els.length) {
			jQuery('.iw_search_empty').show();
		} 
	});

	// sort functionality 
	jQuery("#sortby").change(function() {
		var sortby = jQuery(this).val();
		location.href = '?page=infusedwoo-menu-2&submenu=automation_recipes&sortby=' + sortby;
	});

	jQuery(".iwar-clone").click(function() {
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');
		location.href = ajaxurl + "?action=iwar_clone&recipe=" + recipe_id;
	});

	jQuery(".iwar-export-to-file").click(function() {
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');
		location.href = ajaxurl + "?action=iwar_export_to_file&recipe=" + recipe_id;
		jQuery(".recipe-control-more").hide();
	});

	jQuery(".uploadconf").click(function() {
		jQuery("[name=uploadconf]").click();
	});

	jQuery("[name=uploadconf]").change(function() {
		jQuery(this).closest('form').submit();
	});




	jQuery(".show-stats-button").click(function() {
		jQuery(".recipe-control-more").hide();
		jQuery(this).hide();
		jQuery(this).parent().children('.hide-stats-button').show();
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');

		load_recipe_stats(recipe_id);

	});

	jQuery(".iwar-stat-date-range").change(function() {
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');

		if(jQuery(this).val() == 'custom') {
			jQuery(this).parent().children(".custom-date").show();
		} else {
			jQuery(this).parent().children(".custom-date").hide();
		}
		load_recipe_stats(recipe_id);
	});

	jQuery(".iwar-update-stats").click(function() {
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');
		load_recipe_stats(recipe_id);
	});

	jQuery(".hide-stats-button").click(function() {
		jQuery(".recipe-control-more").hide();
		jQuery(this).hide();
		jQuery(this).parent().children('.show-stats-button').show();
		jQuery(this).closest('.iw-recipe-item').find('.recipe-stat-contain').hide();
	});

	jQuery("body").on('keydown', '.iwar-dynasearch', function() {
		if(!jQuery(this).hasClass('dynasearch-enabled')) {
			jQuery(this).addClass('dynasearch-enabled');

			$this_autocomplete = jQuery(this);
			var endpoint = $this_autocomplete.attr('data-src');
			jQuery(this).autocomplete({
				source: function( request, response ) {
			      	var term = request.term;

			      	if(!isInt(parseInt(term)) && term.length < 3) {
			      		response([{label: "Continue typing to search", value: ""}]);
			      		return true;
			      	} 

			      	if(!((endpoint+'_cache') in iwar)) {
			      		iwar[endpoint+'_cache'] = {}; 
			      		return true;
			      	}

			      	if ( term in iwar[endpoint+'_cache'] ) {
			          response( iwar[endpoint+'_cache'][ term ] );
			          return true;
			        }

			        response([{label: "Searching...", value: ""}]);

			        if(iwar.data_search) iwar.data_search.abort();

			      	iwar.data_search = jQuery.getJSON(ajaxurl, {action: 'infusedwoo_data_src_'+endpoint, term: request.term}, function(data) {
			      		if(data.length > 0) {
			      			if(endpoint == 'infusion_tags') {
				      			data.push({label: term + " (Add new tag)", value: term, custom_action: infusion_add_new_tag});
				      		}
				      		iwar[endpoint+'_cache'][ term ] = data;
				      		response( data );
			      		} else {
			      			if(endpoint == 'infusion_tags') {
			      				 response([{label: term + " (Add new tag)", value: term, custom_action: infusion_add_new_tag}]);
		      				} else {
		      					response([{label: "Cannot find " + term, value: ""}]);
		      				}
			      		}
			      		return true;
			      	});
			      },
				minLength: 1,
				select: function( event, ui ) {
					console.log(event,ui);
					event.preventDefault();
					jQuery(event.target).val('');

					var search_name = jQuery(event.target).attr('name');
					var $event_parent = jQuery(event.target).parent();
					var contain_class = search_name + '-contain';
					
					var check_vals = $event_parent.find('[name="'+search_name+'-val[]"]');
					if(!ui.item.id) {
						if(ui.item.custom_action && typeof ui.item.custom_action === 'function') {
							ui.item.custom_action(ui.item, jQuery(event.target));
						} 

						return false;
					}
					for(var i = 0; i < check_vals.length; i++) {
						if(jQuery(check_vals[i]).val() == ui.item.value) {
							return false;
						}
					}

					
					var append_new = '<span class="'+search_name+'-item">';
					append_new += ui.item.label;
					append_new += '<input type="hidden" name="'+search_name+'-val[]" value="'+ui.item.value+'" />';
					append_new += '<input type="hidden" name="'+search_name+'-label[]" value="'+ui.item.label+'" />';
					append_new += '<i class="fa fa-times-circle"></i>';
					append_new += '</span>';

					$event_parent.find("."+contain_class).append(append_new);
				}
			});	
		}
	});

	jQuery("body").on("click",".dynasearch-contain .fa-times-circle", function(){
		jQuery(this).parent().remove();
	});

	// manually trigger
	jQuery(".manually-trigger").click(function() {
		jQuery(".recipe-control-more").hide();
		var recipe_id = jQuery(this).closest('.iw-recipe-item').attr('recipe-id');

		swal({   
			title: "Enter Contact Email",   
			text: "Please enter the contact's email address",   
			type: "input",   
			showCancelButton: true,   
			closeOnConfirm: false,   
   			inputPlaceholder: "user@domain.com",
   			showLoaderOnConfirm: true,
   		}, function(inputValue) {   
   				if (inputValue === false) return false;      
   				if (inputValue === "") {     
   					swal.showInputError("Please enter contact's email address.");     
   					return false;
   				}

   				jQuery.post(ajaxurl + '?action=iwar_manually_run_actions', {
   					recipe_id: recipe_id,
   					email: inputValue
				}, function(data){
					swal("Done", "Successfully triggered to contact and actions were ran.", "success");
				});
   				
   			});

		

	});

	jQuery(".more-triggers-show").click(function(e) {
		e.preventDefault();
		jQuery(".trigger-advanced").toggle();

		if(jQuery(".trigger-advanced").is(":visible")) {
			jQuery(this).children('.fa-caret-right').hide();
			jQuery(this).children('.fa-caret-down').show();
		} else {
			jQuery(this).children('.fa-caret-right').show();
			jQuery(this).children('.fa-caret-down').hide();
		}
	});
	
	if(jQuery("#merge_dialog").length > 0) {
		merge_dialog = jQuery("#merge_dialog").dialog({
			  autoOpen: false,
		      width: 450,
		      modal: true,
		      buttons: {
		      	Cancel: function() {
		          merge_dialog.dialog( "close" );
		        },
		        "Add Merge Field": merger,
		        
		      },
		      close: function() {
		        jQuery("#merge_dialog form")[0].reset();
		        jQuery(".iwar-adv-merge").hide();
		        iwar_load_merge_fields();
		      }
		    });

		jQuery("body").on('click','.merge-button', function(e) {
			iwar.to_merge = jQuery(this).parent().children('.iwar-mergeable');
			merge_dialog.dialog('open');
			e.preventDefault();
		});

		jQuery(".adv-mrg").click(function(e) {
			e.preventDefault();
			jQuery(".iwar-adv-merge").toggle();
		});

		jQuery("body").on("change",".merge_group", function() {
			iwar_load_merge_fields();
		});

		jQuery("body").on('click','.merger_link', function(e) {
			iwar.to_merge = 'tinymce';
			merge_dialog.dialog('open');
			e.preventDefault();
		});

		jQuery("body").on("click",".triggering-advanced", function(){
			jQuery(this).parent().parent().children(".iwar-action-override").toggle();
		});

	}


});	

function merger() {
	var grp = jQuery(".merge_group").val();
	var key = jQuery(".merge_fld").val();
	var fall = jQuery(".merge-fallback").val();

	if(grp && key) {
    	var scode = '{{' + grp;
    	scode += ':' + key;
    	if(fall) {
    		scode += '|' +  fall;
    	}
    	scode += '}}';

    	if(iwar.to_merge == 'tinymce') {
    		if(tinyMCE.activeEditor) {
    			tinyMCE.activeEditor.execCommand('mceInsertContent', false, scode);
    			iwar_insertAtCaret('iwar_tinymce',scode);
    		} else {
    			iwar_insertAtCaret('iwar_tinymce',scode);
    		}
    	} else {
    		iwar.to_merge.val(iwar.to_merge.val() + scode);
    	}
	}

	merge_dialog.dialog( "close" );
}

function iw_change_trigger() {
	jQuery(".trigger-display").show();
	jQuery(".more-triggers-show").show();
	jQuery(".more-triggers-show").children('.fa-caret-right').show();
	jQuery(".more-triggers-show").children('.fa-caret-down').hide();

	jQuery(".trigger-advanced").hide();
	jQuery('.selected_trigger').removeClass('selected_trigger');

	jQuery(".iw-ar-proceed").hide();
	jQuery(".select-trigger-edit").hide();

	jQuery(".trigger-select-info").show();
	jQuery("[name='iwar-trigger']").val('');

	// remove existing settings:
	jQuery(".iwar-conditions").html('');
	jQuery(".iwar-actions").html('');

}

function preload_conditions(trigger_class) {
	jQuery(".iwar-add-condition").hide();
	jQuery(".iwar-conditions").hide();
	jQuery(".iwar-loading-conditions").show();

	jQuery.getJSON(ajaxurl, {
		action: 'iwar_get_available_conditions',
		trigger: trigger_class
	}, function(data) {
		iwar.available_conditions = data;
		jQuery(".iwar-conditions").show();

		jQuery(".iwar-add-condition").show();
		jQuery(".iwar-loading-conditions").hide();
		refresh_iwar_conditions();

	});
}



function preload_actions(trigger_class) {
	jQuery(".iwar-add-action").hide();
	jQuery(".iwar-actions").hide();
	jQuery(".iwar-loading-actions").show();

	jQuery.getJSON(ajaxurl, {
		action: 'iwar_get_available_actions',
		trigger: trigger_class
	}, function(data) {
		iwar.available_actions = data;
		jQuery(".iwar-actions").show();

		jQuery(".iwar-add-action").show();
		jQuery(".iwar-loading-actions").hide();
	});

	// load merge fields:
	jQuery.getJSON(ajaxurl, {
		action: 'iwar_get_available_merge_fields',
		trigger: trigger_class
	}, function(data) {
		iwar.available_merge_fields = data;

		var merge_htm = '<select name="merge_group" class="merge_group">';
		for(var grp in data) {
			merge_htm += '<option value="'+grp+'">'+data[grp][0]+'</option>';
		}
		merge_htm += '</select>';
		jQuery(".merge-type-contain").html(merge_htm);
		iwar_load_merge_fields();

	});
}

function iwar_load_merge_fields() {
	jQuery(".merge-field-contain").html('<i>Loading...</i>');
	var grp = jQuery(".merge_group").val();

	if(iwar.available_merge_fields[grp]) {
		if(Object.keys(iwar.available_merge_fields[grp].keys).length > 0) {
			var fld_htm = '<select name="merge_fld" class="merge_fld">';
			for(var key in iwar.available_merge_fields[grp].keys) {
				fld_htm += '<option value="'+key+'">'+iwar.available_merge_fields[grp].keys[key]+'</option>';
			}
			fld_htm += '</select>';
		} else {
			fld_htm = '<input type="merge_fld" class="merge_fld" placeholder="Enter Field Name" />';
		}

		jQuery(".merge-field-contain").html(fld_htm);
	} else {
		jQuery(".merge-field-contain").html('<i>No available merge fields</i>');
	}
}


function iwar_available_conditions_html() {
	var cond_html = '<div class="autome-condition"><div class="autome-remove"><i class="fa fa-times"></i></div><select class="iwar_condition" name="iwar_condition[]">';
	var count_cond = 0;
	var first_cond = "";

	for(cond in iwar.available_conditions) {
		if(jQuery.inArray(cond, iwar.selected_conds) == -1 || iwar.available_conditions[cond].allow_multiple) {
			if(first_cond == "") first_cond = cond;
			count_cond++;
			cond_html += '<option value="'+cond+'">' + iwar.available_conditions[cond].title + '</option>';
		}
	}

	cond_html += '</select><form class="iwar-cond-disphtml-area">';
	cond_html += iwar.available_conditions[first_cond].html;
	cond_html += '</form></div>';
	return cond_html;
}

function iwar_available_actions_html() {
	var act_html = '<div class="autome-action"><div class="autome-remove"><i class="fa fa-times"></i></div><select class="iwar_action" name="iwar_action[]">';
	var count_action = 0;
	var first_act = "";

	for(act in iwar.available_actions) {
		act_html += '<option value="'+act+'">' + iwar.available_actions[act].title + '</option>';
		if(first_act == "") first_act = act;
	}

	act_html += '</select><form class="iwar-act-disphtml-area">';
	act_html += iwar.available_actions[first_act].html;
	act_html += iwar_action_override_html();
	act_html += '</form></div>';
	return act_html;
}

function iwar_action_override_html() {
	act_html = '<div style="text-align: right;"><i class="fa fa-cog triggering-advanced" aria-hidden="true" title="Toggle advanced options"></i></div>';
	act_html += '<div class="iwar-action-override"><hr>Custom Email to Trigger&nbsp;&nbsp;<input type="text" name="_override_email" value="" style="width: 210px;" class="iwar-mergeable">';
	act_html += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></div>';
	return act_html;

}



function refresh_iwar_conditions() {
	var conds_selected = jQuery(".iwar_condition");
	iwar.selected_conds = [];

	if(conds_selected.length > 0) {
		for(var i = 0; i < conds_selected.length; i++) {
			iwar.selected_conds.push(jQuery(conds_selected[i]).val());
		}
	}

	var cond_removelimit = false;
	for(cond in iwar.available_conditions) {
		if(iwar.available_conditions[cond].allow_multiple) {
			cond_removelimit = true;
			break;
		}  
	}

	if(iwar.selected_conds.length == Object.keys(iwar.available_conditions).length && cond_removelimit == false) {
		jQuery(".iwar-add-condition").hide();
	} else {
		jQuery(".iwar-add-condition").show();
	}

}

function iwar_save_recipe() {
	var submit_json = {};
	submit_json.trigger = jQuery("[name='iwar-trigger']").val();
	submit_json.id = jQuery("[name='recipe_id']").val();
	submit_json.title = jQuery("[name='recipe-title']").val();
	submit_json.disable_stats = jQuery("[name='disable_stats']").is(":checked");

	if(submit_json.title == '') {
		var error_msg = 'Please enter a recipe title to proceed';
		swal('Error Saving Recipe', error_msg, 'error');
		return false;
	}

	// conditions save:
	var conds = jQuery(".autome-condition");
	submit_json.conditions = [];

	for(var i=0; i< conds.length; i++) {
		var $cond_blk = jQuery(conds[i]);
		var new_cond = {};
		new_cond.condition = $cond_blk.find(".iwar_condition").val();
		var cond_config = $cond_blk.find(".iwar-cond-disphtml-area").serializeArray();
		new_cond.config = {};

		for(var j = 0; j < cond_config.length; j++) {
			var config_name = cond_config[j].name;
			if(config_name.indexOf("[]") !== -1) {
				config_name = config_name.replace('[]','');
				if(!new_cond.config[config_name]) {
					new_cond.config[config_name] = [];
				}					
				new_cond.config[config_name].push(cond_config[j].value);
			} else {
				new_cond.config[config_name] = cond_config[j].value;
			}

		}

		submit_json.conditions.push(new_cond);
	}

	// actions save:
	var acts = jQuery(".autome-action");
	submit_json.actions = [];

	for(var i=0; i< acts.length; i++) {
		var $act_blk = jQuery(acts[i]);
		var new_act = {};
		new_act.action = $act_blk.find(".iwar_action").val();
		var act_config = $act_blk.find(".iwar-act-disphtml-area").serializeArray();
		new_act.config = {};

		for(var j = 0; j < act_config.length; j++) {
			var config_name = act_config[j].name;
			if(config_name.indexOf("[]") !== -1) {
				config_name = config_name.replace('[]','');
				if(!new_act.config[config_name]) {
					new_act.config[config_name] = [];
				}
				
				new_act.config[config_name].push(act_config[j].value);
			} else {
				new_act.config[config_name] = act_config[j].value;
			}
			
		}

		submit_json.actions.push(new_act);
	}

	iwar.submit_json = submit_json;
	jQuery(".iwar-saving-recipe").show();
	jQuery(".iwar-save-recipe").hide();

	jQuery.post(ajaxurl + "?action=iwar_save_recipe", submit_json, function(data) {
		if(!data.success) {
			jQuery(".iwar-saving-recipe").hide();
			jQuery(".iwar-save-recipe").show();

			var errors = data.errors;
			error_msg = errors.join("\n");
			swal('Error Saving Recipe', error_msg, 'error');
		} else {
			var new_href = 'admin.php?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe=' + data.save_id + '&saved#iwar-public-note';
			if(location.href != new_href) {
				location.href = new_href;
			} else {
				location.reload();
			}
		}
	}, 'json');
}

function load_recipe_stats(recipe_id) {
	$recipe_el = jQuery(".iw-recipe-id-"+recipe_id);
	$recipe_el.find('.recipe-stat-contain').show(); 
	var recipe_id = $recipe_el.attr('recipe-id');
	$recipe_el.find('.recipe-stat-chart').hide()
	$recipe_el.find('.recipe-cv').html('<canvas class="recipe-stat-chart" width="500" height="200"></canvas></div>');
	$recipe_el.find(".chartld").show();
	
	jQuery.getJSON(ajaxurl, {
		action: 'iwar_get_stats',
		date_range: $recipe_el.find('.iwar-stat-date-range').val(),
		custom_start: $recipe_el.find('.iwar-stat-start').val(),
		custom_end: $recipe_el.find('.iwar-stat-end').val(),
		recipe_id: recipe_id
	}, function(data) {
		jQuery(".iw-recipe-id-"+data.recipe_id).find(".chartld").hide();
		jQuery(".iw-recipe-id-"+data.recipe_id).find(".recipe-stat-chart").show();

		var chart_data = {
	    labels: data.x,
		    datasets: [
		        {
		            label: "Number times Triggered",
		            fillColor: "rgba(151,187,205,0.2)",
		            strokeColor: "rgba(151,187,205,1)",
		            pointColor: "rgba(151,187,205,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#fff",
		            pointHighlightStroke: "rgba(151,187,205,1)",			            
		            data: data.y
		        }
		    ]
		};

		var chart_area = jQuery(".iw-recipe-id-"+data.recipe_id).find('.recipe-stat-chart').get(0);
		var ctx = chart_area.getContext("2d");
		var myLineChart = new Chart(ctx).Line(chart_data);

	});
}

function iwar_insertAtCaret(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
        "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}