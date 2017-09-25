<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_ProductDiscount_Action extends IW_Automation_Action {
	function get_title() {
		return "Give Product discount(s) to current user";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger',
				'IW_HttpPost_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		if(version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' )) {
			add_filter('woocommerce_product_get_price', array($this, 'apply_product_discounts'),1,2);
			add_filter('woocommerce_product_get_sale_price', array($this, 'apply_product_discounts'),1,2);
		} else {
			add_filter('woocommerce_get_price', array($this, 'apply_product_discounts'),1,2);
			add_filter('woocommerce_get_sale_price', array($this, 'apply_product_discounts'),1,2);
		}
		add_action('template_redirect', array($this,'reset_session_vars'),1,0);
		
		add_filter('woocommerce_variation_prices_price', array($this, 'apply_product_discounts'),1,2);
		add_filter('woocommerce_variation_prices_sale_price', array($this, 'apply_product_discounts'),1,2);

	}

	function display_html($config = array()) {
		$type = isset($config['type']) ? $config['type'] : '';		
		$categ = isset($config['categ']) ? $config['categ'] : array();
		$pts = isset($config['prodtype']) ? $config['prodtype'] : array();
		$smart_apply = isset($config['smart_apply']) ? $config['smart_apply'] : 'on';	

		$discount = isset($config['discount']) ? $config['discount'] : '100%';
		$html = 'Discount Amount  &nbsp;&nbsp;<input type="text" name="discount" value="'.$discount.'" placeholder="" style="width: 150px; top: 0" />';
		$html.= '<br><i style="font-size: 10pt;">Amount without % will be treated as fixed amount. E.g. 100% for free shipping.</i>';

		$html .= '<hr>Products to Discount:<br><select name="type" class="full-select wptype-sel" style="margin-top: 10px; width: 100%" autocomplete="off">
			<option value="all"'.($type == 'specific' ? ' selected' : '').'>all products</option>
			<option value="specific"'.($type == 'specific' ? ' selected' : '').'>specific product / variation...</option>
			<option value="category"'.($type == 'category' ? ' selected' : '').'>from certain product category...</option>
			<option value="wptype"'.($type == 'wptype' ? ' selected' : '').'>from certain product type...</option>
			</select>';
		
		$html .= '<div class="iwar-minisection minisection-wooproducts" style="'. ($type == 'specific' ? '' : 'display:none;') .'">';
		$html .= '<input type="text" name="wooproducts" class="iwar-dynasearch" data-src="wooproducts" placeholder="Start typing to add products..." style="width: 100% !important; margin: 5px 0;" />';
		$html .= '<div class="wooproducts-contain dynasearch-contain">';

		if(isset($config['wooproducts-val']) && is_array($config['wooproducts-val'])) {
			foreach($config['wooproducts-val'] as $k => $val) {
				$wc_prod = wc_get_product($val);
				$test_var = $wc_prod->is_type('variation');
				if(!empty($wc_prod->post->post_parent)) $link_id = $wc_prod->post->post_parent;
				else $link_id = $val;

				$label = isset($config['wooproducts-label'][$k]) ? $config['wooproducts-label'][$k] : 'Product ID # ' . $val;
				$html .= '<span class="wooproducts-item">';
				$html .= '<a href="'. get_edit_post_link($link_id) .'" target="_blank">' . $label . "</a>";
				$html .= '<input type="hidden" name="wooproducts-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="wooproducts-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div></div>';

		$html .= '<div class="iwar-minisection minisection-categ" style="'. ($type == 'category' ? '' : 'display:none;') .'">';
		$html .= '<br><select class="" multiple name="categ[]" style="min-width: 280px;">';

		$args = array(
		  'taxonomy'     => 'product_cat',
		  'orderby'      => 'name',
		  'show_count'   => 0,
		  'pad_counts'   => 0,
		  'hierarchical' => 1,
		  'title_li'     => 0,
		  'hide_empty'   => 0
		);
 		$all_categories = get_categories( $args );

		foreach ($all_categories as $cat) {
		    if($cat->category_parent == 0) {
		        $category_id = $cat->term_id;
		        $html .= '<option value="';
	  			$html .=  $category_id;
	  			$html .= '"'. (in_array($category_id, $categ) ? ' selected' : '') .'>' . $cat->name . " [ {$category_id} ]";
	  			$html .=  "</option>";
		    }
		}
		$html .= '</select>';
		$html .= '</div>';

		$html .= '<div class="iwar-minisection minisection-type" style="'. ($type == 'wptype' ? '' : 'display:none;') .'">';
		$html .= '<br><select class="" multiple name="prodtype[]" style="min-width: 280px;">';

		$prodtypes = wc_get_product_types();		  

		foreach ($prodtypes as $k => $type) {
		        $html .= '<option value="';
	  			$html .=  $k;
	  			$html .= '"'. (in_array($k, $pts) ? ' selected' : '') .'>' . $type;
	  			$html .=  "</option>";
		}
		$html .= '</select>';
		$html .= '</div><hr>';

		$html .= '<br><input type="hidden" name="smart_apply" value="off" /><input type="checkbox" name="smart_apply" '.($smart_apply == 'on' ? 'checked' : '' ).' />';
		$html .= '&nbsp;<span style="font-size: 10pt;"> In case of multiple active discounts, only apply discount if discount amount is higher than the others.</span>';

		return $html;

	}

	function validate_entry($config) {
		if(empty($config['discount'])) {
			return 'Please enter a discount amount';
		} else {
			$check_nan = str_replace('%', '', $config['discount']);
			if(!is_numeric($check_nan))
				return 'Discount amount must be a number or a percent value.'; 
		}
	}

	function process($config, $trigger) {
		$product_discounts = isset($trigger->product_discounts) ? $trigger->product_discounts : array();
		$product_discounts[] = $config;
		$trigger->product_discounts = $product_discounts;
		
		if(!WC()->session) return false;
		WC()->session->set('iwar_prod_discounts', $product_discounts);
	}

	function apply_product_discounts($price, $product) {
		if(is_admin()) return $price;
		if(!WC()->session) return $price;

		$discounts = WC()->session->get('iwar_prod_discounts');

		if(!is_array($discounts) || count($discounts) == 0) return $price;

		foreach($discounts as $config) {
			$sku = "";
			$product_id  =  (int) method_exists($product, 'get_id') ? $product->get_id() : $product->id;
			$proceed = false;

			if($config['type'] == 'all') $proceed = true;			
			else if($config['type'] == 'specific') {
				if(in_array($product_id, $config['wooproducts-val'])) {
					$proceed = true;
				}
			} else if($config['type'] == 'category') { 
				$cats = get_the_terms($product_id, 'product_cat');

				if(is_array($cats)) {
					foreach($cats as $cat) {
						if(in_array($cat->term_id, $config['categ'])) {
							$proceed = true;	
						}
					}
				}
			} else {
				$prodtype = $product->get_type();

				if(in_array($prodtype, $config['prodtype'])) {
					$proceed = true;
				}
			}

			if(!$proceed) continue;

			$discount = $config['discount'];
			$type = 'fixed';
			if(strpos($discount, '%') !== false) {
				$discount = str_replace('%', '', $discount);
				$type = 'percent';
			}

			if($discount > 0) {
				$reg_price = $product->get_regular_price();
				$current_discount = $reg_price - $price;

				if($type == 'fixed') {
					$apply_discount = $discount;
				} else {
					$apply_discount = $reg_price*((float) $discount / 100.0);
				}

				if($current_discount == 0 || ($config['smart_apply'] == 'on' && $apply_discount > $current_discount) || $config['smart_apply'] == 'off') {
					$price = $reg_price - $apply_discount;
					if($price < 0) $price = 0;
				}
			}
		}
		return $price;
	}


	function reset_session_vars() {
		if(is_admin()) return false;
		if(!WC()->session) return false;

		WC()->session->set('iwar_prod_discounts', null);
	}
}

iw_add_action_class('IW_ProductDiscount_Action');