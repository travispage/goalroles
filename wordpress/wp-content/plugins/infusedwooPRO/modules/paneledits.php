<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('woocommerce_product_data_panels', 'ia_woocommerce_options');
add_action('woocommerce_product_write_panel_tabs', 'ia_woocommerce_tab'); 
add_action( 'admin_enqueue_scripts', 'ia_searchable' );
add_action('save_post','ia_woocommerce_process_product', 10, 2 );


add_action('wp_ajax_iw_infusion_products', 'iw_infusion_products');
add_action('wp_ajax_iw_infusion_tags', 'iw_infusion_tags');
add_action('wp_ajax_iw_infusion_emails', 'iw_infusion_emails');
add_action('wp_ajax_iw_infusion_actions', 'iw_infusion_actions');
add_action('wp_ajax_iw_infusion_subs', 'iw_infusion_subs');

function ia_woocommerce_options() {
	global $post;
	global $iwpro;

	$allowsubs 		= false;			
	$pgenabled 		= isset($iwpro->settings['pgenabled']) ? $iwpro->settings['pgenabled'] : ""; 			
	if($pgenabled == 'yes') $allowsubs	= true;
	
	$iwpro->ia_app_connect();		
	$tid = get_post_meta($post->ID, 'infusionsoft_tag', 	true);
	$eid = get_post_meta($post->ID, 'infusionsoft_email', 	true);
	$aid = get_post_meta($post->ID, 'infusionsoft_action', 	true);
	$pid = (int) get_post_meta($post->ID, 'infusionsoft_product', 	true);
	$sid = (int) get_post_meta($post->ID, 'infusionsoft_sub', 	true);			
	$trial = (int) get_post_meta($post->ID, 'infusionsoft_trial', 	true);	
	$sign_up_fee = (float) get_post_meta($post->ID, 'infusionsoft_sign_up_fee', true);	

	$sub_incl_disc = get_post_meta($post->ID, 'infusionsoft_sub_incl_disc', 	true);	
	$sub_incl_ship = get_post_meta($post->ID, 'infusionsoft_sub_incl_ship', 	true);	
					
	?>
	<div id="infusionsoft_tab" class="panel woocommerce_options_panel">
	<div class="options_group" style="margin-bottom: 20px;">
		<input type="hidden" name="iw_product_panel" value="1" />
		<div class="ia-product">
	<?php				
	
		$ifstype 	= get_post_meta($post->ID, 'infusionsoft_type', true);									
		if($allowsubs) {					
			$type_select =  array(
				'id' 			=> 'infusionsoft_type',
				'class'			=> 'iw-select2',
				'value' 		=> $ifstype,
				'label' 		=> __('Product or Subscription?','woothemes'),
				'desc_tip' 		=> __('Select if you are selling a product or a subscription.','woothemes'),
				'options'		=> array('Product' => 'Product', 'Subscription' => 'Subscription')
				);

			woocommerce_wp_select( $type_select );											


			// SUBSCRIPTION SELECT
			if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
				woocommerce_wp_select(array(
					'class' 				=> 'iw-select2 iw-select-full',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_subs',
						'data-type' => 'Subscription',
						'autocomplete' => 'off'
						),
					'id'					=> 'infusionsoft_sub',
					'value' 				=> $sid, 
					'label' 				=> __('Select Subscription','woothemes'),
					'options'				=> array($sid => 'Subscription # ' . $sid)
				));
			} else {
				woocommerce_wp_text_input(array(
					'class' 				=> 'iw-select2 iw-select-full',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_subs',
						'data-type' => 'Subscription',
						'autocomplete' => 'off'
						),
					'id'					=> 'infusionsoft_sub',
					'value' 				=> $sid, 
					'label' 				=> __('Select Subscription','woothemes')
				));
			}
			

			
			$trial_input = array(
				'id' 			=> 'infusionsoft_trial',
				'value' 		=> $trial,
				'label' 		=> __('Number of Trial Days','woothemes'),
				'desc_tip' 		=> __('Number of days until infusionsoft starts charging','woothemes'),	
			);

			$sign_up_input = array(
				'id' 			=> 'infusionsoft_sign_up_fee',
				'value' 		=> $sign_up_fee,
				'label' 		=> __('Sign Up Fee' ,'woothemes') . ' (' . get_woocommerce_currency_symbol() . ')',
				'desc_tip' 		=> __('Sign Up Fee for trying the subscription.','woothemes'),	
				'data_type'		=> 'Price'
			);


			woocommerce_wp_text_input($trial_input);
			woocommerce_wp_text_input($sign_up_input);

			

			echo "<div class=\"calc-rec\"><hr><h3 style='margin-top: 15px; padding-left: 10px;'>Calculation of Recurring Amount</h3>";

			
			woocommerce_wp_checkbox(array(
					'id' 			=> 'infusionsoft_sub_incl_disc',
					'value' 		=> $sub_incl_disc != "no" ? "yes" : "no",
					'label'			=> __('Include Discounts' ,'woothemes'),
					'description' 	=> __('Check to Include discounts in the Recurring Price (if present)' ,'woothemes'),
				));

			woocommerce_wp_checkbox(array(
					'id' 			=> 'infusionsoft_sub_incl_ship',
					'value' 		=> $sub_incl_ship != "no" ? "yes" : "no",
					'label'			=> __('Include Shipping' ,'woothemes'),
					'description' 	=> __('Check to Include shipping fee in the Recurring Price (if shipping is present)' ,'woothemes'),
				));

			echo "<h3 style='margin-top: 15px; padding-left: 10px;'>Other Settings</h3>";

			$iw_override_price_html = get_post_meta($post->ID, 'iw_override_price_html', true);	

			woocommerce_wp_text_input(array(
					'id' 			=> 'iw_override_price_html',
					'value' 		=> $iw_override_price_html,
					'label' 		=> __('Override Price Title','woothemes'),
					'placeholder' 	=> __('e.g. 3 Payments of $30.00','woothemes')
				));

			echo "</div>";

		}	
		
		echo '</div>';


		// PRODUCT SELECT
	 	if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
	 		$pre_opt = array();

	 		//$pre_opt = array(0 => 'Match Infusionsoft Product using SKU Value');
	 		if($pid > 0) $pre_opt[$pid] = 'Product # ' . $pid;

			woocommerce_wp_select(array(
					'class' 				=> 'iw-select2 iw-select-full',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_products',
						'data-type' => 'Product',
						'autocomplete' => 'off',
						'placeholder'  => 'Leave this empty to search IS Product using SKU setting'
						),
					'id'					=> 'infusionsoft_product',
					'value' 				=> $pid, 
					'label' 				=> __('Product being sold','woothemes'),
					'desc_tip' 				=> __('This will appear in the customer\'s invoice','woothemes'),
					'options'				=> $pre_opt
				));
		} else { 
			woocommerce_wp_text_input(array(
					'class' 				=> 'iw-select2 iw-select-full',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_products',
						'data-type' => 'Product',
						'autocomplete' => 'off',
						),
					'placeholder'			=> 'Leave this empty to search IS Product using SKU setting',
					'id'					=> 'infusionsoft_product',
					'value' 				=> $pid, 
					'label' 				=> __('Product being sold','woothemes'),
					'desc_tip' 				=> __('This will appear in the customer\'s invoice','woothemes') 
				));
		}
		echo '</div><div class="options_group" style="margin-bottom: 20px;">';

		// TAG SELECT
		if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
			$pre_opt = array();
			if(isset($tid) && is_array($tid)) {
				foreach($tid as $k) {
					$pre_opt[$k] = "Tag # " . $k;
				}
			}

			infusedwoo_wp_select_multiple(array(
				'class' 				=> 'iw-select2',
				'custom_attributes' 	=> array(
					'data-src' => 'iw_infusion_tags',
					'data-type' => 'Tag',
					'autocomplete' => 'off',
					'placeholder' => 'Select Tag(s)'
					),
				'id'					=> 'infusionsoft_tag[]',
				'value' 				=> $tid, 
				'label' 				=> __('Tags to apply upon successful purchase','woothemes'),
				'options'				=> $pre_opt
			));
		} else {
			woocommerce_wp_text_input(array(
					'class' 				=> 'iw-select2',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_tags',
						'data-type' => 'Tag',
						'autocomplete' => 'off',
						'multiple'		=> 1
						),
					'id'					=> 'infusionsoft_tag',
					'value' 				=> is_array($tid) ? implode(",",$tid) : $tid, 
					'label' 				=> __('Tags to apply upon successful purchase', 'woothemes')			
				));
		}

		

		// EMAIL SELECT
		if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
			$pre_opt = array();
			if(isset($eid) && is_array($eid)) foreach($eid as $k) {
				$pre_opt[$k] = "Email Template # " . $k;
			}

			infusedwoo_wp_select_multiple(array(
				'class' 				=> 'iw-select2',
				'custom_attributes' 	=> array(
					'data-src' => 'iw_infusion_emails',
					'data-type' => 'Email Template',
					'autocomplete' => 'off',
					'placeholder' => 'Select Email Template(s)',
					'multiple'		=> "multiple"
					),
				'id'					=> 'infusionsoft_email[]',
				'value' 				=> $eid, 
				'label' 				=> __('Email Templates to Send upon successful purchase','woothemes'),
				'options'				=> $pre_opt
			));
		} else {
			woocommerce_wp_text_input(array(
					'class' 				=> 'iw-select2',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_emails',
						'data-type' => 'Email Template',
						'autocomplete' => 'off',
						'multiple'		=> 1
						),
					'id'					=> 'infusionsoft_email',
					'value' 				=> is_array($eid) ? implode(",",$eid) : $eid, 
					'label' 				=> __('Email Templates to Send upon successful purchase', 'woothemes')			
				));
		}


		// ACTION SELECT
		if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
			$pre_opt = array();
			if(isset($aid) && is_array($aid)) foreach($aid as $k) {
				$pre_opt[$k] = "Action Set # " . $k;
			}

			infusedwoo_wp_select_multiple(array(
				'class' 				=> 'iw-select2',
				'custom_attributes' 	=> array(
					'data-src' => 'iw_infusion_actions',
					'data-type' => 'Action Set',
					'autocomplete' => 'off',
					'placeholder' => 'Select Action Set(s)'
					),
				'id'					=> 'infusionsoft_action[]',
				'value' 				=> $aid, 
				'label' 				=> __('Action Sets to Run upon successful purchase','woothemes'),
				'options'				=> $pre_opt
			));
		} else {
			woocommerce_wp_text_input(array(
					'class' 				=> 'iw-select2',
					'custom_attributes' 	=> array(
						'data-src' => 'iw_infusion_actions',
						'data-type' => 'Action Set',
						'autocomplete' => 'off',
						'multiple'		=> 1
						),
					'id'					=> 'infusionsoft_action',
					'value' 				=> is_array($aid) ? implode(",",$aid) : $aid, 
					'label' 				=> __('Action Sets to Run upon successful purchase', 'woothemes')			
				));
		}

		
	?>
	</div>
	<div class="options_group ia-woosubs" style="margin-bottom: 20px;">
		<?php
			 
			if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
		 		$wid = get_post_meta($post->ID, 'infusionsoft_sub_activated', 	true);
		 		$pre_opt = array();
		 		if($wid > 0) $pre_opt[$pid] = 'Action Set # ' . $wid;

				woocommerce_wp_select(array(
						'class' 				=> 'iw-select2 iw-select-full',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off',
							'placeholder'  => 'Select Action Set'
							),
						'id'					=> 'infusionsoft_sub_activated',
						'value' 				=> $wid, 
						'label' 				=> __('Action to run when Subscription is <b>Activated</b>','woothemes'),
						'options'				=> $pre_opt
					));
			} else { 
				woocommerce_wp_text_input(array(
						'class' 				=> 'iw-select2',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off'
							),
						'id'					=> 'infusionsoft_sub_activated',
						'value' 				=> get_post_meta($post->ID, 'infusionsoft_sub_activated', 	true), 
						'label' 				=> __('Action to run when Subscription is <b>Activated</b>', 'woothemes')			
					));
			}

			if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
		 		$wid = get_post_meta($post->ID, 'infusionsoft_sub_cancelled', 	true);
		 		$pre_opt = array();
		 		if($wid > 0) $pre_opt[$pid] = 'Action Set # ' . $wid;

				woocommerce_wp_select(array(
						'class' 				=> 'iw-select2 iw-select-full',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off',
							'placeholder'  => 'Select Action Set'
							),
						'id'					=> 'infusionsoft_sub_cancelled',
						'value' 				=> $wid, 
						'label' 				=> __('Action to run when Subscription is <b>Cancelled</b>','woothemes'),
						'options'				=> $pre_opt
					));
			} else {
				woocommerce_wp_text_input(array(
						'class' 				=> 'iw-select2',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off'
							),
						'id'					=> 'infusionsoft_sub_cancelled',
						'value' 				=> get_post_meta($post->ID, 'infusionsoft_sub_cancelled', 	true),
						'label' 				=> __('Action to run when Subscription is <b>Cancelled</b>','woothemes'),			
					));
			}

			if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
		 		$wid = get_post_meta($post->ID, 'infusionsoft_sub_on-hold', 	true);
		 		$pre_opt = array();
		 		if($wid > 0) $pre_opt[$pid] = 'Action Set # ' . $wid;

				woocommerce_wp_select(array(
						'class' 				=> 'iw-select2 iw-select-full',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off',
							'placeholder'  => 'Select Action Set'
							),
						'id'					=> 'infusionsoft_sub_on-hold',
						'value' 				=> $wid, 
						'label' 				=> __('Action to run when Subscription is <b>Set to On-Hold</b>','woothemes'),
						'options'				=> $pre_opt
					));
			} else {
				woocommerce_wp_text_input(array(
						'class' 				=> 'iw-select2',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off'
							),
						'id'					=> 'infusionsoft_sub_on-hold',
						'value' 				=> get_post_meta($post->ID, 'infusionsoft_sub_on-hold', 	true), 
						'label' 				=> __('Action to run when Subscription is <b>Set to On-Hold</b>','woothemes'),		
					));
			}

			if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
		 		$wid = get_post_meta($post->ID, 'infusionsoft_sub_expired', 	true);
		 		$pre_opt = array();
		 		if($wid > 0) $pre_opt[$pid] = 'Action Set # ' . $wid;

				woocommerce_wp_select(array(
						'class' 				=> 'iw-select2 iw-select-full',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off',
							'placeholder'  => 'Select Action Set'
							),
						'id'					=> 'infusionsoft_sub_expired',
						'value' 				=> $wid, 
						'label' 				=> __('Action to run when Subscription <b>Expires</b>','woothemes'),
						'options'				=> $pre_opt
					));
			} else {
				woocommerce_wp_text_input(array(
						'class' 				=> 'iw-select2',
						'custom_attributes' 	=> array(
							'data-src' => 'iw_infusion_actions',
							'data-type' => 'Action Set',
							'autocomplete' => 'off'
							),
						'id'					=> 'infusionsoft_sub_expired',
						'value' 				=> get_post_meta($post->ID, 'infusionsoft_sub_expired', 	true), 
						'label' 				=> __('Action to run when Subscription <b>Expires</b>','woothemes')	
					));
			}

		?>

	</div>
	</div>						
	<?php if($allowsubs) { ?>			
		<script>							
		jQuery('.infusionsoft_sub_field').hide();
		
		var ifstype = jQuery('select#infusionsoft_type').val();	
		if(ifstype == 'Subscription') {	
			jQuery('.infusionsoft_sub_field').show();
			jQuery('.infusionsoft_trial_field').show();
			jQuery('.infusionsoft_product_field').hide();
			jQuery('.calc-rec').show();	

			if(parseInt(jQuery('#infusionsoft_trial').val()) > 0) {
				jQuery('.infusionsoft_sign_up_fee_field').show();	
			}
		} else {
			jQuery('.infusionsoft_sub_field').hide();
			jQuery('.infusionsoft_trial_field').hide();
			jQuery('.infusionsoft_product_field').show();
			jQuery('.infusionsoft_sign_up_fee_field').hide();

			jQuery('.calc-rec').hide();	
		}								
		
		jQuery('select#infusionsoft_type').change(function() {
			var ifstype = jQuery('select#infusionsoft_type').val();
			if(ifstype == 'Subscription') {						
				jQuery('.infusionsoft_sub_field').show();
				jQuery('.infusionsoft_trial_field').show();
				jQuery('.infusionsoft_product_field').hide();	
				jQuery('.calc-rec').show();		
				if(parseInt(jQuery('#infusionsoft_trial').val()) > 0) {
					jQuery('.infusionsoft_sign_up_fee_field').show();	
				}
			
			} else {
				jQuery('.infusionsoft_sub_field').hide();
				jQuery('.infusionsoft_trial_field').hide();
				jQuery('.infusionsoft_product_field').show();
				jQuery('.infusionsoft_sign_up_fee_field').hide();
				jQuery('.calc-rec').hide();	
			}				
		});

		jQuery('#infusionsoft_trial').keyup(function() {
				if(parseInt(jQuery(this).val()) > 0) {
					jQuery('.infusionsoft_sign_up_fee_field').show();
				} else {
					jQuery('.infusionsoft_sign_up_fee_field').hide();
				}
			});
		</script>
	<?php	} ?>
	<?php if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) { ?>
		<script type="text/javascript" src="<?php echo INFUSEDWOO_PRO_URL . "admin-menu/assets/paneledits3.js"; ?>"></script>
	<?php } else { ?>
		<script type="text/javascript" src="<?php echo INFUSEDWOO_PRO_URL . "admin-menu/assets/paneledits.js"; ?>"></script>
	<?php } ?>
	<script>
		jQuery('[name=product-type]').change( function() {
		var product_type = jQuery(this).val();

		if(product_type == 'subscription' || product_type == 'variable-subscription') {
			jQuery('.ia-product').hide();
			jQuery('.ia-woosubs').show();
		} else {
			jQuery('.ia-product').show();
			jQuery('.ia-woosubs').hide();
		}
	});
	</script>
	<?php
}

