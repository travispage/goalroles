<?php

wp_enqueue_script( 'wp-resume-manager-resume-contact-details' );
?>
<div class="resume_contact">
	<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'wp-job-manager-resumes', 'wp-job-manager-packages' ); ?>"/>

	<div class="resume_contact_details">
		<?php do_action( 'job_manager_packages_access_denied_contact_details' ); ?>
	</div>
</div>

