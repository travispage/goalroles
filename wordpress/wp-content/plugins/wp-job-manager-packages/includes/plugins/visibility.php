<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Plugins_Visibility
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Plugins_Visibility {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;
	/**
	 * @var \WPJM_Pack_Admin_Plugins_Visibility
	 */
	public $admin;

	/**
	 * WPJM_Pack_Plugins_Visibility constructor.
	 *
	 * @param $core WP_Job_Manager_Packages
	 */
	public function __construct( $core ){
		$this->core = $core;

		if( is_admin() ){
			$this->admin = new WPJM_Pack_Admin_Plugins_Visibility( $this );
		}

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		// User method give package (and admin area give package)
		add_action( 'job_manager_packages_user_give_user_package_after', array( $this, 'purge_cache' ), 10, 5 );

		// Ended WC Subscription
		add_action( 'job_manager_packages_wc_job_user_subscription_ended', array( $this, 'subscription_purge' ), 10, 3 );
		add_action( 'job_manager_packages_wc_resume_user_subscription_ended', array( $this, 'subscription_purge' ), 10, 3 );
		// On Hold WC Subscription
		add_action( 'job_manager_packages_wc_job_user_subscription_on-hold', array( $this, 'subscription_purge' ), 10, 3 );
		add_action( 'job_manager_packages_wc_resume_user_subscription_on-hold', array( $this, 'subscription_purge' ), 10, 3 );
		// Reactivated WC Subscription
		add_action( 'job_manager_packages_wc_job_user_subscription_reactivated', array( $this, 'subscription_purge' ), 10, 3 );
		add_action( 'job_manager_packages_wc_resume_user_subscription_reactivated', array( $this, 'subscription_purge' ), 10, 3 );
		// Renewed WC Subscription
		add_action( 'job_manager_packages_wc_job_user_subscription_renewed', array( $this, 'subscription_purge' ), 10, 3 );
		add_action( 'job_manager_packages_wc_resume_user_subscription_renewed', array( $this, 'subscription_purge' ), 10, 3 );

		// Admin Update User Package
		add_action( 'job_manager_packages_user_update_user_package', array( $this, 'purge_cache' ), 10, 5 );
		add_filter( 'query', array( $this, 'query' ) );
	}

	/**
	 * Purge Cache from Subscriptions Action Hook
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 * @param $subscription
	 * @param $product
	 */
	public function subscription_purge( $user_id, $subscription, $product ){

		$this->purge_cache( $user_id );

	}

	/**
	 * Filter on WP Query
	 *
	 * This method checks for any WCPL queries to INSERT or UPDATE, and then
	 * purges group caches.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function query( $query ){

		if( strpos( $query, 'wcpl_user_packages' ) !== FALSE && strpos( $query, 'SELECT ' ) === FALSE ){

			if( strpos( $query, 'UPDATE' ) !== FALSE ){
				$this->purge_cache();
			}

			if( strpos( $query, 'INSERT INTO' ) !== FALSE  ){
				// Purge all group cache on WCPL INSERT or UPDATE
				$this->purge_cache();
			}


		}

		return $query;
	}

	/**
	 * Purge User Group Cache (or all Group Cache if no user_id passed)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 * @param $product_id
	 * @param $order_id
	 * @param $handler
	 * @param $insert_id
	 *
	 * @return bool
	 */
	public function purge_cache( $user_id = '', $product_id = '', $order_id = '', $handler = '', $insert_id = ''){


		if( class_exists( 'WP_Job_Manager_Visibility_User_Transients' ) ){

			if( ! empty( $user_id ) && is_numeric( $user_id ) ){
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->remove_group( $user_id );
				return true;
			} else {
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge_group();
				return true;
			}

		}

		return false;
	}

	/**
	 * Add Filter after Plugins Loaded
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function plugins_loaded(){

		if( defined( 'JOB_MANAGER_VISIBILITY_VERSION' ) && version_compare( JOB_MANAGER_VISIBILITY_VERSION, '1.4.2', 'lt' ) ){
			// Required for older versions of Visibility plugin which do not have filter yet (<= 1.4.1)
			add_filter( 'pre_get_posts', array( $this, 'get_user_groups_pre_query' ), 10 );
		} else {
			add_filter( 'jmv_groups_get_user_groups_args', array( $this, 'get_user_groups' ), 10, 2 );
		}
	}

	/**
	 * Return User Package Parent IDs
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public function get_user_packages( $user_id ){

		$handler = $this->core->job->handler->who;

		$user_pkgs_parent_ids = array();

		$job_packages    = WPJM_Pack_User::get_all( $user_id, 'job_listing', $handler );
		$resume_packages = WPJM_Pack_User::get_all( $user_id, 'resume', $handler );


		if( ! empty( $job_packages ) ){

			foreach( (array) $job_packages as $job_package ) {
				$user_pkgs_parent_ids[] = $job_package->product_id;
			}

		}

		if( ! empty( $resume_packages ) ){

			foreach( (array) $resume_packages as $resume_package ) {
				$user_pkgs_parent_ids[] = $resume_package->product_id;
			}

		}

		// Support for WooCommerce Paid Listings User Packages
		if( function_exists( 'wc_paid_listings_get_user_packages' ) ){
			$wcpl_job_packages = wc_paid_listings_get_user_packages( $user_id, 'job_listing' );
			$wcpl_resume_packages = wc_paid_listings_get_user_packages( $user_id, 'resume' );

			if( ! empty( $wcpl_job_packages ) ){

				foreach( (array) $wcpl_job_packages as $wcpl_job_package ) {
					$user_pkgs_parent_ids[] = $wcpl_job_package->product_id;
				}

			}

			if( ! empty( $wcpl_resume_packages ) ){

				foreach( (array) $wcpl_resume_packages as $wcpl_resume_package ) {
					$user_pkgs_parent_ids[] = $wcpl_resume_package->product_id;
				}

			}
		}

		return $user_pkgs_parent_ids;
	}

	/**
	 * Filter on Groups Query to add Group Packages meta query
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function get_user_groups( $args, $user_id ){

		// Get user packages parent IDs
		$user_packages = $this->get_user_packages( $user_id );

		// If there are any, set an additional meta query for group packages
		if( ! empty( $user_packages ) ){
			$args['meta_query'][] = array(
				'key'     => 'group_packages',
				'value'   => $user_packages,
				'compare' => 'IN'
			);
		}

		return $args;
	}

	/**
	 * Filter on WP_Query for legacy versions of Visibility
	 *
	 * This method is called by the pre_get_posts filter on WP_Query, which is used for older versions
	 * of the visibility plugin to add a meta query for package IDs to return.  This is not needed in
	 * WPJM Visibility >= 1.4.2
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $wpq WP_Query
	 */
	public function get_user_groups_pre_query( $wpq ){

		// Verify correct post type in query
		if( $wpq->get( 'post_type' ) !== 'visibility_groups' ){
			return;
		}

		$mq = $wpq->get( 'meta_query' );

		// Check if meta query isn't set, or the first meta query is not `group_users` ... (meta_query[1] should be `group_roles`)
		if( empty( $mq ) || empty( $mq[0] ) || ! array_key_exists( 'key', $mq[0] ) || ! array_key_exists( 'value', $mq[0] ) || $mq[0]['key'] !== 'group_users' ){
			return;
		}

		// Check if group_packages meta query is already set
		if( isset( $mq[2] ) && $mq[2]['key'] === 'group_packages' ){
			return;
		}

		if( empty( $mq[0]['value'] ) ){
			return;
		}

		// Get user ID from meta query value
		$user_id = $mq[0]['value'];

		// Get user packages parent IDs
		$user_packages = $this->get_user_packages( $user_id );

		// If there are any, set an additional meta query for group packages
		if( ! empty( $user_packages ) ){
			$mq[] = array(
				'key'     => 'group_packages',
				'value'   => $user_packages,
				'compare' => 'IN'
			);

			// Set back the meta_query with our added meta query for packages
			$wpq->set( 'meta_query', $mq );
		}

	}
}
