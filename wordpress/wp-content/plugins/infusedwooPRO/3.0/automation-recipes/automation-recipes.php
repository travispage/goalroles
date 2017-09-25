<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// add super-classes
include(INFUSEDWOO_3_DIR . "automation-recipes/IW_Automation_Trigger.class.php");
include(INFUSEDWOO_3_DIR . "automation-recipes/IW_Automation_MergeFields.class.php");
include(INFUSEDWOO_3_DIR . "automation-recipes/IW_Automation_Condition.class.php");
include(INFUSEDWOO_3_DIR . "automation-recipes/IW_Automation_Action.class.php");
include(INFUSEDWOO_3_DIR . "automation-recipes/IW_Automation_Recipe.class.php");

// register automation-recipe custom post type:
add_action('init', 'iw_automation_recipes', 1,1);
add_filter('comments_clauses', 'iwar_exclude_stat_comments' );
add_filter('woocommerce_get_image_size_em_thumb', 'iwar_woo_image_size');

add_action( 'wp_ajax_nopriv_iw_timer_gif', 'iw_generate_dynamic_timer', 10, 0);
add_action( 'wp_ajax_iw_timer_gif', 'iw_generate_dynamic_timer', 10, 0);
add_shortcode( 'iw_merge', 'iw_merge_shortcode' );

function iw_automation_recipes() {
	register_post_type( 'iw_automation_recipe',
	    array(
	      'labels' => array(
	        'name' => __( 'Automation Recipes' ),
	        'singular_name' => __( 'Automation Recipe' )
	      ),
	      'capability_type' => 'iw_automation_recipe',
	      'map_meta_cap'        => true,
	      'publicly_queryable'  => false,
	      'exclude_from_search' => true,
	      'public' => false,
	      'hierarchical'        => false,
		  'show_in_nav_menus'   => false,
		  'rewrite'             => false,
		  'query_var'           => false,
		  'supports'            => array( 'title', 'comments', 'custom-fields' ),
	      'has_archive' => false,
	    )
	  );

	register_post_status( 'iw-enabled', array(
			'internal' => true,
			'public' => false,
			'private' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false
		)); 

	register_post_status( 'iw-disabled', array(
		'internal' => true,
		'exclude_from_search' => true,
		'show_in_admin_all_list' => false,
		'show_in_admin_status_list' => false
	)); 

	// WC_Email template:
	add_filter( 'woocommerce_email_classes', 'iwar_add_wc_email_template' );

}

function iwar_woo_image_size() {
	$size = array();
	$size['width']  = '120';
    $size['height'] = '120';
    $size['crop']   = isset( $size['crop'] ) ? $size['crop'] : 0;
    return $size;
}

function iwar_add_wc_email_template($emails) {
	$emails['WC_Email_InfusedWoo'] = include(INFUSEDWOO_PRO_DIR . '3.0/automation-recipes/WC_Email_InfusedWoo.class.php');
	return $emails;
}

 function iwar_exclude_stat_comments( $clauses ) {
    if(is_admin() && function_exists('get_current_screen')) {
	    $scr =  get_current_screen();
		if(isset($scr->base) && $scr->base == 'dashboard') {
		    global $wpdb;
			
		    if ( ! $clauses['join'] ) {
		        $clauses['join'] = '';
		    }

		    if ( ! strstr( $clauses['join'], "JOIN $wpdb->posts" ) ) {
		        $clauses['join'] .= " LEFT JOIN $wpdb->posts ON comment_post_ID = $wpdb->posts.ID ";
		    }

		    if ( $clauses['where'] ) {
		        $clauses['where'] .= ' AND ';
		    }

		    $clauses['where'] .= " $wpdb->posts.post_type NOT IN ('" . "iw_automation_recipe" . "') ";
		}
	}

	return $clauses;
}

