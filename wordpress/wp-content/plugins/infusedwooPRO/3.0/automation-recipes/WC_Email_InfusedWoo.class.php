<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_InfusedWoo' ) ) :

class WC_Email_InfusedWoo extends WC_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id               = 'infusedwoo_custom';
		$this->title            = __( 'InfusedWoo WC Email', 'woocommerce' );
		$this->description      = __( 'InfusedWoo WC Emailer Template. For admin use only.', 'woocommerce' );
		$this->email_type		= 'html';
	}

	/**
	 * Trigger.
	 *
	 * @param int $order_id
	 */
	public function trigger($recipient, $subject, $content, $headers, $attachments="" ) {
		$this->subject = $subject;
		$this->content = $content;
		$this->send( $recipient, $subject, $this->get_content(), $headers, $attachments );
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		do_action( 'woocommerce_email_header', $this->subject, $this );
		echo $this->content; 
		do_action( 'woocommerce_email_footer', $this );
		return ob_get_clean();
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return strip_tags($this->content);	
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {

	}
}

endif;

return new WC_Email_InfusedWoo();
