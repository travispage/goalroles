<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WishlistEvent_Trigger extends IW_Automation_Trigger {
	public $is_advanced = true;
	public $merge_handlers = array(
			'Wishlist' => array('Wishlist Item', 'merge_handler_wishlist')
		);


	function trigger_when() {
		add_action( 'woocommerce_wishlist_add_item', array($this,'event_add_item'), 10, 7 );
		add_action(	'wc_wishlists_cron', array($this,'cron'));
		//add_action( 'init', array($this,'force_trig'));

		add_filter('woocommerce_add_wishlist_item_data', array($this,'add_iwar_params'), 10, 2);
		add_filter('woocommerce_get_cart_item_from_session', array($this, 'load_iwar_params'), 10, 3);
	}

	public function get_desc() {
		return 'when something happens to user\'s wishlist items';
	}

	function get_title() {
		return 'Wishlist Event Trigger';
	}
	function get_icon() {
		return '<i class="fa fa-heart"></i>';
	}

	function get_contact_email() {
		if(isset($this->user_email)) {
			return $this->user_email;
		} else {
			if(is_user_logged_in()) {
				$user = wp_get_current_user();
				$email = get_user_meta($user->ID, 'billing_email', true);
				if(empty($email)) $email = $user->user_email;

				return $email;
			} else return "";
		}	
	}

	function event_add_item($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $wishlist_id) {
		$id = $product_id;
		if(!empty($variation_id)) $id = $variation_id;

		$this->wl_product_id = $id;

		$wishlist_owner_email = get_post_meta( $wishlist_id, '_wishlist_email', true );

		//Added validation 2.9.0
		$wishlist_owner_validation = get_post_meta( $wishlist_id, '_wishlist_email_validated', true );
		$is_valid_email = false;
		if ( $wishlist_owner_validation === '' ) {
			//If the validated email is exactly empty then this list was created prior to 2.9.0
			$is_valid_email = true;
		} elseif ( $wishlist_owner_validation == $wishlist_owner_email ) {
			$is_valid_email = true;
		}

		if ( $is_valid_email ) {
			$this->user_email = $wishlist_owner_email;
		}

		if ($variation_id > 0) {
			$product_data = new WC_Product_Variation($variation_id);
		} else {
			$product_data = new WC_Product($product_id);
		}

		$wl_price = $product_data->get_price_excluding_tax();
		$wl_stock = isset($product_data->total_stock) ? $product_data->total_stock : 0;
		$this->support_caching = true;
		$this->trigger('add_item', $wishlist_id, $id, $quantity, $wl_price, $wl_price, $wl_stock, $wl_stock);
	}

	function cron() {
		$args = array(
		    'post_type' => 'wishlist',
		    'post_status' => 'publish',
		    'nopaging' => true
		);

		$posts = get_posts( $args );

		$receivers = array();

		$wishlists_message_histories = array();

		foreach ( $posts as $post ) {
			$post_id = $post->ID;
			$wishlist = new WC_Wishlists_Wishlist( $post_id );
			$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $post_id );
			$wishlist_owner_email = get_post_meta( $post_id, '_wishlist_email', true );
			$changed = false;

			$wishlist_message_history = get_post_meta( $post_id, 'iwar_wishlist_message_history', true );
			$wishlist_message_history = empty( $wishlist_message_history ) ? array() : $wishlist_message_history;

			if ( !empty( $wishlist_owner_email ) ) {
				$wishlist_subscribers = array($wishlist_owner_email);
				$changes = array();
				if ( $wishlist_items ) {
					//Inspect each item in the list for price changes. 
					foreach ( $wishlist_items as $key => $item ) {
						$id = isset( $item['variation_id'] ) && !empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];

						if ( function_exists( 'get_product' ) ) {
							$product = get_product( isset( $item['variation_id'] ) && !empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id']  );
						} else {
							if ( isset( $item['variation_id'] ) ) {
								$product = new WC_Product_Variation( $item['variation_id'] );
							} else {
								$product = new WC_Product( $item['product_id'] );
							}
						}

						$price = $product->get_price_excluding_tax();
						$wl_price = isset( $item['iwar_price'] ) ? $item['iwar_price'] : $price;
						$stock = $product->get_total_stock();
						$wl_stock = isset($item['iwar_stock']) ? $item['iwar_stock'] : $stock;


						if ( $wl_price && ($wl_price > $price || $wl_price < $price)) {
							//Grab the wishlist array for this products changes. 
							$event = 'change_price';
							if ( isset( $changes[$event.'_'.$id] ) ) {
								$inspected_lists = isset( $changes[$event.'_'.$id]['wishlists'] ) ? $changes[$event.'_'.$id]['wishlists'] : array($post_id);
							} else {
								$inspected_lists = array($post_id);
							}

							

							$changes[$event.'_'.$id] = array(
							    'title' => $product->get_title(),
							    'old_price' => $wl_price,
							    'quantity' => $item['quantity'],
							    'new_price' => $price,
							    'wishlists' => $inspected_lists,
							    'event' => $event,
							    'old_stock' => $wl_stock,
							    'new_stock' => $stock,
							    'product_id' => $id,
							    'hash' => md5($wl_price . $price)

							);
							$wishlist_items[$key]['iwar_price'] = $price;
							$changed = true;

						} 

						if ( $product->managing_stock() && ($wl_stock > $stock || $wl_stock < $stock)) {
							//Grab the wishlist array for this products changes. 
							$event = 'change_stock';
							if ( isset( $changes[$event.'_'.$id] ) ) {
								$inspected_lists = isset( $changes[$event.'_'.$id]['wishlists'] ) ? $changes[$event.'_'.$id]['wishlists'] : array($post_id);
							} else {
								$inspected_lists = array($post_id);
							}

							$changes[$event.'_'.$id] = array(
							    'title' => $product->get_title(),
							    'old_price' => $wl_price,
							    'quantity' => $item['quantity'],
							    'new_price' => $price,
							    'old_stock' => $wl_stock,
							    'new_stock' => $stock,
							    'wishlists' => $inspected_lists,
							    'event' => $event,
							    'product_id' => $id,
							    'hash'  => md5( $wl_stock . $stock)
							);
							$wishlist_items[$key]['iwar_stock'] = $stock;
							$changed = true;
							
						}
					}
					if($changed) {
						update_post_meta($post_id, '_wishlist_items', $wishlist_items);
					}
				}

				if ( $changes && count( $changes ) ) {
					foreach ( $wishlist_subscribers as $receiver ) {
						if ( !isset( $receivers[$receiver] ) ) {
							$receivers[$receiver] = array();
						}

						if ( !isset( $wishlist_message_history[$receiver] ) ) {
							$wishlist_message_history[$receiver] = array();
						}

						foreach ( $changes as $id => $change ) {
							$send = false;
							$notification_hash = $change['hash'];
							$check_id = $id;

							if ( isset( $wishlist_message_history[$receiver][$check_id] ) ) {
								if ( $wishlist_message_history[$receiver][$check_id] != $notification_hash ) {
									$send = true;
								} else {
									$send = false;  //Price change hash matches the last message we sent. 
								}
							} else {
								$send = true;
							}

							if ( $send) {
								unset($this->pass_vars);
								$this->support_caching = false;
								$wishlist_message_history[$receiver][$check_id] = $notification_hash;
								$receivers[$receiver][$check_id] = $change;
								$this->user_email = $receiver;
								$this->wl_product_id = $change['product_id'];
								$this->change = $change;
								$this->trigger($change['event'],$post_id, $change['product_id'], $change['quantity'], $change['old_price'], $change['new_price'],$change['old_stock'], $change['new_stock']);	 
							}
						}
					}
				}
			}

			$wishlists_message_histories[$post_id] = $wishlist_message_history;
		}

		//Finally update all the wishlists with the receivers => messages we sent. 
		foreach ( $wishlists_message_histories as $wishlist_id => $history ) {
			update_post_meta( $wishlist_id, 'iwar_wishlist_message_history', $history );
		}

		return true;
	}

	function add_iwar_params($cart_item_data, $product_id) {
		$product = new WC_Product($product_id);
		$stock = (int) $product->get_total_stock();
		$price = $product->get_price_excluding_tax();

		$cart_item_data['iwar_stock'] = $stock;
		$cart_item_data['iwar_price'] = $price;

		return $cart_item_data;
	}

	function force_trig() {
		do_action('wc_wishlists_cron');
	}

	function get_log_details() {
		return $this->user_email . print_r($this->pass_vars, true);
	}

	function load_iwar_params($return, $vals, $keys) {
		if(isset($vals['iwar_stock'])) $return['iwar_stock'] = $vals['iwar_stock'];
		if(isset($vals['iwar_price'])) $return['iwar_price'] = $vals['iwar_price'];

		return $return;
	}

	function merge_fields() {
		return array('Wishlist' => array(
				'product_name' => 'Wishlist Item Product Name',
				'product_id' => 'Wishlist Item Product Id',
				'old_price' => 'Wishlist Item Old Price',
				'new_price' =>  'Wishlist Item New Price',
				'old_stock' => 'Wishlist Item Old Stock Level',
				'new_stock' => 'Wishlist Item New Stock Level'
			));
	}

	function merge_handler_wishlist($key) {
		$product_id = $this->wl_product_id;

		if($key == 'product_name' && !empty($product_id)) {
			return get_the_title($product_id);
		} else if($key == 'product_id') {
			return $product_id;
		} else if($key == 'old_price' && isset($this->change)) {
			return wc_price($this->change['old_price']);
		} else if($key == 'new_price' && isset($this->change)) {
			return wc_price($this->change['new_price']);
		} else if($key == 'old_stock' && isset($this->change)) {
			return $this->change['old_stock'];
		} else if($key == 'new_stock' && isset($this->change)) {
			return $this->change['new_stock'];
		}
	}
}




if(class_exists('WC_Wishlists_User')) {
	iw_add_trigger_class('IW_WishlistEvent_Trigger');
}
