<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPJM_Pack_Admin_ListTable_AddPackage
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_ListTable_AddPackage {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;

	/**
	 * @var int
	 */
	private $package_id;

	/**
	 * Constructor
	 *
	 * @param $core \WP_Job_Manager_Packages
	 */
	public function __construct( $core ){

		$this->core = $core;

		$this->package_id = isset( $_REQUEST['package_id'] ) ? absint( $_REQUEST['package_id'] ) : 0;

   		if ( ! empty( $_POST['save_package'] ) && ! empty( $_POST['job_manager_packages_nonce'] ) && wp_verify_nonce( $_POST['job_manager_packages_nonce'], 'save' ) ) {
   			$this->save();
   		}
	}

	/**
	 * Output the form
	 */
	public function form() {
		global $wpdb;

		$user_string = '';
		$user_id     = '';
		$db_table = WPJM_Pack_User::$db_table;
		$job_types = $this->core->job->packages->get_package_types();
		$resume_types = $this->core->resume ? $this->core->resume->packages->get_package_types() : array();

		if ( $this->package_id && ( $package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE id = %d;", $this->package_id ) ) ) ) {
			$package_type     = $package->package_type;
			$user_id          = $package->user_id ? $package->user_id : '';
			$product_id       = $package->product_id;
			$order_id         = $package->order_id ? $package->order_id : '';
			$current_status   = $package->status ? $package->status : 'active';
			if ( ! empty( $user_id ) ) {
				$user        = get_user_by( 'id', $user_id );
				$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ' )';
			}

		} else {
			$package_type     = ''; $product_id       = ''; $order_id         = ''; $current_status = 'active';
		}
		wp_enqueue_script( 'wpjmpack_admin' );
		?>
		<style type="text/css">.job-type, .resume-type { display: none; }</style>
		<table class="form-table">
			<tr>
				<th>
					<label for="package_type"><?php _e( 'Package Type', 'wp-job-manager-packages' ); ?></label>
				</th>
				<td>
					<select name="package_type" id="package_type">
						<option data-type="job" value="job_listing" <?php selected( $package_type, 'job_listing' ); ?>><?php printf( __( '%s Package', 'wp-job-manager-packages' ), job_manager_get_job_post_type_label() ); ?></option>
						<option data-type="resume" value="resume" <?php selected( $package_type, 'resume' ); ?>><?php printf( __( '%s Package', 'wp-job-manager-packages' ), job_manager_get_resume_post_type_label() ); ?></option>
					</select>
				</td>
			</tr>

			<?php
				foreach( (array) $job_types as $job_type => $jtc ):
					$allow_var_name = "allow_{$job_type}"; $limit_var_name = "{$job_type}_limit"; $used_var_name = "{$job_type}_used"; $posts_var_name = "{$job_type}_posts";
					$allow_field_value = isset( $package, $package->$allow_var_name ) ? $package->$allow_var_name : 0;
					$limit_field_value = isset( $package, $package->$limit_var_name ) ? $package->$limit_var_name ? $package->$limit_var_name : '' : '';
					$used_field_value = isset( $package, $package->$used_var_name ) ? $package->$used_var_name : '';
					$posts_field_value = isset( $package, $package->$posts_var_name ) ? maybe_unserialize( $package->$posts_var_name ) : array();
					?>
						<tr class="job-type job-type-allow">
							<th>
								<label for="<?php echo $allow_var_name; ?>"><?php printf( __( 'Allow %s?', 'wp-job-manager-packages' ), $jtc['label'] ); ?></label>
								<img class="help_tip tips" data-tip="<?php _e( 'Whether or not this user package supports this feature', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
							</th>
							<td>
								<input type="checkbox" name="<?php echo $allow_var_name; ?>" data-type="<?php echo $job_type; ?>" id="<?php echo $allow_var_name; ?>" class="input-text job-type-allow-input" <?php checked( $allow_field_value, '1' ); ?> />
							</td>
						</tr>

					<?php if( $jtc['limit'] ): ?>

						<tr class="job-type job-type-<?php echo $job_type; ?>">
							<th>
								<label for="<?php echo $limit_var_name; ?>"><?php printf( ' ↳ ' . __( '%s Limit', 'wp-job-manager-packages' ), $jtc['label'] ); ?></label>
								<img class="help_tip tips" data-tip="<?php _e( 'Set specific package limit (Use 0 for unlimited)', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
							</th>
							<td>
								<input type="number" step="1" name="<?php echo $limit_var_name; ?>" data-type="<?php echo $job_type; ?>" id="<?php echo $limit_var_name; ?>" class="input-text regular-text job-type-limit-input" placeholder="<?php _e( 'Unlimited', 'wp-job-manager-packages' ); ?>" value="<?php echo esc_attr( $limit_field_value ); ?>"/>
							</td>
						</tr>
						<tr class="job-type job-type-<?php echo $job_type; ?> job-type-<?php echo $job_type; ?>-used">
							<th>
								<label for="<?php echo $used_var_name; ?>"><?php printf( ' ↳ ' . __( '%s Used', 'wp-job-manager-packages' ), $jtc['label'] ); ?></label>
								<img class="help_tip tips" data-tip="<?php _e( 'Set the total used out of limit available in package', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
							</th>
							<td>
								<input type="number" step="1" name="<?php echo $used_var_name; ?>" id="<?php echo $used_var_name; ?>" class="input-text regular-text" placeholder="0" value="<?php echo esc_attr( $used_field_value ); ?>"/>
							</td>
						</tr>
						<tr class="job-type job-type-<?php echo $job_type; ?> job-type-<?php echo $job_type; ?>-posts">
							<th>
								<label for="<?php echo $posts_var_name; ?>"><?php printf( ' ↳ ' . __( '%s Posts', 'wp-job-manager-packages' ), $jtc['label'] ); ?></label>
								<img class="help_tip tips" data-tip="<?php _e( 'CSV (Comma Separated Value) of Post IDs used', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
							</th>
							<td>
								<select name="<?php echo $posts_var_name; ?>[]" id="<?php echo $posts_var_name; ?>" class="wc-enhanced-select" data-allow_clear="true" data-placeholder="<?php _e( 'Used posts &hellip;', 'wp-job-manager-packages' ) ?>" style="width:25em" multiple>
										<?php
										echo '<option value=""></option>';
										$args                        = array(
											'post_type'      => 'job_listing',
											'posts_per_page' => -1,
											'post_status'    => 'publish',
											'order'          => 'ASC',
											'orderby'        => 'title',
										);

										$jobs = get_posts( $args );

										if( $jobs ){
											foreach( $jobs as $job ) {
												$job_id = is_array( $posts_field_value ) && array_key_exists( $job->ID, $posts_field_value ) ? $job->ID : 0;
												echo '<option value="' . absint( $job->ID ) . '" ' . selected( $job_id, $job->ID ) . '>' . esc_html( $job->post_title ) . ' (' . $job->ID . ')</option>';
											}
										}
										?>
									</select>
							</td>
						</tr>
					<?php endif; ?>

				<?php endforeach; ?>

			<?php

				if( ! empty( $resume_types ) ):

					foreach( (array) $resume_types as $resume_type => $rtc ):
					$allow_var_name = "allow_{$resume_type}"; $limit_var_name = "{$resume_type}_limit"; $used_var_name = "{$resume_type}_used"; $posts_var_name = "{$resume_type}_posts";
					$allow_field_value = isset( $package, $package->$allow_var_name ) ? $package->$allow_var_name : 0;
					$limit_field_value = isset( $package, $package->$limit_var_name ) ? $package->$limit_var_name ? $package->$limit_var_name : '' : '';
					$used_field_value = isset( $package, $package->$used_var_name ) ? $package->$used_var_name : '';
					$posts_field_value = isset( $package, $package->$posts_var_name ) ? maybe_unserialize( $package->$posts_var_name ) : array();
						?>
							<tr class="resume-type resume-type-allow">
								<th>
									<label for="<?php echo $allow_var_name; ?>"><?php printf( __( 'Allow %s?', 'wp-job-manager-packages' ), $rtc['label'] ); ?></label>
									<img class="help_tip tips" data-tip="<?php _e( 'Whether or not this user package supports this feature', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
								</th>
								<td>
									<input type="checkbox" name="<?php echo $allow_var_name; ?>" data-type="<?php echo $resume_type; ?>" id="<?php echo $allow_var_name; ?>" class="input-text resume-type-allow-input" <?php checked( $allow_field_value, '1' ); ?> />
								</td>
							</tr>

						<?php if( $rtc['limit'] ): ?>

							<tr class="resume-type resume-type-<?php echo $resume_type; ?>">
								<th>
									<label for="<?php echo $limit_var_name; ?>"><?php printf( ' ↳ ' .__( '%s Limit', 'wp-job-manager-packages' ), $rtc['label'] ); ?></label>
									<img class="help_tip tips" data-tip="<?php _e( 'Set specific package limit (Use 0 for unlimited)', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
								</th>
								<td>
									<input type="number" step="1" name="<?php echo $limit_var_name; ?>" data-type="<?php echo $resume_type; ?>" id="<?php echo $limit_var_name; ?>" class="input-text regular-text resume-type-limit-input" placeholder="<?php _e( 'Unlimited', 'wp-job-manager-packages' ); ?>" value="<?php echo esc_attr( $limit_field_value ); ?>"/>
								</td>
							</tr>
							<tr class="resume-type resume-type-<?php echo $resume_type; ?> resume-type-<?php echo $resume_type; ?>-used">
								<th>
									<label for="<?php echo $used_var_name; ?>"><?php printf( ' ↳ ' . __( '%s Used', 'wp-job-manager-packages' ), $rtc['label'] ); ?></label>
									<img class="help_tip tips" data-tip="<?php _e( 'Set the total used out of limit available in package', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
								</th>
								<td>
									<input type="number" step="1" name="<?php echo $used_var_name; ?>" id="<?php echo $used_var_name; ?>" class="input-text regular-text" placeholder="0" value="<?php echo esc_attr( $used_field_value ); ?>"/>
								</td>
							</tr>
							<tr class="resume-type resume-type-<?php echo $resume_type; ?> resume-type-<?php echo $resume_type; ?>-posts">
								<th>
									<label for="<?php echo $used_var_name; ?>"><?php printf( ' ↳ ' . __( '%s Posts', 'wp-job-manager-packages' ), $rtc['label'] ); ?></label>
									<img class="help_tip tips" data-tip="<?php _e( 'CSV (Comma Separated Value) of Post IDs used', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
								</th>
								<td>
									<select name="<?php echo $posts_var_name; ?>[]" id="<?php echo $posts_var_name; ?>" class="wc-enhanced-select" data-allow_clear="true" data-placeholder="<?php _e( 'Used posts &hellip;', 'wp-job-manager-packages' ) ?>" style="width:25em" multiple>
										<?php
										echo '<option value=""></option>';
										$args                        = array(
											'post_type'      => 'resume',
											'posts_per_page' => -1,
											'post_status'    => 'publish',
											'order'          => 'ASC',
											'orderby'        => 'title',
										);

										$resumes = get_posts( $args );

										if( $resumes ){
											foreach( $resumes as $resume ) {
												$resume_id = is_array( $posts_field_value ) && array_key_exists( $resume->ID, $posts_field_value ) ? $resume->ID : 0;
												echo '<option value="' . absint( $resume->ID ) . '" ' . selected( $resume_id, $resume->ID ) . '>' . esc_html( $resume->post_title ) . ' (' . $resume->ID . ')</option>';
											}
										}
										?>
									</select>
								</td>
							</tr>
						<?php endif; ?>

					<?php endforeach; ?>

				<?php endif; ?>

			<tr>
				<th>
					<label for="user_id"><?php _e( 'User', 'wp-job-manager-packages' ); ?></label>
				</th>
				<td>
					<?php
						if ( WP_Job_Manager_Packages::is_woocommerce_pre( '3.0.0' ) ) {
							echo '<input type="hidden" class="wc-customer-search" id="user_id" name="user_id" data-placeholder="' . __( 'Guest', 'wp-job-manager-packages' ) . '" data-selected="' . esc_attr( $user_string ) . '" value="' . esc_attr( $user_id ) . '" data-allow_clear="true" style="width:25em" />';
						} else {
							echo '<select class="wc-customer-search" id="user_id" name="user_id" data-placeholder="' . esc_attr( 'Guest' ) . '" data-allow_clear="true">';
							echo '<option value="' . esc_attr( $user_id ) . '" selected="selected">' . htmlspecialchars( $user_string ) . '</option>';
							echo '</select>';
						}
					?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="product_id"><?php _e( 'Product', 'wp-job-manager-packages' ); ?></label>
					<img class="help_tip tips" data-tip="<?php _e( 'Optionally link this package to a product.', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<select name="product_id" class="wc-enhanced-select" data-allow_clear="true" data-placeholder="<?php _e( 'Choose a product&hellip;', 'wp-job-manager-packages' ) ?>" style="width:25em">
						<?php
							echo '<option value=""></option>';
							$find_terms                  = array();
							$job_package                 = get_term_by( 'slug', 'job_visibility_package', 'product_type' );
							$job_package_subscription    = get_term_by( 'slug', 'job_visibility_subscription', 'product_type' );
							$resume_package              = get_term_by( 'slug', 'resume_visibility_package', 'product_type' );
							$resume_package_subscription = get_term_by( 'slug', 'resume_visibility_subscription', 'product_type' );
							$find_terms[]                = $job_package->term_id;
							$find_terms[]                = $job_package_subscription->term_id;
							$find_terms[]                = $resume_package->term_id;
							$find_terms[]                = $resume_package_subscription->term_id;
							$posts_in                    = array_unique( (array) get_objects_in_term( $find_terms, 'product_type' ) );
							$args                        = array(
								'post_type'      => 'product',
								'posts_per_page' => -1,
								'post_status'    => 'publish',
								'order'          => 'ASC',
								'orderby'        => 'title',
								'include'        => $posts_in
							);

							$products = get_posts( $args );

							if ( $products ) {
								foreach ( $products as $product ) {
									echo '<option value="' . absint( $product->ID ) . '" ' . selected( $product_id, $product->ID ) . '>' . esc_html( $product->post_title ) . '</option>';
								}
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="status"><?php _e( 'Status', 'wp-job-manager-packages' ); ?></label>
					<img class="help_tip tips" data-tip="<?php _e( 'Current status of the package.  Package must be active to show in user account and available to the user.  On-hold status normally means it\'s a subscription pending renewal payment.', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<select name="status" class="wc-enhanced-select" data-allow_clear="false" data-placeholder="<?php _e( 'Choose a status&hellip;', 'wp-job-manager-packages' ) ?>" style="width:25em" required>
						<?php
							echo '<option value=""></option>';
							$statuses = apply_filters( 'job_manager_packages_admin_add_package_available_statuses', array( 'active', 'on-hold' ) );
							if ( $statuses ) {
								foreach ( (array) $statuses as $status ) {
									echo '<option value="' . esc_attr( $status ) . '" ' . selected( $current_status, $status ) . '>' . esc_html( $status ) . '</option>';
								}
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="order_id"><?php _e( 'Order ID', 'wp-job-manager-packages' ); ?></label>
					<img class="help_tip tips" data-tip="<?php _e( 'Optionally link this package to an order.', 'wp-job-manager-packages' ); ?>" src="<?php echo WC()->plugin_url() ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<input type="number" step="1" name="order_id" id="order_id" value="<?php echo esc_attr( $order_id ); ?>" class="input-text regular-text" placeholder="<?php _e( 'N/A', 'wp-job-manager-packages' ); ?>" />
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="hidden" name="package_id" value="<?php echo esc_attr( $this->package_id ); ?>" />
			<input type="submit" class="button button-primary" id="save_package" name="save_package" value="<?php _e( 'Save Package', 'wp-job-manager-packages' ); ?>" />
		</p>
		<?php
	}

	/**
	 * Save/Add Package
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function save() {
		global $wpdb;
		$db_table = WPJM_Pack_User::$db_table;
		$posts_data = array();

		try {
			$package_type     = wc_clean( $_POST['package_type'] );

			if( ! isset( $_POST['user_id'] ) || empty( $_POST['user_id'] ) ){
				throw new exception( __( 'Please select a user from the dropdown to associate this user package with.', 'wp-job-manager-packages' ) );
			}
			if( ! isset( $_POST['status'] ) || empty( $_POST['status'] ) ){
				throw new exception( __( 'A status is required for a package, please select one from the dropdown (must be active to show in user account)', 'wp-job-manager-packages' ) );
			}

			$package_data = array(
				'user_id'      => absint( $_POST['user_id'] ),
				'product_id'   => absint( $_POST['product_id'] ),
				'order_id'     => absint( $_POST['order_id'] ),
				'status'	   => sanitize_key( $_POST['status'] ),
				'package_type' => $package_type,
				'handler'      => $this->core->job->handler->who
			);

			if( $package_type === 'resume' ){

				$resume_types = $this->core->resume ? $this->core->resume->packages->get_package_types() : array();

				foreach( (array) $resume_types as $resume_type => $rtc ){
					$allow = "allow_{$resume_type}"; $limit = "{$resume_type}_limit"; $used  = "{$resume_type}_used"; $posts = "{$resume_type}_posts";

					$package_data[ $allow ] = isset( $_POST[$allow] ) ? 1 : 0;

					if( $package_data[$allow] && $rtc['limit'] ){
						$package_data[ $limit ] = absint( $_POST[ $limit ] );
						$package_data[ $used ] = absint( $_POST[ $used ] );
						$posts_data[ $posts ] = isset( $_POST[ $posts ] ) ? $_POST[ $posts ] : array();
					}

				}

			} else {

				$job_types = $this->core->job->packages->get_package_types();

				foreach( (array) $job_types as $job_type => $jtc ){
					$allow = "allow_{$job_type}"; $limit = "{$job_type}_limit"; $used  = "{$job_type}_used"; $posts = "{$job_type}_posts";

					$package_data[ $allow ] = isset( $_POST[$allow] ) ? 1 : 0;

					if( $package_data[ $allow ] && $jtc['limit'] ){
						$package_data[ $limit ] = absint( $_POST[ $limit ] );
						$package_data[ $used ] = absint( $_POST[ $used ] );
						$posts_data[ $posts ] = isset( $_POST[ $posts ] ) ? $_POST[ $posts ] : array();
					}

				}

			}

			if ( $this->package_id ) {

				// Handling of used posts
				if( ! empty( $posts_data ) ){

					// Get existing package ID so we can compare the added timestamps
					$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} WHERE id = %d;", $this->package_id ) );

					// Loop through each posts data we need to update or set (view_posts, etc)
					foreach( $posts_data as $posts_key => $posts_posts ){

						// Get existing posts from package data (if available)
						$existing_posts = $package && isset( $package->$posts_key ) ? maybe_unserialize( $package->$posts_key ) : array();

						// Loop through each post to add to the database
						foreach( $posts_posts as $posts_post ){

							// Set added value to current timestamp (epoch) .. BUT use existing added timestamp if it exists (to not overwrite existing values set by system)
							$added = $package && isset( $existing_posts[ $posts_post ], $existing_posts[$posts_post][ 'added' ] ) ? $existing_posts[$posts_post]['added'] : time();

							$package_data[ $posts_key ][ $posts_post ] = array(
								'id'    => absint( $posts_post ),
								'added' => $added
							);

						}

						// Serialize the data to insert back into db
						$package_data[ $posts_key ] = maybe_serialize( $package_data[$posts_key] );

					}

				}


				$wpdb->update( "{$wpdb->prefix}{$db_table}", $package_data, array( 'id' => $this->package_id ) );
				do_action( 'job_manager_packages_user_update_user_package', $package_data['user_id'], $package_data['product_id'], $package_data['order_id'], $package_data['handler'], $wpdb->insert_id );

			} else {
				$wpdb->insert( "{$wpdb->prefix}{$db_table}", $package_data );
				$this->package_id = $wpdb->insert_id;

				do_action( 'job_manager_packages_user_give_user_package_after', $package_data['user_id'], $package_data['product_id'], $package_data['order_id'], $package_data['handler'], $wpdb->insert_id );
			}

			echo sprintf( '<div class="updated"><p>%s</p></div>', __( 'Package successfully saved', 'wp-job-manager-packages' ) );

		} catch ( Exception $e ) {
			echo sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
		}
	}
}