<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_GenerateWooCoupon_Action extends IW_Automation_Action {
	function get_title() {
		return "Generate Woocommerce Coupon Code";
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

	function display_html($config = array()) {
		$template_id = isset($config['template']) ? $config['template'] : '';
		$expire = isset($config['expiration']) ? $config['expiration'] : '';
		$prefix = isset($config['prefix']) ? $config['prefix'] : '';
		$suffix = isset($config['suffix']) ? $config['suffix'] : '';
		$length = isset($config['length']) ? $config['length'] : '8';
		$limit = isset($config['limit']) ? $config['limit'] : 'off';


		 global $wpdb; 
         $results = $wpdb->get_results(
                "
                SELECT * FROM $wpdb->posts
                WHERE post_title LIKE 'template_%'
                AND post_type = 'shop_coupon'
                "
         ); 

         $html = '';

        if(is_array($results) && count($results) > 0) {
			$html .= 'Select Coupon Code Template<br>';
			$html .= '<select name="template" style="width: 80%; margin-top: 8px;">';
			$html .= '<option value="">Select a template</option>';
			foreach($results as $res) {
				$name = str_replace('template_', '', $res->post_name);
				$html .= '<option value="'.$res->ID.'" '.($res->ID == $template_id ? 'selected' : '').'>'.$name.'</option>';
			}
			$html .= '</select><hr>';
			$html .= '<table style="margin-top: 8px;">';
			$html .= '<tr><td>Coupon Expiration Date&nbsp;</td><td><input type="text" name="expiration" placeholder="YYYY-MM-DD" value="'.$expire.'" style="width: 150px;" class="iwar-mergeable" />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></td></tr>';
			$html .= '<tr><td>Prefix</td><td><input type="text" name="prefix"  placeholder="e.g. ABC-" style="width: 120px;" value="'.$prefix.'" /></td></tr>';
			$html .= '<tr><td>Suffix</td><td><input type="text" name="suffix" placeholder="e.g. -XYZ" style="width: 120px;" value="'.$suffix.'" /></td></tr>';
			$html .= '<tr><td>Coupon Code Length</td><td><input type="text" name="length" placeholder="" style="width: 80px;" value="'.$length.'" /></td></tr>';
			$html .= '</table>';
			$html .= '<div style="margin-top: 10px;"><input type="hidden" name="limit" value="off" /><input type="checkbox" name="limit" '.($limit == 'on' ? 'checked' : '').' />&nbsp; Limit coupon usage to current email address</div>';

		} else {
			$html .= '<i style="font-size: 10pt;">You currently don\'t have coupon code templates. To generate coupons, you must create a template first. 
				<br><br>To create a template, create a coupon code that starts with "template_" in Woocommerce > Coupons. 
			</i>';
		}
		

		return $html;
	}

	function validate_entry($config) {
		$template_id = isset($config['template']) ? $config['template'] : '';
		$length = isset($config['length']) ? $config['length'] : '8';

		if(empty($template_id)) {
			return 'Please select a coupon code template.';
		}

		if($length < 3) {
			return 'Coupon code template must be more than 3';
		}
	}

	function process($config, $trigger) {
		global $wpdb; 

		$template_id = isset($config['template']) ? $config['template'] : '';
		$expire = isset($config['expiration']) ? $trigger->merger->merge_text($config['expiration']) : '';
		$prefix = isset($config['prefix']) ? $config['prefix'] : '';
		$suffix = isset($config['suffix']) ? $config['suffix'] : '';
		$length = isset($config['length']) ? $config['length'] : '8';
		$limit = isset($config['limit']) ? $config['limit'] : 'off';
		if($template_id <= 0) return false;

		// generate_code
		$duplicate = false;
		$times = 0;

		do {
			$times++;
			if($times > 50) return false;
			$code = $prefix . strtoupper(wp_generate_password( $length, false )) .$suffix;
			
			$results = $wpdb->get_results(
                "
                SELECT * FROM $wpdb->posts
                WHERE post_title LIKE '$prefix'
                AND post_type = 'shop_coupon'
                "
         	); 

         	$duplicate = is_array($results) && count($results) > 0;
		} while($duplicate);

		$post= get_post( $template_id );

		if (isset( $post) && $post != null) {
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $code,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => $code,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
	 
			$new_post_id = wp_insert_post( $args );

			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$template_id");
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

			if(!empty($expire)) {
				update_post_meta( $new_post_id, 'expiry_date', $expire );
				update_post_meta( $new_post_id, 'date_expires', $expire );
			}
			if($limit == 'on') {
				update_post_meta( $new_post_id, 'customer_email', array($trigger->user_email) );
			}

			$trigger->last_generated_coupon = $code;
		}
	}
}

iw_add_action_class('IW_GenerateWooCoupon_Action');