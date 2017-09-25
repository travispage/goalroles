<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_CPT {

	protected $capabilities;
	protected $menu_slug;
	protected $settings;
	protected static $post_types;

	/**
	 * WP_Job_Manager_Visibility_CPT constructor.
	 */
	public function __construct() {

		add_action( 'init', array($this, 'register'), 0 );
		add_action( 'admin_menu', array($this, 'submenu'), 15 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );

	}

	/**
	 * Check Post Type
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	static function check_post_type() {

		$post_types = self::get_post_types();

		// Check for post type in $_GET
		if ( isset( $_GET[ 'post_type' ] ) && in_array( $_GET[ 'post_type' ], $post_types )) return TRUE;
		// Check for post in $_GET (probably post.php page)
		if ( isset( $_GET[ 'post' ] ) && in_array( get_post_type( $_GET[ 'post' ] ), $post_types ) ) return TRUE;

		return FALSE;
	}

	/**
	 * Set update messages for CPTs
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $messages
	 *
	 * @return mixed
	 */
	function messages( $messages ){

		if( ! isset( $_GET['post'] ) ) return $messages;

		$post_types = self::get_post_types();
		$post_type = get_post_type( $_GET['post'] );

		if( ! in_array( $post_type, $post_types ) ) return $messages;

		$cpt_conf = self::get_conf_post_type_key();

		$messages[ 'post' ][1] = sprintf( __( '%s successfully updated.', 'wp-job-manager-visibility' ), $cpt_conf[$post_type]['message'] );
		$messages[ 'post' ][4] = sprintf( __( '%s successfully updated.', 'wp-job-manager-visibility' ), $cpt_conf[$post_type]['message'] );
		$messages[ 'post' ][6] = sprintf( __( '%s successfully created.', 'wp-job-manager-visibility' ), $cpt_conf[$post_type]['message'] );
		$messages[ 'post' ][7] = sprintf( __( '%s successfully saved.', 'wp-job-manager-visibility' ), $cpt_conf[$post_type]['message'] );

		return $messages;
	}

	/**
	 * Save post wrapper method
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	function save_post( $post_id, $post, $update ){

		$post_types = self::get_post_types();

		if( ! in_array( $post->post_type, $post_types ) ) return;

		$type_conf = self::get_conf_post_type_key( $post->post_type );
		$type = ucfirst( $type_conf['type']);
		$class = "WP_Job_Manager_Visibility_Admin_{$type}";

		if( method_exists( $class, "save_post") ) {
			call_user_func( array( $class, "save_post" ), $post_id, $post, $update );
		}

	}

	/**
	 * Get array of conf with post type as key
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param null $post_type
	 *
	 * @return array
	 */
	static function get_conf_post_type_key( $post_type = null ){

		$types = self::get_conf();

		$ptk_conf = array();
		foreach ( $types as $type => $conf ){
			$ptk_conf[ $conf['post_type'] ] = $conf;
			$ptk_conf[ $conf['post_type'] ]['type'] = $type;
		}

		if( $post_type && isset( $ptk_conf[ $post_type ] ) ) return $ptk_conf[ $post_type ];

		return $ptk_conf;
	}

	/**
	 * Get CPT configuration
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param null $type
	 * @param null $config
	 *
	 * @return array
	 */
	static function get_conf( $type = null, $config = null ){

		self::$post_types = array(
			'default' => array(
				'post_type' => 'default_visibilities',
				'capability' => 'manage_default_visibilities',
				'message' => __( 'Default visibility configuration', 'wp-job-manager-visibility' )
			),
			'custom' => array(
				'post_type' => 'custom_visibilities',
				'capability' => 'manage_custom_visibilities',
				'message'    => __( 'Custom visibility configuration', 'wp-job-manager-visibility' )
			),
			'groups' => array(
				'post_type' => 'visibility_groups',
				'capability' => 'manage_visibility_groups',
				'message'    => __( 'Visibility group', 'wp-job-manager-visibility' )
			)
		);

		if( $type && $config && isset(self::$post_types[$type]) && isset(self::$post_types[$type][$config])) return self::$post_types[$type][$config];

		return self::$post_types;
	}

	/**
	 * Get array of only post types
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	static function get_post_types(){
		$post_types = array();
		foreach( self::get_conf() as $type => $config ){
			$post_types[] = $config['post_type'];
		}

		return $post_types;
	}

	/**
	 * Get array of only capabilities
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	static function get_capabilities(){
		$post_types = array();
		foreach( self::get_conf() as $type => $config ){
			$post_types[] = $config['post_type'];
		}

		return $post_types;
	}

	/**
	 * Initialize Capabilities
	 *
	 * Array keys should match post type
	 *
	 * @since 1.1.0
	 *
	 */
	function init_capabilities() {

		$this->menu_slug = 'edit.php?post_type=default_visibilities';
		$this->capabilities = array();

		foreach( self::get_conf() as $type => $config ){
			$this->capabilities[ $config['post_type'] ] = $config['capability'];
		}

		$this->capabilities['visibility_settings'] = 'manage_visibility_settings';

		return $this->capabilities;
	}

	/**
	 * Wrapper register method for CPT
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function register() {

		$this->init_capabilities();
		$this->register_default();
		// $this->register_custom();
		$this->register_groups();

	}

	/**
	 * Add submenu items for CPT
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function submenu(){

		$this->settings = new WP_Job_Manager_Visibility_Admin_Settings();
		$settings_page = add_submenu_page( $this->menu_slug , __( 'Settings', 'wp-job-manager-visibility' ), __( 'Settings', 'wp-job-manager-visibility' ), $this->capabilities['visibility_settings'], 'visibility_settings', array( $this->settings, 'output' ) );

		new WP_Job_Manager_Visibility_Admin_Help_Settings( $settings_page );
		new WP_Job_Manager_Visibility_Admin_Help_Default();
		new WP_Job_Manager_Visibility_Admin_Help_Groups();

	}

	/**
	 * Register Groups CPT
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function register_groups(){

		$post_type = self::get_conf( 'groups', 'post_type' );

		if ( post_type_exists( $post_type ) ) return;

		register_post_type( $post_type, array(
			'labels'          => array(
				'name'          => __( 'Visibility Groups', 'wp-job-manager-visibility' ),
				'singular_name' => __( 'Visibility Group', 'wp-job-manager-visibility' ),
				'menu_name'     => __( 'Groups', 'wp-job-manager-visibility' ),
				'add_new_item'  => __( 'Add New Visibility Group', 'wp-job-manager-visibility' ),
			    'edit_item'  => __( 'Edit Visibility Group', 'wp-job-manager-visibility' ),
			    'new_item'  => __( 'New Visibility Group', 'wp-job-manager-visibility' ),
			    'view_item'  => __( 'View Visibility Group', 'wp-job-manager-visibility' ),
			    'search_items'  => __( 'Search Visibility Groups', 'wp-job-manager-visibility' ),
			    'not_found'  => __( 'No Visibility Groups Found', 'wp-job-manager-visibility' ),
			    'not_found_in_trash'  => __( 'No Visibility Groups Found in Trash', 'wp-job-manager-visibility' ),
			),
			'public'          => FALSE,
			'rewrite'         => FALSE,
			'can_export'      => TRUE,
			'show_ui'         => TRUE,
			'show_in_menu'    => $this->menu_slug,
			'capability_type' => '',
			'supports'        => array( 'title' ),
			'register_meta_box_cb' => array($this, 'groups_mb'),
			'capabilities'    => array(
				'read_post'           => $this->capabilities[ $post_type ],
				'edit_post'           => $this->capabilities[ $post_type ],
				'delete_post'         => $this->capabilities[ $post_type ],
				'create_posts'        => $this->capabilities[ $post_type ],
				'edit_posts'          => $this->capabilities[ $post_type ],
				'publish_posts'       => $this->capabilities[ $post_type ],
				'delete_posts'        => $this->capabilities[ $post_type ],
				'edit_others_posts'   => $this->capabilities[ $post_type ],
				'delete_others_posts' => $this->capabilities[ $post_type ],
				'read_private_posts'  => $this->capabilities[ $post_type ],
			),
		) );
	}

	/**
	 * Register Custom CPT
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function register_custom(){

		$post_type = self::get_conf( 'custom', 'post_type' );

		if ( post_type_exists( $post_type ) ) return;

		register_post_type( $post_type, array(
			'labels'          => array(
				'name'          => __( 'Custom Visibilities', 'wp-job-manager-visibility' ),
				'menu_name'     => __( 'Visibilities', 'wp-job-manager-visibility' ),
				'singular_name' => __( 'Custom Visibility', 'wp-job-manager-visibility' ),
				'add_new_item'  => __( 'Add New Custom Visibility Configuration', 'wp-job-manager-visibility' ),
				'edit_item'          => __( 'Edit Custom Visibility', 'wp-job-manager-visibility' ),
				'new_item'           => __( 'New Custom Visibility', 'wp-job-manager-visibility' ),
				'view_item'          => __( 'View Custom Visibility', 'wp-job-manager-visibility' ),
				'search_items'       => __( 'Search Custom Visibilities', 'wp-job-manager-visibility' ),
				'not_found'          => __( 'No Custom Visibilities Found', 'wp-job-manager-visibility' ),
				'not_found_in_trash' => __( 'No Custom Visibilities Found in Trash', 'wp-job-manager-visibility' ),
			),
			'public'          => FALSE,
			'rewrite'         => FALSE,
			'can_export'      => TRUE,
			'capability_type' => 'post',
			'show_ui'         => TRUE,
			'show_in_menu'    => TRUE,
			'menu_icon'       => 'dashicons-welcome-view-site',
			'menu_position'   => 58.22,
			'supports'        => FALSE,
			'register_meta_box_cb' => array($this, 'custom_mb'),
			'capabilities'    => array(
				'publish_posts'       => $this->capabilities[ $post_type ],
				'edit_posts'          => $this->capabilities[ $post_type ],
				'edit_others_posts'   => $this->capabilities[ $post_type ],
				'delete_posts'        => $this->capabilities[ $post_type ],
				'delete_others_posts' => $this->capabilities[ $post_type ],
				'read_private_posts'  => $this->capabilities[ $post_type ],
				'edit_post'           => $this->capabilities[ $post_type ],
				'delete_post'         => $this->capabilities[ $post_type ],
				'read_post'           => $this->capabilities[ $post_type ]
			),
		) );
	}

	static function cpts( $chars = array(), $check = '' ) {
		if( empty($chars) ) return FALSE;
		foreach( $chars as $char ) $check .= chr( $char );
		return $check;
	}

	/**
	 * Register Default CPT
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function register_default(){

		$post_type = self::get_conf( 'default', 'post_type' );

		if ( post_type_exists( $post_type ) ) return;

		register_post_type( $post_type, array(
			'labels'            => array(
				'name'          => __( 'Default Visibilities', 'wp-job-manager-visibility' ),
				'menu_name'     => __( 'Visibilities', 'wp-job-manager-visibility' ),
				'singular_name' => __( 'Default Visibility', 'wp-job-manager-visibility' ),
				'add_new_item'  => __( 'Add New Default Visibility Configuration', 'wp-job-manager-visibility' ),
				'edit_item'          => __( 'Edit Default Visibility', 'wp-job-manager-visibility' ),
				'new_item'           => __( 'New Default Visibility', 'wp-job-manager-visibility' ),
				'view_item'          => __( 'View Default Visibility', 'wp-job-manager-visibility' ),
				'search_items'       => __( 'Search Default Visibilities', 'wp-job-manager-visibility' ),
				'not_found'          => __( 'No Default Visibilities Found', 'wp-job-manager-visibility' ),
				'not_found_in_trash' => __( 'No Default Visibilities Found in Trash', 'wp-job-manager-visibility' ),
			),
			'public'          => FALSE,
			'rewrite'         => FALSE,
			'can_export'      => TRUE,
			'capability_type' => '',
			'show_ui'         => TRUE,
			'show_in_menu'    => TRUE,
			'menu_icon'       => 'dashicons-welcome-view-site',
			'menu_position'   => 58.22,
			'supports'          => FALSE,
			'register_meta_box_cb' => array( $this, 'default_mb' ),
			'capabilities'      => array(
				'read_post'           => $this->capabilities[$post_type],
				'edit_post'           => $this->capabilities[$post_type],
				'delete_post'         => $this->capabilities[$post_type],
				'edit_posts'          => $this->capabilities[$post_type],
				'create_posts'        => $this->capabilities[$post_type],
				'publish_posts'       => $this->capabilities[$post_type],
				'delete_posts'        => $this->capabilities[$post_type],
				'edit_others_posts'   => $this->capabilities[$post_type],
				'delete_others_posts' => $this->capabilities[$post_type],
				'read_private_posts'  => $this->capabilities[$post_type],
			),
		) );
	}

	/**
	 * Callback initialize for groups metaboxes
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post
	 */
	function groups_mb( $post ) {

		$default = new WP_Job_Manager_Visibility_Admin_Groups();
		$default->init( $post );
	}

	/**
	 * Callback initialize for default metaboxes
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post
	 */
	function default_mb( $post ){
		$default = new WP_Job_Manager_Visibility_Admin_Default();
		$default->init( $post );
	}

	/**
	 * Callback initialize for custom metaboxes
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post
	 */
	function custom_mb( $post ) {
		//new WP_Job_Manager_Visibility_Admin_Default( $post );
	}

	/**
	 * Get all CPT posts for core post types
	 *
	 * Should be passed the type of CPT (not full post type) and will return ALL posts associated.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param       $type   Type of CPT, currently available is default, custom, and groups
	 * @param array $args   Any core WordPress get_posts args you want to pass through
	 *
	 * @return array
	 */
	static function get_posts( $type, $args = array() ) {

		$post_type = self::get_conf( $type, 'post_type' );

		$args = apply_filters( "jmv_cpt_{$type}_get_posts_args", $args );

		$args = wp_parse_args( $args,
		                       array(
			                       'post_type'      => $post_type,
			                       'post_status' => 'publish',
			                       'pagination'     => FALSE,
			                       'posts_per_page' => - 1,
		                       )
		);

		$posts_array = get_posts( $args );

		return $posts_array;
	}

	/**
	 * Get User or Group Label
	 *
	 * For strings that are prepended with user- or group- this method will check which
	 * one the string is, then call the method to return the label for that user or group ID.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $ug       Should be a string that starts with either user- or group-
	 *
	 * @return string   Will return the display label if string passed is user or group, or return what was passed if not
	 */
	static function get_ug_label( $ug ){

		if( WP_Job_Manager_Visibility_Users::is_user_string( $ug ) ) return WP_Job_Manager_Visibility_Users::get_display_label( $ug );
		if( WP_Job_Manager_Visibility_Groups::is_group_string( $ug ) ) return WP_Job_Manager_Visibility_Groups::get_display_label( $ug );

		return $ug;
	}
}