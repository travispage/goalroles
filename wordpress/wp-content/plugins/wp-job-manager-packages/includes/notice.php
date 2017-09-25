<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Notice
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Notice {

	/**
	 * @var string
	 */
	public $message;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $transient;
	/**
	 * @var int Transient timeout period (30 second default)
	 */
	public $timeout = 99999;

	/**
	 * WPJM_Pack_Notice constructor.
	 *
	 * @param string $message       Message for the notice
	 * @param string $type          Type of message to show (error, info, or message)
	 * @param int    $user_id
	 */
	public function __construct( $message, $type = 'message', $user_id = 0 ) {

		if( empty( $user_id ) ){
			$user_id = get_current_user_id();
		}

		$this->message = apply_filters( 'job_manager_packages_notice_message', $message, $type, $user_id );
		$this->transient = "jmpack_notice_{$user_id}";
		$this->type = $type;

		// Use WooCommerce notices over our own internal ones (if available)
		if( function_exists( 'wc_add_notice' ) ){

			// Set message type to success
			$notice_type = $this->type === 'message' ? 'success' : $this->type;

			// or info type to notice (for wc compatibility)
			if( $notice_type === 'info' ){
				$notice_type = 'notice';
			}

			wc_add_notice( "<div class=\"jmpack-woonotice\">{$this->message}</div>", $notice_type );

		} else {

			$this->set();

		}
	}

	/**
	 * Set Notice Transient
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function set(){
		set_transient( $this->transient, $this, $this->timeout );
	}

	/**
	 * Remove Notice Transient
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function remove(){
		delete_transient( $this->transient );
	}

	/**
	 * Show/Output Notice
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function show(){
		echo "<div class=\"job-manager-{$this->type}\">{$this->message}</div>";
	}
}
