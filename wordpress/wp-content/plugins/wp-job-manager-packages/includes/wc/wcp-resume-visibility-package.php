<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WC_Product_Resume_Visibility_Package
 *
 * @since 1.0.0
 *
 */
class WC_Product_Resume_Visibility_Package extends WC_Product {

	/**
	 * WC_Product_Resume_Visibility_Package constructor.
	 *
	 * @param int|object|\WC_Product $product
	 */
	public function __construct( $product ) {
		$this->product_type = 'resume_visibility_package';
		parent::__construct( $product );
	}

	/**
	 * Sold Individually
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function is_sold_individually(){

		return apply_filters( 'jmpack_' . $this->product_type . '_is_sold_individually', TRUE );
	}

	/**
	 * Add to Cart URL
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function add_to_cart_url(){

		$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Add to Cart Text
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function add_to_cart_text(){

		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Add to cart', 'wp-job-manager-packages' ) : __( 'Read More', 'wp-job-manager-packages' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Always Purchasable
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_purchasable(){

		return TRUE;
	}

	/**
	 * Always Virtual
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_virtual(){

		return TRUE;
	}

	/**
	 * Check if Allow Enabled
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function allow_enabled( $type ){

		if( $this->get_meta( "_allow_resume_{$type}" ) === 'yes' ){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check if Has Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function has_limit( $type ){

		if( $this->get_meta( "_{$type}_resume_limit" ) && $this->get_meta( "_allow_resume_{$type}" ) === 'yes' ){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check if Unlimited
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function is_unlimited( $type ){

		return ! $this->has_limit( $type );
	}

	/**
	 * Get Limit Value
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return int|mixed
	 */
	public function get_limit( $type ){

		$allow_view_type = $this->get_meta( "_allow_resume_{$type}" );
		$limit_type      = $this->get_meta( "_{$type}_resume_limit" );

		if( $limit_type && $allow_view_type === 'yes' ){
			return $limit_type;
		}

		return 0;
	}

	/**
	 * Get View Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return int|mixed
	 */
	public function get_view_limit(){

		$resume_limit = $this->get_meta( '_view_resume_limit' );

		if ( $resume_limit && $this->get_meta( '_allow_resume_view' ) === 'yes' ) {
			return $resume_limit;
		}

		return 0;
	}

	/**
	 * Get View Name Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return int|mixed
	 */
	public function get_view_name_limit(){

		$resume_limit = $this->get_meta( '_view_name_resume_limit' );

		if ( $resume_limit && $this->get_meta( '_allow_resume_view_name' ) === 'yes' ) {
			return $resume_limit;
		}

		return 0;
	}

	/**
	 * Get Contact Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return int|mixed
	 */
	public function get_contact_limit(){

		$resume_limit = $this->get_meta( '_contact_resume_limit' );

		if ( $resume_limit && $this->get_meta( '_allow_resume_contact' ) === 'yes' ) {
			return $resume_limit;
		}

		return 0;
	}

	/**
	 * Check if Use Short Description Enabled
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function use_short_description(){

		if( $this->get_meta( '_resume_use_sd' ) === 'yes' ){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Escape and Echo passed variable
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $variable
	 */
	public function eattr( $variable ){

		// Backwards compatibility with templates that call this method
		if ( $variable === 'id' ) {
			echo esc_attr( $this->get_id() );
		}

		echo esc_attr( $this->$variable );
	}

	/**
	 * Is this a subscription?
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_subscription(){

		return FALSE;
	}

	/**
	 * Get internal type.
	 *
	 * @since 1.1.2
	 *
	 * @return string
	 */
	public function get_type() {
		return 'resume_visibility_package';
	}
}
