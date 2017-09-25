<?php
global $wpjm_pack_post;
/**
 * @var $form WPJM_Pack_Form
 */

/**
 * The $product->eattr() method will escape and echo out variable passed to it (if there is a value)
 *
 * @see WC_Product_Job_Visibility_Package (wcp-job-visibility-package.php)
 * @see WC_Product_Resume_Visibility_Package (wcp-resume-visibility-package.php)
 * @see WPJM_Pack_User_Package
 */
wp_enqueue_style( 'jmpack-std-pkg-select' );
$slug = esc_attr($form->type->slug);
if ( $form->packages || $form->user_packages ) :
	// Set/select radio on user package first by default
	$checked = 1;
	$maybe_title = '';
	// Build listing link for output
	if ( $form->listing_id && in_array( get_post_type( $form->listing_id ), array( 'job_listing', 'resume' ) ) ) {
		$maybe_title = '<small style="font-style: italic;">' . sprintf( __( ' (for: <a href="%1$s" target="_blank">%2$s</a>)', 'wp-job-manager-packages' ), get_permalink( $form->listing_id ), get_the_title( $form->listing_id ) ) . '</small>';
	}
	?>
	<ul class="<?php echo $slug; ?>_packages">
		<?php if ( $form->user_packages && $form->listing_id ) : ?>
			<?php
				/**
				 * Filter Listing link/title output next to Your Packages
				 *
				 * Return an empty string to disable output next to Your Packages
				 *
				 * @since @@since
				 *
				 * @param string          $maybe_title    Empty string if no listing ID found, otherwise link to listing
				 * @param \WPJM_Pack_Form $form           From class object
				 */
				if( $your_packages_title = apply_filters( 'job_manager_packages_user_packages_use_for_show_title', $maybe_title, $form ) ){
					// Set $maybe_title to null if $your_packages_title is not empty string (to not dupe output after Purchase Package:)
					$maybe_title = null;
				}
			?>
			<li class="package-section"><?php printf( __( 'Your Packages%1$s:', 'wp-job-manager-packages' ), $your_packages_title ); ?></li>
			<?php foreach ( (array) $form->user_packages as $key => $package ) :
				$package = job_manager_packages_get_user_package( $package );
				?>
				<li class="user-<?php echo $slug; ?>-package">
					<input type="radio" <?php checked( $checked, 1 ); ?> name="<?php echo $slug; ?>_visibility_package" value="user-<?php echo $key; ?>" id="user-package-<?php $package->eattr( 'id' ); ?>" />
					<label for="user-package-<?php $package->eattr( 'id' ) ?>"><?php echo $package->get_title(); ?></label><br/>
					<?php
						$used = $package->get_used( $form->form_type );
						$limit = $package->get_limit( $form->form_type );
						$type_label = $form->type->packages->get_package_type( $form->form_type, 'label', true );

						printf( __( '%1$s out of %2$d available %3$s used', 'wp-job-manager-packages' ), $used, $limit, $type_label );

						// Set back to zero
						$checked = 0;
					?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $form->packages ) : ?>
			<?php
				/**
				 * Filter Listing link/title output next to Purchase Package
				 *
				 * @since @@since
				 *
				 * @param string          $maybe_title    Empty string if already output in Your Packages, or if no listing ID found, otherwise link to listing
				 * @param \WPJM_Pack_Form $form           From class object
				 * @param bool            $already_output Whether or not link has already been output in the Your Packages area
				 */
				$purchase_package_title = apply_filters( 'job_manager_packages_user_packages_use_for_show_title', $maybe_title === null ? '' : $maybe_title, $form, $maybe_title === null );
			?>
			<li class="package-section"><?php printf( __( 'Purchase Package%1$s:', 'wp-job-manager-packages' ), $purchase_package_title ); ?></li>
			<?php foreach ( $form->packages as $key => $package ) :
				// Get the product object
				$product = $form->type->handler->get_product( $package );
				// Continue to next in loop if not one of our types, or not purchasable
				if ( ! $product->is_type( array( $form->type->package_type, $form->type->sub_type ) ) || ! $product->is_purchasable() ) {
					continue;
				}
			?>
				<li class="<?php echo $slug; ?>-package">
					<input type="radio" <?php checked( $checked, 1 ); ?> name="<?php echo $slug; ?>_visibility_package" value="<?php echo $product->get_id(); ?>" id="package-<?php echo $product->get_id(); ?>" />
					<label class="<?php echo $slug; ?>-package-label" for="package-<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></label><br/>

					<?php
						// Output Product short description if enabled in settings
						if( $product->use_short_description() ){
							// Set global for shortcode support (when using excerpt/short description)
							$wpjm_pack_post = $package;
							echo do_shortcode( $package->post_excerpt );

						} else {
							// Otherwise output generated summary
							$form->type->packages->output_selection_summary( $product );
						}

						$checked = 0;
					?>

				</li>

			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
<?php else : ?>

	<p><?php _e( 'No packages found', 'wp-job-manager-packages' ); ?></p>

<?php endif; ?>