function ia_woocommerce_tab() {
	?>

	<style type="text/css">
		#woocommerce-coupon-data ul.wc-tabs li.linked_infusionsoft a::before, #woocommerce-product-data ul.wc-tabs li.linked_infusionsoft a::before, .woocommerce ul.wc-tabs li.linked_infusionsoft a::before {
		  content: "";
		}

	</style>
	<li class="custom_tab linked_infusionsoft"><a href="#infusionsoft_tab">
		<img   style="width: 10px;margin-left: 0px;margin-right: 8px;" src="<?php echo INFUSEDWOO_PRO_URL . 'images/infusionsoft-icon.png' ?>" /> <?php _e('Infusionsoft', 'woothemes'); ?></a></li>
	<?php
}		

function ia_searchable($hook) {			
	global $woocommerce;

	$showto = array(
			"woocommerce_page_woocommerce_settings",
			"post.php",
			"post-new.php",
			"product_page_ia_custom_fields"
		);

	if(!in_array($hook, $showto)) return;

	$type = isset($_GET['post']) ? get_post_type( $_GET['post'] ) : false;
	if(!$type) $type = isset($_GET['post_type']) ? $_GET['post_type'] : "";

	if($type == 'product') {
		wp_enqueue_style( 'ia_admin_styles', (INFUSEDWOO_PRO_URL . 'assets/custom.css') ); 
		wp_enqueue_script( 'ia_searchable', (INFUSEDWOO_PRO_URL . 'assets/chosen_v1.3.0/chosen.jquery.min.js'), array('jquery') ); 
	    wp_enqueue_script( 'ia_admin_scripts', (INFUSEDWOO_PRO_URL . 'assets/admin_scripts.js'), array('ia_searchable') ); 
	    wp_enqueue_style( 'ia_searchable_styles', (INFUSEDWOO_PRO_URL . 'assets/chosen_v1.3.0/chosen.min.css') );
  	    wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
	}

	if('product_page_ia_custom_fields' != $hook ) return;
	wp_enqueue_script( 'ia_custfield_scripts', (INFUSEDWOO_PRO_URL . 'assets/admin_custfields.js'), array('jquery')  ); 			
}		