function iw_update_automation_recipe($title="New Recipe", $trigger_class, $config, $actions = array(), $id = false) {
	if(!class_exists($trigger_class)) throw new Exception("No Trigger $trigger_class found");


	$trigger = new $trigger_class($config, $actions);
	$action_titles = array();

	foreach($actions as $action) {
		if(class_exists($action)) {
			$new_action = new $action;

			$action_config = isset($config['action_config'][$action]) ? $config['action_config'][$action] : array();
			$action_titles[] = $new_action->get_title($action_config);	
		}
	}

	if($id == false) {
		$new_post = array(
				'post_title' 	=> $title,
				'post_type' 	=> 'iw_automation_recipe',
				'post_content' 	=> serialize(array($action_titles, $config)),
				'post_status'	=> 'iw-enabled',
				'meta_input'	=> array(
						'iw_trigger_class' 	=> $trigger_class,
						'iw_recipe_config'	=> $config,
						'iw_recipe_actions'	=> $actions,
						'iwar_last_triggered' => 0
					)
			);

		return wp_insert_post($new_post);
	} else {
		$edit_post = array(
		  'ID'           => $id,
		  'post_title'   => $title,
		  'post_content' => serialize(array($action_titles, $config)),
		);

		wp_update_post( $edit_post );

		update_post_meta( $id, 'iw_trigger_class', $trigger_class );
		update_post_meta( $id, 'iw_recipe_config', $config );
		update_post_meta( $id, 'iw_recipe_actions', $actions );

		return $id;
	}
}

function iw_add_trigger_class($trigger_class) {
	global $trig_main_class_iwar;
	if(!isset($trig_main_class_iwar)) $trig_main_class_iwar = new IW_Automation_Trigger;
	$trig_main_class_iwar->add_trigger_class($trigger_class);
}

function iw_add_condition_class($condition_class) {
	global $cond_main_class_iwar;
	if(!isset($cond_main_class_iwar)) $cond_main_class_iwar = new IW_Automation_Condition;
	$cond_main_class_iwar->add_condition_class($condition_class);
}

function iw_add_action_class($action_class) {
	global $action_main_class_iwar;
	if(!isset($action_main_class_iwar)) $action_main_class_iwar = new IW_Automation_Action;
	$action_main_class_iwar->add_action_class($action_class);
}

function iwar_get_available_conditions($trigger_class="") {
	global $cond_main_class_iwar;
	$httpreq = false;
	if(isset($_GET['trigger']) && empty($trigger_class)) {
		$trigger_class = $_GET['trigger'];
		$httpreq = true;
	}


	$conditions_raw = $cond_main_class_iwar->get_available_conditions($trigger_class);
	$conditions = array();

	foreach($conditions_raw as $k => $c) {
		$conditions[$k] = array(
				'title' => $c->get_title(),
				'html' 	=> $c->display_html(array(), $trigger_class),
				'allow_multiple' => (isset($c->allow_multiple) && $c->allow_multiple) ? 1 : 0 
			);
	}

	if($httpreq) {
		echo json_encode($conditions);
		exit();
	} else {
		return $conditions;
	}
}

function iwar_get_available_actions($trigger_class="") {
	global $action_main_class_iwar;

	$httpreq = false;
	if(isset($_GET['trigger']) && empty($trigger_class)) {
		$trigger_class = $_GET['trigger'];
		$httpreq = true;
	}

	$actions_raw = $action_main_class_iwar->get_available_actions($trigger_class);
	$actions = array();

	foreach($actions_raw as $k => $a) {
		$actions[$k] = array(
				'title' => $a->get_title(),
				'html' 	=> $a->display_html(array(), $trigger_class)
			);
	}

	if($httpreq) {
		echo json_encode($actions);
		exit();
	} else {
		return $actions;
	}
}

