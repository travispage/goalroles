<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;

	/**
	 * WPJM_Pack_Admin constructor.
	 *
	 * @param $core WP_Job_Manager_Packages
	 */
	public function __construct( $core ) {

		$this->core = $core;
		new WPJM_Pack_Admin_Setup();

		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'parse_query', array( $this, 'parse_query' ) );

	}


	/**
	 * Add Visibility Packages User Menu Item
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function admin_menu(){

		add_submenu_page( 'users.php', __( 'Visibility Packages', 'wp-job-manager-packages' ), __( 'Visibility Packages', 'wp-job-manager-packages' ), 'manage_options', 'job_manager_packages', array(
			$this,
			'packages_page'
		) );
	}

	/**
	 * Output User Packages Page
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function packages_page(){

		global $wpdb;

		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
		$db_table = WPJM_Pack_User::$db_table;

		if( 'delete' === $action && ! empty( $_GET['delete_nonce'] ) && wp_verify_nonce( $_GET['delete_nonce'], 'delete' ) ){
			$package_id = absint( $_REQUEST['package_id'] );
			$wpdb->delete( "{$wpdb->prefix}{$db_table}", array( 'id' => $package_id ) );
			echo sprintf( '<div class="updated"><p>%s</p></div>', __( 'Package successfully deleted', 'wp-job-manager-packages' ) );
		}

		if( 'add' === $action || 'edit' === $action ){

			$this->add_package_page();

		} else {

			$table = new WPJM_Pack_Admin_ListTable( $this->core );
			$table->prepare_items();
			?>
			<div class="woocommerce wrap">
				<h2><?php _e( 'Visibility Packages', 'wp-job-manager-packages' ); ?>
					<a href="<?php echo esc_url( add_query_arg( 'action', 'add', admin_url( 'users.php?page=job_manager_packages' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add User Package', 'wp-job-manager-packages' ); ?></a></h2>
				<form id="package-management" method="post">
					<input type="hidden" name="page" value="job_manager_packages"/>
					<?php $table->display() ?>
					<?php wp_nonce_field( 'save', 'job_manager_packages_nonce' ); ?>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Output Add Package Form
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function add_package_page(){

		$add_package = new WPJM_Pack_Admin_ListTable_AddPackage( $this->core );
		?>
		<div class="woocommerce wrap">
			<h2><?php _e( 'Add User Package', 'wp-job-manager-packages' ); ?></h2>
			<form id="package-add-form" method="post">
				<input type="hidden" name="page" value="job_manager_packages"/>
				<?php $add_package->form() ?>
				<?php wp_nonce_field( 'save', 'job_manager_packages_nonce' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Check for show_ids in $_GET to display specific listings
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function parse_query( $query ){

		global $typenow, $wp_query;

		if( 'job_listing' === $typenow || 'resume' === $typenow ){
			if( isset( $_GET['show_ids'] ) ){
				$posts_to_show = explode( ',', $_GET['show_ids'] );
				$query->query_vars['post__in'] = $posts_to_show;
			}
		}

		return $query;
	}

	/**
	 * Screen IDS
	 *
	 * Add users page to allow WooCommerce styles/script to load (for add user package handling and list table styles)
	 *
	 * @param  array $ids
	 *
	 * @return array
	 */
	public function add_screen_ids( $ids ){

		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce', 'wp-job-manager-packages' ) );

		return array_merge( $ids, array(
			'users_page_job_manager_packages'
		) );
	}

	/**
	 * Register Admin JS/CSS Scripts
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function assets(){

		wp_register_style( 'wpjmpack-sui-icon', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/icon.min.css' ), array(), FALSE );
		wp_register_style( 'wpjmpack-sui-divider', WP_Job_Manager_Packages::url( 'assets/semantic/dist/components/divider.min.css' ), array( 'wpjmpack-sui-icon' ), FALSE );
		wp_register_script( 'wpjmpack_settings', WP_Job_Manager_Packages::url('assets/js/settings.js' ), array( 'jquery' ), false, true );
		wp_register_script( 'wpjmpack_admin', WP_Job_Manager_Packages::url('assets/js/admin.js' ), array( 'jquery' ), false, true );
	}
}