function ia_woocommerce_process_product( $post_id, $post = null  ) {
	global $woocommerce; 
	global $iwpro;

	if ( $post->post_type == "product" ) {
		if(isset($_POST['infusionsoft_type']) && $_POST['infusionsoft_type'] == 'Subscription') {
			if($_POST['product-type'] == 'variable') wp_die(__('Sorry but subscriptions don\'t work with variable products for now..','woothemes'));		
			if(empty($_POST['infusionsoft_sub'])) wp_die(__('You need to select a subscription plan. Click back button.','woothemes'));		
			
			if($iwpro->ia_app_connect()) {		
				$sid = (int) $_POST['infusionsoft_sub'];				
				$returnFields = array('DefaultPrice');
				$sub = $iwpro->app->dsLoad('CProgram',$sid,$returnFields);

				$sub_price = $iwpro->ia_get_sub_price($sid, $sub['DefaultPrice']);	
				if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
					$this_prod = new WC_Product($post_id);
					$this_prod->set_regular_price( $sub_price );
					$this_prod->save();
				} else {
					update_post_meta( $post_id, '_regular_price', $sub_price);
				}	
				
			}
		}


		if(isset($_POST['iw_product_panel'])) {
			if(empty($_POST['infusionsoft_tag'])) 
				$tags = array();
			else 
				$tags = is_array($_POST['infusionsoft_tag']) ? $_POST['infusionsoft_tag'] : explode(",", $_POST['infusionsoft_tag']);
			
			update_post_meta( $post_id, 'infusionsoft_tag', $tags);
		}

		if(isset($_POST['iw_product_panel'])) {
			if(empty($_POST['infusionsoft_email'])) 
				$emails = array();
			else 
				$emails = is_array($_POST['infusionsoft_email']) ? $_POST['infusionsoft_email'] : explode(",", $_POST['infusionsoft_email']);

			update_post_meta( $post_id, 'infusionsoft_email', 	$emails);
		} 

		if(isset($_POST['iw_product_panel'])) {
			if(empty($_POST['infusionsoft_action']))
				$actions = array();
			else 
				$actions = is_array($_POST['infusionsoft_action']) ? $_POST['infusionsoft_action'] : explode(",", $_POST['infusionsoft_action']);

			update_post_meta( $post_id, 'infusionsoft_action', 	$actions);
		}
		
		if(isset($_POST['iw_product_panel'])) {
		 	update_post_meta( $post_id, 'infusionsoft_product', $_POST['infusionsoft_product']);								
			update_post_meta( $post_id, 'infusionsoft_sub', $_POST['infusionsoft_sub']);								
			update_post_meta( $post_id, 'infusionsoft_type', $_POST['infusionsoft_type']);
			update_post_meta( $post_id, 'infusionsoft_trial', $_POST['infusionsoft_trial']);
			update_post_meta( $post_id, 'infusionsoft_sign_up_fee', $_POST['infusionsoft_sign_up_fee']);
		}

		if(isset($_POST['iw_override_price_html'])) update_post_meta( $post_id, 'iw_override_price_html', $_POST['iw_override_price_html']);
		
		if(isset($_POST['infusionsoft_sub_incl_disc'])) {
			if(empty($_POST['infusionsoft_sub_incl_disc']))
				update_post_meta( $post_id, 'infusionsoft_sub_incl_disc', "no");
			else
				update_post_meta( $post_id, 'infusionsoft_sub_incl_disc', $_POST['infusionsoft_sub_incl_disc']);
		}
		

		if(isset($_POST['infusionsoft_sub_incl_ship'])) {
			if(empty($_POST['infusionsoft_sub_incl_ship'])) 
				update_post_meta( $post_id, 'infusionsoft_sub_incl_ship', "no");
			else
				update_post_meta( $post_id, 'infusionsoft_sub_incl_ship', $_POST['infusionsoft_sub_incl_ship']);
		}

		if(isset($_POST['infusionsoft_sub_activated'])) update_post_meta( $post_id, 'infusionsoft_sub_activated', 	$_POST['infusionsoft_sub_activated']);
		if(isset($_POST['infusionsoft_sub_cancelled'])) update_post_meta( $post_id, 'infusionsoft_sub_cancelled', 	$_POST['infusionsoft_sub_cancelled']);
		if(isset($_POST['infusionsoft_sub_on-hold'])) update_post_meta( $post_id, 'infusionsoft_sub_on-hold', 	$_POST['infusionsoft_sub_on-hold']);
		if(isset($_POST['infusionsoft_sub_expired'])) update_post_meta( $post_id, 'infusionsoft_sub_expired', 	$_POST['infusionsoft_sub_expired']);

	}
}

