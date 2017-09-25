<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPJM_Pack_WC_Subscriptions_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC_Subscriptions_Resume extends WPJM_Pack_WC_Subscriptions {

	/**
	 * Subscription Renewed
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $subscription \WC_Subscription
	 */
	public function renewed( $subscription ){

		global $wpdb;

		$user_id = $subscription->get_user_id();

		foreach( $subscription->get_items() as $item ) {

			$product  = wc_get_product( $item['product_id'] );
			$pid      = $item['product_id'];
			$db_table = WPJM_Pack_User::$db_table;

			if( ! $product->is_type( $this->type->sub_type ) ){
				return;
			}

			// Get the existing user package
			$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE user_id = %d AND order_id = %d AND product_id = %d;", $user_id, $subscription->get_id(), $pid ) );

			if( $user_package ){

				// Set default update data values
				$update_data = array(
					'view_limit'      => absint( $product->get_view_limit() ),
					'view_used'       => 0,
					'view_posts'      => '',
					'view_name_limit' => absint( $product->get_view_name_limit() ),
					'view_name_used'  => 0,
					'view_name_posts' => '',
					'contact_limit'   => absint( $product->get_contact_limit() ),
					'contact_used'    => 0,
					'contact_posts'   => '',
					'status'          => 'active'
				);

				$update_data = apply_filters( 'job_manager_packages_wc_job_sub_renewal_data_before_rollover', $update_data, $user_id, $user_package, $subscription, $item );

				// Check for VIEW rollovers
				if( $update_data['view_limit'] > 0 && $product->rollover_enabled( 'view' ) ){
					// Get how many unused view credits are available
					$unused_view = absint( $user_package->view_limit ) - absint( $user_package->view_used );

					if( $unused_view > 0 ){
						// Increase view_limit with the number of unused view credits
						$update_data['view_limit'] = absint( $update_data['view_limit'] + $unused_view );
					}
				}

				// Check for VIEW NAME rollovers
				if( $update_data['view_name_limit'] > 0 && $product->rollover_enabled( 'view_name' ) ){
					// Get how many unused view_name credits are available
					$unused_view_name = absint( $user_package->view_name_limit ) - absint( $user_package->view_name_used );

					if( $unused_view_name > 0 ){
						// Increase view_name_limit with the number of unused view_name credits
						$update_data['view_name_limit'] = absint( $update_data['view_name_limit'] + $unused_view_name );
					}
				}

				// Check for CONTACT rollovers
				if( $update_data['contact_limit'] > 0 && $product->rollover_enabled( 'contact' ) ){
					// Get how many unused contact credits are available
					$unused_contact = absint( $user_package->contact_limit ) - absint( $user_package->contact_used );

					if( $unused_contact > 0 ){
						// Increase contact_limit with the number of unused contact credits
						$update_data['contact_limit'] = absint( $update_data['contact_limit'] + $unused_contact );
					}
				}

				$update_data = apply_filters( 'job_manager_packages_wc_job_sub_renewal_data_after_rollover', $update_data, $user_id, $user_package, $subscription, $item );

				$wpdb->update(
					"{$wpdb->prefix}{$db_table}",
					$update_data,
					array(
						'user_id'    => $user_id,
						'order_id'   => $subscription->get_id(),
						'product_id' => $pid
					)
				);

			} else {

				$user_package = $this->type->packages->user->give( $user_id, $pid, $subscription->get_id(), 'woocommerce' );

			}

			do_action( "job_manager_packages_wc_{$this->type->slug}_user_subscription_renewed", $user_id, $item, $subscription, $user_package );

		}

	}
}