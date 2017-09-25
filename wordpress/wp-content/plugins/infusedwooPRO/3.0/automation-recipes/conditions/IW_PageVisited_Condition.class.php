<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_PageVisited_Condition extends IW_Automation_Condition {
	function get_title() {
		return 'When Page visited is ...';
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger',
			);
	}

	function on_class_load() {
		add_action('adm_automation_recipe_after', array($this, 'js_scripts'));
	}


	function display_html($config = array()) {
		$type = isset($config['type']) ? $config['type'] : 'page';		
		$input = isset($config['input-val']) ? $config['input-val'] : array();
		$pg_id = isset($config['pg_id']) ? $config['pg_id'] : '';
		$op = isset($config['op']) ? $config['op'] : '';
		$url = isset($config['url']) ? $config['url'] : '';


		$html = '<select name="type" class="full-select pagetype" style="width: 100%" autocomplete="off">
			<option value="page"'.($type == 'page' ? ' selected' : '').'>a specific wordpress page...</option>
			<option value="cart"'.($type == 'cart' ? ' selected' : '').'>the woocommerce cart page</option>
			<option value="home"'.($type == 'home' ? ' selected' : '').'>the home page</option>
			<option value="product"'.($type == 'product' ? ' selected' : '').'>a woocommerce product page</option>
			<option value="checkout"'.($type == 'checkout' ? ' selected' : '').'>the woocommerce checkout page</option>
			<option value="sproduct"'.($type == 'sproduct' ? ' selected' : '').'>a specific woocommerce product...</option>
			<option value="url"'.($type == 'url' ? ' selected' : '').'>specific url...</option>
			</select>';

		$html .= '<div class="iwar-minisection minisection-sproduct" style="'. ($type != 'sproduct' || empty($type) ? 'display:none;' : '') .'">';

		$html .= '<input type="text" name="input" class="iwar-dynasearch" data-src="wooproducts" placeholder="Start typing to add products..." style="width: 100% !important; margin: 5px 0;" />';
		$html .= '<div class="input-contain dynasearch-contain">';

		if(isset($config['input-val']) && is_array($config['input-val'])) {
			foreach($config['input-val'] as $k => $val) {
				$wc_prod = new WC_Product($val);
				$test_var = $wc_prod->is_type('variation');
				if(!empty($wc_prod->post->post_parent)) $link_id = $wc_prod->post->post_parent;
				else $link_id = $val;

				$label = isset($config['input-label'][$k]) ? $config['input-label'][$k] : 'Product ID # ' . $val;
				$html .= '<span class="wooproducts-item">';
				$html .= '<a href="'. get_edit_post_link($link_id) .'" target="_blank">' . $label . "</a>";
				$html .= '<input type="hidden" name="input-label[]" value="'.$label.'" />';
				$html .= '<input type="hidden" name="input-val[]" value="'.$val.'" />';
				$html .= '<i class="fa fa-times-circle"></i>';
				$html .= '</span>';
			}
		}

		$html .= '</div></div>';

		$html .= '<div class="iwar-minisection minisection-page" style="'. ($type != 'page' || empty($type) ? 'display:none;' : '') .'">';

		$args = array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		); 
		$pages = get_pages($args); 

		$html .= '<select name="pg_id" style="width: 100%" autocomplete="off">';
		foreach($pages as $page) {
			$html .= '<option'.($pg_id == $page->ID ? ' selected' : '').' value="'.$page->ID.'">'.$page->post_title.' ('.$page->ID.')</option>';
		}

		$html .= '</select>';
		$html .= '</div>';

		$html .= '<div class="iwar-minisection minisection-url" style="margin-top: 5px;'. ($type != 'url' || empty($type) ? 'display:none;' : '') .'">';
		$html .= '<hr>&nbsp;&nbsp;Page URL is <select name="op">
				<option value="like"'.($op == 'like' ? ' selected ' : '').'>is equal to (case insensitive)</option>
				<option value="contain"'.($op == 'contain' ? ' selected ' : '').'> contains</option>
				<option value="notequal"'.($op == 'notequal' ? ' selected ' : '').'>is not Equal to</option>
				<option value="startswith"'.($op == 'startswith' ? ' selected ' : '').'>starts with</option>
				</select><br><br><span style="margin-left: 5px;">'.get_site_url().'/ </span><input type="text" style="position: relative; top:0px; width: 42%" name="url" placeholder="" value="'.$url.'" />';

		$html .= '</div>';



		return $html;
	}

	function validate_entry($conditions) {
		if($conditions['type'] == 'sproduct') {
			if(empty($conditions['input-val'])) {
				return 'Please enter at least one product.';
			}
		}
	}


	function test($config, $trigger) {
		global $post;

		if($config['type'] == 'sproduct') {
			return (is_product() && in_array($post->ID, $config['input-val']));
		} else if($config['type'] == 'page') {
			return is_page($config['pg_id']);
		} else if($config['type'] == 'url') {
			$current_url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$compare_url = get_site_url() . "/" . $config['url'];
			$compare_url = str_replace('http://', '', $compare_url);
			$compare_url = str_replace('https://', '', $compare_url);

			return $trigger->compare_val($current_url, $config['op'], $compare_url); 
		} else if($config['type'] == 'cart') {
			return is_cart();
		} else if($config['type'] == 'checkout') {
			return is_checkout();
		} else if($config['type'] == 'product') {
			return is_product();
		} else if($config['type'] == 'home') {
			return is_front_page();
		}

		return false;
	}

	function js_scripts() {
		?>
		<script>
			jQuery("body").on('change','.pagetype', function() {
				var show = jQuery(this).val();
				jQuery(this).closest('form').find('.iwar-minisection').hide();
				jQuery(this).closest('form').find('.minisection-'+show).show();
			});
		</script>

		<?php
	}
}

iw_add_condition_class('IW_PageVisited_Condition');