function infusedwoo_wp_select_multiple( $field ) {
    global $thepostid, $post, $woocommerce;

     // Custom attribute handling
	  $custom_attributes = array();

	  if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

	    foreach ( $field['custom_attributes'] as $attribute => $value ){
	      $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
	    }
	  }


    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['value']         = isset( $field['value'] ) ? $field['value'] : ( get_post_meta( $thepostid, $field['id'], true ) ? get_post_meta( $thepostid, $field['id'], true ) : array() );

    $field['value'] = is_array($field['value']) ? $field['value'] : explode(",", $field['value']);

    echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '"';

    if(isset($field['custom_attributes'])) foreach($field['custom_attributes'] as $k => $v) {
    	echo ' ' . "$k=" . '"' . esc_attr($v) . '"';
    }

    echo ' multiple="multiple" >';
    foreach ( $field['options'] as $key => $value ) {

        echo '<option value="' . esc_attr( $key ) . '" ' . (( (is_array($field['value']) && in_array( $key, $field['value'])) || (!is_array($field['value']) && $key == $field['value'])) ? 'selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';

    }

    echo '</select> ';

    if ( ! empty( $field['description'] ) ) {

        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
        }

    }
    echo '</p>';
}



function iw_infusion_products() {
	global $iwpro;
	$iwpro->ia_app_connect();

	if(isset($_GET['q'])) $term = '%' . $_GET['q'] . '%';
	else $term = '%';

	if(isset($_GET['page'])) {
		$page = $_GET['page'] - 1;
		$total = 30;
	} else {
		$page = 0;
		$total = 1000;
	}

	$data = array();

	if(!isset($_GET['id'])) {
		$products = $iwpro->app->dsFind('Product', $total, $page, 'ProductName', $term, array('Id','ProductName'));
		$data['total_items'] = $iwpro->app->dsCount('Product', array('ProductName' => $term));
	} else {
		$product = $iwpro->app->dsLoad('Product',$_GET['id'], array('Id','ProductName'));

		if(isset($product['Id'])) {
			$products = array($product);
			$data['total_items'] = 1;
		}
	}

	$items = array();
	foreach($products as $product) {
		$items[] = array('id' => $product['Id'], 'text' => $product['ProductName'] . " (# {$product['Id']})");
	}

	$data['items'] = $items;

	echo json_encode($data);
	exit();
}

