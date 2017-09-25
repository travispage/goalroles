<div class="options_group show_if_<?php echo $product_type; ?> show_if_<?php echo $sub_type; ?> <?php echo $product_type; ?>_listing_options <?php echo $sub_type; ?>_listing_options">
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_resume_view',
		                               'label'       => sprintf( __( 'View %s?', 'wp-job-manager-packages' ), $label ),
		                               'description' => __( 'Allow to view single listings with this package (if package required in settings).', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_allow_resume_view', TRUE )
	                               ) ); ?>
	<?php
		woocommerce_wp_text_input( array(
		                                 'id'                => '_view_resume_limit',
		                                 'label'             => sprintf( __( 'View %s limit', 'wp-job-manager-packages' ), $label ),
		                                 'description'       => __( 'The number of listings a user can view with this package.  Leave blank to allow unlimited.', 'wp-job-manager-packages' ),
		                                 'value'             => ( $limit = get_post_meta( $post_id, '_view_resume_limit', TRUE ) ) ? $limit : '',
		                                 'placeholder'       => __( 'Unlimited', 'wp-job-manager-packages' ),
		                                 'type'              => 'number',
		                                 'desc_tip'          => TRUE,
		                                 'custom_attributes' => array(
			                                 'min'  => '',
			                                 'step' => '1'
		                                 )
	                                 ) ); ?>
	<?php woocommerce_wp_checkbox( array(
		                               'id'            => '_allow_resume_view_rollover',
		                               'wrapper_class' => 'show_if_' . $sub_type . ' hide_if_' . $product_type,
		                               'label'         => __( 'View Rollover?', 'wp-job-manager-packages' ),
		                               'description'   => __( 'Rollover (Add) unused credits to package when subscription is renewed.', 'wp-job-manager-packages' ),
		                               'value'         => get_post_meta( $post_id, '_allow_resume_view_rollover', TRUE )
	                               ) ); ?>
	<hr/>
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_resume_view_name',
		                               'label'       => sprintf( __( 'View %s Name?', 'wp-job-manager-packages' ), $label ),
		                               'description' => sprintf( __( 'Allow to view %s name with this package (if package required in settings).', 'wp-job-manager-packages' ), $label ),
		                               'value'       => get_post_meta( $post_id, '_allow_resume_view_name', TRUE )
	                               ) ); ?>

	<?php woocommerce_wp_text_input( array(
		                           'id'                => '_view_name_resume_limit',
		                           'label'             => sprintf( __( 'View %s Name limit', 'wp-job-manager-packages' ), $label ),
		                           'description'       => sprintf( __( 'The number of %s names a user can view with this package.  Leave blank to allow unlimited.', 'wp-job-manager-packages' ), $label ),
		                           'value'             => ( $limit = get_post_meta( $post_id, '_view_name_resume_limit', TRUE ) ) ? $limit : '',
		                           'placeholder'       => __( 'Unlimited', 'wp-job-manager-packages' ),
		                           'type'              => 'number',
		                           'desc_tip'          => TRUE,
		                           'custom_attributes' => array(
			                           'min'  => '',
			                           'step' => '1'
		                           )
	                           ) ); ?>
	<?php woocommerce_wp_checkbox( array(
		                               'id'            => '_allow_resume_view_name_rollover',
		                               'wrapper_class' => 'show_if_' . $sub_type . ' hide_if_' . $product_type,
		                               'label'         => __( 'View Name Rollover?', 'wp-job-manager-packages' ),
		                               'description'   => __( 'Rollover (Add) unused credits to package when subscription is renewed.', 'wp-job-manager-packages' ),
		                               'value'         => get_post_meta( $post_id, '_allow_resume_view_name_rollover', TRUE )
	                               ) ); ?>
	<hr/>
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_resume_contact',
		                               'label'       => __( 'Allow Contact?', 'wp-job-manager-packages' ),
		                               'description' => __( 'Allow to contact listing with this package (if package required in settings).', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_allow_resume_contact', TRUE )
	                               ) ); ?>

	<?php woocommerce_wp_text_input( array(
		                                 'id'                => '_contact_resume_limit',
		                                 'label'             => sprintf( __( 'Contact %s limit', 'wp-job-manager-packages' ), $label ),
		                                 'description'       => sprintf( __( 'The number of %s a user can contact with this package. Leave blank to allow unlimited.', 'wp-job-manager-packages' ), $label ),
		                                 'value'             => ( $limit = get_post_meta( $post_id, '_contact_resume_limit', TRUE ) ) ? $limit : '',
		                                 'placeholder'       => __( 'Unlimited', 'wp-job-manager-packages' ),
		                                 'type'              => 'number',
		                                 'desc_tip'          => TRUE,
		                                 'custom_attributes' => array(
			                                 'min'  => '',
			                                 'step' => '1'
		                                 )
	                                 ) ); ?>
	<?php woocommerce_wp_checkbox( array(
		                               'id'            => '_allow_resume_contact_rollover',
		                               'wrapper_class' => 'show_if_' . $sub_type . ' hide_if_' . $product_type,
		                               'label'         => __( 'Contact Rollover?', 'wp-job-manager-packages' ),
		                               'description'   => __( 'Rollover (Add) unused credits to package when subscription is renewed.', 'wp-job-manager-packages' ),
		                               'value'         => get_post_meta( $post_id, '_allow_resume_contact_rollover', TRUE )
	                               ) ); ?>
	<hr/>
	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_allow_resume_browse',
		                               'label'       => __( 'Allow Browsing?', 'wp-job-manager-packages' ),
		                               'description' => __( 'Allow to browse listings with this package (if package required in settings).', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_allow_resume_browse', TRUE )
	                               ) ); ?>

	<?php woocommerce_wp_checkbox( array(
		                               'id'          => '_resume_use_sd',
		                               'label'       => __( 'Use Short Description', 'wp-job-manager-packages' ),
		                               'description' => __( 'Enable this to use the short description for your own custom wording on the package selection form for listings.', 'wp-job-manager-packages' ),
		                               'value'       => get_post_meta( $post_id, '_resume_use_sd', TRUE )
	                               ) ); ?>

	<script type="text/javascript">
		jQuery(function($){
			var wpjmpack_allow_contact = $( '#_allow_resume_contact' );
			var wpjmpack_allow_view_name = $( '#_allow_resume_view_name' );
			var wpjmpack_allow_view = $( '#_allow_resume_view' );
			var product_selector = $( '#product-type' );

			$( '#postexcerpt' ).find( 'div.inside' ).append( '<?php echo $shortcode_html; ?>' );

			$('.pricing').addClass( 'show_if_<?php echo $product_type; ?>' );
			$('._tax_status_field').closest('div').addClass( 'show_if_<?php echo $product_type; ?> show_if_<?php echo $sub_type; ?>' );

			$('.show_if_subscription, .grouping').addClass( 'show_if_<?php echo $sub_type; ?>' );

			// Allow contact
			wpjmpack_allow_contact.click( function () { wpjmpack_resume_dynamic_wc_allow_click( jQuery( this ), 'contact' ); } );
			// Allow View
			wpjmpack_allow_view.click( function () { wpjmpack_resume_dynamic_wc_allow_click( jQuery( this ), 'view' ); } );
			// Allow view name
			wpjmpack_allow_view_name.click( function () { wpjmpack_resume_dynamic_wc_allow_click( jQuery( this ), 'view_name' ); });

			function wpjmpack_resume_dynamic_wc_allow_click( check, type ) {

				var sub_selected = false;

				if ( $( '.show_if_subscription' ).is( ':visible' ) ) {
					sub_selected = true;
				}

				if ( check.is( ':checked' ) ) {
					$( '._' + type + '_resume_limit_field' ).show();

					if( sub_selected ){
						$( '._allow_resume_' + type + '_rollover_field' ).show();
					}
				} else {
					$( '._' + type + '_resume_limit_field' ).hide();
					$( '._allow_resume_' + type + '_rollover_field' ).hide();
				}

			}

			product_selector.on( 'change', function () {
				if ( this.value === '<?php echo $product_type; ?>' || this.value === '<?php echo $sub_type; ?>' ) {
					wpjmpack_resume_dynamic_wc_allow_click( wpjmpack_allow_contact, 'contact' );
					wpjmpack_resume_dynamic_wc_allow_click( wpjmpack_allow_view, 'view' );
					wpjmpack_resume_dynamic_wc_allow_click( wpjmpack_allow_view_name, 'view_name' );
				}
			} );

			product_selector.change();
		});
	</script>
</div>