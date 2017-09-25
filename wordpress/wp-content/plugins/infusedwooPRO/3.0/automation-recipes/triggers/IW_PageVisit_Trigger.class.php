<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_PageVisit_Trigger extends IW_Automation_Trigger {
	function trigger_when() {
		add_action('template_redirect', array($this,'trigger'), 9);
		add_action('template_redirect', array($this, 'pg_script_handler'),8,0);
		add_action('wp_footer', array($this,'page_script_triggers'));
		add_action('wp_head', array($this,'page_script_support'));
		add_action('iwar_process_action', array($this,'check_for_dup_actions'),10,3);
	}

	public function get_desc() {
		return 'when customer visits a wordpress page / post';
	}


	function get_title() {
		return 'Page Visit Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-eye"></i>';
	}

	function get_contact_email() {
		if(is_user_logged_in()) {
			$user = wp_get_current_user();
			$email = get_user_meta($user->ID, 'billing_email', true);
			if(empty($email)) $email = $user->user_email;

			return $email;
		} else {
			return WC()->session->get('session_email');
		}
	}

	function get_log_details() {
		global $post;
		if(isset($post->ID)) {
			return "Visited Page # " . $post->ID;
		}
	}

	function page_script_support() {
		$page_testers = apply_filters('iw_page_triggers', array());	
		if(count($page_testers) > 0) {
			wp_enqueue_style( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.css" );
			wp_enqueue_script( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.min.js", array('jquery'));
		}
	}

	function page_script_triggers() {
		global $post;
		global $product;
		if(is_admin()) { return false; }


		$first_site_visit = WC()->session->get('iw_first_site_visit');
		if(empty($first_site_visit)) {
			$first_site_visit = time();
			WC()->session->set('iw_first_site_visit', $first_site_visit);
		}

		if(isset($post->ID) || isset($product->ID) && !is_home()) {
			$pg_id = isset($post->ID) ? $post->ID : $product->ID;
			$first_pg_visit = WC()->session->get('iw_first_pg_visit_' . $pg_id);
			if(empty($first_pg_visit)) {
				$first_pg_visit = time();
				WC()->session->set("iw_first_pg_visit_{$pg_id}" , $first_pg_visit);
			}
		} else {
			$first_pg_visit = '0';
		}

		$page_testers = apply_filters('iw_page_triggers', array());	
		$iwar_live_ran = apply_filters('iwar_live_ran', array());

		// add more accurate time:
		foreach($page_testers as $k => $tester) {
			if(isset($tester['trigger_type']) && $tester['trigger_type'] == 'site_visit') {
				$page_testers[$k]['trigger_on'] = ($first_site_visit + $tester['wait_time']) - time();
			} else if(isset($tester['trigger_type']) && $tester['trigger_type'] == 'page_visit') {
				$page_testers[$k]['trigger_on'] = ($first_pg_visit + $tester['wait_time']) - time();
			}
		}

		if(count($page_testers) > 0 && !did_action('iw_page_triggers')) {
			wp_enqueue_style( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.css" );
			wp_enqueue_script( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.min.js", array('jquery'));
			?>
			<div class="iwar_footer_control"></div>
			<script>
			var iw_first_site_visit = <?php echo $first_site_visit; ?>;
			var iw_first_page_visit = <?php echo $first_pg_visit; ?>;
			var iw_page_testers = <?php echo json_encode($page_testers); ?>;

			for(var i = 0; i < iw_page_testers.length; i++) {
				var checkthis = iw_page_testers[i];
				if(checkthis.trigger_on && checkthis.trigger_on > 0) {
					setTimeout(function() {
						iwar_pg_trig(checkthis.trigger_type)
					}, checkthis.trigger_on*1000);
				} else if(checkthis.trigger_type == 'leavepage') {
					jQuery(document).mouseleave(function() {
						if(typeof(iwar_leave_trig) === 'undefined' || !iwar_leave_trig) {
							iwar_leave_trig = true;
						    iwar_pg_trig('leavepage');
						}
					});
				}
			}

			function iwar_pg_trig(trig) {
				if(typeof(iwar_live_ran) === 'undefined') iwar_live_ran = <?php echo json_encode($iwar_live_ran); ?>;

				jQuery.getJSON('<?php echo "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>', {iwar_hold: 1, trig: trig}, function(data){
					if(data.length) {
						for(var j = 0; j < data.length; j++) {
							if(data[j].to_eval) {
								for(var k = 0; k < iwar_live_ran.length; k++) {
									if(data[j].id == iwar_live_ran[k]) {
										continue;
									}
								}

								iwar_live_ran.push(data[j].id);
								eval(data[j].eval);
							}
						}
					}
				});
			}

			</script>
			<?php
		}
	}

	function pg_script_handler() {
		if(isset($_GET['iwar_hold']) && $_GET['iwar_hold'] == 1) {
			$this->trigger();
			$actions = apply_filters('iw_page_live_actions', array());	
			echo json_encode($actions);
			exit();
		}
	}

	function check_for_dup_actions($proceed, $action, $trigger) {
		$allow_dup = array('IW_AddCartFee_Action','IW_AutoApplyCoupon_Action','IW_FreeShipping_Action','IW_ProductDiscount_Action','IW_Redirect_Action','IW_SiteNotice_Action','IW_ShowPopup_Action');
		if(in_array(get_class($action), $allow_dup)) return true;


		if(get_class($trigger) == get_class($this)) {
			if(!is_user_logged_in()) {
				global $woocommerce;
				$woocommerce->session->set_customer_session_cookie(true);
			}

			$triggered_actions = WC()->session->get("iw_triggered_actions");
			$action_id = $action->recipe_id_proc . '-' . $action->sequence_id;

			if(!is_array($triggered_actions)) $triggered_actions = array();

			if(in_array($action_id, $triggered_actions)) {
				return false;
			} else {
				if(!in_array(get_class($action), $allow_dup) && !(isset($_GET['iwar_hold']) && get_class($action) == 'IW_ShowPopup_Action')) {
					$triggered_actions[$action_id] = $action_id;
					WC()->session->set("iw_triggered_actions", $triggered_actions);
				}				

				return true;
			}

		} else {
			return true;
		}
	}
}

iw_add_trigger_class('IW_PageVisit_Trigger');