function iw_infusion_tags() {
	global $iwpro;
	$iwpro->ia_app_connect();

	if(isset($_GET['q'])) $term = '%' . $_GET['q'] . '%';
	else $term = '%';

	if(isset($_GET['page'])) {
		$page = $_GET['page'] - 1;
		$total = 30;
	} else {
		$page = 0;
		$total = 1000;
	}

	$data = array();

	if(!isset($_GET['id'])) {
		$grps = $iwpro->app->dsFind('ContactGroup', $total, $page, 'GroupName', $term, array('Id','GroupName'));
		$data['total_items'] = $iwpro->app->dsCount('ContactGroup', array('GroupName' => $term));
	} else {
		if(strpos($_GET['id'], ',') !== false) {
			$ids = explode(",", $_GET['id']);

			$grps = array();
			foreach($ids as $id) {
				$grp = $iwpro->app->dsLoad('ContactGroup',$id, array('Id','GroupName'));
				$grps[] = $grp;
			}
		} else {
			$grp = $iwpro->app->dsLoad('ContactGroup',$_GET['id'], array('Id','GroupName'));

			if(isset($grp['Id'])) {
				$grps = array($grp);
				$data['total_items'] = 1;
			}
		}
	}

	$items = array();
	foreach($grps as $grp) {
		$items[] = array('id' => $grp['Id'], 'text' => $grp['GroupName'] . " (# {$grp['Id']})");
	}

	$data['items'] = $items;

	echo json_encode($data);
	exit();
}

