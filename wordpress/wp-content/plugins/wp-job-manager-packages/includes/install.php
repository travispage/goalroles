<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Install
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Install {

	/**
	 * WPJM_Pack_Install constructor.
	 */
	public function __construct() {

	}

	/**
	 * Do Initial Install
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public static function do_install(){

		global $wpdb;

		$wpdb->hide_errors();

		$collate = '';
		if( $wpdb->has_cap( 'collation' ) ){
			if( ! empty( $wpdb->charset ) ){
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if( ! empty( $wpdb->collate ) ){
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$user_db_table = WPJM_Pack_User::$db_table;

		/**
		 * Table for user packages
		 */
		$sql = "
CREATE TABLE {$wpdb->prefix}{$user_db_table} (
  id bigint(20) NOT NULL auto_increment,
  user_id bigint(20) NOT NULL,
  product_id bigint(20) NOT NULL,
  order_id bigint(20) NOT NULL default 0,
  status varchar(20) NULL default 'active',
  allow_view int(1) NULL,
  allow_view_name int(1) NULL,
  allow_browse int(1) NULL,
  allow_apply int(1) NULL,
  allow_contact int(1) NULL,
  view_limit bigint(20) NOT NULL,
  view_used bigint(20) NOT NULL default 0,
  view_posts longtext NULL,
  view_name_limit bigint(20) NULL,
  view_name_used bigint(20) NOT NULL default 0,
  view_name_posts longtext NULL,
  apply_limit bigint(20) NULL,
  apply_used bigint(20) NOT NULL default 0,
  apply_posts longtext NULL,
  contact_limit bigint(20) NULL,
  contact_used bigint(20) NOT NULL default 0,
  contact_posts longtext NULL,
  package_type varchar(100) NOT NULL,
  handler varchar(100) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
";
		dbDelta( $sql );

		// Update version
		update_option( 'wpjmpack_db_version', WP_Job_Manager_Packages::VERSION );

		add_action( 'shutdown', array( 'WPJM_Pack_Install', 'delayed_install' ) );

		self::setup();
	}

	/**
	 * Execute Setup
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public static function setup(){

		$ph_options = array(
			'job_manager_job_visibility_require_package_browse_ph' => sprintf( __( 'A <a href="%s">package</a> is required to browse listings, please select one from below.', 'wp-job-manager-packages' ), '[browse_job_packages_url]' ) . '<br/>[browse_job_packages]',
		    'job_manager_job_visibility_require_package_view_ph' => '[view_job_packages]',
		    'job_manager_job_visibility_require_package_apply_ph' => '[apply_job_packages]',
		    'job_manager_resume_visibility_require_package_browse_ph' => sprintf( __( 'Please <a href="%s">select a package</a> to browse resumes.', 'wp-job-manager-packages' ), '[browse_resume_packages_url]' ),
		    'job_manager_resume_visibility_require_package_view_ph' => '[view_resume_packages]',
		    'job_manager_resume_visibility_require_package_view_name_ph' => sprintf( __( 'Please select a <a href="%s">package</a> to view the full candidate name.', 'wp-job-manager-packages' ), '[view_name_resume_packages_url]' ),
		    'job_manager_resume_visibility_require_package_contact_ph' => '[contact_resume_packages]',
		);

		if( ! get_option( 'wp_job_manager_packages_version' ) ){
			set_transient( '_job_manager_packages_activation_redirect', 1, HOUR_IN_SECONDS );

			// Set default placeholder values
			foreach( $ph_options as $ph_option => $ph_value ){
				if( ! get_option( $ph_option, false ) ){
					update_option( $ph_option, $ph_value );
				}
			}

		}

		update_option( 'wp_job_manager_packages_version', WP_Job_Manager_Packages::VERSION );
	}

	/**
	 * Copy Plugin Image to Other Plugins
	 *
	 * Without the plugin image available, users wont be able to activate the plugin from the smyles licenses page
	 * as the missing image will cause the inputs to not be useable.  To get around this, on install/activate of this
	 * plugin, we check if the files exist already, and if not, copy them over.
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public static function updater_files(){

		/**
		 * @var $wp_filesystem WP_Filesystem_Direct
		 */
		global $wp_filesystem;

		if( ! $wp_filesystem && ! WP_Filesystem() ){
			return;
		}

		$logo_path = WP_Job_Manager_Packages::dir( '/includes/updater/assets/wp-job-manager-packages.png' );
		$fs_logo = str_replace( ABSPATH, $wp_filesystem->abspath(), $logo_path );

		// WP Job Manager
		if( defined( 'WPJM_FIELD_EDITOR_PLUGIN_DIR' ) ){
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), WPJM_FIELD_EDITOR_PLUGIN_DIR );

			if( $wp_filesystem->exists( $plugin_path . '/includes/updater/assets/' ) && ! $wp_filesystem->exists( $plugin_path . '/includes/updater/assets/wp-job-manager-packages.png' ) ){
				$wp_filesystem->copy( $fs_logo, $plugin_path . '/includes/updater/assets/wp-job-manager-packages.png' );
			}
		}

		// WP Job Manager Emails
		if( defined( 'JOB_MANAGER_EMAILS_PLUGIN_DIR' ) ){
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), JOB_MANAGER_EMAILS_PLUGIN_DIR );

			if( $wp_filesystem->exists( $plugin_path . '/includes/updater/assets/' ) && ! $wp_filesystem->exists( $plugin_path . '/includes/updater/assets/wp-job-manager-packages.png' ) ){
				$wp_filesystem->copy( $fs_logo, $plugin_path . '/includes/updater/assets/wp-job-manager-packages.png' );
			}
		}

	}

	/**
	 * Delayed Install Setup
	 *
	 * This method is called on shutdown which the action is added on plugin activation.
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public static function delayed_install(){

		WPJM_Pack_WC::delayed_install();
		self::updater_files();
	}

}

new WPJM_Pack_Install();