function iwar_save_recipe() {
	// validate first before saving:
	// validate conditions
	$conditions = isset($_POST['conditions']) && is_array($_POST['conditions']) ? $_POST['conditions'] : array();
	$errors = array();

	foreach($conditions as $k => $c) {
		$cond_class = $c['condition'];
		$cond_num = $k+1;
		if(!class_exists($cond_class)) {
			$errors[] = "Condition # $cond_num is not valid anymore. Please remove condition or refresh the page to load available conditions.";
			continue;
		}

		$cond = new $cond_class;
		$config = isset($c['config']) ? $c['config'] : array();
		$validate_cond = $cond->validate_entry($config);

		if(!empty($validate_cond)) {
			$errors[] = "Condition # $cond_num: " .$validate_cond;
		}
	}

	// validate actions
	$actions = isset($_POST['actions']) && is_array($_POST['actions']) ? $_POST['actions'] : array();

	foreach($actions as $k => $a) {
		$act_class = $a['action'];
		$act_num = $k+1;
		if(!class_exists($act_class)) {
			$errors[] = "Action # $act_num is not valid anymore. Please remove action or refresh the page to load available actions.";
			continue;
		}

		$act = new $act_class;
		$config = isset($a['config']) ? $a['config'] : array();
		$validate_act = $act->validate_entry($config);

		if(!empty($validate_act)) {
			$errors[] = "Action # $act_num: " . $validate_act;
		}
	}

	if(count($errors) > 0) {
		echo json_encode(array(
				'success' => false,
				'errors' => $errors
			));
	} else {
		// save here
		$save_id = 1;
		$trigger = $_POST['trigger'];
		$title = $_POST['title'];
		$config = array(
				'actions' 		=> $actions,
				'conditions' 	=> $conditions
			);

		$action_simple = array();

		foreach($actions as $action) {
			$action_simple[] = $action['action'];
		}

		if($_POST['id'] == 'new') {
			$save_id = iw_update_automation_recipe($title, $trigger, $config, $action_simple);
		} else {
			$save_id = iw_update_automation_recipe($title, $trigger, $config, $action_simple, (int) $_POST['id']);
		}

		if(isset($_POST['disable_stats']) && $_POST['disable_stats'] == 'true') {
			update_post_meta( $save_id, 'disable_stats', 1 );
		} else {
			update_post_meta( $save_id, 'disable_stats', 0 );
		}

		$back_href = admin_url('admin.php?page=infusedwoo-menu-2&submenu=automation_recipes');
		set_transient('iwar_edit_notice_success', 'Successfully Saved Recipe #' . $save_id . '. <a href="'.$back_href.'">Click Here</a> to go back to Recipe List', 60);

		echo json_encode(array(
				'success' => true,
				'save_id' => $save_id
			));

	}

	exit();

}

function iwar_get_recipe_settings($id = 0) {
	$httpreq = false;

	if(isset($_GET['id']) && $id = 0) {
		$id = $_GET['id'];
		$httpreq = true;
	}

	if($id == 0) return false;

	$recipe_post = get_post($id);
	$return = array(
			'title' 	=> $recipe_post->post_title,
			'trigger'	=> get_post_meta( $id, 'iw_trigger_class', true ),
			'config'	=> get_post_meta( $id, 'iw_recipe_config', true )
		);

	if($httpreq) {
		echo json_encode($return);
		exit();
	} else {	
		return $return;
	}
}

function iwar_delete_recipe() {
	if(is_admin() && isset($_GET['delete_recipe'])) {
		set_transient('iwar_main_notice_success', 'Successfully Deleted Recipe #' . $_GET['delete_recipe'], 60*60);
		$delete_recipe = $_GET['delete_recipe'];
		wp_delete_post( $delete_recipe, true );
		header("Location: " . admin_url("admin.php?page=infusedwoo-menu-2&submenu=automation_recipes&deleted_recipe"));
		exit();
	}
}

