<?php
global $resume_preview;

if ( $resume_preview ) {
	return;
}

	wp_enqueue_script( 'wp-resume-manager-resume-contact-details' );	
?>
	<div class="resume_contact">
		<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'wp-job-manager-resumes' ); ?>" />

		<div class="resume_contact_details">
			Choose Job:
			<select>
				<?php foreach ( $jobs as $job ) : ?>
					<option><?php wpjm_the_job_title( $job ); ?></option>
				<?php endforeach; ?>
			</select>
			<a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $email; ?>&su=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Send</a>
		</div>
	</div>