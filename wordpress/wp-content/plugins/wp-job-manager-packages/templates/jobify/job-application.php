<?php if ( $apply = get_the_job_application_method() ) :
	$form_only = is_job_manager_packages_placeholder_form_only( 'apply', 'job' );
	wp_enqueue_script( 'wp-job-manager-job-application' );
	?>
	<div class="job_application application">
		<?php do_action( 'job_application_start', $apply ); ?>
		
		<input type="button" class="application_button button" value="<?php _e( 'Apply for job', 'wp-job-manager', 'wp-job-manager-packages' ); ?>" />
		
		<div class="application_details">

			<?php if( ! $form_only ): ?>
				<h2 class="modal-title"><?php _e( 'Apply for job', 'wp-job-manager', 'wp-job-manager-packages' ); ?></h2>
			<?php endif; ?>

			<?php
				/**
				 * job_manager_application_details_email or job_manager_application_details_url hook
				 */
				do_action( 'job_manager_application_details_' . $apply->type, $apply );
			?>
		</div>
		<?php do_action( 'job_application_end', $apply ); ?>
	</div>
<?php endif; ?>