function iwar_clear_stats_recipe() {
	if(is_admin() && isset($_GET['clear_stats_recipe'])) {
		set_transient('iwar_main_notice_success', 'Successfully Cleared Stats for Recipe #' . $_GET['clear_stats_recipe'], 60*60);
		$delete_stats = $_GET['clear_stats_recipe'];
		
		global $wpdb;
		$wpdb->delete($wpdb->comments, array('comment_post_ID' => $delete_stats), array( '%d' ));

		header("Location: " . admin_url("admin.php?page=infusedwoo-menu-2&submenu=automation_recipes&deleted_recipe"));
		exit();
	}
}

function iwar_deactivate_recipe() {
	if(is_admin() && isset($_GET['deactivate_recipe'])) {
		set_transient('iwar_main_notice_success', 'Successfully Deactivated Recipe #' . $_GET['deactivate_recipe'], 60*60);
		$recipe_id = $_GET['deactivate_recipe'];
		$upd_post = array(
		      'ID'           => $recipe_id,
		      'post_status'   => 'iw-disabled',
		  );
		wp_update_post( $upd_post );
		header("Location: " . admin_url("admin.php?page=infusedwoo-menu-2&submenu=automation_recipes"));
		exit();
	}
}

function iwar_activate_recipe() {
	if(is_admin() && isset($_GET['activate_recipe'])) {
		set_transient('iwar_main_notice_success', 'Successfully Activated Recipe #' . $_GET['activate_recipe'], 60*60);
		$recipe_id = $_GET['activate_recipe'];
		$upd_post = array(
		      'ID'           => $recipe_id,
		      'post_status'   => 'iw-enabled',
		  );
		wp_update_post( $upd_post );
		header("Location: " . admin_url("admin.php?page=infusedwoo-menu-2&submenu=automation_recipes"));
		exit();
	}
}

function iwar_get_available_merge_fields() {
	if(is_admin() && isset($_GET['trigger'])) {
		$trigger = new $_GET['trigger'];
		$merger = new IW_Automation_MergeFields($trigger, array());
		echo json_encode($merger->get_merge_fields());
		exit();
	}
}

function iwar_get_stats() {
	$recipe_id = $_GET['recipe_id'];
	$date_range = $_GET['date_range'];

	if($date_range == 'today') {
		$start = strtotime(date("Y-m-d"));
		$end = strtotime(date("Y-m-d",time()+24*3600)) - 1;
	} else if($date_range == 'week') {
		$start = strtotime("sunday last week");
		$end = strtotime("sunday this week") - 1;
	} else if($date_range == 'month') {
		$start = strtotime(date("Y-m-1"));
		$end = strtotime("next month", $start) - 1;
	} else {
		$start = strtotime($_GET['custom_start']);
		$end = strtotime($_GET['custom_end']);

		$start = strtotime(date("Y-m-d", $start));
		$end =  strtotime(date("Y-m-d", $end + 24*3600)) - 1;
	}

	$x_data = array();
	$y_data = array();
	$meta_data = array();
	if(($end - $start) < 86401) {
		for($i = $start; $i < $end; $i += 3600) {
			$comments_query = new WP_Comment_Query;
			$args = array(
					'post_id' => $recipe_id,
					'date_query' => array(
						'after' => date("Y-m-d H:i:s", $i),
						'before' => date("Y-m-d H:i:s", $i+3600)
					),
					'count' => 1
				);
			if($date_range == 'today') {
				$x_data[] = date("gA", $i);
			} else {
				$x_data[] = date("M-j gA", $i);
			}
			$y_data[] = (int) $comments_query->query( $args );
			$meta_data[] = array($i, $i+3600);
			
		}
	} else if(($end-$start) < 24*3600*31) {
		for($i = $start; $i < $end; $i += 24*3600) {
			$comments_query = new WP_Comment_Query;
			$args = array(
					'post_id' => $recipe_id,
					'date_query' => array(
						'after' => date("Y-m-d H:i:s", $i),
						'before' => date("Y-m-d H:i:s", $i+24*3600)
					),
					'count' => 1
				);

			if(date("Y") == date("Y", $i)) {
				$x_data[] = date("M-j", $i);
			} else {
				$x_data[] = date("Y-m-d", $i);
			}
			
			$y_data[] = (int) $comments_query->query( $args );
			$meta_data[] = array($i, $i+3600);
			
		}
	} else {
		$continue = true;
		$i = strtotime(date("Y-m-1", $start));
		do {
			$from = $i;
			$to = strtotime('next month', $i);

			$comments_query = new WP_Comment_Query;
			$args = array(
					'post_id' => $recipe_id,
					'date_query' => array(
						'after' => date("Y-m-d H:i:s",$from),
						'before' => date("Y-m-d H:i:s",$to)
					),
					'count' => 1
				);

			if(date("Y") == date("Y", $i)) {
				$x_data[] = date("M", $i);
			} else {
				$x_data[] = date("Y-M", $i);
			}
			
			$y_data[] = (int) $comments_query->query( $args );
			$meta_data[] = array($from, $to);
			$i = strtotime('next month', $i);

			if($i >= $end) {
				$continue = false;
			}
		} while($continue);		
	}

	echo json_encode(array(
		'recipe_id' => $recipe_id,
		'x' => $x_data, 
		'y' => $y_data, 
		'meta' => $meta_data, 
		'start' => $start, 
		'end' => $end
	));
	exit();
}

