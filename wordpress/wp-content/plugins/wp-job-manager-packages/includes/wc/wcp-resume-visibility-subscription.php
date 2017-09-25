<?php
/**
 * Resume Package Product Type
 */
class WC_Product_Resume_Visibility_Subscription extends WC_Product_Subscription {

	/**
	 * Constructor
	 */
	public function __construct( $product ) {
		parent::__construct( $product );
		$this->product_type = 'resume_visibility_subscription';
	}

	/**
	 * Get internal type.
	 *
	 * @since 1.1.2
	 *
	 * @return string
	 */
	public function get_type() {
		return 'resume_visibility_subscription';
	}

	/**
	 * Checks the product type.
	 *
	 * Backwards compat with downloadable/virtual.
	 *
	 * @access public
	 * @param mixed $type Array or string of types
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( 'resume_visibility_subscription' == $type || ( is_array( $type ) && in_array( 'resume_visibility_subscription', $type ) ) ) ? true : parent::is_type( $type );
	}

	/**
	 * We want to sell resumes one at a time
	 * @return boolean
	 */
	public function is_sold_individually() {
		return true;
	}

	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Jobs are always virtual
	 * @return boolean
	 */
	public function is_virtual() {
		return true;
	}

	/**
	 * Get product id
	 * @return int
	 */
	public function get_product_id() {
		return $this->get_id();
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
	public function allow_enabled( $type ) {

		if ( $this->get_meta( "_allow_resume_{$type}" ) === 'yes' ) {
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
	public function has_limit( $type ) {

		if ( $this->get_meta( "_{$type}_resume_limit" ) && $this->get_meta( "_allow_resume_{$type}" ) === 'yes' ) {
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
	public function get_limit( $type ) {

		$allow_view_type = $this->get_meta( "_allow_resume_{$type}" );
		$limit_type      = $this->get_meta( "_{$type}_resume_limit" );

		if ( $limit_type && $allow_view_type === 'yes' ) {
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
	public function get_view_limit() {

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
	public function get_view_name_limit() {

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
	public function get_contact_limit() {

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
	public function use_short_description() {

		if ( $this->get_meta( '_resume_use_sd' ) === 'yes' ) {
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

		return true;
	}

	/**
	 * Check if Rollover is Enabled
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public function rollover_enabled( $type ){

		if( $this->get_meta( "_allow_resume_{$type}_rollover" ) === 'yes' ){
			return TRUE;
		}

		return FALSE;
	}

}