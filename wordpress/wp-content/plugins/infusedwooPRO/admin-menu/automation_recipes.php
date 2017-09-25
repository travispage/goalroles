<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// sortby save
if(isset($_GET['sortby'])) {
	update_option( 'iwar_sortby', $_GET['sortby'] );
	$sortby = $_GET['sortby'];
} else {
	$sortby = get_option( 'iwar_sortby' );
	if(empty($sortby)) $sortby = 'datecreation';
}

$args = array(
	 'posts_per_page' 	=> -1,
	 'post_type' 		=> 'iw_automation_recipe',
	 'post_status'		=> array('iw-enabled','iw-disabled','draft'),
	 'meta_key'			=> 'iwar_last_triggered',
	 'meta_value'		=> 0,
	 'meta_compare'		=> '>='
);

switch ($sortby) {
	case 'datelasttriggered':
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'DESC';
		break;
	case 'numtriggeredall':
		$args['orderby'] = 'comment_count';
		$args['order'] = 'DESC';
		break;
	case 'alphabetical':
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
		break;


}

$automation_recipes =  get_posts( $args );
wp_enqueue_script('jquery-ui-autocomplete');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-dialog');
wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
wp_enqueue_style( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.css" );
wp_enqueue_script( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.min.js", array('jquery'));
wp_enqueue_script( 'iwar_chartjs', INFUSEDWOO_PRO_URL . "admin-menu/assets/Chart.min.js", array('iw_sweetalert'));
wp_enqueue_script( 'iw_automation_recipe', INFUSEDWOO_PRO_URL . "3.0/automation-recipes/automation_recipes.js", array('iw_sweetalert','iwar_chartjs','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'));

do_action('adm_automation_recipe_before');

?>

<?php if(!isset($_GET['edit_recipe'])) { ?>
<h1>Automation Recipes</h1>
<?php } else if($_GET['edit_recipe'] == 'new') { ?>
<a class="iw-top-control" href="?page=infusedwoo-menu-2&submenu=automation_recipes">
<i class="fa fa-long-arrow-left"></i>&nbsp; Back to Automation Recipes List</a>
<h1>New Automation Recipe</h1>

<?php } else if($_GET['edit_recipe'] > 0) { ?>
<a class="iw-top-control" href="?page=infusedwoo-menu-2&submenu=automation_recipes">
<i class="fa fa-long-arrow-left"></i>&nbsp; Back to Automation Recipes List</a>
<h1>Edit Automation Recipe</h1>
<?php } ?>

<hr>




<?php if(!isset($_GET['edit_recipe'])) { ?>

	<?php if(!is_array($automation_recipes) || count($automation_recipes) == 0) { ?>
	<center>
		<br><br><br>
	<img src="https://s3.amazonaws.com/infusedaddons/screenshots/InfusedWooNinja_meditate.png" style="max-height: 200px;" />
	<br><br>
	<h3>
	You currently don't have an automation recipe.</h3>
	<a href="?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe=new"><div class="blue-button" style="">Create New Recipe</div></a> 
	<a href="https://infusedaddons.com/redir.php?to=automation_recipes" target="_blank"><div class="purple-button" style="">Learn More</div></a>
	</h3><br><br>
	<form  action="?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe=new" method="POST"  enctype="multipart/form-data">
			  <a style="font-size: 11pt; cursor: pointer;" class="uploadconf" >or upload a config file...</a>
			    <input type="file" name="uploadconf" id="fileToUpload" style="display: none;" accept=".conf">
			</form>

	</center>

	<?php } else { ?>
		<div class="autome-controls">
				Search <input type="text" id="search_recipes" placeholder="Search Recipes..." />
			<a href="?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe=new">
				<div class="blue-button" style="float: right;">Create New Recipe</div><br>
			</a>
			<form style="float:right" action="?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe=new" method="POST"  enctype="multipart/form-data">
			  <a style="font-size: 10pt; cursor: pointer;" class="uploadconf" >or upload a config file...</a>
			    <input type="file" name="uploadconf" id="fileToUpload" style="display: none;" accept=".conf">
			</form>
			<label>Sort by</label> <select name="sortby" id="sortby" autocomplete="off">
				<option value="datecreation" <?php echo $sortby == 'datecreation' ? 'selected': ''; ?>>Date of Creation</option>
				<option value="datelasttriggered" <?php echo $sortby == 'datelasttriggered' ? 'selected': ''; ?>>Date Last Triggered</option>
				<option value="numtriggeredall" <?php echo $sortby == 'numtriggeredall' ? 'selected': ''; ?>>Number of times triggered all-time</option>
				<option value="alphabetical" <?php echo $sortby == 'alphabetical' ? 'selected': ''; ?>>Alphabetical</option>
			</select> 

		</div>
		<?php 
		$success_notice = get_transient( 'iwar_main_notice_success' );
		if($success_notice !== false) { 
			?>
			<div class="iwar-success-notice"><?php echo $success_notice; ?></div>
		<?php 
			delete_transient(  'iwar_main_notice_success' );
		} ?>
		<center class="iw_search_empty" style="display:none"><i>No automation recipes found from search keyword.</i></center>
		<?php
		foreach($automation_recipes as $recipe) { 
			$new_recipe = new IW_Automation_Recipe($recipe->ID);
			$triggers = wp_count_comments($recipe->ID);
			$total_triggers = (int) $triggers->all;
			$last_triggered_timestamp = get_post_meta( $recipe->ID, 'iwar_last_triggered', true );
			$stat_disabled = get_post_meta( $recipe->ID, 'disable_stats', true );
			if(!empty($last_triggered_timestamp)) {
				if(date('Y-m-d', $last_triggered_timestamp) == date('Y-m-d')) {
					$day = 'Today';
				} else if(date('Y', $last_triggered_timestamp) == date('Y')) {
					$day = date('M-d',$last_triggered_timestamp);
				} else {
					$day = date('Y-M-d',$last_triggered_timestamp);
				}
				$last_triggered_string = 'Last Triggered: ' . $day . ' ' . date('h:i A', $last_triggered_timestamp);
			} else {
				$last_triggered_string = '';
			}

			$comments_query = new WP_Comment_Query;
			$args = array(
					'post_id' => $recipe->ID,
					'date_query' => array(
						'year'    => date( 'Y' ),
						'month'   => date( 'm' ),
						'day'	  => date( 'd' ),
					),
					'count' => 1
				);
			$triggered_today = (int) $comments_query->query( $args );

			$recipe_html = '<div class="iw-recipe-item iw-recipe-id-'.$recipe->ID . (!$new_recipe->enabled ? ' iwar-recipe-disabled' : '') . '" recipe-id="'.$recipe->ID.'">';
			$recipe_html .= '<div class="recipe-item-top">';
			if(!$stat_disabled) {
				$recipe_html .= '<div class="recipe-stats"><span class="iw-recipe-stat-today" title="# Triggered Today">'.$triggered_today.'</span><span  title="# Triggered All Time" class="iw-recipe-stat-all">'.$total_triggers.'</span></div>';
			}
			$recipe_html .= '<div class="recipe-title">'. get_the_title( $recipe->ID ) . '</div>';
			$recipe_html .= '</div>';
			$recipe_html .= '<div class="recipe-item-controls">';
			$recipe_html .= '<div class="recipe-last-triggered">'.$last_triggered_string.'</div>';
			$recipe_html .= '<a href="?page=infusedwoo-menu-2&submenu=automation_recipes&edit_recipe='.$recipe->ID.'"><div class="recipe-control"><i class="fa fa-pencil"></i> Edit</div></a>';
			
			if($new_recipe->enabled) {
				$recipe_html .= '<a href="?page=infusedwoo-menu-2&submenu=automation_recipes&deactivate_recipe='.$recipe->ID.'">';
				$recipe_html .= '<div class="recipe-control"><i class="fa fa-power-off"></i> Deactivate</div>';
				$recipe_html .= '</a>';
			} else {
				$recipe_html .= '<a href="?page=infusedwoo-menu-2&submenu=automation_recipes&activate_recipe='.$recipe->ID.'">';
				$recipe_html .= '<div class="recipe-control"><i class="fa fa-power-off"></i> Activate</div>';
				$recipe_html .= '</a>';
			}
			$recipe_html .= '<div class="recipe-control cont-more"><i class="fa fa-caret-down"></i> More Actions </div>';
			$recipe_html .= '<ul class="recipe-control-more">';
			$recipe_html .= '<li class="iwar-clone"><i class="fa fa-clone"></i>Clone</li>';
			if(!$stat_disabled) {
				$recipe_html .= '<li class="show-stats-button"><i class="fa fa-line-chart"></i>Show Stats</li>';
				$recipe_html .= '<li class="hide-stats-button" style="display:none;"><i class="fa fa-line-chart"></i>Hide Stats</li>';
			}
			$recipe_html .= '<li class="manually-trigger"><i class="fa fa-share"></i>Manually trigger to contact</li>';
			$recipe_html .= '<li class="iwar-export-to-file"><i class="fa fa-download"></i>Export to File</li>';
			$recipe_html .= '<a class="iwar-delete-recipe" href="?page=infusedwoo-menu-2&submenu=automation_recipes&delete_recipe='.$recipe->ID.'"><li><i class="fa fa-trash-o"></i>Delete</li></ul></a>';


			$recipe_html .= '</div>';
			$recipe_html .= '<div class="recipe-stat-contain">';
			$recipe_html .= '<span class="recipe-stat-control">Date Range &nbsp;<select class="iwar-stat-date-range">';
			$recipe_html .= '<option value="today">Today</option>';
			$recipe_html .= '<option value="week">This Week</option>';
			$recipe_html .= '<option value="month">This Month</option>';
			$recipe_html .= '<option value="custom">Custom</option>';
			$recipe_html .= '</select>';
			$recipe_html .= '<a class="iwar-clear-stats" href="?page=infusedwoo-menu-2&submenu=automation_recipes&clear_stats_recipe='.$recipe->ID.'"><input type="button"  value="Clear Stats" style="float:right; cursor: pointer;" /></a>';

			$recipe_html .= '&nbsp;&nbsp;<span class="custom-date"><input type="text" class="iwar-stat-start" value="'.date("m/d/Y", time()).'" /> &mdash; ';
			$recipe_html .= '<input type="text" class="iwar-stat-end" value="'.date("m/d/Y", time()).'" />&nbsp;&nbsp;<input type="button" class="iwar-update-stats" value="Update Stats" /></span>';

			$recipe_html .= '</span><center><div class="chartld">Fetching Stats...</div><div class="recipe-cv"><canvas class="recipe-stat-chart" width="500" height="200"></canvas></div></center>';
			$recipe_html .= '</div>';
			$recipe_html .= '</div>';

			
			echo $recipe_html;
		?>
		
	<?php } } ?>


<?php } else if(isset($_GET['edit_recipe'])) { 
	$recipe_id = $_GET['edit_recipe']; 
	$existing_recipe = false;
	$stat_disabled = false;

	if($recipe_id != 'new') {
		$settings = iwar_get_recipe_settings($recipe_id);
		$stat_disabled = get_post_meta( $recipe_id, 'disable_stats', true );
		if(!empty($settings['trigger'])) $existing_recipe = true;
	} else {
		if(isset($_FILES['uploadconf']["tmp_name"])) {
			$conf = file_get_contents($_FILES["uploadconf"]["tmp_name"]);
			$conf = maybe_unserialize( $conf );

			$metas = array();
			if(is_array($conf['metas'])) foreach($conf['metas'] as $meta) {
				$metas[$meta->meta_key] = maybe_unserialize( $meta->meta_value );
			}

			$settings = array(
					'title' 	=> isset($conf['post_title']) ? $conf['post_title'] : '',
					'trigger'	=> isset($metas['iw_trigger_class']) ? $metas['iw_trigger_class'] : '',
					'config'	=> isset($metas['iw_recipe_config']) ? $metas['iw_recipe_config'] : array(),
				);

			if(!empty($settings['trigger'])) $existing_recipe = true;
			$stat_disabled = isset($metas['disable_stats']) ? $metas['disable_stats'] : 0;
		}			
	}
	?>

	<?php 
		$success_notice = get_transient( 'iwar_edit_notice_success' );
		if($success_notice !== false) { 
			?>
			<div class="iwar-success-notice"><?php echo $success_notice; ?></div>
		<?php 
			delete_transient(  'iwar_edit_notice_success' );
		} ?>

	<span class="circle-step">1</span> <span class="step-head">Select Trigger</span> <span class="step-edit select-trigger-edit" style="display: none;">[<u>Edit</u>]</span><br>
	<input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>" />
	<div class="big-row">
			<span class="trigger-select-info" <?php echo $existing_recipe ? 'style="display:none"' : ""; ?>>When do you want this recipe to be triggered? Select one of the available triggers below.</span>
			<span class="trigger-selected select-trigger-edit" <?php echo !$existing_recipe ? 'style="display:none"' : ""; ?>><a href="#">Click here to change trigger</a></span>
			<input type="hidden" name="iwar-trigger" value="<?php echo $existing_recipe ? $settings['trigger'] : ''; ?>" />
			<div class="trigger-set">

			<?php
				global $trig_main_class_iwar;
				$trigger_classes = $trig_main_class_iwar->get_available_triggers();

				foreach($trigger_classes as $k => $trigger) if(!$trigger->is_advanced) {
					?>
					<div class="trigger-display <?php echo $existing_recipe && $k == $settings['trigger'] ? 'selected_trigger' : ''; ?>" value="<?php echo $k; ?>" <?php echo $existing_recipe && $k != $settings['trigger'] ? 'style="display:none"' : ""; ?>>
					<div class="trigger-display-icon">
						<?php echo $trigger->get_icon(); ?>
					</div>
					<div class="trigger-title">
						<?php echo $trigger->get_title(); ?>
					</div>
					<div class="trigger-desc">
						Triggers <?php echo $trigger->get_desc(); ?>
					</div>

					</div>
				<?php } ?>
				
				<a href="#" class="more-triggers-show"<?php echo isset($settings['trigger']) ? ' style="display:none;"' : ""; ?>>
					<i class="fa fa-caret-right"></i><i class="fa fa-caret-down" style="display: none;"></i> More Triggers
				</a>
				
				<?php foreach($trigger_classes as $k => $trigger) if($trigger->is_advanced) {
					?>
					<div class="trigger-advanced trigger-display <?php echo $existing_recipe && $k == $settings['trigger'] ? 'selected_trigger' : ''; ?>" value="<?php echo $k; ?>" <?php echo $existing_recipe && $k == $settings['trigger'] ? '' : 'style="display:none"'; ?>>
					<div class="trigger-display-icon">
						<?php echo $trigger->get_icon(); ?>
					</div>
					<div class="trigger-title">
						<?php echo $trigger->get_title(); ?>
					</div>
					<div class="trigger-desc">
						Triggers <?php echo $trigger->get_desc(); ?>
					</div>

					</div>
				<?php } ?>

		</div>

	</div><br>

	<div class="iw-ar-proceed" <?php echo !$existing_recipe ? 'style="display:none"' : ""; ?>>
	<span class="circle-step">2</span> <span class="step-head">Add Conditions</span><br>

	<div class="big-row">
			Actions will only run when all conditions added here are met. If no conditions are added, actions will always run when this recipe is triggered.
			<br>
			<div class="iwar-conditions">
				<?php 
					if(isset($settings['config']['conditions'])) {
						$conditions = $settings['config']['conditions'];
						$selected_conditions = array();
						$available_conditions = iwar_get_available_conditions($settings['trigger']);

						foreach($conditions as $condition) {
							?>
								<div class="autome-condition"><div class="autome-remove"><i class="fa fa-times"></i></div><select class="iwar_condition" name="iwar_condition[]" autocomplete="off">
									<?php foreach($available_conditions as $k => $cond) if(!in_array($k, $selected_conditions) || $cond['allow_multiple']) { ?>
										<option value="<?php echo $k ?>" <?php echo $k == $condition['condition'] ? 'selected="selected"' : ''; ?>><?php echo $cond['title']; ?></option>
									<?php } ?>
								</select>

								<form class="iwar-cond-disphtml-area">
								<?php 
									$this_cond = new $condition['condition'];
									$config = isset($condition['config']) ? $condition['config'] : array();
									echo $this_cond->display_html($config, $existing_recipe ? $settings['trigger'] : '');
								?>
							</form>
							</div>
							<?php
							$selected_conditions[] = $condition['condition'];
						}
					}
				?>
			</div>
			<div class="iwar-loading-conditions"><img src="<?php echo INFUSEDWOO_PRO_URL . "images/ajax.gif" ?>" /></div>
			<div class="blue-button iwar-add-condition" style="display:none"><i class="fa fa-plus"></i> Add Condition</div>
	</div>
	<br><br>
	<span class="circle-step">3</span> <span class="step-head">Add Actions</span><br>

	<div class="big-row">
			These actions will run and applied to the contact when the recipe is triggered and conditions are met.
			<br>
			<div class="iwar-actions">
				<?php
				if(isset($settings['config']['actions'])) {
						$actions = $settings['config']['actions'];
						$available_actions = iwar_get_available_actions($settings['trigger']);

						foreach($actions as $action) { ?>
						<div class="autome-action"><div class="autome-remove"><i class="fa fa-times"></i></div><select class="iwar_action" name="iwar_action[]" autocomplete="off">
							<?php foreach($available_actions as $k => $act) { ?>
										<option value="<?php echo $k ?>" <?php echo $k == $action['action'] ? 'selected="selected"' : ''; ?>><?php echo $act['title']; ?></option>
									<?php } ?>
						</select>
						<form class="iwar-act-disphtml-area">
								<?php 
									$this_act = new $action['action'];
									$action_config = isset($action['config']) ? $action['config'] : array();
									$override_email = isset($action_config['_override_email']) ? $action_config['_override_email'] : '';

									echo $this_act->display_html($action_config, $existing_recipe ? $settings['trigger'] : '');
									echo '<div style="text-align: right;"><i class="fa fa-cog triggering-advanced" aria-hidden="true" title="Toggle advanced options"></i></div>';
									echo '<div class="iwar-action-override"><hr>Custom Email to Trigger&nbsp;&nbsp;<input type="text" name="_override_email" value="'.$override_email.'" class="iwar-mergeable" style="width: 210px;">';
									echo '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></div>';
								?>
							</form>
						</div>
						<?php } ?>

				<?php } ?>

			</div>
			<br>
			<div class="iwar-loading-actions"><img src="<?php echo INFUSEDWOO_PRO_URL . "images/ajax.gif" ?>" /></div>
			<div class="purple-button iwar-add-action" style="display:none"><i class="fa fa-plus"></i> Add Action</div>
	</div>
	<br>
	Recipe Title &nbsp;&nbsp;<input placeholder="Enter Recipe Title..." type="text" name="recipe-title" value="<?php echo isset($settings['title']) ? $settings['title'] : "";  ?>" style="width: 400px;" />
	<div style="margin-top: 18px;">
	Disable Stats&nbsp;&nbsp;&nbsp;<input type="checkbox" name="disable_stats" <?php echo $stat_disabled ? 'checked' : ''?>>
	</div> 
	<br>
	<?php 
		$trigger = isset($settings['trigger']) ? new $settings['trigger'] : "";

		if(!empty($trigger) && method_exists($trigger, 'admin_public_note')) {
			$add_note = $trigger->admin_public_note($recipe_id, $settings['config']);
		} else {
			$add_note = "";
		}

		if(!empty($add_note)) { 
	?>
		<div class="iwar-public-note" id="iwar-public-note">
			<?php echo $add_note; ?>
		</div>
	<?php } ?>
	<hr>
	<center>
	<div class="green-button big-button iwar-save-recipe">Save Recipe</div>
	<div class="iwar-saving-recipe" style="display:none;"><img src="<?php echo INFUSEDWOO_PRO_URL . "images/ajax.gif" ?>" /></div><br><br>
	</center>


	</div>
<?php } ?>


<?php do_action('adm_automation_recipe_after'); ?>
<div id="tinymce_dialog" class="iw-jq-modal iw-tag-dialog-modal" title="Edit Content">
	<form>
	<div style="float:left; position: relative; top: 4px; z-index: 9999"><a href="#" style="font-size: 11pt;" class="merger_link">Insert Merge Field</a></div>
	<?php wp_editor('','iwar_tinymce', array(
			'wpautop' => true, 
		    'media_buttons' => false, 
		    'textarea_name' => 'iwar_tinymce', 
		    'editor_height' => 300, 
		    'tabindex' => '',
		    'editor_css' => '', 
		    'editor_class' => '', 
		    'teeny' => false, 
		    'dfw' => false, 
		    'tinymce' => true,
		    'quicktags' => true
		)); ?>
	</form>
</div>
<script>
	jQuery(document).ready(function() {
		tinymce_dialog = jQuery("#tinymce_dialog").dialog({
		  autoOpen: false,
	      height: 550,
	      width: 800,
	      modal: true,
	      buttons: {
	      	Cancel: function() {
	          tinymce_dialog.dialog( "close" );
	        },
	        "Save Content": function() {
	        	if(tinymce_caller) {
	        		if(tinyMCE.activeEditor) {
	        			var content = tinyMCE.activeEditor.getContent();
	        		} else {
	        			var content = jQuery('textarea#iwar_tinymce').val();
	        		}
	        		tinymce_caller.children('input[type=hidden]').val(content);
	        		tinymce_dialog.dialog( "close" );

	        	}
	        },
	        
	      },
	      close: function() {
	      	if(tinyMCE.activeEditor) {
	        	tinyMCE.activeEditor.setContent('');
	        }
			jQuery('textarea#iwar_tinymce').val( '' );
			
	      }
	    });

	    jQuery('body').on('click', ".tincymce_expand", function() {
	    	tinymce_caller = jQuery(this).parent();
	    	var content = tinymce_caller.children('input[type=hidden]').val();

	    	tinymce_dialog.dialog('open');
	    	if(tinyMCE.activeEditor) {
	    		tinyMCE.activeEditor.setContent(content);
	    	}
	    	jQuery('textarea#iwar_tinymce').val(content); 
	    	return false;
	    });
	});

</script>


<div id="merge_dialog" class="iw-jq-modal iw-tag-dialog-modal" title="Insert Merge Field">
	<form>
	<table>
		<tr>
	      <td><label for="name">Merge Type</label></td>
	      <td class="merge-type-contain"><i>Loading...</i></td>
	   
	    </tr>
	    <tr>
	      <td><label for="email">Merge Field</label></td>
	      <td class="merge-field-contain"><i>Loading...</i></td>
	     </tr>
	     <tr>
	     	<td colspan="2"><a href="#" class="adv-mrg" style="font-size: 10pt;">Advanced Options</a></td>
	     </tr>
	     <tr class="iwar-adv-merge" display: none;>
	     	<td colspan="2">
	     		<hr>
	     		<label for="email">Fallback</label><br>
				<input type="text" value="" class="merge-fallback" placeholder="Enter default value..." style="margin-top: 5px;"/>
	      		<br><span class="lil-info">This is the default value that will be returned in the event that the merge field value is empty</span>
	      	</td>
	     </tr>
	 </table>
	</form>
</div>

