<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * sMyles_Updater_v2_UI
 *
 * @version 2.0
 * @author  Mike Jolley, Myles McNamara
 */
class sMyles_Updater_v2_Licenses {

	private static $instance;

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Job_Manager_Field_Editor
	 */
	static function get_instance() {

		if( NULL == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

	/**
	 * sMyles_Updater_v2_Licenses constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array($this, 'add_licenses_page') );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( "wp_ajax_smyles_updater_v2_activation", array( $this, 'ajax_activate' ) );
		add_action( "wp_ajax_smyles_updater_v2_deactivation", array( $this, 'ajax_deactivate' ) );

	}

	/**
	 * Register Assets for Licenses Page
	 *
	 *
	 * @since 2.0
	 *
	 */
	function register_assets(){

		$base_path = $this->asset_path();

		wp_register_style( 'smylesv2-semantic', $base_path . 'semantic.min.css' );
		wp_register_style( 'smylesv2', $base_path . 'smylesv2.css' );
		wp_register_script( 'smylesv2-semantic', $base_path . 'semantic.min.js', array( 'jquery' ), TRUE );
		wp_register_script( 'smylesv2', $base_path . 'smylesv2.js', array( 'jquery' ), TRUE );

	}

	function plugin_image( $slug ){

		$plugin_image_check = $this->asset_path( "{$slug}.png", FALSE );

		$plugin_image = apply_filters( 'smyles_updater_v2_plugin_image_path', $plugin_image_check, $slug );

		if( file_exists( $plugin_image ) ){
			$plugin_image_url = $this->asset_path( "{$slug}.png", TRUE );
			$plugin_image_url = apply_filters( 'smyles_updater_v2_plugin_image_url', $plugin_image_url, $slug, $plugin_image );
			return $plugin_image_url;
		}

		return $this->asset_path( 'default.png' );
	}

	function asset_path( $file_name = '', $url = TRUE ) {
		// Get full path to updater files, and normalize to fix paths on Windows servers
		$asset_path = wp_normalize_path( untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		// Only content directory needed, return with file added to assets path
		if ( $url ) {
			// Get wp-content directory path (and normalize to fix path on Windows)
			$content_dir = wp_normalize_path( untrailingslashit( WP_CONTENT_DIR ) );
			// Replace content_dir with actual URL to content dir, in the updater_path
			$asset_path = str_replace( $content_dir, content_url(), $asset_path );
		}
		// Return value with assets and filename added
		return apply_filters( 'smyles_updater_v2_asset_path', "{$asset_path}/assets/{$file_name}", $file_name, $url );
	}

	/**
	 * Ajax License Deactivation
	 *
	 *
	 * @since 2.0
	 *
	 */
	function ajax_deactivate(){

		check_ajax_referer( 'smyles_updater_v2_activation', 'nonce' );
		if( ! isset($_POST['slug']) ) wp_die( json_encode( array( 'error_title' => __( 'Error'  ), 'error_msg' => __( 'Unable to determine slug to deactivate license.'  ) ) ) );

		$plugin_slug = sanitize_text_field( $_POST['slug'] );

		$license_key       = get_option( $plugin_slug . '_licence_key' );
		$email 			   = get_option( $plugin_slug . '_email' );
		$instance_id       = get_option( $plugin_slug . '_instance' );
		$plugin_product_id = sanitize_text_field( $_POST['product_id'] );

		if( ! class_exists( 'sMyles_Updater_v2_Key_API' ) ) include_once __DIR__ . '/smyles-updater-key-api.php';

		try {

			if( empty($plugin_product_id) ) {
				throw new Exception( __( 'Error determining the plugin Product ID.'  ) );
			}

			if( empty($license_key) ){
				throw new Exception( __( 'Error pulling the license key from option data.'  ) );
			}

			if( empty($email) ) {
				throw new Exception( __( 'Error pulling the email associated with the license key from option data.'  ) );
			}

			if( empty($instance_id) ) {
				throw new Exception( __( 'Error pulling the instance ID associated with the license key from option data.'  ) );
			}

			$deactivate_results = json_decode( sMyles_Updater_v2_Key_API::deactivate( array(
																	'product_id'  => $plugin_product_id,
																	'licence_key' => $license_key,
																	'email'       => $email,
																	'instance'    => $instance_id,
															) ) );

			if( is_object( $deactivate_results ) ) $deactivate_results = (array) $deactivate_results;

			if( FALSE === $deactivate_results ) {
				throw new Exception( __( 'Connection failed to the License Key API server - possible server issue.'  ) );

			} elseif( isset($deactivate_results['error_code']) ) {
				throw new Exception( $deactivate_results['error'] );

			} elseif( ! empty($deactivate_results['deactivated']) ) {

				$results = array('success_title'    => $plugin_product_id . __( ' Successfully Deactivated!'  ),
								 'success_msg'      => sprintf( __( 'You have successfully deactivated the %s license.'  ), $plugin_product_id ),
								 'deactivate_results' => $deactivate_results
				);

			} else {

				throw new Exception( 'License could not deactivate. Please visit the My Account page to remove any stale activations.' );

			}

		} catch ( Exception $e ) {

			$results = array('error_title' => __( 'Error!'  ), 'error_msg' => $e->getMessage() . '<br/>' . sprintf( __( 'The license has been successfully deactivated locally, but please visit your sMyles Plugins <a href="%s" target="_blank">My Account</a> page to verify this activation has been removed from our server.  There was an error automatically removing it, and chances are that is because it was already removed via your <a href="%s" target="_blank">My Account</a> page already..'  ), 'https://plugins.smyl.es/my-account/' ) );

			if( isset( $deactivate_results ) ) $results['deactivate_results'] = $deactivate_results;

		}

		delete_option( $plugin_slug . '_licence_key' );
		delete_option( $plugin_slug . '_email' );
		delete_option( $plugin_slug . '_errors' );
		delete_option( $plugin_slug . '_instance' );
		delete_option( $plugin_slug . '_activation_date' );
		delete_site_transient( 'update_plugins' );

		$known_plugins = $this->get_known_plugins();

		if( array_key_exists( $plugin_slug, $known_plugins ) ) {
			$results['card_html'] = $this->output_card( $plugin_slug, $known_plugins[ $plugin_slug ], TRUE );
		}

		echo json_encode( $results );
		die();
	}

	/**
	 * Ajax License Activation
	 *
	 *
	 * @since 2.0
	 *
	 */
	function ajax_activate() {

		check_ajax_referer( 'smyles_updater_v2_activation', 'nonce' );
		if( ! isset($_POST['slug'], $_POST['license_key'], $_POST['email']) ) {
			wp_die( json_encode( array(
										 'error_title' => __( 'Error'  ),
										 'error_msg'   => __( 'Unable to determine slug, license key, and/or email to activate license, contact support.'  ),
								 ) ) );
		}

		$activate_results = $results = array();

		$plugin_slug 		= sanitize_text_field( $_POST['slug'] );
		$license_key        = sanitize_text_field( $_POST['license_key'] );
		$email   			= sanitize_text_field( $_POST['email'] );
		$plugin_product_id  = sanitize_text_field( $_POST['product_id'] );
		$plugin_version     = sanitize_text_field( $_POST['version'] );
		$instance_id        = get_option( $plugin_slug . '_instance' );

		if( empty( $instance_id ) ) {

			if( ! class_exists( 'sMyles_Updater_Password_Management' ) ) include_once __DIR__ . '/smyles-updater-passwords.php';

			$smyles_gen_instance = new sMyles_Updater_v2_Password_Management();

			// Generate a unique installation $instance id
			$instance_id = $smyles_gen_instance->generate_password( 12, FALSE );
			update_option( $plugin_slug . '_instance', $instance_id );

		}

		if( ! class_exists( 'sMyles_Updater_v2_Key_API' ) ) include_once __DIR__ . '/smyles-updater-key-api.php';

		try {

			if( empty( $plugin_product_id ) ){
				throw new Exception( 'Product ID is required in order to activate a license!' );
			}

			if( strpos( strtolower( $license_key ), 'wpjm-' ) !== FALSE ) {
				throw new Exception( 'You can not activate a sMyles Plugin using a license key for a wpjobmanager.com plugin.<br />>Please visit your My Account page at <a href="http://plugins.smyl.es/my-account/" target="_blank">sMyles Plugins <span class="dashicons dashicons-external"></span></a> to obtain your API/License key.' );
			}

			$activate_results = json_decode(
					sMyles_Updater_v2_Key_API::activate(
							array(
									'email'            => $email,
									'licence_key'      => $license_key,
									'product_id'       => $plugin_product_id,
									'software_version' => $plugin_version,
									'instance'         => $instance_id,
							) ), TRUE );

			if( FALSE === $activate_results ) {
				throw new Exception( 'Connection failed to the License Key API server - possible server issue.' );

			} elseif( isset($activate_results['error_code']) ) {
				throw new Exception( $activate_results['error'] );

			} elseif( ! empty($activate_results['activated']) ) {
				update_option( $plugin_slug . '_licence_key', $license_key );
				update_option( $plugin_slug . '_email', $email);
				update_option( $plugin_slug . '_activation_date', time() );
				delete_option( $plugin_slug . '_errors' );

				$results = array( 'success_title' => $plugin_product_id . __( ' Successfully Activated!'  ), 'success_msg' => sprintf( __( 'You have successfully activated the %s license, and will now be able to automatically upgrade this plugin through WordPress when an update is available.'  ), $plugin_product_id ), 'activate_results' => $activate_results );
				$known_plugins = $this->get_known_plugins();

				if( array_key_exists( $plugin_slug, $known_plugins ) ) {
					$results['card_html'] = $this->output_card( $plugin_slug, $known_plugins[ $plugin_slug ], TRUE );
				}

			} else {

				throw new Exception( 'License could not activate. Please contact support.' );

			}

		} catch ( Exception $e ) {

			$results = array('error_title' => __( 'Error!'  ), 'error_msg' => $e->getMessage(), 'activate_results' => $activate_results );

		}

		echo json_encode( $results );
		die();
	}

	/**
	 * Add sMyles Licenses Page to WordPress
	 *
	 *
	 * @since 2.0
	 *
	 */
	function add_licenses_page() {

		add_submenu_page(
			'index.php',
			'sMyles Licenses',
			'sMyles Licenses',
			'update_plugins',
			'smyles-licenses',
			array($this, 'licenses_page_output')
		);

	}

	/**
	 * Return Array of Known Plugins with Config
	 *
	 *
	 * @since 2.0
	 *
	 * @return mixed|void
	 */
	function get_known_plugins(){

		$plugins = apply_filters( 'smyles_updater_v2_known_plugins',
								  array(
										  'wp-job-manager-field-editor' => array(
												  'title'      => 'WP Job Manager - Field Editor',
												  'class'      => 'WP_Job_Manager_Field_Editor',
												  'product_id' => 'WPJM_FIELD_EDITOR_PROD_ID',
												  'version'    => 'WPJM_FIELD_EDITOR_VERSION'
										  ),
										  'wp-job-manager-emails'       => array(
												  'title'      => 'WP Job Manager - Emails',
												  'class'      => 'WP_Job_Manager_Emails',
												  'product_id' => 'JOB_MANAGER_EMAILS_PROD_ID',
												  'version'    => 'JOB_MANAGER_EMAILS_VERSION'
										  ),
										  'wp-job-manager-visibility'   => array(
												  'title'      => 'WP Job Manager - Visibility',
												  'class'      => 'WP_Job_Manager_Visibility',
												  'product_id' => 'JOB_MANAGER_VISIBILITY_PROD_ID',
												  'version'    => 'JOB_MANAGER_VISIBILITY_VERSION'
										  ),
										  'wp-job-manager-packages'   => array(
												  'title'      => 'WP Job Manager - Packages',
												  'class'      => 'WP_Job_Manager_Packages',
												  'product_id' => 'JOB_MANAGER_PACKAGES_PROD_ID',
												  'version'    => 'JOB_MANAGER_PACKAGES_VERSION'
										  )
								  )
		);

		return $plugins;
	}

	/**
	 * Output Licenses Page HTML
	 *
	 *
	 * @since 2.0
	 *
	 */
	function licenses_page_output() {

		wp_enqueue_style('smylesv2-semantic');
		wp_enqueue_style('smylesv2');
		wp_enqueue_script('smylesv2');
		wp_enqueue_script('smylesv2-semantic');
		?>

		<div id="smylesv2-modal" class="ui small basic modal" data-slug="">
			<div class="ui icon header">
				<i class="unlink icon"></i>
				<?php printf( __('Deactivate the %s License?' ), '<span id="smylesv2-modal-slug" style="color: #db2828;"></span>' ); ?>
			</div>
			<div class="actions">
				<div class="ui red basic cancel inverted button">
					<i class="remove icon"></i>
					<?php _e('No' ); ?>
				</div>
				<div class="ui green ok inverted button">
					<i class="checkmark icon"></i>
					<?php _e('Yes' ); ?>
				</div>
			</div>
		</div>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2><?php _e('sMyles Plugins Licensing' ); ?></h2>
			<h4 class="ui horizontal divider header">
				<i class="wordpress icon"></i>
				<?php _e('Plugins' ); ?>
			</h4>
			<div id="smylesv2-msg" class="ui icon message hidden" style="padding: 0;">
				<i id="smylesv2-msg-close" class="close icon"></i>
				<i id="smylesv2-msg-icon" class="info icon" style="padding-left: 10px;"></i>
				<div class="content" style="padding-top: 10px; padding-bottom: 10px;">
					<div id="smylesv2-msg-header" class="header"></div>
					<p id="smylesv2-msg-details"></p>
				</div>
			</div>
			<div class="ui cards">
		<?php

			wp_nonce_field( 'smyles_updater_v2_activation', 'smyles_updater_v2_activation' );

			$known_plugins = $this->get_known_plugins();

			foreach( $known_plugins as $slug => $plugin ){

				$this->output_card( $slug, $plugin );

			}

		?>
			</div>
		</div>
	<?php
	}

	/**
	 * Output (or Return) Card HTML
	 *
	 *
	 * @since 2.0
	 *
	 * @param      $slug
	 * @param      $plugin
	 * @param bool $return
	 *
	 * @return string
	 */
	function output_card( $slug, $plugin, $return = false ){

		$api_key          = get_option( $slug . '_licence_key' );
		$activation_email = get_option( $slug . '_email' );
		$activation_date  = get_option( $slug . '_activation_date' );

		// Variables are used by included files, do not remove
		// Create human readable activation date from WordPress date format
		$hr_activation_date = ! empty($activation_date) ? date_i18n( get_option( 'date_format' ), $activation_date ) : '';
		$plugin_installed = class_exists( $plugin['class'] ) ? TRUE : FALSE;
		$plugin_image = $this->plugin_image( $slug );

		$product_id       = defined( $plugin['product_id'] ) ? constant( $plugin['product_id'] ) : '';
		$version          = defined( $plugin['version'] ) ? constant( $plugin['version'] ) : '';

		if( $return ) ob_start();
		// Version
		echo "<input type=\"hidden\" id=\"{$slug}_version\" value=\"{$version}\">";
		// Product ID
		echo "<input type=\"hidden\" id=\"{$slug}_product_id\" value=\"{$product_id}\">";

		if( ! $api_key || ! $activation_email ) {
			include __DIR__ . '/views/html-license-activate.php';
		} else {
			include __DIR__ . '/views/html-license-deactivate.php';
		}

		if( $return ) return ob_get_clean();
	}
}
