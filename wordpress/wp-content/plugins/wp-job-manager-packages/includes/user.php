<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_User
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_User {

	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public $type;
	/**
	 * @var string Datable Table
	 */
	public static $db_table = 'wpjmpack_user_packages';

	/**
	 * WPJM_Pack_User constructor.
	 */
	public function __construct( $type ){

		$this->type = $type;

		// User deletion
		add_action( 'delete_user', array( $this, 'delete_user_packages' ) );
	}

	/**
	 * Add Post and Post Details to User Package
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param integer $user_id              User ID associated with package
	 * @param integer $user_package_id      User package ID (ID column in table) to update
	 * @param string $type                  Type to use and update (view, apply, etc)
	 * @param integer $post_id              Post ID to add to the posts array
	 *
	 * @return mixed
	 */
	public function add_post( $user_id, $user_package_id, $type, $post_id ){
		global $wpdb;

		$user_db_table = self::$db_table;

		if( ! $this->is_valid( $user_id, $user_package_id, $type ) ){
			return new WP_Error( __( 'This is not a valid package, or is an unlimited package.', 'wp-job-manager-packages' ) );
		}

		$packages = self::get( $user_id );

		$type_used = "{$type}_used";
		$type_posts = "{$type}_posts";

		// Set initial values in case passed package ID does not exist
		$new_used = 1;
		$posts = array();

		if( isset( $packages[ $user_package_id ] ) ){
			$new_used = $packages[ $user_package_id ]->$type_used + 1;
			$posts = ! empty( $packages[$user_package_id]->$type_posts ) ? maybe_unserialize( $packages[$user_package_id]->$type_posts ) : array();
		}

		// Add post data to $posts array
		$posts["{$post_id}"] = array(
			'post_id' => $post_id,
			'added'   => time()
		);

		$posts = apply_filters( 'job_manager_packages_user_add_post_posts', $posts, $user_id, $user_package_id, $type );
		$new_used = apply_filters( 'job_manager_packages_user_add_post_new_used', $new_used, $user_id, $user_package_id, $type );

		//if( defined( 'WP_DEBUG' ) && WP_DEBUG == true ){
		//	$wpdb->show_errors();
		//}

		$update = $wpdb->update( "{$wpdb->prefix}{$user_db_table}",
			array(
				$type_posts => maybe_serialize( $posts ),
				$type_used => $new_used,
			),
			array(
				'user_id' => $user_id,
				'id'      => $user_package_id
			),
			array( '%s', '%d' ),
			array( '%d', '%d' )
		);

		//if( defined( 'WP_DEBUG' ) && WP_DEBUG == TRUE ){
		//	$wpdb->print_error();
		//}

		return $update;
	}

	/**
	 * Verify Package is Valid for Type
	 *
	 * This method verifies that the passed type is a limit type, for the specified package,
	 * and that the limit has not been reached (or is unlimited)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 * @param $user_package_id
	 * @param $type
	 *
	 * @return bool
	 */
	public function is_valid( $user_id, $user_package_id, $type ){

		global $wpdb;
		$user_db_table = self::$db_table;

		// False by default that package is not valid
		$is_valid = false;

		$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$user_db_table} WHERE user_id = %d AND id = %d AND status = %s;", $user_id, $user_package_id, 'active' ) );

		$type_limit = "{$type}_limit";
		$type_used  = "{$type}_used";

		// If a package was found
		if( $package ){
			// And the limit is set to 0 (means its unlimited), or there are still credits available up to the limit
			if( $package->$type_limit == 0 || ( (int) $package->$type_used < (int) $package->$type_limit ) ){
				$is_valid = TRUE;
			}
		}

		return apply_filters( 'job_manager_packages_user_package_is_valid', $is_valid, $user_id, $user_package_id, $type, $this->type );
	}

	/**
	 * Give User Package
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id       User ID to give package to
	 * @param int    $product_id    Associated product ID
	 * @param int    $order_id      Order ID
	 * @param string $handler       Checkout handler to associate the package with (default: woocommerce)
	 *
	 * @return bool|int
	 */
	public function give( $user_id, $product_id, $order_id = 0, $handler = 'woocommerce' ){

		global $wpdb;
		$user_db_table = self::$db_table;

		$package = wc_get_product( $product_id );

		if( ! $package->is_type( $this->type->handler->product_type ) && ! $package->is_type( $this->type->handler->sub_type ) ){
			return FALSE;
		}

		$insert_array = $this->type->packages->get_insert_array( $package, array(
			                                                 'user_id'      => $user_id,
			                                                 'product_id'   => $product_id,
			                                                 'order_id'     => $order_id,
			                                                 'handler'      => $handler,
		                                                     'status'       => 'active',
		                                                 )
		);

		$insert_array = apply_filters( 'job_manager_packages_give_user_package_insert_data', $insert_array, $user_id, $this->type );

		$wpdb->insert( "{$wpdb->prefix}{$user_db_table}", $insert_array );

		do_action( 'job_manager_packages_user_give_user_package_after', $user_id, $product_id, $order_id, $handler, $wpdb->insert_id );

		return $wpdb->insert_id;
	}

	/**
	 * Check for Package based user permissions
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function can( $type ){

		$pkgs = self::get( get_current_user_id(), $this->type->post_type, $type, $this->type->handler->who );

		if( empty( $pkgs ) ){
			return FALSE;
		}

		// Browse does not have limits, so if a pkg was returned that means allowed to browse
		if( $type === 'browse' ){
			return TRUE;
		}

		foreach( (array) $pkgs as $pkg ) {

			$limit_val = $pkg->{$type . '_limit'};

			// Check if this is an unlimited package
			if( empty( $limit_val ) && is_numeric( $limit_val ) ){
				return TRUE;
			}

			// Check if current post ID is associated with this pkg
			$posts = maybe_unserialize( $pkg->{$type . '_posts'} );

			if( ! empty( $posts ) && array_key_exists( get_the_ID(), $posts ) ){
				// User has already approved current listing and has already been deducted from limit
				return TRUE;
			}

		}

		return FALSE;
	}

	/**
	 * Get All User Packages
	 *
	 * @since 1.0.0
	 *
	 * @param        $user_id
	 * @param string $package_type Package type as defined in the `package_type` column (job_visibility_package, resume_visibility_package)
	 * @param string $handler
	 *
	 * @return array|bool|null|object
	 */
	public static function get_all( $user_id, $package_type = '', $handler = 'woocommerce' ){

		if( empty( $user_id ) && ! $user_id = get_current_user_id() ){
			return FALSE;
		}

		global $wpdb;
		$user_db_table = self::$db_table;

		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$package_type = is_string( $package_type ) && ! empty( $package_type ) ? array( $package_type ) : array( 'job_listing', 'resume' );

		$query = $wpdb->prepare(
			"
			SELECT * FROM {$wpdb->prefix}{$user_db_table} 
			WHERE user_id = %d 
			AND `handler` = %s 
			AND status = %s 
			AND package_type IN ( '" . implode( "','", $package_type ) . "' );", $user_id, $handler, 'active' );

		//$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$user_db_table} WHERE user_id = %d AND `handler` = %s AND package_type IN ( '" . implode( "','", $package_type ) . "' ) AND ( {$type_used} < {$type_limit} OR {$type_limit} = 0 );", $user_id, $handler );
		$packages = $wpdb->get_results( $query, OBJECT_K );

		return apply_filters( 'job_manager_packages_user_get_all_packages', $packages, $user_id, $package_type, $handler );
	}

	/**
	 * Get User Allow Packages
	 *
	 * Returns any user packages for passed user ID, that are/have enabled/allow in the package configuration,
	 * regardless of the limit configurations.  This is useful to determine if a user has a package that may already
	 * have reached its limit, and check if listing ID they are trying to view, is one of the IDs already used in the
	 * limit package.
	 *
	 * @since 1.0.0
	 *
	 * @param        $user_id
	 * @param string $package_type Package type as defined in the `package_type` column (job_visibility_package, resume_visibility_package)
	 * @param string $type         Table column to use for returning user packages
	 * @param string $handler
	 *
	 * @return array|bool|null|object
	 */
	public static function get( $user_id, $package_type = '', $type = 'view', $handler = 'woocommerce' ){

		if( empty( $user_id ) && ! $user_id = get_current_user_id() ){
			return FALSE;
		}

		global $wpdb;
		$user_db_table = self::$db_table;

		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$package_type = is_string( $package_type ) && ! empty( $package_type ) ? array( $package_type ) : array( 'job_listing', 'resume' );

		// Limit types that should also check limits (0 for unlimited)
		//$limit_types = in_array( $type, array( 'view_name', 'apply', 'contact', 'view' ), FALSE ) ? " AND ( {$type}_used < {$type}_limit OR {$type}_limit = 0 )" : '';

		$query = $wpdb->prepare(
			"
			SELECT * FROM {$wpdb->prefix}{$user_db_table} 
			WHERE user_id = %d 
			AND `handler` = %s 
			AND status = %s 
			AND package_type IN ( '" . implode( "','", $package_type ) . "' ) 
			AND `allow_{$type}` = 1;", $user_id, $handler, 'active', $type );

		//$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$user_db_table} WHERE user_id = %d AND `handler` = %s AND package_type IN ( '" . implode( "','", $package_type ) . "' ) AND ( {$type_used} < {$type_limit} OR {$type_limit} = 0 );", $user_id, $handler );
		$packages = $wpdb->get_results( $query, OBJECT_K );

		return apply_filters( 'job_manager_packages_user_get_packages', $packages, $user_id, $package_type, $handler, $type );
	}

	/**
	 * Get Use-able User Packages
	 *
	 * Returns any limit user packages for passed user ID, that are/have enabled/allow in the package configuration,
	 * for the passed type, and have not reached their limit yet.
	 *
	 * @since 1.0.0
	 *
	 * @param        $user_id
	 * @param string $package_type Package type as defined in the `package_type` column (job_visibility_package, resume_visibility_package)
	 * @param string $type         Table column to use for returning user packages
	 * @param string $handler
	 *
	 * @return array|bool|null|object
	 */
	public static function get_useable( $user_id, $package_type = '', $type = 'view', $handler = 'woocommerce' ){

		if( empty( $user_id ) ){
			return FALSE;
		}

		global $wpdb;
		$user_db_table = self::$db_table;

		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$package_type = is_string( $package_type ) && ! empty( $package_type ) ? array( $package_type ) : array( 'job_listing', 'resume' );

		// Limit types that should also check limits (0 for unlimited)
		$limit_types = in_array( $type, array( 'view_name', 'apply', 'contact', 'view' ), FALSE ) ? " AND ( {$type}_used < {$type}_limit )" : '';

		$query = $wpdb->prepare(
			"
			SELECT * FROM {$wpdb->prefix}{$user_db_table} 
			WHERE user_id = %d 
			AND `handler` = %s 
			AND status = %s 
			AND package_type IN ( '" . implode( "','", $package_type ) . "' ) 
			AND `allow_{$type}` = 1 {$limit_types};", $user_id, $handler, 'active', $type );

		//$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$user_db_table} WHERE user_id = %d AND `handler` = %s AND package_type IN ( '" . implode( "','", $package_type ) . "' ) AND ( {$type_used} < {$type_limit} OR {$type_limit} = 0 );", $user_id, $handler );
		$packages = $wpdb->get_results( $query, OBJECT_K );

		return apply_filters( 'job_manager_packages_user_get_useable_packages', $packages, $user_id, $package_type, $handler, $type );
	}

	/**
	 * Delete any user packages when user is removed
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 */
	public function delete_user_packages( $user_id ){

		global $wpdb;
		$db_table = self::$db_table;

		$user_id = apply_filters( 'job_manager_packages_user_delete_packages_user_deleted', $user_id );

		if( $user_id ){
			$wpdb->delete(
				"{$wpdb->prefix}{$db_table}",
				array(
					'user_id' => $user_id
				)
			);
		}
	}
}
