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
wp_enqueue_style( 'jmpack-listable-ps' );
if( ! function_exists( 'listable_get_woocommerce_price_format_on_listing_visibility' ) ){
	function listable_get_woocommerce_price_format_on_listing_visibility( $format, $currency_pos ){

		$currency_pos = get_option( 'woocommerce_currency_pos' );
		$format       = '%1$s%2$s';

		switch ( $currency_pos ) {
			case 'left' :
				$format = '<sup class="package__currency">%1$s</sup>%2$s';
				break;
			case 'right' :
				$format = '%2$s<sup class="package__currency">%1$s</sup>';
				break;
			case 'left_space' :
				$format = '<sup class="package__currency">%1$s</sup>&nbsp;%2$s';
				break;
			case 'right_space' :
				$format = '%2$s&nbsp;<sup class="package__currency">%1$s</sup>';
				break;
		}

		return $format;
	}
}

if( ! has_filter( 'woocommerce_price_format', 'listable_get_woocommerce_price_format_on_paid_listings') ){
	add_filter( 'woocommerce_price_format', 'listable_get_woocommerce_price_format_on_listing_visibility', 10, 2 );
}

$slug = esc_attr($form->type->slug);
if ( $form->packages || $form->user_packages ) :
	// Set/select radio on user package first by default
	$checked = 1;
	?>

		<?php if ( $form->user_packages && $form->listing_id ) : ?>
			<h2 class="package-list__title"><?php _e( 'Your packages', 'listable', 'wp-job-manager-packages' ); ?></h2>
				<div class="package-list  package-list--user visibility-package-list">
					<?php foreach ( (array) $form->user_packages as $key => $package ) :
						$package = job_manager_packages_get_user_package( $package );
						?>

						<div class="package  package--featured">
							<h2 class="package__title"><?php echo $package->get_title(); ?></h2>
							<div class="package__content">
							<?php
								$used = $package->get_used( $form->form_type );
								$limit = $package->get_limit( $form->form_type );
								$type_label = $form->type->packages->get_package_type( $form->form_type, 'label', TRUE );

								printf( __( '%1$s out of %2$d available %3$s used', 'wp-job-manager-packages' ), $used, $limit, $type_label );

								// Set back to zero
								$checked = 0;
							?>
							</div>
							<button class="btn package__btn" type="submit" name="<?php echo $slug; ?>_visibility_package" value="user-<?php echo $key; ?>" id="package-<?php $package->eattr( 'id' ); ?>">
								<?php _e('Select Package', 'wp-job-manager-packages' ) ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
		<?php endif; ?>
		<?php if ( $form->packages ) : ?>
			<?php if ( $form->user_packages ) : ?>
				<h2 class="package-list__title"><?php _e( 'Purchase packages', 'listable', 'wp-job-manager-packages' ); ?></h2>
			<?php endif; ?>
			<div class="package-list">
			<?php foreach ( $form->packages as $key => $package ) :
				// Get the product object
				$product = $form->type->handler->get_product( $package );
				// Continue to next in loop if not one of our types, or not purchasable
				if ( ! $product->is_type( array( $form->type->package_type, $form->type->sub_type ) ) || ! $product->is_purchasable() ) {
					continue;
				}
				$tags = get_the_terms($product->get_id(), 'product_tag');
				$taggedClass = ( ! is_wp_error( $tags ) && ! empty($tags) ) ? 'package--labeled' : '';
				$taggedClass = $taggedClass !== '' ? $taggedClass . ' ' . $taggedClass . '-' . $tags[0]->slug : '';
				?>
				<div class="package <?php echo $taggedClass; ?>" style="margin-bottom: 20px;">
					<?php if ( ! is_wp_error( $tags ) && ! empty($tags) ) { ?>
						<div class="featured-label"><?php echo $tags[0]->name; ?></div>
					<?php } ?>
					<h2 class="package__title">
						<?php echo $product->get_title(); ?>
					</h2>
					<div class="package__price">
						<?php if ( $product->price ){
							echo wc_price( $product->price );
						} else {
							esc_html_e('Free', 'listable', 'wp-job-manager-packages');
						} ?>
					</div>
					<div class="package__description">
					<?php if( $product->use_short_description() ){

							// Remove listable filter (to output price in short description)
							if( has_filter( 'woocommerce_price_format', 'listable_get_woocommerce_price_format_on_paid_listings' ) ){
								remove_filter( 'woocommerce_price_format', 'listable_get_woocommerce_price_format_on_paid_listings', 10 );
								$filter	= 'listable_get_woocommerce_price_format_on_paid_listings';
							} elseif( has_filter('woocommerce_price_format', 'listable_get_woocommerce_price_format_on_listing_visibility' ) ) {
								remove_filter( 'woocommerce_price_format', 'listable_get_woocommerce_price_format_on_listing_visibility', 10 );
								$filter = 'listable_get_woocommerce_price_format_on_listing_visibility';
							}

							// Set global for shortcode support (when using excerpt/short description)
							$wpjm_pack_post = $package;
							echo do_shortcode( $package->post_excerpt );

							// Add filter back
							if( isset( $filter ) ){
								add_filter( 'woocommerce_price_format', $fitler, 10, 2 );
							}

						} else {
							// Otherwise output generated summary
							$form->type->packages->output_selection_summary( $product );
						}

						$checked = 0;
					?>
					</div>
					<div class="package__content">
						<?php //echo apply_filters( 'the_content', $product->post->post_content ) ?>
					</div>
					<button class="btn package__btn" type="submit" name="<?php echo $slug; ?>_visibility_package" value="<?php echo $product->get_id(); ?>" id="package-<?php echo $product->get_id(); ?>">
						<?php _e('Get Started', 'listable', 'wp-job-manager-packages') ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

<?php else : ?>

	<p class="no-packages"><?php _e( 'No packages found', 'wp-job-manager-packages' ); ?></p>

<?php endif; ?>