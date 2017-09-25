<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_ProductsInCart_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'If cart contains some product(s) ...';
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_PageVisit_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger',
			);
	}

	function display_html($config = array()) {
		$type = isset($config['type']) ? $config['type'] : '';		
		$categ = isset($config['categ']) ? $config['categ'] : array();
		$pts = isset($config['prodtype']) ? $config['prodtype'] : array();


		$html = '<select name="type" class="full-select wptype-sel" style="width: 100%" autocomplete="off">
			<option value="specific"'.($type == 'specific' ? ' selected' : '').'>specific product / variation...</option>
			<option value="category"'.($type == 'category' ? ' selected' : '').'>from certain product category...</option>
			<option value="wptype"'.($type == 'wptype' ? ' selected' : '').'>from certain product type...</option>
			</select>';

		$html .= '<div class="iwar-minisection minisection-wooproducts" style="'. ($type == 'specific' || empty($type) ? '' : 'display:none;') .'">';
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
		$html .= '</div>';
		$html .= '<script> 
				jQuery(".wptype-sel").change(function() {
					jQuery(this).parent().children(".iwar-minisection").hide();
					if(jQuery(this).val() == "specific") {
						jQuery(this).parent().children(".minisection-wooproducts").show();
					} else if(jQuery(this).val() == "category") {
						jQuery(this).parent().children(".minisection-categ").show();
					} else {
						jQuery(this).parent().children(".minisection-type").show();
					}
				});

			</script>';


		return $html;
	}

	function validate_entry($conditions) {
		if($conditions['type'] == 'specific') {
			if(!isset($conditions['wooproducts-val']) || empty($conditions['wooproducts-val'])) {
				return 'Please add at least one product.';
			}
		} else if($conditions['type'] == 'category') {
			if(empty($conditions['categ'])) {
				return 'Please select at least one category.';
			}
		} else {
			if(empty($conditions['prodtype'])) {
				return 'Please select at least one product type.';
			}
		}
	}


	function test($config, $trigger) {
		$cart =& WC()->cart;
		$contents = $cart->get_cart();

		foreach($contents as $content) {
			$product = $content['data'];
			$product_id = $product->id;
			$variation_id = $product->variation_id;

			if($config['type'] == 'specific') {
				if(in_array($product_id, $config['wooproducts-val']) || in_array($variation_id, $config['wooproducts-val'])) {
					return true;
				}
			} else if($config['type'] == 'category') { 
				$cats = get_the_terms($product_id, 'product_cat');

				if(is_array($cats)) {
					foreach($cats as $cat) {
						if(in_array($cat->term_id, $config['categ'])) {
							return true;	
						}
					}
				}
			} else {
				$wc_product = wc_get_product($product_id);
				$prodtype = $wc_product->get_type();

				if(in_array($prodtype, $config['prodtype'])) {
					return true;
				}
			}
		}

		return false;

	}
}

iw_add_condition_class('IW_ProductsInCart_Condition');