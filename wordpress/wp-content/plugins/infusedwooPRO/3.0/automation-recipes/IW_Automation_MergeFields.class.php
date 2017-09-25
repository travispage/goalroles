<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Automation_MergeFields {
	function __construct($trigger, $config=array()) {
		$this->trigger = $trigger;
		$this->config = $config;
		$this->load_handlers();
		$this->trigger_type = get_class($this->trigger);
	}

	function load_handlers() {
		$this->handlers = array(
				'InfContact' => array('Infusionsoft Contact Fields','inf_contact_handler'),
				'WPUser'	=> array('Wordpress User Fields', 'wp_user_handler'),
				'Woocommerce' => array('Woocommerce','woo_handler'),
				'WPUserMeta' => array('WP User Meta Field', 'wp_usermeta_handler'),
				'FilterHook' => array('Custom Filter Hook (Developers Only)', 'custom_filter_hook_handler'),
				'Utility' => array('Utility / Other Merge Fields', 'utility_handler'),
				'WCOrder' => array('Woocommerce Order Data','wc_order_handler'),
				'CheckoutFields' => array('Checkout Custom Fields (InfusedWoo)','ccf_handler'),
				'WCOrderMeta' => array('Woocommerce Order Meta','wc_ordermeta_handler'),
				'WCOrderAttribute' => array('Woocommerce Order Variation Attribute','wc_ordervar_handler'),
				'IWSession'		=> array('Session Value', 'iw_session_handler')
			);

		
		$this->handlers['GET'] = array('URL Passed Value (GET value)','get_handler');

		if(isset($this->trigger->merge_handlers) && is_array($this->trigger->merge_handlers)) {
			$this->handlers = array_merge($this->trigger->merge_handlers, $this->handlers);
		}

	}

	function modifiers() {
		return array(
			'md5' => 'md5',
			'sha1' => 'sha1',
			'curr' => 'wc_price'
		);
	}

	function get_merge_fields() {
		global $iwpro; 

		if(method_exists($this->trigger, 'merge_fields')) {
			$merge_fields = $this->trigger->merge_fields();
			if(!is_array($merge_fields)) $merge_fields = array();
		} else {
			$merge_fields = array();
		}

		$merge_fields['WPUser'] = array(
				'ID' => 'User Id',
				'first_name' => 'First Name',
				'last_name' => 'Last Name',
				'user_email' => 'Email',
				'user_login' => 'Username',
				'pass_reset_link' => 'Password Reset Link',
				'display_name' => 'Display Name',
				'LastOrderId' => 'Woocommerce Last Order ID',
				'LastPendingOrder' => 'Woocommerce Last Pending Order ID',
				'LastProcessingOrder' => 'Woocommerce Last Processing Order ID',
				'LastCompletedOrder' => 'Woocommerce Last Completed Order ID',
				'custom_meta_value' => 'Custom Meta Value',
				'billing_first_name' => 'Billing First Name',
                'billing_last_name'  => 'Billing Last Name',
                'billing_company'    => 'Billing Company',
                'billing_address_1'  => 'Billing Address 1',
                'billing_address_2'  => 'Billing Address 2',
                'billing_city'       => 'Billing City',
                'billing_state'      => 'Billing State',
                'billing_postcode'   => 'Billing PostCode',
                'billing_country'    => 'Billing Country',
                'billing_email'      => 'Billing Email',
                'billing_phone'      => 'Billing Phone',
                'shipping_first_name' => 'Shipping First Name',
                'shipping_last_name'  => 'Shipping Last Name',
                'shipping_company'    => 'Shipping Company',
                'shipping_address_1'  => 'Shipping Address 1',
                'shipping_address_2'  => 'Shipping Address 2',
                'shipping_city'       => 'Shipping City',
                'shipping_state'      => 'Shipping State',
                'shipping_postcode'   => 'Shipping PostCode',
                'shipping_country'    => 'Shipping Country',
                'total_value'		 => 'Total Customer Value (All Woocommerce Order Total)',
                'generate_random_string' => 'Generate Password',
                'last_generated_string' => 'Last Generated Password',
                'role'				=> 'User Role'

 			);

		$merge_fields['InfContact'] = array(
				"Id" => "Contact ID",
				"FirstName" => "First Name",
				"MiddleName" => "Middle Name",
				"LastName" => "Last Name",
				"Nickname" => "Nickname",
				"Suffix" => "Suffix",
				"Phone1" => "Phone",
				"Company" => "Company",
				"CompanyID" => "CompanyID",
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
				"Anniversary" => "Anniversary",
				"Birthday" => "Birthday",
				"AssistantName" => "Assistant Name",
				"AssistantPhone" => "Assistant Phone",
				"Email"	=>	'Email Address',
				"EmailAddress2" => "Email Address 2",
				"EmailAddress3" => "Email Address 3",
				"Phone2" => "Phone 2",
				"Phone3" => "Phone 3",
				"Fax1" => "Fax",
				"Fax2" => "Fax 2",
				"JobTitle" => "Job Title",
				"SpouseName" => "Spouse Name",
				"Title" => "Title",
				"Website" => "Website",
				"LastOrderId" => "Most Recent Order ID",
				"LastPaidOrderId" => "Most Recent Paid Order ID",
				"LastUnpaidOrderId" => "Most Recent Unpaid Order ID",
				"Language" => 'Language',
				"TimeZone" => 'TimeZone',
				"Facebook" => "Facebook",
				"Twitter"  => "Twitter",
				"LinkedIn" => 'LinkedIn',
				"AllOrderTotal" => "Customer Value (All Paid Order Total)"

			);

		$merge_fields['WPUserMeta'] = array();
		$merge_fields['IWSession'] = array();

		$merge_fields['Woocommerce'] = array(
				"Website" => "Website",
				"SessionLeadsource" => "Session Leadsource",
				"GeoCountry" => "Country Code (via geolocation)",
				"SessionAffId" => "Lead Referral Partner Id",
				"LastCouponCode" => "Last Generated Coupon Code",
				"SavedCartURI" => "Saved Cart Retrieve URL",
				"SavedCartwPrice" => "Saved Cart with Price",
				"SavedCart" => "Saved Cart without Price",
				"PSavedCartwPrice" => "Saved Cart with Price (Plain Text)",
				"PSavedCart" => "Saved Cart without Price (Plain Text)",
				"SessEmail" => "Session Saved Email Address" 
			);

		$merge_fields['Utility'] = array(
				"current_datetime" => "Current Datetime (Y-m-d H:i:s)",
				"current_date" => "Current Date (Y-m-d)",
				"current_timestamp" => "Current Timestamp",
				"N_days_datetime" => 'Datetime N days from current time. (Y-m-d H:i:s)',
				"N_days_date" => 'Date N days from current time. (Y-m-d)',
				'N_days_timestamp' => 'Timestamp N days from current time.',
				'timer_gif' => 'Timer GIF Image',
				'timer_gif_small' => 'Timer GIF Image Small',
				'timer_gif_large' => 'Timer GIF Image Large',
				'wpml_lang'		  => 'WPML Language',
				'wpml_lang_code'  => 'WPML Language Code'
			);

		if(in_array($this->trigger_type, array('IW_HttpPost_Trigger','IW_PageVisit_Trigger','IW_UserAction_Trigger'))) {
			$merge_fields['GET'] = array();
		}

		if(in_array($this->trigger_type, array('IW_OrderCreation_Trigger','IW_OrderStatusChange_Trigger','IW_Purchase_Trigger'))) {
			$merge_fields['WCOrder'] = array(
					'OrderId' => 'Order Id',
					'InfOrderId' => 'Infusionsoft Order Id',
					'InfInvoiceId' => 'Infusionsoft Invoice Id',
					'OrderNumber' => 'Order Number',
					'OrderDate' => 'Order Date',
					'OrderTimestamp' => 'Order Timestamp',
					'OrderStatus' => 'Order Status',
					'CouponUsed' => 'Coupon Codes Used (comma-separated if multiple)',
					'billing_first_name' => 'Billing First Name',
	                'billing_last_name'  => 'Billing Last Name',
	                'billing_company'    => 'Billing Company',
	                'billing_address_1'  => 'Billing Address 1',
	                'billing_address_2'  => 'Billing Address 2',
	                'billing_city'       => 'Billing City',
	                'billing_state'      => 'Billing State',
	                'billing_postcode'   => 'Billing PostCode',
	                'billing_country'    => 'Billing Country',
	                'billing_email'      => 'Billing Email',
	                'billing_phone'      => 'Billing Phone',
	                'shipping_first_name' => 'Shipping First Name',
	                'shipping_last_name'  => 'Shipping Last Name',
	                'shipping_company'    => 'Shipping Company',
	                'shipping_address_1'  => 'Shipping Address 1',
	                'shipping_address_2'  => 'Shipping Address 2',
	                'shipping_city'       => 'Shipping City',
	                'shipping_state'      => 'Shipping State',
	                'shipping_postcode'   => 'Shipping PostCode',
	                'shipping_country'    => 'Shipping Country',
	                'order_total'		  => 'Order Total',
	                'order_tax'			  => 'Order Tax Total',
	                'order_shipping'	  => 'Order Shipping Free Total',
	                'payment_method_title'	  => 'Payment Method',
	                'order_currency'	  => 'Order Currency',
	                'customer_ip_address' => 'Customer IP',
	                'customer_note'		  => 'Customer Note',
	                'cart_discount'		  => 'Cart Discount',
	                'OrderKey'			  => 'Order Key',
	                'ProductNames'		  => 'Product Names',
	                'ProductNamesQ'		  => 'Product Names with Quantity',
	                'OrderItemTable'	  => 'Order Items Table',
	                'InfJob_JobTitle'	  => 'Infusionsoft Order Title',
	                'InfJob_DateCreated'  => 'Infusionsoft Order Date Created',
	                'InfJob_DueDate'	  => 'Infusionsoft Order Due Date',
	                'InfJob_JobNotes'     => 'Infusionsoft Order Job Notes',
	                'InfJob_LastUpdated'  => 'Infusionsoft Order Last Updated',
	                'InfJob_OrderType'    => 'Infusionsoft Order Type',
	                'Inf_AffiliateId'     => 'Infusionsoft Referral Partner ID',
	                'Inf_AffiliateEmail'  => 'Infusionsoft Referral Partner Email',


				);

			$merge_fields['WCOrderMeta'] = array();
			$attribs = $this->get_all_attribs();
			if(count($attribs) > 0) {
				$merge_fields['WCOrderAttribute'] = $attribs;
			}

			$iw_cf_fields = get_option('iw_cf_fields');
			if(is_array($iw_cf_fields)) {
				$merge_fields['CheckoutFields'] = array();
				foreach($iw_cf_fields as $field) {
					$k = iw_slugify($field['name']) . $field['grpid'];
					$v = $field['name'];
					$merge_fields['CheckoutFields'][$k] = $v;
				}
			}
		}
		

		$merge_fields['Woocommerce']['wishlist_items_html'] = 'Wishlist Items (Table with images)';
		$merge_fields['Woocommerce']['wishlist_items_list'] = 'Wishlist Items (List)';
		

		if($iwpro->ia_app_connect()) {
			$custfields = $iwpro->app->dsFind("DataFormField", 200,0, "FormId", -1, array("Name","Label","DataType"));
			if(is_array($custfields) && count($custfields) > 0) {
				foreach($custfields as $custfield) {
					$merge_fields['InfContact']["_" . $custfield["Name"]] = "Custom Field: " . $custfield["Label"];
				}
			}

			$ordfields = $iwpro->app->dsFind("DataFormField", 200,0, "FormId", -9, array("Name","Label","DataType"));
			if(is_array($ordfields) && count($ordfields) > 0) {
				foreach($ordfields as $ordfield) {
					$merge_fields['WCOrder']["InfJob__" . $ordfield["Name"]] = "Custom Field (Infusionsoft): " . $ordfield["Label"];
				}
			}
		}




		$trigger = $this->trigger;
		if(isset($trigger->merge_doesnt_support)) {
			foreach($trigger->merge_doesnt_support as $grp) {
				unset($merge_fields[$grp]);
			}
		} else if(isset($trigger->merge_only_support)) {
			foreach($merge_fields as $grp => $arr) {
				if(!in_array($grp, $trigger->merge_only_support)) {
					unset($merge_fields[$grp]);
				}
			}
		}

		$merge_fields['FilterHook'] = array();
		
		$merge_support = array();
		$grp_support = array_keys($merge_fields);

		foreach($this->handlers as $grp => $info) {
			if(in_array($grp, $grp_support)) {
				$merge_support[$grp] = $info;
				$merge_support[$grp]['keys'] = $merge_fields[$grp]; 
			}
		}

		return $merge_support;
	}

	public function early_fetch() {
		$config_string = print_r($this->config, true);
		$check_fields = $this->get_bits($config_string);

		$inf_fields = array();

		foreach($check_fields as $check) {
			if($check['group'] == 'InfContact') {
				$inf_fields[] = $check['key'];
			}
		}

		if(count($inf_fields) > 0) {
			global $iwpro;
			if($iwpro->ia_app_connect()) {
				$cid = (int) $this->trigger->search_infusion_contact_id();
				if($cid > 0) {
					$contact = $iwpro->app->dsLoad('Contact',$cid,$inf_fields);

					if(is_array($contact)) foreach($contact as $k => $v) {
						$this->save_to_cache('InfContact', $k, $v);
					}
				}
			}
		}
	}

	public function merge_handler($group, $key, $fallback = "") {
		$groups = array_keys($this->handlers);
		$modifiers = $this->modifiers();

		$post_fcns = array();
		foreach($modifiers as $mod_key => $mod_fcn) {
			$pos = strpos($key, "_$mod_key");
			if($pos !== false) {
				$key = str_replace("_$mod_key", "", $key);
				$post_fcns[$pos] = $mod_fcn;
			}
		}
		if(count($post_fcns) > 0) ksort($post_fcns);

		$val = apply_filters( 'infusedwoo_merge_value', "", $group, $key, $fallback);

		if(!empty($val)) {
			return $val;
		}

		if(isset($this->cache[$group][$key])) {
			$val = $this->cache[$group][$key];
		} else if(isset($this->trigger->merge_handlers) && in_array($group, array_keys($this->trigger->merge_handlers))) {
			$handler = $this->trigger->merge_handlers[$group][1];
			if(method_exists($this->trigger, $handler)) {
				$val = $this->trigger->$handler($key);
			} else $val = '';
		} else if(in_array($group, $groups)) {
			$handler = $this->handlers[$group][1];
			if(method_exists($this, $handler)) {
				$val = $this->$handler($key);
			} else $val = '';
		} 

		if(empty($val)) {
			$this->save_to_cache($group, $key, $val);
			return $this->merge_text($fallback);
		} else {
			$this->save_to_cache($group, $key, $val);

			if(count($post_fcns) > 0) {
				foreach($post_fcns as $fcn) {
					if(is_array($fcn)) {
						$obj = $fcn[0];
						$fn = $fcn[1];
						$val = $obj->$fn($val);
					} else {
						$val = $fcn($val);
					}
				}
			}

			return $val;
		}
	}

	public function save_to_cache($group, $key, $value) {
		if(!isset($this->cache)) $this->cache = array();
		if(!isset($this->cache[$group])) $this->cache[$group] = array();

		$this->cache[$group][$key] = $value;
	}

	public function merge_text($text) {
		$bits = $this->get_bits($text);

		foreach($bits as $bit) {
			$merged_val = $this->merge_handler($bit['group'], $bit['key'], $bit['fallback']);
			$text = str_replace($bit['replace'], $merged_val, $text);
		}

		return $text;
	}

	function get_bits($text) {
		$text_split = explode("{{", $text);
		$bits = array();
		$replace = "";
		$new_bit = true;
		$close_count = 0;


		foreach($text_split as $n => $split) {
			if($n == 0) continue;
			$close_count++;
			
			if(strpos($split, '}}') !== false) {
				$sub_bits = explode("}}", $split);
				unset($sub_bits[count($sub_bits)-1]);
				$close_count = $close_count - count($sub_bits);
			} else {
				$sub_bits = array($split);
			}
			
			if($new_bit) {
				if(strpos($sub_bits[0], '|') !== false) {
					$pair = explode("|", $sub_bits[0]);
					$group_key = trim($pair[0]);
					$has_fallback = true;
				} else {
					$group_key = trim($sub_bits[0]);
					$fallback = "";
					$has_fallback = false;
				}
			} 

			if($close_count <= 0) {
				$group_key_split = explode(":", $group_key);
				
				$replace .= "{{";
				foreach($sub_bits as $k => $sub_bit) {
					$replace .= $sub_bit . "}}";
				}

				if($has_fallback) {
					$first_occ = strpos($replace, "|")+1;
					$last_occ = strrpos($replace, "}}");

					$fallback = substr($replace, $first_occ, $last_occ-$first_occ);
				} else {
					$fallback = "";
				}

				$bits[] = array(
					'group' 	=> $group_key_split[0],
					'key' 		=> $group_key_split[1],
					'fallback' 	=> $fallback,
					'replace' 	=> $replace
					);

				$close_count = 0;
				$replace = "";
				$new_bit = true;
			} else {
				$new_bit = false;
				$replace .= "{{" . $split;
			}

		}

		return $bits;
	}

	function inf_contact_handler($key) {
		global $iwpro;

		if($iwpro->ia_app_connect()) {
			$cid = (int) $this->trigger->search_infusion_contact_id();

			if($cid > 0) {
				if(in_array($key, array('LastOrderId','LastPaidOrderId','LastUnpaidOrderId'))) {
					$search = array('ContactId' => $cid);
					if($key == 'LastPaidOrderId') $search['OrderStatus'] = 0;
					if($key == 'LastUnpaidOrderId') $search['OrderStatus'] = 1;

					$ids = $iwpro->app->dsQueryOrderBy('Job', 1, 0, $search, array('Id'), 'Id', false);
					if(isset($ids[0])) return $ids[0]['Id'];
					else return 0;
				} else if($key == 'AllOrderTotal') {
					$paid = $iwpro->app->dsFind('Invoice',1000,0,'ContactId',$cid,array('TotalPaid'));
					$total = 0;
					if(is_array($paid)) foreach($paid as $p) {
						$total += $p['TotalPaid'];
					}

					return $total;
				} else if(in_array($key, array('Facebook','Twitter','LinkedIn'))) {
					$info = $iwpro->app->dsQuery('SocialAccount', 1, 0, array('ContactId' => $cid, 'AccountType' => $key), array('AccountName'));
					if(isset($info[0]['AccountName'])) return $info[0]['AccountName'];
				} else {
					$info = $iwpro->app->dsLoad('Contact', $cid, array($key));

					if(is_array($info) && in_array($key, array_keys($info)))
						return (string) $info[$key];
				}
			}
		} 
	}

	function wp_user_handler($key) {
		$user_id = (int) $this->trigger->search_wp_user_id();
		
		//if($user_id > 0) {
			if(strpos($key, 'billing') !== false || strpos($key, 'shipping') !== false) {
				return (string) get_user_meta( $user_id, $key, true );
			} else if(in_array($key, array('LastOrderId','LastPendingOrder','LastCompletedOrder','LastProcessingOrder'))) {
				if($key == 'LastOrderId') {
					$post_status = array_keys( wc_get_order_statuses() );
				} else if($key == 'LastPendingOrder') {
					$post_status = array('wc-pending');
				} else if($key == 'LastCompletedOrder') {
					$post_status = array('wc-completed');
				} else {
					$post_status = array('wc-processing');
				}

				$customer_orders = get_posts(array(
				  'post_type' 		=>  wc_get_order_types(),
				  'post_status' 	=> $post_status,
				  'orderby'    		=> 'post_date',
				  'order'      		=> 'DESC',
				  'numberposts' 	=> 1,
				  'meta_key'    => '_customer_user',
        		  'meta_value'  => $user_id
				));

				if(isset($customer_orders[0]->ID)) return $customer_orders[0]->ID;
				else 0;
			} else if($key == 'generate_random_string') {
				$new_pass = wp_generate_password(8, false);
				$this->trigger->last_generated_string = $new_pass;
				return $new_pass;
			} else if($key == 'last_generated_string') {
				return isset($this->trigger->last_generated_string) ? $this->trigger->last_generated_string : '';
			} else if($key == 'pass_reset_link') {
				global $wpdb;
				$user_data = get_user_by( 'ID', $user_id );
				$user_login = $user_data->user_login;
				$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
				
				if(!empty($key) && function_exists('check_password_reset_key')) {
					$check = check_password_reset_key( $key, $user_login );
					$check = is_wp_error($check);
				}

			    if ( empty($key) || $check) {
			    	if(function_exists('get_password_reset_key')) {
			    		$user_login = $user_data->user_login;
						$user_email = $user_data->user_email;
						$key = get_password_reset_key( $user_data );
			    	} else {
				        $key = wp_generate_password(20, false);
				        do_action('retrieve_password_key', $user_login, $key);
				        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
			    	}
			    }

			    return network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
			} else if($key == 'total_value') {
				$args = array(
				    'meta_query' => array(
				        array(
				            'key' => '_billing_email',
				            'value' => $this->trigger->user_email
				        )
				    ),
				    'nopaging' => true,
				    'post_type' => 'shop_order',
				    'posts_per_page' => -1
				);
				$posts = get_posts($args);
				$total = 0;
				foreach($posts as $post) {
					$base_curr = get_post_meta($post->ID, '_order_total_base_currency',true );
					$curr_total = get_post_meta($post->ID, '_order_total',true );

					if($base_curr > 0) $total += $base_curr;
					else $total += $curr_total; 
				}

				return $total;
			} else if($key == 'role') {
				$user = get_user_by( 'ID', $user_id );
				if(isset($user->roles)) {
					return implode(",", $user->roles);
				}
			} else {
				$user = get_user_by( 'ID', $user_id );
				return $user->$key;
			}
		//}
	}

	function woo_handler($key) {
		if(!isset($this->trigger->wp_user_id)) {
			$user_id = $this->trigger->search_wp_user_id();
		} else {
			$user_id = $this->trigger->wp_user_id;
		}


		if(strpos($key, 'wishlist_items_') !== false) {
			 $args = array(
	            'post_type' => 'wishlist',
	            'orderby' => 'modified',
	            'order'	=> 'desc',
	            'nopaging' => true,
	            'meta_query' => array(
	                array(
	                    'key' => '_wishlist_email',
	                    'value' => $trigger->user_email,
	                    'compare' => 'LIKE'
	                )
	            )
	        );

	        $posts = get_posts($args);
	        $lists = array();
	        if ($posts) {
	            foreach ($posts as $post) {
	                $lists[] = new WC_Wishlists_Wishlist($post->ID);
	            }
	        }

	        if(count($lists) == 0) return '';

	        $plain 	= '';
	        $html 	= '<table>';
	        foreach($lists as $list) {
	        	$collection = new WC_Wishlists_Wishlist_Item_Collection($list->id);
				$items = $collection->get_items($list->id);

				if(count($items) > 0) {
					$wl = new WC_Wishlists_Wishlist($list->id);
					$wl_link = WC_Wishlists_Wishlist::get_the_url_view($list->id);
					$title = get_the_title( $list->id );


					$html .= '<thead><tr><td colspan="2">';
					$html .= '<a target="_blank" href="'.$wl_link.'"><div style="font-size: 14pt;">'.$title.'</div></a></td>';
					$html .= '</tr></thead><tbody>';
					$plain .= '<b>'.$title.'</b><ul>';
					foreach($items as $item) {
						$wc_product = $item['data'];
						$price = $item['wl_price'];

						$add_to_cart_url = add_query_arg(array('add-to-cart' => $wc_product->id), wc_get_cart_url()); ;
						$pid = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
						$excerpt = $wc_product->post->post_excerpt;
						$content = $wc_product->post->post_content;	
						$desc = !empty($excerpt) ? strip_tags($excerpt) : strip_tags($content);					
						$desc = strlen($desc) > 100 ? substr($desc,0,100)."..." : $desc;

						$html .= '<tr>';
						$html .= '<td><center>'.$wc_product->get_image().'</center></td>';
						$html .= '<td valign="top"><a target="_blank" href="'.$wc_product->get_permalink().'"><div style="font-size: 12pt; margin-bottom: 8px">';
						$html .= $wc_product->get_title().' x '.$item['quantity'].'</div></a>';
						$html .= '<b>'.__( 'Price', 'woocommerce' ).': ';
						$plain .= '<li>';
						$plain .= $wc_product->get_title().' x '.$item['quantity'] . "<br>";
						$plain .= __( 'Price', 'woocommerce' ).": ";

						if($wc_product->is_on_sale()) {
							$html .= '<s>'.wc_price($wc_product->regular_price).'</s>' . '&nbsp; <span style="color:red">' . wc_price($wc_product->price) ."<span>";
							$plain .= '<s>'.wc_price($wc_product->regular_price).'</s>' . '&nbsp; <span style="color:red">' . wc_price($wc_product->price) ."<span><br>";
						} else {
							$html .= wc_price($wc_product->price);
							$plain .= wc_price($wc_product->price) . "<br>";
						}
						$html .= '</b>';
						if(!empty($desc)) {
							$html .= '<br><br>';
							$html .= '<span style="font-size: 9pt; color: #777">'.$desc.'</span>';
						}

						$html .= '<br><br>';
						$html .= '<a target="_blank" href="'.$add_to_cart_url.'">'.__( 'Add to Cart', 'woocommerce' ).'</a>';
						$html .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
						$html .= '<a target="_blank" href="'.$wc_product->get_permalink().'">'.__( 'Learn More', 'woocommerce' ).'</div></a>';
						$html .= '</td></tr>';
						$plain .= '<a target="_blank" href="'.$add_to_cart_url.'">'.__( 'Add to Cart', 'woocommerce' ).'</a>';
						$plain .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
						$plain .= '<a target="_blank" href="'.$wc_product->get_permalink().'">'.__( 'Learn More', 'woocommerce' ).'</a></li>';

					}

					$html .= '</tbody>';
				}
	        }

	        $plain 	.= '</ul><br>';
	        $html 	.= '</table>';

	        if($key == 'wishlist_items_html') {
	       	 	return $html;
	       	} else {
	       		return $plain;
	       	}
		} else if($key == 'LastCouponCode') {
			return isset($this->trigger->last_generated_coupon) ? $this->trigger->last_generated_coupon : '';
		} else if($key == 'SessionLeadsource') {
			return (WC()->session) ? WC()->session->get('iw_leadsource') : 
			(isset($_COOKIE['ia_leadsource']) ? $_COOKIE['ia_leadsource'] : '');
		} else if($key == 'GeoCountry') {
			if(class_exists('WC_Geolocation')) {
				$geolocate = WC_Geolocation::geolocate_ip();
				return $geolocate['country'];
			}
		} else if($key == 'SessionAffId') {
			$is_aff = isset($_COOKIE['is_aff']) ? (int) $_COOKIE['is_aff'] : "";

			if(empty($is_aff)) {
				$is_aff = (WC()->session) ? WC()->session->get('iw_is_aff') : "";
			}

			$is_aff = apply_filters( 'iw_set_leadaffiliateid', $is_aff );

			return $is_aff;
		} else if($key == 'Website') {
			return get_site_url();
		} else if($key == 'SavedCartURI') {
			return ia_getSavedCartURI($this->trigger->user_email, true);			
		} else if($key == 'SavedCartwPrice') {
			return ia_renderLastCartHTML($this->trigger->user_email, false,true);			
		} else if($key == 'SavedCart') {
			return ia_renderLastCartHTML($this->trigger->user_email);			
		} else if($key == 'PSavedCartwPrice') {
			return ia_renderLastCartHTML($this->trigger->user_email, true,true);			
		} else if($key == 'PSavedCart') {
			return ia_renderLastCartHTML($this->trigger->user_email, true);			
		} else if($key == 'SessEmail') {
			return WC()->session->get('session_email');
		}
	}

	function custom_filter_hook_handler($key) {
		return apply_filters( $key, '', $this->trigger );
	}

	function wp_usermeta_handler($key) {
		if(!isset($this->trigger->wp_user_id)) {
			$user_id = $this->trigger->search_wp_user_id();
		} else {
			$user_id = $this->trigger->wp_user_id;
		}

		if($user_id > 0) {
			return get_user_meta($user_id, $key, true); 
		}
	}

	function get_handler($key) {
		return isset($_GET[$key]) ? $_GET[$key] : '';
	}

	function utility_handler($key) {
		if($key == 'current_datetime') {
			return current_time( 'mysql' );
		} else if($key == 'current_date') {
			return date("Y-m-d", current_time( 'timestamp' ));
		} else if($key == 'current_timestamp') {
			return current_time( 'timestamp' );
		} else if(strpos($key, '_days_datetime') !== false ) {
			$days = (int) substr($key, 0, strpos($key, '_'));
			return date("Y-m-d H:i:s", current_time( 'timestamp' ) + $days*24*3600);
		} else if(strpos($key, '_days_date') !== false ) {
			$days = (int) substr($key, 0, strpos($key, '_'));
			return date("Y-m-d", current_time( 'timestamp' ) + $days*24*3600);
		} else if(strpos($key, '_days_timestamp') !== false ) {
			$days = (int) substr($key, 0, strpos($key, '_'));
			return current_time( 'timestamp' ) + $days*24*3600;
		} else if(strpos($key, 'timer_gif') !== false ) {
			$timer_ses = WC()->session->get('iw_timer_gif');

			if(!empty($timer_ses)) {
				$time = $timer_ses;
			} else if(isset($this->trigger->iw_timer_gif)) {
				$time = $this->trigger->iw_timer_gif;
			} else {
				$time = str_replace('timer_gif_', '', $key);
				$time = str_replace('small_', '', $time);
				$time = str_replace('large_', '', $time);
			}

			if(strpos($key, 'small') !== false) {
				$width = "130px";
			} else if(strpos($key, 'large') !== false) {
				$width = "400px";
			} else {
				$width = "200px";
			}

			$url = admin_url( "admin-ajax.php?action=iw_timer_gif&time=$time");
			return '<style>.infusedwoo_custom_notice .iw-timer-gif {float: right;}</style><img class="iw-timer-gif" src="'.$url.'" style="display:inline-block; margin-left: 5px; margin-right: 5px; max-width:100%; width: '.$width.';" />';
		} else if($key == 'wpml_lang_code') {
			return ICL_LANGUAGE_CODE;
		} else if($key == 'wpml_lang') {
			if(null !== ICL_LANGUAGE_CODE) {
				$langs = apply_filters( 'wpml_active_languages','');
				$lc = ICL_LANGUAGE_CODE;
				if(isset($langs[$lc]['translated_name'])) {
					return $langs[$lc]['translated_name'];
				}
			}
		}

	}

	function get_all_attribs() {
	    global $wpdb;
	    $key = '_product_attributes';
	    $type = 'product';
	    $status = 'publish';
	   
	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND p.post_status = '%s' 
	        AND p.post_type = '%s'
	    ", $key, $status, $type ) );

	    $attribs = array();
	    if(is_array($r)) foreach($r as $a) {
	    	$atts = maybe_unserialize( $a );
	    	foreach($atts as $k => $v) {
	    		if($v['is_variation']) {
	    			$attribs[$k] = $v['name'];
	    		}
	    	}
	    }

	    return $attribs;
	}

	function wc_order_handler($key) {
		global $iwpro;

		$order_id = $this->trigger->pass_vars[0];
		if(!$order_id) return '';

		$order = new WC_Order($order_id);
		$timestamp = strtotime($order->order_date);

		if($key == 'OrderId') {
			return $order_id;
		} else if($key == 'OrderDate') {
			return date("F j, Y");
		} else if($key == 'OrderTimestamp') {
			return $timestamp;
		} else if($key == 'OrderStatus') {
			return $order->get_status();
		} else if($key == 'CouponUsed') {
			if(sizeof( $order->get_used_coupons() ) > 0) {
				$coupons = $order->get_used_coupons();
				return implode(",", $coupons);
			}
		} else if($key == 'OrderNumber') { 
			return $order->get_order_number();
		} else if($key == 'OrderKey') { 
			return $order->order_key;
		} else if($key == 'ProductNames') {
			$products = $order->get_items(); 
			$names = array();
			foreach($products as $product) {
				$id  =  (int) $product['product_id'];
				$names[] = get_the_title($product['product_id']);
			}

			return implode(', ', $names);
		} else if($key == 'ProductNamesQ') {
			$products = $order->get_items(); 
			$names = array();
			foreach($products as $product) {
				$id  =  (int) $product['product_id'];
				$names[] = get_the_title($product['product_id']) . " x " . $product['qty'];
			}

			return implode(', ', $names);
		} else if($key == 'OrderItemTable') {
			ob_start();
			wc_get_template( 'emails/email-order-details.php', array( 'order' => $order, 'sent_to_admin' => false, 'plain_text' => false, 'email' => '' ));
			return ob_get_clean();
		} else if($key == 'InfOrderId') {
			return get_post_meta( $order_id, 'infusionsoft_order_id', true );
		} else if($key == 'InfInvoiceId') {
			return get_post_meta( $order_id, 'infusionsoft_invoice_id', true );
		} else if($key == 'Inf_AffiliateId') {
			$inv_id = (int) get_post_meta( $order_id, 'infusionsoft_invoice_id', true );
			if($iwpro->ia_app_connect()) {
				$inv_info = $iwpro->app->dsLoad('Invoice', $inv_id, array('AffiliateId'));
				return isset($inv_info['AffiliateId']) ? $inv_info['AffiliateId'] : 0;
			}
		} else if($key == 'Inf_AffiliateEmail') {
			$inv_id = (int) get_post_meta( $order_id, 'infusionsoft_invoice_id', true );
			if($iwpro->ia_app_connect()) {
				$inv_info = $iwpro->app->dsLoad('Invoice', $inv_id, array('AffiliateId'));
				$aff_id = isset($inv_info['AffiliateId']) ? $inv_info['AffiliateId'] : 0;
				if($aff_id != 0) {
					$aff_info = $iwpro->app->dsLoad('Affiliate', $aff_id, array('ContactId'));
					$c_info = $iwpro->app->dsLoad('Contact', $aff_info['ContactId'], array('Email'));

					return isset($c_info['Email']) ? $c_info['Email'] : '';
				} else {
					return '';
				}
			}
		} else if(strpos($key, 'InfJob_') !== false) { 
			$inf_job_id =  get_post_meta( $order_id, 'infusionsoft_order_id', true );
			if($inf_job_id > 0 && $iwpro->ia_app_connect()) {
				$job_field = str_replace('InfJob_', '', $key);
				$fieldval = $iwpro->app->dsLoad('Job', $inf_job_id, array($job_field));
				return isset($fieldval[$job_field]) ? $fieldval[$job_field] : '';
			}
		} else {
			return isset($order->$key) ? $order->$key : '';
		}	
	}

	function wc_ordermeta_handler($key) {
		$order_id = $this->trigger->pass_vars[0];
		if(!$order_id) return '';

		return get_post_meta( $order_id, $key, true );
	}

	function wc_ordervar_handler($key) {
		$order_id = $this->trigger->pass_vars[0];
		if(!$order_id) return '';

		$order = new WC_Order($order_id);

		if(isset($this->trigger->product_items)) {
			$products = $this->trigger->product_items;
		} else {
			$products = $order->get_items();
			$this->trigger->product_items = $products;
		}

		foreach($products as $product) {
			$id  =  (int) $product['product_id'];
			$vid =  (int) $product['variation_id'];

			$item_meta_arr = $product['item_meta_array'];
			$item_meta_kv = array();
			$item_meta_keys = array();

			foreach($item_meta_arr as $meta) {
				$item_meta_kv[$meta->key] = $meta->value;
				$item_meta_keys[] = $meta->key;
			}

 			if(in_array($key, $item_meta_keys)) {
 				return $item_meta_kv[$key];
 			}
		}

		return '';
	}

	function ccf_handler($key) {
		return $this->wc_ordermeta_handler($key);
	}

	function iw_session_handler($key) {
		return WC()->session->get("iwc_$key");
	}

}


