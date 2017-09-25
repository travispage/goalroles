<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPJM_Pack_WC_Subscriptions_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC_Subscriptions_Job extends WPJM_Pack_WC_Subscriptions {

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

			$product   = wc_get_product( $item['product_id'] );
			$pid       = $item['product_id'];
			$db_table  = WPJM_Pack_User::$db_table;

			if( ! $product->is_type( $this->type->sub_type ) ){
				return;
			}

			// Get the existing user package
			$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE user_id = %d AND order_id = %d AND product_id = %d;", $user_id, $subscription->get_id(), $pid ) );

			if( $user_package ){

				// Set default update data values
				$update_data = array(
					'view_limit'  => $product->get_view_limit(),
					'view_used'   => 0,
					'view_posts'  => '',
					'apply_limit' => $product->get_apply_limit(),
					'apply_used'  => 0,
					'apply_posts' => '',
				    'status'      => 'active'
				);

				$update_data = apply_filters( 'job_manager_packages_wc_job_sub_renewal_data_before_rollover', $update_data, $user_id, $user_package, $subscription, $item );

				// Check for VIEW rollovers
				if( $product->rollover_enabled( 'view' ) && $update_data['view_limit'] > 0 ){
					// Get how many unused view credits are available
					$unused_view = absint( $user_package->view_limit ) - absint( $user_package->view_used );

					if( $unused_view > 0 ){
						// Increase view_limit with the number of unused view credits
						$update_data['view_limit'] = absint( $update_data['view_limit'] + $unused_view );
					}
				}

				// Check for APPLY rollovers
				if( $product->rollover_enabled( 'apply' ) && $update_data['apply_limit'] > 0 ){
					// Get how many unused apply credits are available
					$unused_apply = absint( $user_package->apply_limit ) - absint( $user_package->apply_used );

					if( $unused_apply > 0 ){
						// Increase apply_limit with the number of unused apply credits
						$update_data['apply_limit'] = absint( $update_data['apply_limit'] + $unused_apply );
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