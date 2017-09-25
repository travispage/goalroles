<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Packages
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Packages {

	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public $type;
	/**
	 * @var \WPJM_Pack_User
	 */
	public $user;

	/**
	 * WPJM_Pack_Packages constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {

		$this->type = $type;
		$this->user = new WPJM_Pack_User( $type );
	}

	/**
	 * Get Package Limit
	 *
	 * This method will return the package limit (or unlimited), when either a post object is passed,
	 * or by using the global $post, and then global $wpjm_pack_post to attempt to get values.  Useful
	 * for things like shortcodes, etc, when you may not have the post object available.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param      $type
	 * @param bool $post_object
	 *
	 * @return mixed
	 */
	public function get_package_limit( $type, $post_object = false ){
		global $post, $wpjm_pack_post;

		$limit = '';

		if( ! empty( $post_object ) && is_object( $post_object ) ){
			$post = $post_object;
		}

		// First try global $post object
		$product = $this->type->handler->get_product( $post );

		// If that didn't work, try our own custom $wpjm_pack_post global
		if( ! is_object( $product ) ){
			$product = $this->type->handler->get_product( $wpjm_pack_post );
		}

		if( is_object( $product ) ){
			$limit = $product->get_limit( $type );

			if( empty( $limit ) && is_numeric( $limit ) ){
				$limit = __('unlimited', 'wp-job-manager-packages');
			}
		}

		return apply_filters( 'job_manager_packages_get_package_limit', $limit, $type, $post, $product );
	}

	/**
	 * Get Package Price HTML
	 *
	 * This method returns HTML to output the price for a specific package.  If a post object is
	 * not passed, the global $post will be tried first, and then the global $wpjm_pack_post.
	 * This is useful for things like shortcodes, etc.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool $post_object
	 *
	 * @return mixed
	 */
	public function get_package_price_html( $post_object = false ){
		global $post, $wpjm_pack_post;

		if( ! empty( $post_object ) && is_object( $post_object ) ){
			$post = $post_object;
		}

		$price_html = '';

		// First try global $post object
		$product = $this->type->handler->get_product( $post );

		// If that didn't work, try our own custom $wpjm_pack_post global
		if( ! is_object( $product ) ){
			$product = $this->type->handler->get_product( $wpjm_pack_post );
		}

		if( is_object( $product ) ){
			$price_html = $product->get_price_html();
		}

		// Return empty string as last resort
		return apply_filters( 'job_manager_packages_get_package_price_html', $price_html, $post, $product );
	}

	/**
	 * Return Package Type Config Value
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @param string $key
	 * @param bool   $lowercase
	 *
	 * @return string
	 */
	public function get_package_type( $type, $key = '', $lowercase = false ){

		$types = $this->get_package_types();

		if( isset( $types[ $type ] ) ){

			$return_val = $types[ $type ];

			if( ! empty( $key ) ){

				if( ! empty( $types[$type][$key] ) ){
					$return_val = $types[$type][$key];
				} elseif( $key === 'verb' ) {
					$return_val = $types[$type]['label'];
				} else {
					$return_val = $type;
				}

			}

			return $lowercase ? strtolower( $return_val ) : $return_val;

		} else {

			return $type;

		}

	}

	/**
	 * Return Package Type Config
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_package_type_config( $type ){

		$types = $this->get_package_types();

		if( isset( $types[$type] ) ){

			return $types[$type];

		} else {

			return array();

		}

	}
}
