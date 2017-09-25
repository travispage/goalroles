<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPJM_Pack_WC_Subscriptions
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC_Subscriptions {

	/**
	 * @var \WPJM_Pack_Job|\WPJM_Pack_Resume
	 */
	public $type;

	/**
	 * WPJM_Pack_WC_Subscriptions constructor.
	 *
	 * @param $type \WPJM_Pack_Job|\WPJM_Pack_Resume
	 */
	public function __construct( $type ){

		$this->type = $type;

		if ( class_exists( 'WC_Subscriptions_Synchroniser' ) && method_exists( 'WC_Subscriptions_Synchroniser', 'save_subscription_meta' ) ) {
			add_action( 'woocommerce_process_product_meta_job_visibility_subscription', 'WC_Subscriptions_Synchroniser::save_subscription_meta', 10 );
			add_action( 'woocommerce_process_product_meta_resume_visibility_subscription', 'WC_Subscriptions_Synchroniser::save_subscription_meta', 10 );
		}

		add_filter( 'woocommerce_is_subscription', array( $this, 'is_subscription' ), 10, 2 );

		// Handle all subscription status changes from one status to another (active, cancelled, on-hold, expired)
		add_action( 'woocommerce_subscription_status_changed', array( $this, 'status_changed' ), 10, 3 );

		// On renewal
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'renewed' ) ); // When the subscription is renewed

		// Subscription is switched
		add_action( 'woocommerce_subscriptions_switched_item', array( $this, 'switched' ), 10, 3 ); // When the subscription is switched and a new subscription is created
		add_action( 'woocommerce_subscription_item_switched', array( $this, 'item_switched' ), 10, 4 ); // When the subscription is switched and only the item is changed
	}

	/**
	 * Handle Subscription Status Changes
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $subscription_id
	 * @param $old_status
	 * @param $new_status
	 */
	public function status_changed( $subscription_id, $old_status, $new_status ){

		$subscription = wcs_get_subscription( $subscription_id);

		foreach( $subscription->get_items() as $item ) {

			$product = wc_get_product( $item['product_id'] );

			if( ! $product->is_type( $this->type->sub_type ) ){
				return;
			}

			switch ( $new_status ) {

				case 'active':

					if( $old_status === 'on-hold' ){
						$this->reactivated( $subscription, $product, $item );
					} else {
						$this->activated( $subscription, $product, $item );
					}

					break;
				case 'cancelled':
				case 'expired':
					$this->ended( $subscription, $product, $item );
					break;

				case 'on-hold':
					$this->on_hold( $subscription, $product, $item );
					break;
			}

		}

	}

	/**
	 * Subscription is on-hold for payment, change user package status to on-hold
	 *
	 * 1.) the store manager has manually suspended the subscription
	 * 2.) the customer has manually suspended the subscription from their My Account page
	 * 3.) a renewal payment is due (subscriptions are suspended temporarily for automatic renewal payments until the payment is processed successfully
	 *     and indefinitely for manual renewal payments until the customer logs in to the store to pay for the renewal.
	 *
	 * @since 1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 */
	public function on_hold( $subscription, $product, $item ){

		global $wpdb;

		$db_table = WPJM_Pack_User::$db_table;
		$pid = $item['product_id'];

		$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE order_id = %d AND product_id = %d;", $subscription->get_id(), $pid ) );

		if( $user_package ){

			// Delete the package
			$wpdb->update( "{$wpdb->prefix}{$db_table}", array( 'status' => 'on-hold' ), array( 'id' => $user_package->id ) );

			// Store the old package data in subscription meta
			update_post_meta( $subscription->get_id(), "_{$this->type->slug}_visibility_user_package_{$pid}_status", 'on-hold' );

			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_on-hold", $subscription->get_user_id(), $item, $subscription );
		}

		delete_post_meta( $subscription->get_id(), "wpjmpack_{$this->type->slug}_subscription_package_process_complete" );
	}

	/**
	 * Subscription Reactivated (on-hold to active status)
	 *
	 *
	 * @since    1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 * @param $product      \WC_Product|\WC_Product_Resume_Visibility_Subscription|\WC_Product_Job_Visibility_Subscription
	 * @param $item
	 */
	public function reactivated( $subscription, $product, $item ){

		global $wpdb;
		$db_table = WPJM_Pack_User::$db_table;
		$pid      = $item['product_id'];

		$on_hold_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE order_id = %d AND product_id = %d AND status = %s;", $subscription->get_id(), $pid, 'on-hold' ) );

		// If package found that is on hold, let's set it back to active
		if( $on_hold_package ){

			// Set the package back to active status
			$wpdb->update( "{$wpdb->prefix}{$db_table}", array( 'status' => 'active' ), array( 'id' => $on_hold_package->id ) );

			// Store the old package data in subscription meta
			update_post_meta( $subscription->get_id(), "_{$this->type->slug}_visibility_user_package_{$pid}_status", 'on-hold' );

			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_reactivated", $subscription->get_user_id(), $item, $subscription );
		}

	}

	/**
	 * Subscription has expired - remove user package
	 *
	 *
	 * @since    1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 * @param $product      \WC_Product|\WC_Product_Resume_Visibility_Subscription|\WC_Product_Job_Visibility_Subscription
	 * @param $item
	 */
	public function ended( $subscription, $product, $item ) {
		global $wpdb;

		$db_table = WPJM_Pack_User::$db_table;

		$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE order_id = %d AND product_id = %d;", $subscription->get_id(), $item['product_id'] ) );

		if( $user_package ){
			// Delete the package
			$wpdb->delete( "{$wpdb->prefix}{$db_table}", array( 'id' => $user_package->id ) );
			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_ended", $subscription->get_user_id(), $item, $subscription );
		}

		// Remove processed meta
		delete_post_meta( $subscription->get_id(), "wpjmpack_{$this->type->slug}_subscription_package_process_complete" );
	}

	/**
	 * Subscription Activated
	 *
	 * Same as the order_paid() method in WPJM_Pack_WC, but for
	 * subscriptions.
	 *
	 * @since 1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 * @param $product      \WC_Product|\WC_Product_Resume_Visibility_Subscription|\WC_Product_Job_Visibility_Subscription
	 * @param $item
	 */
	public function activated( $subscription, $product, $item ){

		global $wpdb;

		if( get_post_meta( $subscription->get_id(), "wpjmpack_{$this->type->slug}_subscription_package_process_complete", TRUE ) ){
			return;
		}

		// Give user packages for this subscription
		if( ! isset( $item['switched_subscription_item_id'] ) && $product->is_type( $this->type->sub_type ) && $subscription->get_user_id() ){

			$db_table = WPJM_Pack_User::$db_table;

			// Remove any old packages for this subscription (shouldn't be necessary, but just in case)
			$wpdb->delete( "{$wpdb->prefix}{$db_table}", array( 'order_id' => $subscription->get_id() ) );

			$user_package_id = $this->type->packages->user->give( $subscription->get_user_id(), $product->get_id(), $subscription->get_id(), 'woocommerce' );

			update_post_meta( $subscription->get_id(), '_jmpack_user_sub_package_ids', $user_package_id );

			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_activated", $subscription->get_user_id(), $item, $subscription );

		}

		update_post_meta( $subscription->get_id(), "wpjmpack_{$this->type->slug}_subscription_package_process_complete", TRUE );

	}

	/**
	 * When the subscription is switched and only the item is changed
 	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $order
	 * @param $subscription \WC_Subscription
	 * @param $new_order_item_id
	 * @param $old_order_item_id
	 */
	public function item_switched( $order, $subscription, $new_order_item_id, $old_order_item_id ){

		global $wpdb;

		$new_order_item = WC_Subscriptions_Order::get_item_by_id( $new_order_item_id );
		$old_order_item = WC_Subscriptions_Order::get_item_by_id( $old_order_item_id );

		$new_subscription = (object) array(
			'id'           => $subscription->get_id(),
			'subscription' => $subscription,
			'product_id'   => $new_order_item['product_id'],
			'product'      => wc_get_product( $new_order_item['product_id'] )
		);

		$old_subscription = (object) array(
			'id'           => $subscription->get_id(),
			'subscription' => $subscription,
			'product_id'   => $old_order_item['product_id'],
			'product'      => wc_get_product( $old_order_item['product_id'] )
		);

		$this->switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	/**
	 * When the subscription is switched and a new subscription is created
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 * @param $new_order_item
	 * @param $old_order_item
	 */
	public function switched( $subscription, $new_order_item, $old_order_item ){

		global $wpdb;

		$new_subscription = (object) array(
			'id'         => $subscription->get_id(),
			'product_id' => $new_order_item['product_id'],
			'product'    => wc_get_product( $new_order_item['product_id'] ),
		);

		$old_subscription = (object) array(
			'id'         => $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d ", $new_order_item['switched_subscription_item_id'] ) ),
			'product_id' => $old_order_item['product_id'],
			'product'    => wc_get_product( $old_order_item['product_id'] ),
		);

		$this->switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	/**
	 * Handle Subscription Package Switching
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id
	 * @param object $new_subscription
	 * @param object $old_subscription
	 *
	 * @return bool
	 */
	public function switch_package( $user_id, $new_subscription, $old_subscription ){

		global $wpdb;
		$db_table = WPJM_Pack_User::$db_table;

		if( ! $new_subscription->product->is_type( $this->type->sub_type ) ){
			return false;
		}

		// Get the user package
		$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE order_id = %d AND product_id = %d;", $old_subscription->id, $old_subscription->product_id ) );

		if( $user_package ){

			// Give new package to user
			$switching_to_package_id = $this->type->packages->user->give( $user_id, $new_subscription->product_id, $new_subscription->id );

			// Delete the old package
			$wpdb->delete( "{$wpdb->prefix}{$db_table}", array( 'id' => $user_package->id ) );

			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_switched", $user_id, $switching_to_package_id, $new_subscription, $old_subscription );
		}

	}

	/**
	 * Is this a subscription?
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $is_subscription
	 * @param $product_id
	 *
	 * @return bool
	 */
	public function is_subscription( $is_subscription, $product_id ){

		$product = wc_get_product( $product_id );
		if( $product && $product->is_type( $this->type->sub_type ) ){
			$is_subscription = TRUE;
		}

		return $is_subscription;
	}

}