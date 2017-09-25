<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WishlistItems_Condition extends IW_Automation_Condition {
	public $allow_multiple = true; 

	function get_title() {
		return 'If user has saved wishlist items';
	}

	function allowed_triggers() {
		return array(
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_HttpPost_Trigger',
				'IW_PageVisit_Trigger',
				'IW_UserAction_Trigger',
				'IW_WooSubEvent_Trigger'		
			);
	}

	function on_class_load() {
		
	}

	function display_html($config = array()) {
		return '';
	}

	function validate_entry($conditions) {

	}


	function test($config, $trigger) {
		if(!isset($trigger->wp_user_id)) {
			$user_id = $trigger->search_wp_user_id();
		} else {
			$user_id = $trigger->wp_user_id;
		}

		if(class_exists('WC_Wishlists_Query')) {
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

			foreach($lists as $wishlist) {
				$collection = new WC_Wishlists_Wishlist_Item_Collection($wishlist->id);
				$items = $collection->get_items($wishlist->id);

				if(count($items) > 0) {
					return true;
				}
			}
		}

		return false;
	}
}

iw_add_condition_class('IW_WishlistItems_Condition');