function iwar_manually_run_actions() {
	if(is_admin()) {
		$recipe_id = $_POST['recipe_id'];
		$email = $_POST['email'];

		if(!empty($recipe_id) && !empty($email)) {
			$current_user = wp_get_current_user();
			$trigger = get_post_meta( $recipe_id, 'iw_trigger_class', true);

			$manual = new $trigger;
			$manual->merger = new IW_Automation_MergeFields($manual, array());
			$manual->user_email = $email;
			$manual->log_stats($recipe_id, 'Manual by ' . $current_user->user_login);
			$manual->run_action($recipe_id);

			echo "ok";
		}

		exit();
	}
}

function iwar_clone() {
	if(isset($_GET['recipe']) && !empty($_GET['recipe'])) {
		$recipe_id = (int) $_GET['recipe'];
		global $wpdb;
		$post = get_post( $recipe_id );

	 
		if (isset( $post ) && $post != null) {
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'iw-disabled',
				'post_title'     => 'Clone of ' . $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
	 
			$new_post_id = wp_insert_post( $args );

			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$recipe_id");
			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}

			update_post_meta( $new_post_id, 'iwar_last_triggered', 0 );
	 		set_transient('iwar_main_notice_success', 'Successfully Cloned Recipe', 60*60);
			wp_redirect( admin_url( 'admin.php?page=infusedwoo-menu-2&submenu=automation_recipes&cloned=' . $new_post_id ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=infusedwoo-menu-2&submenu=automation_recipes') );
			exit;
		}
	}
}

function iwar_export_to_file() {
	if(isset($_GET['recipe']) && !empty($_GET['recipe'])) {
		$recipe_id = (int) $_GET['recipe'];
		global $wpdb;
		$post = get_post( $recipe_id );

	 
		if (isset( $post ) && $post != null) {
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"" . "iwar_recipe_$recipe_id.conf" . "\""); 

			$to_file = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'iw-disabled',
				'post_title'     => 'Clone of ' . $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);

			$to_file['metas'] = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$recipe_id");
			$config_txt = serialize($to_file);
			$upload_dir = wp_upload_dir();

			echo $config_txt;
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=infusedwoo-menu-2&submenu=automation_recipes') );
			exit;
		}
	}
}

