<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPJM_Pack_Admin_Setup
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Setup {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'redirect' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 12 );
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_dashboard_page( __( 'Setup', 'wp-job-manager-packages' ), __( 'Setup', 'wp-job-manager-packages' ), 'manage_options', 'job-manager-packages-setup', array( $this, 'output' ) );
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'job-manager-packages-setup' );
	}

	/**
	 * Sends user to the setup page on first activation
	 */
	public function redirect() {
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_job_manager_packages_activation_redirect' ) ) {
			return;
	    }

	    if ( ! current_user_can( 'manage_options' ) ) {
	    	return;
	    }

		// Delete the redirect transient
		delete_transient( '_job_manager_packages_activation_redirect' );

		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'wp-job-manager-packages.php' ) ) ) {
			return;
		}

		wp_redirect( admin_url( 'index.php?page=job-manager-packages-setup' ) );
		exit;
	}

	/**
	 * Enqueue scripts for setup page
	 */
	public function admin_enqueue_scripts() {
		//wp_enqueue_style( 'resume_manager_setup_css', RESUME_MANAGER_PLUGIN_URL . '/assets/css/setup.css', array( 'dashicons' ) );
		wp_register_style( 'jmpack-admin-setup', WP_Job_Manager_Packages::url( 'assets/css/setup.css' ), array( 'dashicons' ) );
	}

	/**
	 * Create a page.
	 *
	 * @param  string $title
	 * @param  string $content
	 * @param  string $option
	 *
	 * @return bool
	 */
	public function create_page( $title, $content, $option ) {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => sanitize_title( $title ),
			'post_title'     => $title,
			'post_content'   => $content,
			'post_parent'    => 0,
			'comment_status' => 'closed'
		);
		$page_id = wp_insert_post( $page_data );

		if ( $option && $page_id ) {
			update_option( $option, $page_id );
			return true;
		}

		if( ! $page_id ){
			return false;
		}
	}

	/**
	 * Create a package.
	 *
	 * @param  string $title
	 * @param  array $config
	 *
	 * @return bool
	 */
	public function create_package( $title, $config, $name ) {

		$package_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'post_author'    => 1,
			'post_name'      => sanitize_title( $title ),
			'post_title'     => $title,
			'post_content'   => $config['content'],
			'post_parent'    => 0,
			'comment_status' => 'closed'
		);

		$package_id = wp_insert_post( $package_data );

		if ( $package_id ) {

			foreach( (array) $config['meta'] as $meta_key => $meta_val ) {
				update_post_meta( $package_id, "_{$meta_key}", $meta_val );
			}

			$term = strpos( $name, 'job' ) !== FALSE ? 'job_visibility_package' : 'resume_visibility_package';

			wp_set_object_terms( $package_id, $term, 'product_type' );

			update_post_meta( $package_id, '_regular_price', $config['price'] );
			update_post_meta( $package_id, '_visibility', 'visible' );
			update_post_meta( $package_id, '_stock_status', 'instock' );
			update_post_meta( $package_id, 'total_sales', '0' );
			update_post_meta( $package_id, '_price', $config['price'] );

			return true;

		} else {

			return false;
		}

	}

	/**
	 * Output setup page
	 */
	public function output() {

		wp_enqueue_style( 'jmpack-admin-setup' );

		$step = ! empty( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
		$inc_resumes = WP_Job_Manager_Packages::wprm_active();
		$no_woo = ! class_exists( 'WooCommerce' ) ? true : false;
		$end_step = $inc_resumes ? 5 : 4;

		$job_packages    = array(
			'job_starter_package'    => array(
				'title'   => __( 'Starter Package (View 1, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '0',
				'content' => __( 'View 1 Full Listing<br/>Browse All Listings', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_job_browse' => 'yes',
					'allow_job_view'   => 'yes',
					'view_job_limit'   => 1
				)
			),
			'job_plus_package'       => array(
				'title'   => __( 'Plus Package (View 10, Apply/Contact 5, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '5.99',
				'content' => __( 'View 10 Full Listings<br/>Apply/Contact 5 Listings<br/>Browse All Listings', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_job_browse' => 'yes',
					'allow_job_view'   => 'yes',
					'allow_job_apply'  => 'yes',
					'view_job_limit'   => 10,
					'apply_job_limit'  => 5
				)
			),
			'job_premium_package'    => array(
				'title'   => __( 'Premium Package (View Unlimited, Apply/Contact 10, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '9.99',
				'content' => __( 'View Unlimited Full Listings<br/>Apply/Contact 10 Listings<br/>Browse All Listings', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_job_browse' => 'yes',
					'allow_job_view'   => 'yes',
					'allow_job_apply'  => 'yes',
					'view_job_limit'   => 0,
					'apply_job_limit'  => 10
				)
			),
			'job_basic_apply_single' => array(
				'title'   => __( 'Single Application/Contact', 'wp-job-manager-packages' ),
				'price'   => '2.99',
				'content' => __( 'Apply/Contact 1 Listing', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_job_apply' => 'yes',
					'apply_job_limit' => 1
				)
			),
		);
		$resume_packages = array(
			'resume_starter_package'      => array(
				'title'   => __( 'Starter Package (View 1, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '0',
				'content' => __( 'View 1 Full Resume<br/>Browse All Resumes', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_resume_browse' => 'yes',
					'allow_resume_view'   => 'yes',
					'view_resume_limit'   => 1
				)
			),
			'resume_plus_package'         => array(
				'title'   => __( 'Plus Package (View 10, View 5 Full Name, Contact 5, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '5.99',
				'content' => __( 'View 10 Full Resumes<br/>View 5 Candidate Full Names<br/>Contact 5 Candidates<br/>Browse All Resumes', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_resume_browse'    => 'yes',
					'allow_resume_view'      => 'yes',
					'allow_resume_view_name' => 'yes',
					'allow_resume_contact'   => 'yes',
					'view_resume_limit'      => 10,
					'view_name_resume_limit' => 5,
					'contact_resume_limit'     => 5
				)
			),
			'resume_premium_package'      => array(
				'title'   => __( 'Premium Package (View Unlimited, Unlimited Full Names, Contact 10, Browse All)', 'wp-job-manager-packages' ),
				'price'   => '9.99',
				'content' => __( 'View Unlimited Full Resumes<br/>View Unlimited Candidate Full Names<br/>Contact 10 Candidates<br/>Browse All Resumes', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_resume_browse'    => 'yes',
					'allow_resume_view'      => 'yes',
					'allow_resume_view_name' => 'yes',
					'allow_resume_contact'   => 'yes',
					'view_resume_limit'      => 0,
					'view_name_resume_limit' => 0,
					'contact_resume_limit'     => 10
				)
			),
			'resume_basic_contact_single' => array(
				'title'   => __( 'Single Contact', 'wp-job-manager-packages' ),
				'price'   => '2.99',
				'content' => __( 'Contact 1 Candidate', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_resume_contact' => 'yes',
					'contact_resume_limit' => 1
				)
			),
			'resume_view_name_five'       => array(
				'title'   => __( 'Silver Full Name Package (View 5 Full Names)', 'wp-job-manager-packages' ),
				'price'   => '4.99',
				'content' => __( 'View 5 Candidate Full Names', 'wp-job-manager-packages' ),
				'meta'    => array(
					'allow_resume_view_name' => 'yes',
					'view_name_resume_limit' => 5
				)
			)
		);

		if ( ( ( 3 === $step && ! $inc_resumes ) || ( $inc_resumes && 4 === $step ) ) && ! empty( $_POST ) ) {

			if ( FALSE == wp_verify_nonce( $_REQUEST['setup_wizard'], 'jmpack_setup' ) ) {
				wp_die( 'Error in nonce. Try again.' );
			}

			$create_pages    = isset( $_POST['wp-job-manager-packages-create-page'] ) ? $_POST['wp-job-manager-packages-create-page'] : array();
			$page_titles     = $_POST['wp-job-manager-packages-page-title'];
			$pages_to_create = array(
				'job_visibility_packages'    => '[job_visibility_packages]',
				'job_visibility_dashboard'   => '[job_visibility_dashboard]',
				'browse_job_packages'        => '[browse_job_packages]',
				'apply_job_packages'         => '[apply_job_packages]',
				'view_job_packages'          => '[view_job_packages]',
				'resume_visibility_packages'  => '[resume_visibility_packages]',
				'resume_visibility_dashboard' => '[resume_visibility_dashboard]',
				'browse_resume_packages'      => '[browse_resume_packages]',
				'contact_resume_packages'     => '[contact_resume_packages]',
				'view_resume_packages'        => '[view_resume_packages]',
				'view_name_resume_packages'   => '[view_name_resume_packages]',
			);

			$total_created = 0;

			foreach ( $pages_to_create as $page => $content ) {
				if ( ! isset( $create_pages[ $page ] ) || empty( $page_titles[ $page ] ) ) {
					continue;
				}

				$pre_option = strpos( $page, 'resume' ) !== FALSE ? 'resume' : 'job';

				$create_result = $this->create_page( sanitize_text_field( $page_titles[ $page ] ), $content, "{$pre_option}_manager_{$page}_page_id" );

				if( $create_result ){
					$total_created++;
				}
			}

			if( $total_created > 0 ){
				$step_notice = sprintf( __( 'A total of %d pages were successfully created!', 'wp-job-manager-packages' ), $total_created );
			}

		}

		if( ( ( 4 === $step && ! $inc_resumes ) || ( $inc_resumes && 5 === $step ) ) && ! empty( $_POST ) ){

			if ( FALSE == wp_verify_nonce( $_REQUEST['setup_wizard'], 'jmpack_setup' ) ) {
				wp_die( 'Error in nonce. Try again.' );
			}

			$create_packages    = isset( $_POST['wp-job-manager-packages-create-packages'] ) ? $_POST['wp-job-manager-packages-create-packages'] : array();
			$package_titles     = $_POST['wp-job-manager-packages-package-title'];
			$package_prices     = $_POST['wp-job-manager-packages-package-price'];

			$total_created = 0;

			$combined_packages = array_merge( $job_packages, $resume_packages );

			foreach( $combined_packages as $package_name => $package ) {
				if( ! isset( $create_packages[$package_name] ) || empty( $package_titles[$package_name] ) ){
					continue;
				}

				if( isset( $package_prices[ $package_name ] ) ){
					$package['price'] = sanitize_text_field( $package_prices[ $package_name ] );
				}

				$create_result = $this->create_package( sanitize_text_field( $package_titles[$package_name] ), $package, $package_name );

				if( $create_result ){
					$total_created ++;
				}
			}

			if( $total_created > 0 ){
				$step_notice = sprintf( __( 'A total of %d packages were successfully created!', 'wp-job-manager-packages' ), $total_created );
			}

		}

		?>
		<div class="wrap wp_job_manager wp_job_manager_addons_wrap">
			<h2><?php _e( 'Job Manager Packages Setup', 'wp-job-manager-packages' ); ?></h2>

			<ul class="wp-job-manager-packages-setup-steps <?php if( $inc_resumes ) echo 'wp-job-manager-packages-setup-steps-inc-resume'; ?>">
				<li class="<?php if ( $step === 1 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 1 ) ); ?>"><?php _e( '1. Introduction', 'wp-job-manager-packages' ); ?></a></li>
				<li class="<?php if ( $step === 2 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 2 ) ); ?>"><?php _e( '2. Page Setup', 'wp-job-manager-packages' ); ?></a></li>

				<?php if( $inc_resumes ): ?>
					<li class="<?php if( $step === 3 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 3 ) );  ?>"><?php _e( '3. Resume Page Setup', 'wp-job-manager-packages' ); ?></a></li>
					<li class="<?php if( $step === 4 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 4 ) );  ?>"><?php _e( '4. Packages Setup', 'wp-job-manager-packages' ); ?></a></li>
					<li class="<?php if( $step === 5 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 5 ) );  ;?>"><?php _e( '5. Done', 'wp-job-manager-packages' ); ?></a></li>
				<?php else: ?>
					<li class="<?php if( $step === 3 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 3 ) ); ?>"><?php _e( '3. Packages Setup', 'wp-job-manager-packages' ); ?></a></li>
					<li class="<?php if( $step === 4 ) echo 'wp-job-manager-packages-setup-active-step'; ?>"><a href="<?php echo esc_url( add_query_arg( 'step', 4 ) ); ?>"><?php _e( '4. Done', 'wp-job-manager-packages' ); ?></a></li>
				<?php endif; ?>

			</ul>

			<?php if( 1 === $step ) : ?>

				<h3><?php _e( 'Setup Wizard Introduction', 'wp-job-manager-packages' ); ?></h3>

				<p><?php _e( 'Thanks for installing <em>WP Job Manager Packages</em>!', 'wp-job-manager-packages' ); ?></p>
				<p><?php _e( 'This setup wizard will help you get started by creating the pages for package selection, and configuring those values in the settings for you.', 'wp-job-manager-packages' ); ?></p>
				<p><?php printf( __( 'If you want to skip the wizard and setup the pages and shortcodes yourself manually, you can do so very easily using any of the available shortcodes. Refer to the %sdocumentation%s for help.', 'wp-job-manager-packages' ), '<a href="https://plugins.smyl.es/docs/wp-job-manager-packages/" target="_blank">', '</a>' ); ?></p>

				<?php if( $no_woo ): ?>

					<div class="wp-job-manager-packages-setup-error-box">
						<h2><?php _e( 'WooCommerce Was Not Detected', 'wp-job-manager-packages' ); ?></h2>
						<p><?php _e( 'Currently the only integration available with WP Job Manager Packages, is through WooCommerce (although others will be added later). WooCommerce was not detected as being activated on your site.', 'wp-job-manager-packages' ); ?></p>
						<p><a href="<?php echo admin_url( 'plugin-install.php?s=WooCommerce&tab=search&type=term' ); ?>" class="button button-primary" target="_blank"><?php _e( 'Click Here to Active/Install WooCommerce', 'wp-job-manager-packages' ); ?></a></p>
						<p></p>
						<h4><?php _e('Once you have installed/activated, refresh this page, or click below to continue setup.', 'wp-job-manager-packages'); ?></h4>
					</div>

				<?php endif; ?>

				<p class="submit">
					<a href="<?php echo esc_url( add_query_arg( 'step', 2 ) ); ?>" class="button button-primary"><?php _e( 'Continue to page setup', 'wp-job-manager-packages' ); ?></a>
					<a href="<?php echo esc_url( add_query_arg( 'skip-job-manager-packages-setup', 1, admin_url( 'index.php?page=job-manager-packages-setup&step=' . $end_step ) ) ); ?>" class="button"><?php _e( 'Skip setup. I will setup the plugin manually', 'wp-job-manager-packages' ); ?></a>
				</p>

			<?php endif; ?>
			<?php if ( 2 === $step ) : ?>

				<h3><?php _e( 'Page Setup', 'wp-job-manager-packages' ); ?></h3>

				<p><?php printf( __( '<em>WP Job Manager Packages</em> includes %1$sshortcodes%2$s which can be used within your %3$spages%2$s to output content. These can be created for you below. For more information on the available shortcodes, view the %4$sshortcode documentation%2$s.', 'wp-job-manager-packages' ), '<a href="http://codex.wordpress.org/Shortcode" title="What is a shortcode?" target="_blank" class="help-page-link">', '</a>', '<a href="http://codex.wordpress.org/Pages" target="_blank" class="help-page-link">', '<a href="https://plugins.smyl.es/docs/wp-job-manager-packages/" target="_blank" class="help-page-link">' ); ?></p>

				<form action="<?php echo esc_url( add_query_arg( 'step', 3 ) ); ?>" method="post">
					<?php wp_nonce_field( 'jmpack_setup', 'setup_wizard' ); ?>
					<table class="wp-job-manager-packages-shortcodes widefat">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th><?php _e( 'Page Title', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Page Description', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Content Shortcode', 'wp-job-manager-packages' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><input type="checkbox" name="wp-job-manager-packages-create-page[job_visibility_dashboard]"/></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Job Visibility Dashboard', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[job_visibility_dashboard]"/></td>
								<td>
									<p><?php _e( 'This page will show the list table of all user\'s Job visibility packages.', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is not required, as the list table will automatically output at the bottom of the My Account page (which you can disable in settings).  This should only be used if you want to create a separate page for the Job Visibility Dashboard.  You can also just use the shortcode in any other page to output the list table.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[job_visibility_dashboard]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[job_visibility_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Visibility Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[job_visibility_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used as the default page for package selection, and works for any of the package types (view, browse, etc).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'If you do not plan to use the specific package type shortcodes (for separate pages), you need to make sure this page exists as it will be used as the fallback page if the type specific pages do not exist (or are not configured in settings).', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[job_visibility_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[browse_job_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Browse Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[browse_job_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for browse packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the browse package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[browse_job_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[apply_job_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Apply/Contact Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[apply_job_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for apply/contact packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the apply/contact package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[apply_job_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[view_job_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'View Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[view_job_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for view listing packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the view listing package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[view_job_packages]</code></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="4">
									<input type="submit" class="button button-primary" value="Create selected pages" />
									<a href="<?php echo esc_url( add_query_arg( 'step', 3 ) ); ?>" class="button"><?php _e( 'Skip this step', 'wp-job-manager-packages' ); ?></a>
								</th>
							</tr>
						</tfoot>
					</table>

				</form>

			<?php endif; ?>

			<?php if ( $inc_resumes && 3 === $step ) : ?>

				<?php if( isset( $step_notice ) && ! empty( $step_notice ) ): ?>
				<div class="job-manager-message">
					<?php echo $step_notice; ?>
				</div>
				<?php endif; ?>

				<h3><?php _e( 'Resume Page Setup', 'wp-job-manager-packages' ); ?></h3>
				<p><?php _e('It looks like you have WP Job Manager Resumes installed/activated, so let\'s setup those pages as well!', 'wp-job-manager-packages'); ?></p>
				<p><?php printf( __( '<em>WP Job Manager Packages</em> includes %1$sshortcodes%2$s which can be used within your %3$spages%2$s to output content. These can be created for you below. For more information on the available shortcodes, view the %4$sshortcode documentation%2$s.', 'wp-job-manager-packages' ), '<a href="http://codex.wordpress.org/Shortcode" title="What is a shortcode?" target="_blank" class="help-page-link">', '</a>', '<a href="http://codex.wordpress.org/Pages" target="_blank" class="help-page-link">', '<a href="https://plugins.smyl.es/docs/wp-job-manager-packages/" target="_blank" class="help-page-link">' ); ?></p>

				<form action="<?php echo esc_url( add_query_arg( 'step', 4 ) ); ?>" method="post">
					<?php wp_nonce_field( 'jmpack_setup', 'setup_wizard' ); ?>
					<table class="wp-job-manager-packages-shortcodes widefat">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th><?php _e( 'Page Title', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Page Description', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Content Shortcode', 'wp-job-manager-packages' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><input type="checkbox" name="wp-job-manager-packages-create-page[resume_visibility_dashboard]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Resume Visibility Dashboard', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[resume_visibility_dashboard]" /></td>
								<td>
									<p><?php _e( 'This page will show the list table of all user\'s Resume visibility packages.', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is not required, as the list table will automatically output at the bottom of the My Account page (which you can disable in settings).  This should only be used if you want to create a separate page for the Resume Visibility Dashboard.   You can also just use the shortcode in any other page to output the list table.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[resume_visibility_dashboard]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[resume_visibility_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Resume Visibility Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[resume_visibility_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used as the default page for package selection, and works for any of the package types (view, browse, etc).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'If you do not plan to use the specific package type shortcodes (for separate pages), you need to make sure this page exists as it will be used as the fallback page if the type specific pages do not exist (or are not configured in settings).', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[resume_visibility_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[browse_resume_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Browse Resume Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[browse_resume_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for browse packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the browse package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[browse_resume_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[contact_resume_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'Resume Contact Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[contact_resume_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for apply/contact packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the apply/contact package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[contact_resume_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[view_resume_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'View Resume Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[view_resume_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for view listing packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the view listing package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[view_resume_packages]</code></td>
							</tr>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-page[view_name_resume_packages]" /></td>
								<td><input type="text" value="<?php echo esc_attr( _x( 'View Name Resume Packages', 'Default page title (wizard)', 'wp-job-manager-packages' ) ); ?>" name="wp-job-manager-packages-page-title[view_name_resume_packages]" /></td>
								<td>
									<p><?php _e( 'This page will be used only for view name listing packages (not required).', 'wp-job-manager-packages' ); ?></p>

									<p><?php _e( 'This page is useful if you wish to customize the view listing package selection page, separate from the standard one above.', 'wp-job-manager-packages' ); ?></p>
								</td>
								<td><code>[view_name_resume_packages]</code></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="4">
									<input type="submit" class="button button-primary" value="Create selected pages" />
									<a href="<?php echo esc_url( add_query_arg( 'step', 4 ) ); ?>" class="button"><?php _e( 'Skip this step', 'wp-job-manager-packages' ); ?></a>
								</th>
							</tr>
						</tfoot>
					</table>

				</form>

			<?php endif; ?>

			<?php if( ( ! $inc_resumes && 3 === $step ) || ( $inc_resumes && 4 === $step ) ) : ?>

				<?php if( isset( $step_notice ) && ! empty( $step_notice ) ): ?>
				<div class="job-manager-message">
					<?php echo $step_notice; ?>
				</div>
				<?php endif; ?>

				<h3><?php _e( 'Package Setup', 'wp-job-manager-packages' ); ?></h3>
				<p><?php _e('If you would like, we can also setup <strong>DEMO</strong> packages for you to use.  This is not required, and is provided for demonstration purposes only, you will still need to customize the packages to suit your needs.', 'wp-job-manager-packages'); ?></p>

				<?php if( $no_woo ): ?>

					<div class="wp-job-manager-packages-setup-error-box">
						<h2><?php _e( 'WooCommerce Was Not Detected', 'wp-job-manager-packages' ); ?></h2>
						<p><?php _e( 'Currently the only integration available with WP Job Manager Packages, is through WooCommerce (although others will be added later). WooCommerce was not detected as being activated on your site.', 'wp-job-manager-packages' ); ?></p>
						<p><?php _e( 'DO NOT proceed with this step before installing and activating WooCommerce!  WooCommerce must be installed and activated before adding packages!', 'wp-job-manager-packages' ); ?></p>
						<p><a href="<?php echo admin_url( 'plugin-install.php?s=WooCommerce&tab=search&type=term' ); ?>" class="button button-primary" target="_blank"><?php _e( 'Click Here to Active/Install WooCommerce', 'wp-job-manager-packages' ); ?></a></p>
						<p></p>
						<h4><?php _e( 'Once you have installed AND activated, refresh this page, or click below to continue setup.', 'wp-job-manager-packages' ); ?></h4>
					</div>

				<?php endif; ?>

				<form action="<?php echo esc_url( add_query_arg( 'step', $end_step ) ); ?>" method="post">
					<?php wp_nonce_field( 'jmpack_setup', 'setup_wizard' ); ?>
					<table class="wp-job-manager-packages-shortcodes widefat">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th><?php _e( 'Package Title', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Package Price', 'wp-job-manager-packages' ); ?></th>
								<th><?php _e( 'Package Description', 'wp-job-manager-packages' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<tr>
							<td colspan="4" style="text-align: center; background-color: rgb(0, 116, 162);">
								<span style="color: #ffffff;"><?php _e( 'Listing Packages', 'wp-job-manager-packages' ); ?></span>
							</td>
						</tr>
						<?php foreach( $job_packages as $job_package_slug => $job_package ): ?>
							<tr>
								<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-packages[<?php echo $job_package_slug; ?>]"/></td>
								<td><input type="text" value="<?php echo $job_package['title']; ?>" name="wp-job-manager-packages-package-title[<?php echo $job_package_slug; ?>]"/></td>
								<td><input type="text" value="<?php echo $job_package['price']; ?>" name="wp-job-manager-packages-package-price[<?php echo $job_package_slug; ?>]"/></td>
								<td><?php echo str_replace( '<br/>', ', ', $job_package['content'] ); ?></td>
							</tr>
						<?php endforeach; ?>

						<?php if( $inc_resumes ): ?>
							<tr>
								<td colspan="4" style="text-align: center; background-color: rgb(0, 116, 162);">
									<span style="color: #ffffff;"><?php _e( 'Resume Packages', 'wp-job-manager-packages' ); ?></span>
								</td>
							</tr>
							<?php foreach( $resume_packages as $resume_package_slug => $resume_package ): ?>
								<tr>
									<td><input type="checkbox" checked="checked" name="wp-job-manager-packages-create-packages[<?php echo $resume_package_slug; ?>]"/></td>
									<td><input type="text" value="<?php echo $resume_package['title']; ?>" name="wp-job-manager-packages-package-title[<?php echo $resume_package_slug; ?>]"/></td>
									<td><input type="text" value="<?php echo $resume_package['price']; ?>" name="wp-job-manager-packages-package-price[<?php echo $resume_package_slug; ?>]"/></td>
									<td><?php echo str_replace( '<br/>', ', ', $resume_package['content'] ); ?></td>
								</tr>
							<?php endforeach; ?>

						<?php endif; ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="4">
									<input type="submit" class="button button-primary" value="<?php _e('Create Selected Packages', 'wp-job-manager-packages'); ?>" />
									<a href="<?php echo esc_url( add_query_arg( 'step', $end_step ) ); ?>" class="button"><?php _e( 'Skip this step', 'wp-job-manager-packages' ); ?></a>
								</th>
							</tr>
						</tfoot>
					</table>

				</form>

			<?php endif; ?>

			<?php if ( ( ! $inc_resumes && 4 === $step ) || ( $inc_resumes && 5 === $step ) ) : ?>

				<?php if( isset( $step_notice ) && ! empty( $step_notice ) ): ?>
					<div class="job-manager-message">
					<?php echo $step_notice; ?>
				</div>
				<?php endif; ?>

				<h3><?php _e( 'All Done!', 'wp-job-manager-packages' ); ?></h3>

				<p><?php _e( 'Looks like you\'re all set to start using the plugin. In case you\'re wondering where to go next:', 'wp-job-manager-packages' ); ?></p>

				<ul class="wp-job-manager-packages-next-steps">
					<li><a href="<?php echo admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings' ); ?>"><?php _e( 'Tweak Visibility Settings', 'wp-job-manager-packages' ); ?></a></li>
					<?php if( $inc_resumes ): ?>
						<li><a href="<?php echo admin_url( 'edit.php?post_type=resume&page=resume-manager-settings' ); ?>"><?php _e( 'Tweak Resume Visibility Settings', 'wp-job-manager-packages' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo admin_url( 'post-new.php?post_type=product' ); ?>"><?php _e( 'Add a new Product/Package', 'wp-job-manager-packages' ); ?></a></li>
				</ul>

				<p><?php printf( __( 'And don\'t forget, if you need any more help using <em>WP Job Manager Packages</em> you can consult the %1$sdocumentation%2$s or %3$scontact me via the support area%2$s!', 'wp-job-manager-packages' ), '<a href="https://plugins.smyl.es/docs/wp-job-manager-packages">', '</a>', '<a href="https://plugins.smyl.es/support/">' ); ?></p>

				<h2><?php _e('Flow Process:', 'wp-job-manager-packages'); ?></h2>
				<p><?php _e('To give you an understanding of how this plugin works, below is the flow process with examples. Examples below assume you have configured packages required for browse, view, and contact/apply.  This also assumes you have used the shortcode to output the form either on a page created in a previous setup step, or placed the shortcode inside the placeholder editor (in settings).', 'wp-job-manager-packages'); ?></p>
				<ol class="wp-job-manager-packages-next-steps">
					<li>
						<?php _e('User visits Listings page and is either redirected to package form page, or shown the package selection form.', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('User selects a package, and is redirected to checkout page to complete purchase.', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('After purchase is complete, notification is shown with link to return to page user was previously viewing.', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('When user selects a listing, they will be shown the listing like normal (if they have unlimited package), otherwise they are either redirected or shown the package form page (like above).', 'wp-job-manager-packages' ); ?>
					</li>
					<li>
						<?php _e('User will be able to select an existing package from the package form to use (or they can select one to purchase).  Once they submit the form, they are redirected back to the listing to view like normal.', 'wp-job-manager-packages'); ?>
					</li>
				</ol>
				<h2><?php _e('Few key points:', 'wp-job-manager-packages'); ?></h2>
				<ul class="wp-job-manager-packages-next-steps">
					<li><?php _e( 'If the user has an unlimited package, they will immediately be able to view, contact, etc.  Only packages with limits will require them to select a package to use.', 'wp-job-manager-packages' ); ?></li>
					<li><?php _e( 'All current user packages can be viewed (along with associated listings) on the My Account page.', 'wp-job-manager-packages' ); ?></li>
					<li>
						<?php _e('Administrators are not required to have packages, make sure to use a separate user account to test with.', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('This plugin has been tested with Listify, Jobify, Listable, Pet Sitter, and Babysitter themes ... if you have issues, or your theme is not compatible, please let me know!', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('To use a template override, make sure you place the file in the <code>jm_packages</code> directory/folder, in your child theme\'s directory/folder.', 'wp-job-manager-packages' ); ?>
					</li>
					<li>
						<?php _e('Listable currently only supports the redirect feature.', 'wp-job-manager-packages'); ?>
					</li>
					<li>
						<?php _e('Subscriptions will be included in one of the next releases!', 'wp-job-manager-packages' ); ?>
					</li>
					<li>
						<?php _e('Currently WooCommerce is the only support backend payment/cart/e-commerce system.  Let me know if there\'s another one you would like integrated.', 'wp-job-manager-packages' ); ?>
					</li>
					<li>
						<?php _e('This plugin is full documented, check out the source code!', 'wp-job-manager-packages' ); ?>
					</li>
				</ul>

				<div class="wp-job-manager-packages-support-the-plugin">
					<h3><?php _e( 'Support the Ongoing Development of the core plugin, WP Job Manager', 'wp-job-manager-packages' ); ?></h3>
					<p><?php _e( 'There are many ways to support open-source projects such as WP Job Manager, for example code contribution, translation, or even telling your friends how awesome the plugin (hopefully) is. Thanks in advance for your support - it is much appreciated!', 'wp-job-manager-packages' ); ?></p>
					<ul>
						<li class="icon-review"><a href="https://wordpress.org/support/view/plugin-reviews/wp-job-manager#postform"><?php _e( 'Leave a positive review', 'wp-job-manager-packages' ); ?></a></li>
						<li class="icon-localization"><a href="https://www.transifex.com/projects/p/wp-job-manager/"><?php _e( 'Contribute a localization', 'wp-job-manager-packages' ); ?></a></li>
						<li class="icon-code"><a href="https://github.com/automattic/wp-job-manager"><?php _e( 'Contribute code or report a bug', 'wp-job-manager-packages' ); ?></a></li>
						<li class="icon-forum"><a href="https://wordpress.org/support/plugin/wp-job-manager"><?php _e( 'Help other users on the forums', 'wp-job-manager-packages' ); ?></a></li>
					</ul>
				</div>

			<?php endif; ?>
		</div>
		<?php
	}
}