<?php
/**
 * Plugin Name: WP Job Manager - Packages
 * Plugin URI:  https://plugins.smyl.es/wp-job-manager-packages
 * Description: Create and configure custom Visibility Packages for WP Job Manager and WP Resume Manager to require visitors to purchase a package to view, apply, contact, browse, and more!
 * Version:     1.1.2
 * Author:      Myles McNamara
 * Author URI:  https://smyl.es
 * Donate link: https://plugins.smyl.es
 * License:     GPLv3
 * Text Domain: wp-job-manager-packages
 * Domain Path: /languages
 * Requires at least: 4.1
 * Tested up to: 4.7.3
 * Last Updated: Wed Jul 05 2017 15:59:59
 */

/**
 * Copyright (c) 2017 Myles McNamara (email : myles@smyl.es)
 */

if( ! defined( 'ABSPATH' ) ){
	exit;
}

include_once __DIR__ . '/includes/functions.php';

if( ! class_exists( 'sMyles_Updater_v2' ) ){
	include_once __DIR__ . '/includes/updater/smyles-updater.php';
}

/**
 * Autoloads files with classes when needed
 *
 * @since  1.0.0
 * @param  string $class Name of the class being requested
 * @return void
 */
function wpjmpack_autoload_classes( $class ) {
	if ( 0 !== strpos( $class, 'WPJM_Pack_' ) || 'WP_Job_Manager_Packages' === $class ) {
		return;
	}

	$class_file = str_replace( 'WPJM_Pack_', '', $class );
	$file_array = array_map( 'strtolower', explode( '_', $class_file ) );

	$dirs = 0;
	$file = '';

	while( $dirs ++ < count( $file_array ) ) {
		$file .= '/' . $file_array[$dirs - 1];
	}

	WP_Job_Manager_Packages::include_file( $file );
}
spl_autoload_register( 'wpjmpack_autoload_classes' );


