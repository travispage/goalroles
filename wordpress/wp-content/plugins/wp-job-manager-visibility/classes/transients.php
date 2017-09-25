<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Transients {

	protected $prefix;
	protected $cache_prefix = "jmvc_";

	public function __construct() { }

	/**
	 * Return if Cache is Enabled
	 *
	 *
	 * @since 1.4.0
	 *
	 * @return mixed|void
	 */
	function cache_enabled(){

		return get_option( 'jmv_enable_cache', true );

	}

	/**
	 * Get Cached Value
	 *
	 * Will return cached value, or false if cache does not exist.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param string $append
	 *
	 * @return bool|mixed
	 */
	function get( $append = '' ){

		if( ! $this->cache_enabled() || isset( $_GET[ 'no_cache' ] ) ) return false;

		$check = get_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" );

		if( $check === FALSE ) return false;

		return $check;
	}

	/**
	 * Set Cache Value
	 *
	 * Set cache value from data
	 *
	 * @since 1.4.0
	 *
	 * @param  string $append
	 * @param         $data
	 * @param  null   $expire
	 *
	 * @return bool     False if value was not set and true if value was set.
	 */
	function set( $append = '', $data, $expire = null ){

		if ( ! $this->cache_enabled() || isset( $_GET['no_cache'] ) ) return FALSE;

		// Default expiration
		if( ! $expire ) $expire = ($default_expire = get_option('jmv_cache_expiration')) === FALSE ? 4 * WEEK_IN_SECONDS: $default_expire;

		return set_transient( "{$this->cache_prefix}{$this->prefix}_{$append}", $data, $expire );

	}

	/**
	 * Remove Cache Value
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param string $append
	 *
	 * @return bool
	 */
	function remove( $append = '' ){

		return delete_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" );

	}

	/**
	 * Count Cached Values
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param bool $with_timeout Count caches with timeouts (Default: true)
	 *
	 * @return int
	 */
	function count( $with_timeout = TRUE ){

		global $wpdb;

		$prefix = esc_sql( "{$this->cache_prefix}{$this->prefix}" );
		$timeout = $with_timeout ? '_timeout' : '';

		$options = $wpdb->options;

		$t = esc_sql( "_transient{$timeout}_{$prefix}%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		return count( $transients );
	}

	/**
	 * Purge All Cached Values
	 *
	 * By default this will purge all cached values with expirations (caches should have an expiration).
	 * Call with $set_timeout as FALSE to purge all caches without an expiration.  Purge is called from
	 * settings both with and without timeout when purge/clear all cache values.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param bool $with_timeout    Whether or not to purge values without a timeout
	 */
	function purge( $with_timeout = true ) {

		global $wpdb;

		$prefix = esc_sql( "{$this->cache_prefix}{$this->prefix}" );
		$timeout = $with_timeout ? '_timeout' : '';

		$options = $wpdb->options;

		$t = esc_sql( "_transient{$timeout}_{$prefix}%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		// For each transient...
		foreach ( $transients as $transient ) {

			// Strip away the WordPress prefix in order to arrive at the transient key.
			$key = str_replace( "_transient{$timeout}_", '', $transient );

			// Now that we have the key, use WordPress core to the delete the transient.
			delete_transient( $key );

		}

		// But guess what?  Sometimes transients are not in the DB, so we have to do this too:
		wp_cache_flush();
	}

}