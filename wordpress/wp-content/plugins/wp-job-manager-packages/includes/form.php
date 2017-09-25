<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Form
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Form {

	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public $type;
	/**
	 * @var string
	 */
	public $form_type;
	/**
	 * @var string
	 */
	public $button_text;
	/**
	 * @var integer
	 */
	public $listing_id;
	/**
	 * @var string
	 */
	public $header_text;
	/**
	 * @var string
	 */
	public $redirect;
	/**
	 * @var array
	 */
	public $atts;
	/**
	 * @var array
	 */
	public $packages;
	/**
	 * @var array
	 */
	public $user_packages;
	/**
	 * @var boolean
	 */
	public $placeholder = false;

	/**
	 * WPJM_Pack_Form constructor.
	 *
	 * @param        $type
	 * @param string $form_type
	 */
	public function __construct( $type, $form_type = 'any' ) {

		$this->form_type = $form_type;
		$this->type = $type;

		add_action( 'wp', array( $this, 'form_handler' ) );

	}

	/**
	 * Form Handler/Processing
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function form_handler(){

		if( empty( $_POST[ 'job_manager_packages_form' ] ) || empty( $_POST["{$this->type->slug}_visibility_package"] ) ){
			return;
		}

		$package_id = sanitize_key( $_POST["{$this->type->slug}_visibility_package"] );
		$user_id = get_current_user_id();

		$invalid_chars = 'FvtodsiB1s3DELwLtjgh5wEE';
		// Get listing id
		$listing_id = ! empty( $_REQUEST['listing_id'] ) ? absint( $_REQUEST['listing_id'] ) : get_the_ID();
		// Get Form Type
		$form_type = ! empty( $_REQUEST['job_manager_packages_form_type'] ) ? sanitize_key( $_REQUEST['job_manager_packages_form_type'] ) : $this->form_type;
		// Get Redirect
		$redirect = ! empty( $_REQUEST['job_manager_packages_redirect'] ) ? esc_url_raw( $_REQUEST['job_manager_packages_redirect'] ) : FALSE;

		// User Package Selected
		if( ! is_numeric( $package_id ) ){

			// Strip out `user-` as all user packages start with `user-`
			$user_package_id = absint( substr( $package_id, 5 ) );

			// Verify valid package with available uses
			if( $this->type->packages->user->is_valid( $user_id, $user_package_id, $form_type ) ){

				do_action( 'job_manager_packages_form_user_package_before_add_post', $listing_id, $user_package_id, $form_type );

				// Add passed listing ID to used posts
				$result = $this->type->packages->user->add_post( $user_id, $user_package_id, $form_type, $listing_id );

				do_action( 'job_manager_packages_form_user_package_after_add_post', $result, $listing_id, $user_package_id, $form_type );

				// Set redirect to permalink of listing id (if redirect not specified)
				$redirect = empty( $redirect ) ? get_permalink( $listing_id ) : $redirect;
				$redirect = apply_filters( 'job_manager_packages_form_user_package_add_post_redirect', $redirect , $result, $listing_id, $user_package_id, $form_type );

				$type_verb = $this->type->packages->get_package_type( $form_type, 'verb' );

				$notice = apply_filters( 'job_manager_packages_form_user_package_selected_noticed', sprintf( __( 'You can now %s this listing', 'wp-job-manager-packages' ), $type_verb ), $package_id, $listing_id, $user_package_id, $form_type );
				new WPJM_Pack_Notice( $notice, 'message' );

				// Redirect back to origin listing
				wp_redirect( $redirect );
				exit;
			}

		} else {

			// Purchase Package Selected
			$meta = array( 'listing_id' => $listing_id, 'form_type' => $form_type, 'redirect' => $redirect );

			do_action( 'job_manager_packages_form_process_form_before', $package_id, $meta );

			$this->type->handler->process_form( $package_id, $meta );

		}

	}

	/**
	 * Output Frontend Package Selection Form
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $atts
	 * @param       $packages
	 * @param array $user_packages
	 */
	public function output( $atts, $packages, $user_packages = array() ){

		$this->atts = $atts;
		$this->packages = $packages;
		$this->user_packages = $user_packages;

		$this->button_text = apply_filters( 'job_manager_packages_output_form_submit_button_text', $atts['button_text'], $packages, $user_packages, $atts );
		$this->listing_id = apply_filters( 'job_manager_packages_output_form_listing_id', $atts['listing_id'], $packages, $user_packages, $atts );
		$this->header_text = apply_filters( 'job_manager_packages_output_form_header_text', $atts['header_text'], $packages, $user_packages, $atts );
		$this->redirect = apply_filters( 'job_manager_packages_output_form_redirect', ! empty( $atts['redirect'] ) ? $atts['redirect'] : '', $packages, $user_packages, $atts );
		$is_placeholder = apply_filters( 'job_manager_packages_output_form_is_placeholder', array_key_exists( 'placeholder', $atts ) ? $atts['placeholder'] : false, $packages, $user_packages, $atts );

		do_action( 'job_manager_packages_output_form_before_form', $this );
		do_action( "job_manager_packages_output_form_{$this->form_type}_before_form", $this );

		$theme = WP_Job_Manager_Packages::get_theme_name( TRUE, FALSE );
		$theme_class = ! ( empty( $theme ) ) && is_string( $theme ) ? esc_attr( "job_manager_packages_{$theme}_form" ) : '';

		// Using echo to prevent wpautop adding <p> tags due to tabs or return characters
		echo "<form method=\"post\" id=\"job_package_selection\" class=\"job_manager_packages_form {$theme_class}\">";
		echo "<input type=\"hidden\" id=\"job_manager_packages_form_listing_id\" name=\"listing_id\" value=\"{$this->listing_id}\"/>";
		echo "<input type=\"hidden\" id=\"job_manager_packages_form_form\" name=\"job_manager_packages_form\" value=\"{$this->form_type}\"/>";
		echo "<input type=\"hidden\" id=\"job_manager_packages_form_form_type\" name=\"job_manager_packages_form_type\" value=\"{$this->form_type}\"/>";
		echo "<input type=\"hidden\" name=\"job_manager_packages_post_type\" value=\"{$this->type->post_type}\"/>";
		if( ! empty( $this->redirect ) ){
			echo "<input type=\"hidden\" id=\"job_manager_packages_form_redirect\" name=\"job_manager_packages_redirect\" value=\"{$this->redirect}\"/>";
		}
		get_job_manager_packages_form_template( $this->form_type, $this->type->slug, 'package-form', array( 'form' => $this, 'placeholder' => $is_placeholder ) );
		echo '</form>';

		do_action( "job_manager_packages_output_form_{$this->form_type}_after_form", $this );
		do_action( 'job_manager_packages_output_form_after_form', $this );
	}

	/**
	 * Return Form
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $atts
	 * @param       $packages
	 * @param array $user_packages
	 *
	 * @return string
	 */
	public function get( $atts, $packages, $user_packages = array() ){

		ob_start();
		$this->output( $atts, $packages, $user_packages );
		$form_html = ob_get_contents();
		ob_end_clean();

		// Remove anything that would cause wpautop to add <p> tags
		$form_html = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $form_html );

		return $form_html;
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
		echo esc_attr( $this->$variable );
	}

	/**
	 * @param bool $placeholder
	 */
	public function setPlaceholder( $placeholder ){
		global $jmpack_placeholder;
		$jmpack_placeholder = $placeholder;
		$this->placeholder  = $jmpack_placeholder;
	}
}
