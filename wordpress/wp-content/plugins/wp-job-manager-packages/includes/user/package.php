<?php

/**
 * Class WPJM_Pack_WC_Package
 *
 * @property string allow_browse
 * @property string allow_view
 * @property string allow_view_name
 * @property string allow_apply
 * @property string allow_contact
 *
 * @property integer view_limit
 * @property integer view_name_limit
 * @property integer contact_limit
 * @property integer apply_limit
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_User_Package {

	/**
	 * @var object User Package Object
	 */
	private $package;
	/**
	 * @var WP_Post
	 */
	private $product;

	/**
	 * WPJM_Pack_User_Package constructor.
	 *
	 * @param $package
	 */
	public function __construct( $package ) {
		$this->package = $package;
	}

	/**
	 * Get Package ID
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->package->id;
	}

	/**
	 * Get Product (Post)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array|null|\WP_Post
	 */
	public function get_product() {
		if ( empty( $this->product ) ) {
			$this->product = get_post( $this->get_product_id() );
		}
		return $this->product;
	}

	/**
	 * Get Product ID
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_product_id() {
		return $this->package->product_id;
	}

	/**
	 * Get Title (Post Title)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		$product = $this->get_product();
		return $product ? $product->post_title : '-';
	}

	/**
	 * Get Used
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return int
	 */
	public function get_used( $type ){

		$used = "{$type}_used";

		if( $this->package->$used ){
			return $this->package->$used;
		} else {
			return 0;
		}

	}

	/**
	 * Get Associated Posts
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return array|mixed
	 */
	public function get_posts( $type ){

		$posts = "{$type}_posts";

		if( $this->package->$posts ){
			return maybe_unserialize( $this->package->$posts );
		} else {
			return array();
		}
	}

	/**
	 * Check for limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function has_limit( $type ){

		$allow_view_type = "allow_{$type}";
		$limit_type      = "{$type}_limit";

		if( $this->package->$limit_type && $this->package->$allow_view_type ){
			return TRUE;
		} else {
			return FALSE;
		}

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
	 * Get the Limit
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return int
	 */
	public function get_limit( $type ){

		$allow_view_type = "allow_{$type}";
		$limit_type      = "{$type}_limit";

		if( $this->package->$limit_type && $this->package->$allow_view_type ){
			return $this->package->$limit_type;
		} else {
			return 0;
		}
	}

	/**
	 * Check if Allow is Enabled
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function allow_enabled( $type ){

		$allow_view_type = "allow_{$type}";

		if( $this->package->$allow_view_type ){
			return TRUE;
		} else {
			return FALSE;
		}

	}

	/**
	 * Get Magic Method
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $what
	 */
	public function __get( $what ){
		$this->package->$what;
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
		echo esc_attr( $this->package->$variable );
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
		$is_sub = false;

		$product = $this->get_product();
		if( $product ){
			$package = wc_get_product( $product );
			$is_sub = $package->is_subscription();
		}

		return $is_sub;
	}

	/**
	 * Get Order ID
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_order_id(){

		return $this->order_id;
	}
}