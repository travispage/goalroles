<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_WC
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_WC {

	/**
	 * @var \WPJM_Pack_Admin_WC_Job|\WPJM_Pack_Admin_WC_Resume
	 */
	public $admin;
	/**
	 * @var string
	 */
	public $post_type;
	/**
	 * @var string
	 */
	public $slug;
	/**
	 * @var string
	 */
	public $label_fallback;
	/**
	 * @var string
	 */
	public $product_type;
	/**
	 * @var string
	 */
	public $sub_type;
	/**
	 * @var array Meta fields to store in order meta
	 */
	public $meta_fields = array( 'listing_id', 'form_type', 'redirect', 'job_id', 'resume_id' );
	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	protected $type;
	/**
	 * @var string
	 */
	public $who = 'woocommerce';

	/**
	 * WPMJ_Pack_WC constructor.
	 *
	 * @param $type WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public function __construct( $type ){

		$this->type = $type;
		// Init configuration
		$config = $this->init_config();
		// Init hooks or other construct() like handling
		$this->init();

		// Initialize admin class object
		if( is_admin() ){
			$this->admin( $config );
		}

		// Require account on checkout
		add_filter( 'option_woocommerce_enable_guest_checkout', array( $this, 'enable_guest_checkout' ) );
		add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', array( $this, 'enable_signup_and_login_from_checkout' ) );

		add_action( 'woocommerce_thankyou', array( $this, 'thank_you' ), 5 );

		// Displaying user packages on the frontend
		add_action( 'woocommerce_before_my_account', array( $this, 'my_packages' ), 20 );

		// Statuses
		add_action( 'woocommerce_order_status_completed', array( $this, 'order_paid' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'order_paid' ) );

		// Order Meta
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );

		if ( WP_Job_Manager_Packages::is_woocommerce_pre( '3.0.0' ) ) {
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'legacy_order_item_meta' ), 10, 2 );
		} else {
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );
		}

		add_filter( 'woocommerce_get_item_data', array( $this, 'get_cart_item_data' ), 10, 2 );
	}

	/**
	 * Output meta values in cart area
	 *
	 * @since @@since
	 *
	 * @param  array $data
	 * @param  array $cart_item
	 *
	 * @return array
	 */
	public function get_cart_item_data( $data, $cart_item ) {

		if( ! array_key_exists( 'product_id', $cart_item ) ){
			return $data;
		}

		/**
		 * @var $product \WC_Product_Job_Visibility_Package|\WC_Product_Job_Visibility_Subscription|\WC_Product_Resume_Visibility_Package|\WC_Product_Resume_Visibility_Subscription
		 */
		$product = wc_get_product( $cart_item['product_id'] );

		if( ! $product->is_type( $this->product_type ) && ! $product->is_type( $this->sub_type ) ){
			return $data;
		}

		$package_types = $this->type->packages->get_package_types();

		// Loop through each package type setting cart data based on configuration
		foreach( (array) $package_types as $pt_key => $pt_conf ){

			if( ! $product->allow_enabled( $pt_key ) ) {
				continue;
			}

			$type_data = array(
				'name'  => $this->type->packages->get_package_type( $pt_key, 'label' ),
				'value' => $product->is_unlimited( $pt_key ) ? __( 'Unlimited', 'wp-job-manager-packages' ) : $product->get_limit( $pt_key )
			);

			/**
			 * Filter cart item type data
			 *
			 * @since @@since
			 *
			 * @param array  $type_data
			 * @param string $pt_key    Package type array key (browse, view, etc)
			 * @param array  $pt_conf   Package type configuration values
			 * @param array  $data       Existing cart item data array
			 * @param array  $cart_item
			 * @param \WC_Product_Job_Visibility_Package|\WC_Product_Job_Visibility_Subscription|\WC_Product_Resume_Visibility_Package|\WC_Product_Resume_Visibility_Subscription $product
			 */
			$data[] = apply_filters( 'job_manager_packages_wc_get_cart_item_type_data', $type_data, $pt_key, $pt_conf, $data, $cart_item, $product );
		}

		/**
		 * Cart item metadata filter
		 *
		 * @since @@since
		 *
		 * @param array $data
		 * @param array $cart_item
		 * @param \WC_Product_Job_Visibility_Package|\WC_Product_Job_Visibility_Subscription|\WC_Product_Resume_Visibility_Package|\WC_Product_Resume_Visibility_Subscription $product
		 */
		return apply_filters( 'job_manager_packages_wc_get_cart_item_data', $data, $cart_item, $product );
	}

	/**
	 * Get Cart Data from Session
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $cart_item
	 * @param $values
	 *
	 * @return mixed
	 */
	public function get_cart_item_from_session( $cart_item, $values ){

		$fields = apply_filters( 'job_manager_packages_wc_get_cart_item_from_session_fields', $this->meta_fields, $cart_item, $values );

		foreach( (array) $fields as $field ) {
			if( ! empty( $values[$field] ) ){
				$cart_item[$field] = $values[$field];
			}
		}

		return $cart_item;
	}

	/**
	 * Legacy Add Meta to Order
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $item_id
	 * @param $values
	 */
	public function legacy_order_item_meta( $item_id, $values ){

		$fields = apply_filters( 'job_manager_packages_wc_order_item_meta_fields', $this->meta_fields, $item_id, $values );

		foreach( (array) $fields as $field ){
			if( isset( $values[$field] ) ){
				wc_add_order_item_meta( $item_id, "_{$field}", $values[$field] );
			}
		}
	}

	/**
	 * Set the order line item's meta data prior to being saved (WC >= 3.0.0).
	 *
	 * @since @@since
	 *
	 * @param WC_Order_Item_Product $order_item
	 * @param string                $cart_item_key  The hash used to identify the item in the cart
	 * @param array                 $cart_item_data The cart item's data.
	 * @param WC_Order              $order          The order or subscription object to which the line item relates
	 */
	public function checkout_create_order_line_item( $order_item, $cart_item_key, $cart_item_data, $order ) {

		$fields = apply_filters( 'job_manager_packages_wc_checkout_create_order_line_item_fields', $this->meta_fields, $order_item, $cart_item_key, $cart_item_data, $order );

		foreach ( (array) $fields as $field ) {
			if ( isset( $cart_item_data[ $field ] ) ) {
				$order_item->update_meta_data( "_{$field}", $cart_item_data[ $field ] );
			}
		}
	}

	/**
	 * Return Standard and Subscription Types
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_types(){

		if( ! $this->product_type || ! $this->sub_type ){
			$this->init_config();
		}

		return array( $this->product_type, $this->sub_type );
	}

	/**
	 * Check if cart contains standard or subscription visibility package
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function cart_contains_package(){

		global $woocommerce;

		if( ! empty( $woocommerce->cart->cart_contents ) ){
			foreach( $woocommerce->cart->cart_contents as $cart_item ) {
				$product = $cart_item['data'];
				if( $product instanceof WC_Product && $product->is_type( $this->product_type ) && ! $product->is_type( $this->sub_type ) ){
					return TRUE;
				}
			}
		}

	}

	/**
	 * Disable Guest Checkout
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function enable_guest_checkout( $value ){

		if( $this->cart_contains_package() ){
			return 'no';
		} else {
			return $value;
		}
	}

	/**
	 * Allow Signup and Login on Checkout page
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function enable_signup_and_login_from_checkout( $value ){

		remove_filter( 'option_woocommerce_enable_guest_checkout', array( $this, 'enable_guest_checkout' ) );
		$woocommerce_enable_guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );
		add_filter( 'option_woocommerce_enable_guest_checkout', array( $this, 'enable_guest_checkout' ) );

		if( 'yes' === $woocommerce_enable_guest_checkout && $this->cart_contains_package() ){
			return 'yes';
		} else {
			return $value;
		}
	}

	/**
	 * Get Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array $post__in
	 * @param bool  $allows
	 *
	 * @param array $product_types
	 *
	 * @return array
	 */
	public function get_packages( $post__in = array(), $allows = false, $product_types = array() ){

		// If any type passed, set to false to return all
		if( $allows === 'any' ){
			$allows = false;
		}

		if( empty( $product_types ) ){
			$product_types = array( $this->product_type, $this->sub_type );
		}

		$args = array(
			'post_type'        => 'product',
			'posts_per_page'   => - 1,
			'post__in'         => $post__in,
			'order'            => 'asc',
			'orderby'          => 'menu_order',
			'suppress_filters' => FALSE,
			'tax_query'        => WC()->query->get_tax_query( array(
				'relation'     => 'AND',
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $product_types,
					'operator' => 'IN',
				)
			) ),
			'meta_query'       => WC()->query->get_meta_query(),
		);

		if ( ! empty( $allows ) ) {

			$args['meta_query']['relation'] = 'AND';

			$slug = $this->type->slug;

			foreach ( (array) $allows as $allow ) {
				$args['meta_query'][] = array(
					'key'     => "_allow_{$slug}_{$allow}",
					'value'   => 'yes',
					'compare' => '='
				);
			}
		}

		$args = apply_filters( 'job_manager_packages_wc_get_packages_args', $args );
		$results = get_posts( $args );

		return $results;
	}

	/**
	 * Delayed Install of WooCommerce Terms
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public static function delayed_install(){

		// Only add if product_type taxonomy exists (meaning WC may not be installed), as this method is called by install class which does not check if WC is installed
		if( ! taxonomy_exists( 'product_type' ) ){
			return;
		}

		if( ! get_term_by( 'slug', sanitize_title( 'job_visibility_package' ), 'product_type' ) ){
			wp_insert_term( 'job_visibility_package', 'product_type' );
		}
		if( ! get_term_by( 'slug', sanitize_title( 'resume_visibility_package' ), 'product_type' ) ){
			wp_insert_term( 'resume_visibility_package', 'product_type' );
		}
		if( ! get_term_by( 'slug', sanitize_title( 'job_visibility_subscription' ), 'product_type' ) ){
			wp_insert_term( 'job_visibility_subscription', 'product_type' );
		}
		if( ! get_term_by( 'slug', sanitize_title( 'resume_visibility_subscription' ), 'product_type' ) ){
			wp_insert_term( 'resume_visibility_subscription', 'product_type' );
		}

	}

	/**
	 * Process/Handle Form Submission
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $package_id
	 * @param array $meta
	 */
	public function process_form( $package_id, $meta = array() ){

		// Add package to the cart
		WC()->cart->add_to_cart( $package_id, 1, '', '', $meta );

		// Enable/Add "added to cart" message
		wc_add_to_cart_message( $package_id );

		do_action( 'job_manager_packages_wc_process_form_before_redirect', $package_id, $meta );

		// Redirect to checkout page
		wp_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );
		exit;

	}

	/**
	 * WooCommerce Order Completed/Paid
	 *
	 * This method handles adding the package to the user package table after an order is
	 * marked as completed/paid.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $order_id
	 */
	public function order_paid( $order_id ){

		// Get the order
		$order = wc_get_order( $order_id );

		if( get_post_meta( $order_id, "wpjmpack_{$this->type->slug}_package_process_complete", TRUE ) ){
			return;
		}

		foreach( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if( $order->get_customer_id() && $product->is_type( $this->type->handler->product_type ) ){

				$user_package_ids = array();

				// Give packages to user
				for( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id[] = $this->type->packages->user->give( $order->get_customer_id(), $product->get_id(), $order_id, 'woocommerce' );
				}

				update_post_meta( $order_id, '_jmpack_user_package_ids', $user_package_ids );

				if( isset( $item['listing_id'] ) ){
					// do something

				}
			}
		}

		update_post_meta( $order_id, "wpjmpack_{$this->type->slug}_package_process_complete", TRUE );

		do_action( 'job_manager_packages_wc_order_paid', $order_id, $order );
	}

	/**
	 * Thank You Notice
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $order_id
	 */
	public function thank_you( $order_id ){

		global $wp_post_types;

		$order = wc_get_order( $order_id );

		foreach( $order->get_items() as $item ) {

			$product = wc_get_product( $item['product_id'] );

			if( $product->is_type( $this->type->handler->product_type ) || $product->is_type( $this->type->handler->sub_type ) ){

				if( ! empty( $item['redirect'] ) ){

					$redirect = esc_url_raw( $item['redirect'] );
					$redirect_to = __( 'page', 'wp-job-manager-packages' );

				} elseif( isset( $item['listing_id'] ) && 'publish' === get_post_status( $item['listing_id'] ) ){

					if( in_array( get_post_type( $item['listing_id'] ), array( 'job_listing', 'resume' ), false ) ){
						$redirect_to = __( 'listing', 'wp-job-manager-packages' );
					} else {
						$redirect_to = __( 'page', 'wp-job-manager-packages' );
					}

					$redirect = get_permalink( $item['listing_id'] );
				}

				if( isset( $redirect, $redirect_to ) ){

					$thank_you_msg = apply_filters( 'job_manager_packages_wc_thank_you_redirect_message', sprintf( __( 'To go back to the %1$s you were previously viewing, <a href="%2$s">click here</a>.', 'wp-job-manager-packages' ), $redirect_to, $redirect ), $item, $redirect_to, $redirect, $product );

					echo "<div class=\"job-manager-message\">{$thank_you_msg}</div>";
				}

				do_action( 'job_manager_packages_wc_thank_you_after', $item, $product, $order_id, $order );

			}

		}
	}

	/**
	 * Return Product
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $package
	 *
	 * @return \WC_Product_Job_Visibility_Package|\WC_Product_Resume_Visibility_Package|\WC_Product
	 */
	public function get_product( $package ){
		return wc_get_product( $package );
	}

	/**
	 * Add Table Classes for WooCommerce
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $classes
	 * @param $enable_sui
	 *
	 * @return string
	 */
	public function table_classes( $classes, $enable_sui ){

		if( ! empty( $enable_sui ) ){
			return $classes;
		} else {
			return $classes . ' shop_table';
		}

	}

	/**
	 * Output My Packages Table
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool $shortcode
	 */
	public function my_packages( $shortcode = false ){

		$slug = $this->type->slug;

		if( ! $shortcode && ! get_option( "job_manager_{$slug}_visibility_dashboard_my_account", true ) ){
			return;
		}


		if( ( $packages = WPJM_Pack_User::get_all( get_current_user_id(), $this->type->post_type ) ) && is_array( $packages ) && count( $packages ) > 0 ){

			wp_enqueue_script( 'jmpack-sui' );
			wp_enqueue_style( 'jmpack-sui' );

			//if( get_option( "job_manager_{$slug}_visibility_sui_table_color_details", TRUE ) ){
			//	wp_enqueue_script( 'jmpack-sui-colordetails' );
			//}

			$enable_sui  = get_option( "job_manager_{$slug}_visibility_use_semantic", TRUE );
			$table_color = get_option( "job_manager_{$slug}_visibility_sui_table_color", 'black' );
			$sui         = apply_filters( "job_manager_packages_my_packages_{$slug}_table_classes", ( ! empty( $enable_sui ) ? 'ui' : 'no-ui' ), $enable_sui );

			if( empty( $enable_sui ) ){
				// Load theme specific CSS if Semantic UI is disabled
				wp_enqueue_style( 'jmpack-theme-noui' );
			} else {
				wp_enqueue_style( 'jmpack-theme-ui' );
			}

			$located = wc_locate_template( "my-{$slug}-packages.php", 'jm_packages/', WP_Job_Manager_Packages::dir( '/templates/' ) );

			if( file_exists( $located ) ){
				$filename = "my-{$slug}-packages.php";
			} else {
				$filename = 'my-packages.php';
			}

			wc_get_template( $filename, array( 'slug' => $slug,
			                                               'sui'           => $sui,
			                                               'table_color'   => $table_color,
			                                               'enable_sui'    => $enable_sui,
			                                               'packages'      => $packages,
			                                               'post_type'     => $this->type->post_type,
			                                               'package_types' => $this->type->packages->get_package_types()
			), 'jm_packages/', WP_Job_Manager_Packages::dir( '/templates/' ) );

		} else {

			do_action( "job_manager_packages_my_packages_{$slug}_none_found" );

		}

	}
}