function iw_infusion_emails() {
	global $iwpro;
	$iwpro->ia_app_connect();

	if(isset($_GET['q'])) $term = '%' . $_GET['q'] . '%';
	else $term = '%';

	if(isset($_GET['page'])) {
		$page = $_GET['page'] - 1;
		$total = 30;
	} else {
		$page = 0;
		$total = 1000;
	}

	$data = array();

	if(!isset($_GET['id'])) {
		$fetch = $iwpro->app->dsQuery('Template', $total, $page, array('PieceTitle' => $term, 'PieceType' => 'Email'), array('Id','PieceTitle'));
		$data['total_items'] = $iwpro->app->dsCount('Template', array('PieceTitle' => $term, 'PieceType' => 'Email'));
	} else {
		if(strpos($_GET['id'], ',') !== false) {
			$ids = explode(",", $_GET['id']);

			$fetch = array();
			foreach($ids as $id) {
				$ft = $iwpro->app->dsLoad('Template',$id, array('Id','PieceTitle'));
				$fetch[] = $ft;
			}
		} else {
			$ft = $iwpro->app->dsLoad('Template',$_GET['id'], array('Id','PieceTitle'));

			if(isset($ft['Id'])) {
				$fetch = array($ft);
				$data['total_items'] = 1;
			}
		}
	}

	$items = array();
	foreach($fetch as $f) {
		$items[] = array('id' => $f['Id'], 'text' => $f['PieceTitle'] . " (# {$f['Id']})");
	}

	$data['items'] = $items;

	echo json_encode($data);
	exit();
}