/**
 * Main initiation class
 *
 * @since  1.0.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class WP_Job_Manager_Packages {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  1.0.0
	 */
	const VERSION = '1.1.2';

	/**
	 * Plugin Product ID
	 *
	 * @var  string
	 * @since 1.0.0
	 */
	const PROD_ID = 'WP Job Manager - Packages';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $basename = '';

	/**
	 * @var \WPJM_Pack_Job
	 */
	public $job;

	/**
	 * @var \WPJM_Pack_Resume
	 */
	public $resume;

	/**
	 * @var \WPJM_Pack_Admin
	 */
	public $admin;

	/**
	 * Singleton instance of plugin
	 *
	 * @var WP_Job_Manager_Packages
	 * @since  1.0.0
	 */
	protected static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  1.0.0
	 * @return WP_Job_Manager_Packages A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  1.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		//$this->plugin_classes();
		$this->constants();
		add_filter( 'smyles_updater_v2_known_plugins', array( $this, 'smyles_updater_v2_known_plugins' ) );
		new WPJM_Pack_Plugins_Visibility( $this );

		//add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Define Constants
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function constants(){

		if( ! defined( 'JOB_MANAGER_PACKAGES_VERSION' ) ) define( 'JOB_MANAGER_PACKAGES_VERSION', WP_Job_Manager_Packages::VERSION );
		if( ! defined( 'JOB_MANAGER_PACKAGES_PROD_ID' ) ) define( 'JOB_MANAGER_PACKAGES_PROD_ID', WP_Job_Manager_Packages::PROD_ID );
		if( ! defined( 'JOB_MANAGER_PACKAGES_PLUGIN_DIR' ) ) define( 'JOB_MANAGER_PACKAGES_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		if( ! defined( 'JOB_MANAGER_PACKAGES_PLUGIN_URL' ) ) define( 'JOB_MANAGER_PACKAGES_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

	}

	/**
	 * Add this plugin to known sMyles Plugins licensing
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $plugins
	 *
	 * @return mixed
	 */
	public function smyles_updater_v2_known_plugins( $plugins ){

		$plugins['wp-job-manager-packages']   = array(
			'title'      => 'WP Job Manager - Packages',
			'class'      => 'WP_Job_Manager_Packages',
			'product_id' => 'JOB_MANAGER_PACKAGES_PROD_ID',
			'version'    => 'JOB_MANAGER_PACKAGES_VERSION'
		);

		return $plugins;
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function plugin_classes() {

		new sMyles_Updater_v2( __FILE__, self::PROD_ID, self::VERSION );

		$this->init_theme();

		// Attach other plugin classes to the base plugin class.
		$this->job = new WPJM_Pack_Job( $this );

		if( self::wprm_active() ){
			$this->resume = new WPJM_Pack_Resume( $this );
		}

		new WPJM_Pack_Notices();

		if( is_admin() ){
			$this->admin = new WPJM_Pack_Admin( $this );
		}

		if( class_exists( 'WC_Subscriptions' ) && version_compare( WC_Subscriptions::$version, '2.0', '<' ) ){
			add_action( 'admin_notices', array( $this, 'subscriptions_update_required' ) );
		}

		if( class_exists( 'WooCommerce' ) && self::is_woocommerce_pre( '2.6' ) ){
			add_action( 'admin_notices', array( $this, 'wc_update_required' ) );
		}
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function hooks() {
		register_activation_hook( __FILE__, array( $this, '_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

		add_action( 'init', array( $this, 'init' ) );

		$this->plugin_classes();
	}

	/**
	 * Subscriptions 2.0+ Required
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function subscriptions_update_required(){

		?>
		<div class="update-nag">
		<?php _e( 'WP Job Manager Packages requires WooCommerce Subscriptions 2.0 and above. Please upgrade as soon as possible!', 'wp-job-manager-packages' ); ?>
		</div><?php
	}

	/**
	 * WooCommerce 2.6+ Required
	 *
	 *
	 * @since @@since
	 *
	 */
	public function wc_update_required(){

		?>
		<div class="update-nag">
		<?php _e( 'WP Job Manager Packages requires WooCommerce 2.6 or newer (3.0+ recommended). Please upgrade as soon as possible otherwise you will have issues!', 'wp-job-manager-packages' ); ?>
		</div><?php
	}

	/**
	 * Register JS/CSS
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function register_assets(){

		// Standard package selection style
		wp_register_style( 'jmpack-std-pkg-select', WP_Job_Manager_Packages::url( 'assets/css/standard.css' ), array(), false );

		// Version 2.2.7
		wp_register_script( 'jmpack-sui-full', WP_Job_Manager_Packages::url( 'assets/semantic/dist/semantic.js' ), array( 'jquery' ), time(), TRUE );
		wp_register_style( 'jmpack-sui-full', WP_Job_Manager_Packages::url( 'assets/semantic/dist/semantic.css' ), array(), time() );

		// JS Components
		wp_register_script( 'jmpack-sui-dimmer', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/dimmer.js' ), array( 'jquery' ), time(), TRUE );
		wp_register_script( 'jmpack-sui-modal', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/modal.js' ), array( 'jmpack-sui-dimmer', 'jquery' ), time(), TRUE );
		wp_register_script( 'jmpack-sui-transition', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/transition.js' ), array( 'jquery' ), time(), TRUE );
		wp_register_script( 'jmpack-sui-popup', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/popup.js' ), array( 'jmpack-sui-transition', 'jquery' ), time(), TRUE );

		// CSS Components
		wp_register_style( 'jmpack-sui-transition', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/transition.min.css' ), array(), false );
		wp_register_style( 'jmpack-sui-popup', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/popup.min.css' ), array( 'jmpack-sui-transition' ), false );
		wp_register_style( 'jmpack-sui-table', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/table.css' ), array(), false );
		wp_register_style( 'jmpack-sui-icon', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/icon.css' ), array(), false );
		wp_register_style( 'jmpack-sui-label', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/label.css' ), array(), false );
		wp_register_style( 'jmpack-sui-loader', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/loader.css' ), array(), false );
		wp_register_style( 'jmpack-sui-loader', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/dimmer.css' ), array(), false );
		wp_register_style( 'jmpack-sui-modal', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/modal.css' ), array( 'jmpack-sui-loader', 'jmpack-sui-dimmer' ), false );

		// Semantic Related Custom Styles/JS
		wp_register_style( 'jmpack-sui', WP_Job_Manager_Packages::url( 'assets/css/frontend.css' ), array( 'jmpack-sui-table', 'jmpack-sui-icon', 'jmpack-sui-label' ), time() );
		wp_register_script( 'jmpack-sui', WP_Job_Manager_Packages::url( 'assets/js/frontend.js' ), array( 'jquery' ), time(), TRUE );
		wp_register_script( 'jmpack-popup-js', WP_Job_Manager_Packages::url( 'assets/js/popup.js' ), array( 'jquery', 'jmpack-sui-popup' ), false, TRUE );

		// Color table details background
		wp_register_script( 'jmpack-sui-colordetails', WP_Job_Manager_Packages::url( 'assets/js/colordetails.js' ), array( 'jmpack-sui', 'jquery' ), time(), TRUE );

		// Check for theme specific CSS files
		$possible_names = self::get_theme_name();
		$last_checked = '';
		foreach( (array) $possible_names as $type => $name ) {

			if( $name === $last_checked ){
				continue;
			}

			if( file_exists( WP_Job_Manager_Packages::dir( "assets/css/{$name}.css" ) ) ){
				wp_register_style( 'jmpack-theme-noui', WP_Job_Manager_Packages::url( "assets/css/{$name}.css" ), array(), FALSE );
			}

			if( file_exists( WP_Job_Manager_Packages::dir( "assets/css/{$name}-ui.css" ) ) ){
				wp_register_style( 'jmpack-theme-ui', WP_Job_Manager_Packages::url( "assets/css/{$name}-ui.css" ), array(), FALSE );
			}

			$last_checked = $name;
		}

		//wp_enqueue_style( 'jmpack-sui-popup' );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded
		//flush_rewrite_rules();
		add_action( 'shutdown', array( $this, 'delayed_install' ) );
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'wp-job-manager-packages', false, dirname( $this->basename ) . '/languages/' );
		}
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('')

		// We have met all requirements
		return true;
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  1.0.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'WP Job Manager - Packages is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'wp-job-manager-packages' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  1.0.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  1.0.0
	 *
	 * @param  string $filename Name of the file to be included
	 *
	 * @return bool Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}
		return false;
	}

	/**
	 * Include a view file from the includes directory
	 *
	 * This method differs from the standard include file as this method allows you to include a single file
	 * multiple times, whereas $this->include_file() will only include_once
	 *
	 * @since  1.0.0
	 *
	 * @param  string $filename Name of the file to be included
	 * @param  array  $variables
	 *
	 * @return bool Result of include call.
	 */
	public static function include_view( $filename, $variables = array() ){

		$file = self::dir( 'includes/' . $filename . '.php' );
		if( file_exists( $file ) ){

			if( $variables && is_array( $variables ) ){
				extract( $variables, EXTR_OVERWRITE );
			}

			return include $file;
		}

		return FALSE;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  1.0.0
	 * @param  string $path (optional) appended path
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  1.0.0
	 * @param  string $path (optional) appended path
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	/**
	 * Check if WP Resume Manager is Active
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function wprm_active(){

		$wprm = 'wp-job-manager-resumes/wp-job-manager-resumes.php';

		if( ! defined( 'RESUME_MANAGER_PLUGIN_DIR' ) || ! class_exists( 'WP_Job_Manager_Resumes' ) ){

			// Let's try and see if we can check if it's active through core (in case this method is called before RM class is loaded)
			if( ! function_exists( 'is_plugin_active' ) ){
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if( is_plugin_active( $wprm ) ){
				return TRUE;
			}

			// Nope, that didn't work, chances are it's not activated or installed
			return FALSE;
		}

		// Constant or class is defined
		return TRUE;

	}

	/**
	 * Get current site Theme Name
	 *
	 * This method will get the theme name by default from parent theme, and
	 * if not set it will return the textdomain.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool|TRUE $parent     Whether or not to use the parent theme if current theme is child theme
	 * @param bool|TRUE $return_all Should the name and textdomain be returned in an array
	 * @param null      $return     If return_all is false, provide the string variable value to return (name or textdomain)
	 *
	 * @return array|string
	 */
	public static function get_theme_name( $parent = TRUE, $return_all = TRUE, $return = NULL ){

		$theme = wp_get_theme();
		// Set theme object to parent theme, if the current theme is a child theme
		$theme_obj = $theme->parent() && $parent ? $theme->parent() : $theme;

		$name       = $theme_obj->get( 'Name' );
		$textdomain = $theme_obj->get( 'TextDomain' );
		$version    = $theme_obj->get( 'Version' );
		$cache_bust = 'WPCLEANCACHEBUST';

		// Use name if possible, otherwise use textdomain
		$theme_name = isset( $name ) && ! empty( $name ) ? strtolower( $name ) : strtolower( $textdomain );

		if( $return_all ) $return_array = array(
			'name'       => strtolower( $name ),
			'textdomain' => strtolower( $textdomain ),
			'theme_name' => $theme_name,
		);

		if( $return_all ) return $return_array;
		// If return is set to one of vars above (name, textdomain), and is set, return that value
		if( ! empty( $return ) && is_string( $return ) && isset( $$return ) ) return $$return;

		return $theme_name;
	}

	/**
	 * Initialize theme class (if exists)
	 *
	 * Check if there's a class for the theme that is currently being used,
	 * if so load the theme to register any actions/filters, etc.
	 *
	 * @since 1.0.0
	 *
	 */
	public function init_theme(){

		$possible_names = self::get_theme_name();
		$last_checked = '';

		foreach( (array) $possible_names as $type => $name ) {

			if( $name === $last_checked ){
				continue;
			}

			$theme_class = 'WPJM_Pack_Themes_' . ucfirst( $name );

			if( class_exists( $theme_class ) ){
				$theme = new $theme_class();
				break;
			}

			$last_checked = $name;
		}

	}

	/**
	 * Check if the installed version of WooCommerce is older than a specified version.
	 *
	 * @since 2.7.2
	 * @from  Prospress/woocommerce-subscriptions
	 */
	public static function is_woocommerce_pre( $version ) {

		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $version, '<' ) ) {
			$woocommerce_is_pre_version = TRUE;
		} else {
			$woocommerce_is_pre_version = FALSE;
		}

		return $woocommerce_is_pre_version;
	}
}

/**
 * Grab the WP_Job_Manager_Packages object and return it.
 * Wrapper for WP_Job_Manager_Packages::get_instance()
 *
 * @since  1.0.0
 * @return WP_Job_Manager_Packages  Singleton instance of plugin class.
 */
function wpjmpack() {
	return WP_Job_Manager_Packages::get_instance();
}

// Kick it off
add_action( 'wp_enqueue_scripts', array( wpjmpack(), 'register_assets' ) );
add_action( 'plugins_loaded', array( wpjmpack(), 'hooks' ) );

// Activation install call (to be called before plugins_loaded)
register_activation_hook( __FILE__, array( 'WPJM_Pack_Install', 'do_install' ) );