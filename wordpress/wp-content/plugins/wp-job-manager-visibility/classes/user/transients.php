<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_User_Transients extends WP_Job_Manager_Visibility_Transients {

	private $std_prefix = "uconf";
	private $user_prefix = "uconf_user";
	private $group_prefix = "uconf_group";


	public function __construct() {

		$this->prefix = $this->std_prefix;

	}

	function count_user(){

		$this->prefix = $this->user_prefix;
		return $this->count();
	}

	function count_group(){

		$this->prefix = $this->group_prefix;
		return $this->count();
	}

	function purge_user(){

		$this->prefix = $this->user_prefix;
		$this->purge();

	}

	function purge_group() {

		$this->prefix = $this->group_prefix;
		$this->purge();

	}

	function remove_user( $user_id ){

		$name = str_replace( "user-", "", "user_{$user_id}" );
		return $this->remove( $name );

	}

	function remove_group( $user_id ){

		$name = str_replace( "user-", "", "group_{$user_id}" );
		return $this->remove( $name );
	}

	function get_user( $user_id ){

		// Remove any user- in string, prepend with user_
		$name = str_replace( "user-", "", "user_{$user_id}");

		$check = $this->get( $name );
		// False means expired or transient doesn't exist
		if( $check !== FALSE ) return $check;

		$user_conf = WP_Job_Manager_Visibility_Default::get_user( $user_id );
		$this->set( $name, $user_conf );

		return $user_conf;
	}

	function get_groups( $user_id ){

		if( empty( $user_id ) ) $user_id = "anonymous";

		// Remove any user- in string, prepend with user_
		$name = str_replace( "user-", "", "group_{$user_id}" );

		$check = $this->get( $name );
		// False means expired or transient doesn't exist
		if( $check !== FALSE ) return $check;

		$groups_conf = WP_Job_Manager_Visibility_Default::get_user_groups( $user_id );
		$this->set( $name, $groups_conf );

		return $groups_conf;
	}

}