function iw_infusion_actions() {
	global $iwpro;
	$iwpro->ia_app_connect();

	if(isset($_GET['q'])) $term = '%' . $_GET['q'] . '%';
	else $term = '%';

	if(isset($_GET['page'])) {
		$page = $_GET['page'] - 1;
		$total = 30;
	} else {
		$page = 0;
		$total = 1000;
	}

	$data = array();

	if(!isset($_GET['id'])) {
		$fetch = $iwpro->app->dsQuery('ActionSequence', $total, $page, array('TemplateName' => $term), array('Id','TemplateName'));
		$data['total_items'] = $iwpro->app->dsCount('ActionSequence', array('TemplateName' => $term));
	} else {
		if(strpos($_GET['id'], ',') !== false) {
			$ids = explode(",", $_GET['id']);

			$fetch = array();
			foreach($ids as $id) {
				$ft = $iwpro->app->dsLoad('ActionSequence',$id, array('Id','TemplateName'));
				$fetch[] = $ft;
			}
		} else {
			$ft = $iwpro->app->dsLoad('ActionSequence',$_GET['id'], array('Id','TemplateName'));

			if(isset($ft['Id'])) {
				$fetch = array($ft);
				$data['total_items'] = 1;
			}
		}
	}

	$items = array();
	foreach($fetch as $f) {
		$items[] = array('id' => $f['Id'], 'text' => $f['TemplateName'] . " (# {$f['Id']})");
	}

	$data['items'] = $items;

	echo json_encode($data);
	exit();
}


