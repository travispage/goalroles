<div class="options_group show_if_<?php echo $product_type; ?> show_if_<?php echo $sub_type; ?> <?php echo $product_type; ?>_listing_options <?php echo $sub_type; ?>_listing_options">
	<?php
		global $woocommerce;
		$debug = $product_type;
	?>

	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_job_view',
		                               'label'       => sprintf( __( 'View %s?', 'wp-job-manager-packages' ), $label ),
		                               'description' => __( 'Allow to view single listings with this package (if package required in settings).', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_allow_job_view', TRUE )
	                               ) ); ?>
	<?php woocommerce_wp_text_input( array(
		                                 'id'                => '_view_job_limit',
		                                 'label'             => sprintf( __( 'View %s limit', 'wp-job-manager-packages' ), $label ),
		                                 'description'       => __( 'The number of listings a user can view with this package.  Leave blank to allow unlimited.', 'wp-job-manager-packages' ),
		                                 'value'             => ( $limit = get_post_meta( $post_id, '_view_job_limit', TRUE ) ) ? $limit : '',
		                                 'placeholder'       => __( 'Unlimited', 'wp-job-manager-packages' ),
		                                 'type'              => 'number',
		                                 'desc_tip'          => TRUE,
		                                 'custom_attributes' => array(
			                                 'min'  => '',
			                                 'step' => '1'
		                                 )
	                                 ) ); ?>
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_job_view_rollover',
		                               'wrapper_class' => 'show_if_' . $sub_type . ' hide_if_' . $product_type,
		                               'label'       => __( 'View Rollover?', 'wp-job-manager-packages' ),
		                               'description' => __( 'Rollover (Add) unused credits to package when subscription is renewed.', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_allow_job_view_rollover', TRUE )
	                               ) ); ?>
<hr/>
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_job_apply',
		                               'label'       => sprintf( __( 'Allow %s?', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() ),
		                               'description' => sprintf( __( 'Allow to %s listings with this package (if package required in settings).', 'wp-job-manager-packages' ), $apply_label ),
		                               'value'       => get_post_meta( $post_id, '_allow_job_apply', TRUE )
	                               ) ); ?>

	<?php woocommerce_wp_text_input( array(
		                           'id'                => '_apply_job_limit',
		                           'label'             => sprintf( __( '%1$s %2$s limit', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() , $label ),
		                           'description'       => sprintf( __( 'The number of listings a user can %s with this package. Leave blank to allow unlimited.', 'wp-job-manager-packages' ), $apply_label  ),
		                           'value'             => ( $limit = get_post_meta( $post_id, '_apply_job_limit', TRUE ) ) ? $limit : '',
		                           'placeholder'       => __( 'Unlimited', 'wp-job-manager-packages' ),
		                           'type'              => 'number',
		                           'desc_tip'          => TRUE,
		                           'custom_attributes' => array(
			                           'min'  => '',
			                           'step' => '1'
		                           )
	                           ) ); ?>
	<?php woocommerce_wp_checkbox( array(
		                               'id'            => '_allow_job_apply_rollover',
		                               'wrapper_class' => 'show_if_' . $sub_type . ' hide_if_' . $product_type,
		                               'label'         => sprintf( __( '%1$s Rollover?', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() ),
		                               'description'   => __( 'Rollover (Add) unused credits to package when subscription is renewed.', 'wp-job-manager-packages' ),
		                               'value'         => get_post_meta( $post_id, '_allow_job_apply_rollover', TRUE )
	                               ) ); ?>
	<hr/>
	<?php woocommerce_wp_checkbox( array( 'id'          => '_allow_job_browse',
	                                      'label'       => __( 'Allow Browsing?', 'wp-job-manager-packages' ),
	                                      'description' => __( 'Allow to browse listings with this package (if package required in settings).', 'wp-job-manager-packages' ),
	                                      'value'       => get_post_meta( $post_id, '_allow_job_browse', TRUE )
	                               ) ); ?>

	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_job_use_sd',
		                               'label'       => __( 'Use Short Description', 'wp-job-manager-packages' ),
		                               'description' => __( 'Enable this to use the short description for your own custom wording on the package selection form for listings.', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_job_use_sd', TRUE )
	                               ) ); ?>
	<p class="form-field linked_products">
		<label for="linked_products"><?php _e( 'Product Select', 'woocommerce', 'wp-job-manager-packages' ); ?></label>
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
					'posts_per_page' => - 1,
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
		<img class="help_tip" data-tip='<?php _e( 'Your description here', 'woocommerce', 'wp-job-manager-packages' ) ?>' src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16"/>
	</p>

	<script type="text/javascript">
		jQuery(function($){

			var wpjmpack_allow_apply = $( '#_allow_job_apply' );
			var wpjmpack_allow_view = $( '#_allow_job_view' );
			var product_selector = $( '#product-type' );

			$( '#postexcerpt' ).find( 'div.inside' ).append( '<?php echo $shortcode_html; ?>' );

			$('.pricing').addClass( 'show_if_<?php echo $product_type; ?>' );
			$('._tax_status_field').closest('div').addClass( 'show_if_<?php echo $product_type; ?> show_if_<?php echo $sub_type; ?>' );

			$('.show_if_subscription, .grouping').addClass( 'show_if_<?php echo $sub_type; ?>' );

			// Allow Apply
			wpjmpack_allow_apply.click( function () { wpjmpack_dynamic_wc_allow_click( $( this ), 'apply' ); });
			// Allow View
			wpjmpack_allow_view.click( function () { wpjmpack_dynamic_wc_allow_click( $( this ), 'view' ); });

			function wpjmpack_dynamic_wc_allow_click( check, type ){

				var sub_selected = false;

				if( $( '.show_if_subscription' ).is( ':visible' ) ){
					sub_selected = true;
				}

				if( check.is( ':checked' ) ){
					$( '._' + type + '_job_limit_field' ).show();

					if( sub_selected ){
						$( '._allow_job_' + type + '_rollover_field' ).show();
					}

				} else {
					$( '._' + type + '_job_limit_field' ).hide();
					$( '._allow_job_' + type + '_rollover_field' ).hide();
				}

			}

			product_selector.on( 'change', function(){
				if( this.value === '<?php echo $product_type; ?>' || this.value === '<?php echo $sub_type; ?>' ){
					wpjmpack_dynamic_wc_allow_click( wpjmpack_allow_apply, 'apply' );
					wpjmpack_dynamic_wc_allow_click( wpjmpack_allow_view, 'view' );
				}
			});

			product_selector.change();

		});
	</script>
</div>