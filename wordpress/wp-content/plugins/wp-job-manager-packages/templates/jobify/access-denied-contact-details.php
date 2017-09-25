<?php
$form_only = is_job_manager_packages_placeholder_form_only( 'contact', 'resume' );
wp_enqueue_script( 'wp-resume-manager-resume-contact-details' );
?>
<div class="resume_contact">
	<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'wp-job-manager-resumes', 'wp-job-manager-packages' ); ?>"/>

	<div class="resume_contact_details">

		<?php if( ! $form_only ): ?>
			<h2 class="modal-title"><?php _e('Contact', 'wp-job-manager-packages' ); ?></h2>
			<div class="resume_contact_details_inner">
		<?php endif; ?>

			<?php do_action( 'job_manager_packages_access_denied_contact_details' ); ?>

		<?php if( ! $form_only ): ?>
			</div>
		<?php endif; ?>

	</div>
</div>