function iw_infusion_subs() {
	global $iwpro;
	$iwpro->ia_app_connect();

	if(isset($_GET['q'])) $term = '%' . $_GET['q'] . '%';
	else $term = '%';
	$returnFields = array('Id','ProgramName','DefaultPrice','DefaultCycle','DefaultFrequency');

	if(isset($_GET['page'])) {
		$page = $_GET['page'] - 1;
		$total = 30;
	} else {
		$page = 0;
		$total = 1000;
	}



	$data = array();

	if(!isset($_GET['id'])) {
		$fetch = $iwpro->app->dsQuery('CProgram', $total, $page, array('ProgramName' => $term), $returnFields);
		$data['total_items'] = $iwpro->app->dsCount('CProgram', array('ProgramName' => $term));
	} else {
		if(strpos($_GET['id'], ',') !== false) {
			$ids = explode(",", $_GET['id']);

			$fetch = array();
			foreach($ids as $id) {
				$ft = $iwpro->app->dsLoad('CProgram',$id, $returnFields);
				$fetch[] = $ft;
			}
		} else {
			$ft = $iwpro->app->dsLoad('CProgram',$_GET['id'], $returnFields);

			if(isset($ft['Id'])) {
				$fetch = array($ft);
				$data['total_items'] = 1;
			}
		}
	}

	$items = array();
	foreach($fetch as $sub) {
		$value = $sub['Id'];										
		switch($sub['DefaultCycle']) {						
			case 1: $stringCycle = 'year'; break;						
			case 2: $stringCycle = 'month'; break;						
			case 3: $stringCycle = 'week'; break;						
			case 6: $stringCycle = 'day'; break;					
		}		
			
		$addS = '';					
		if($sub['DefaultFrequency'] > 1) $addS = 's';	

		$nsub = $iwpro->app->dsLoad('SubscriptionPlan', $sub['Id'], array('NumberOfCycles'));
		$ncycles = isset($nsub['NumberOfCycles']) ? $nsub['NumberOfCycles'] : 0;

				
		$sub_price = $iwpro->ia_get_sub_price($value, $sub['DefaultPrice']);
		$text = "{$sub['ProgramName']} (" .'$' ." {$sub_price} every {$sub['DefaultFrequency']} {$stringCycle}{$addS}";

		if($ncycles > 0) {
			$naddS = $ncycles > 1 ? 's' : '';
			$text .= " for $ncycles {$stringCycle}{$naddS}";
		} 

		$text .= ") [ {$sub['Id']} ]";	
		$items[] = array('id' => $sub['Id'], 'text' => $text . " (# {$sub['Id']})");
	}

	$data['items'] = $items;

	echo json_encode($data);
	exit();
}