function iw_generate_dynamic_timer() {
	include (INFUSEDWOO_3_DIR . '/automation-recipes/GIFEncoder.class.php');
	$time = $_GET['time'];
	$future_date = new DateTime(date('r',strtotime($time)));
	$time_now = time();
	$now = new DateTime(date('r', $time_now));
	$frames = array();
	$delays = array();
	$image = imagecreatefrompng(INFUSEDWOO_PRO_DIR . 'images/countdown.png');

	$delay = 100; // milliseconds
	$font = array(
		'size'=>40,
		'angle'=>0,
		'x-offset'=>10,
		'y-offset'=>70,
		'file'=>INFUSEDWOO_PRO_DIR . 'assets/DIGITALDREAM.ttf',
		'color'=>imagecolorallocate($image, 255, 255, 255),
	);
	for($i = 0; $i <= 60; $i++){
		$interval = date_diff($future_date, $now);
		if($future_date < $now){
			// Open the first source image and add the text.
			$image = imagecreatefrompng(INFUSEDWOO_PRO_DIR . 'images/countdown.png');
			$text = $interval->format('00:00:00:00');
			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
	                $loops = 1;
			ob_end_clean();
			break;
		} else {
			// Open the first source image and add the text.
			$image = imagecreatefrompng(INFUSEDWOO_PRO_DIR . 'images/countdown.png');;
			$text = $interval->format('%a:%H:%I:%S');
			// %a is weird in that it doesn’t give you a two digit number
			// check if it starts with a single digit 0-9
			// and prepend a 0 if it does
			if(preg_match('/^[0-9]\:/', $text)){
				$text = '0'.$text;
			}
			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
	                $loops = 0;
			ob_end_clean();
		}
		$now->modify('+1 second');
	}
	//expire this image instantly
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' ); 
	$gif = new AnimatedGif($frames,$delays,$loops);
	$gif->display();
}


// helper shortcodes

function iw_inf_contact_field_val($args=array(), $content="") {
	global $iwpro;
	$arr = $_POST;

	if(!is_array($_POST) || count($_POST) == 0) {
		$arr = $_GET;
	}

	$skip_ver = false;

	if(empty($args['field'])) return '';
	else $field = $args['field'];

	if(!$iwpro->ia_app_connect()) return 'Cannot connect to Infusionsoft.';
	$force_id = isset($args['force_id']) ? $args['force_id'] : "";
	if(isset($iwpro->force_id)) $force_id = $iwpro->force_id;

	$contact_id = 0;
	
	if(empty($force_id)) {
		$email_source = array('Email','inf_field_Email','email','ContactOEmail','e-mail','E-mail','emailaddress','EmailAddress');
		$cid_source	  = array('Id','id','contactId','ContactId','cid','CID','inf_field_Id');
		$oid_source = array('orderId','order','orderid','order_id','oid');

		foreach($email_source as $param) {
			if(isset($arr[$param]) && !empty($arr[$param])) {
				$contact_email = urldecode($arr[$param]);
				break;
			}
		}

		foreach($cid_source as $param) {
			if(isset($arr[$param]) && !empty($arr[$param])) {
				$contact_id = (int) $arr[$param];
				break;
			}
		}

		foreach($oid_source as $param) {
			if(isset($arr[$param]) && !empty($arr[$param])) {
				$order_id = (int) $arr[$param];
				break;
			}
		}

		if(empty($contact_email) || empty($contact_id)) {
			// check if logged-in
			if (!empty($order_id) && !empty($contact_email)) { 
				$o = $iwpro->app->dsLoad('Job', $order_id, array('ContactId'));
				$contact_id = isset($o['ContactId']) ? $o['ContactId'] : 0;

				if($contact_id == 0) {
					return 0;
				} else {
					$skip_ver = true;
				}
			} else if(is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$contact_email = $current_user->user_email;
			} else {
				return '';
			}
		}

	} else {
		$contact_id = do_shortcode( $force_id );
		$skip_ver = true;
	}

	$iwpro->force_id = $contact_id;

	if(empty($contact_id)) {
		$contact = $iwpro->app->dsFind('Contact',1,0,'Email',$contact_email, array('Id'));
		if(isset($contact[0]['Id'])) $contact_id = $contact[0]['Id'];
		else return '';
	}

	if(!$skip_ver) {
		$contact = $iwpro->app->dsLoad('Contact', $contact_id, array('Email'));
		if(isset($contact['Email'])) {
			if(strtoupper($contact['Email']) != strtoupper($contact_email)) {
				return '';
			}
		} else return '';
	}

	if(!in_array($field, array('AffCode','AffId'))) {
		$contact_fields = $iwpro->app->dsLoad('Contact',$contact_id, array($field));
		if(isset($contact_fields[$field])) return $contact_fields[$field];
		else return '';
	} else {
		$contact_fields = $iwpro->app->dsFind('Affiliate',1,0,'ContactId', $contact_id, array('AffCode','ContactId'));
		if($field == 'AffCode') return isset($contact_fields[0]['AffCode']) ? $contact_fields[0]['AffCode'] : '';
		else if($field == 'AffId') return isset($contact_fields[0]['Id']) ? $contact_fields[0]['Id'] : '';
		else return '';
	}
}

function iw_get_val($args=array(), $content="") {
	if(empty($args['field'])) return '';
	else {
		$field = $args['field'];
		return isset($_GET[$field]) ? $_GET[$field] : '';
	}
}

function iw_post_val($args=array(), $content="") {
	if(empty($args['field'])) return '';
	else {
		$field = $args['field'];
		return isset($_POST[$field]) ? $_POST[$field] : '';
	}
}

function iw_merge_shortcode($attr, $content='') {
	// Merge Capability
	$manual = new IW_PageVisit_Trigger;
	$manual->merger = new IW_Automation_MergeFields($manual, array());
	

	if(isset($attr['user_email']) && !empty($attr['user_email'])) {
		$manual->user_email = do_shortcode($attr['user_email']);
	} else {
		$current_user = wp_get_current_user();
		$manual->user_email = isset($current_user->user_email) ? $current_user->user_email : '';
	}

	return $manual->merger->merge_text($content);
}


// include triggers:
foreach (glob(INFUSEDWOO_3_DIR . "automation-recipes/triggers/*.php") as $filename) include $filename;

// include conditions
foreach (glob(INFUSEDWOO_3_DIR . "automation-recipes/conditions/*.php") as $filename) include $filename;

// include actions
foreach (glob(INFUSEDWOO_3_DIR . "automation-recipes/actions/*.php") as $filename) include $filename;


add_action( 'wp_ajax_iwar_get_available_conditions', 'iwar_get_available_conditions', 10, 0 );
add_action( 'wp_ajax_iwar_get_available_actions', 'iwar_get_available_actions', 10, 0 );
add_action( 'wp_ajax_iwar_save_recipe', 'iwar_save_recipe', 10, 0 );
add_action( 'wp_ajax_iwar_get_recipe_settings', 'iwar_get_recipe_settings', 10, 0 );
add_action( 'wp_ajax_iwar_get_stats', 'iwar_get_stats', 10, 0 );
add_action( 'wp_ajax_iwar_manually_run_actions', 'iwar_manually_run_actions', 10, 0 );
add_action( 'wp_ajax_iwar_get_available_merge_fields','iwar_get_available_merge_fields');
add_action( 'wp_ajax_iwar_clone', 'iwar_clone', 10, 0 );
add_action( 'wp_ajax_iwar_export_to_file', 'iwar_export_to_file', 10, 0 );

add_action(	'admin_init', 'iwar_delete_recipe');
add_action(	'admin_init', 'iwar_clear_stats_recipe');
add_action(	'admin_init', 'iwar_deactivate_recipe');
add_action(	'admin_init', 'iwar_activate_recipe');

// add shortcodes:
add_shortcode( 'iw_inf_contact_field_val', 'iw_inf_contact_field_val' );
add_shortcode( 'iw_get_val', 'iw_get_val' );
add_shortcode( 'iw_post_val', 'iw_post_val' );
