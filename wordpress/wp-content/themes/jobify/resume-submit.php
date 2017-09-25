<?php
/**
 * Resume Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script( 'wp-resume-manager-resume-submission' );
?>
<form action="<?php echo $action; ?>" method="post" id="submit-resume-form" class="job-manager-form" enctype="multipart/form-data">

	<?php do_action( 'submit_resume_form_start' ); ?>

	<?php if ( apply_filters( 'submit_resume_form_show_signin', true ) ) : ?>

		<?php get_job_manager_template( 'account-signin.php', array( 'class' => $class ), 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

	<?php endif; ?>

	<?php if ( resume_manager_user_can_post_resume() ) : ?>

		<?php if ( get_option( 'resume_manager_linkedin_import' ) ) : ?>

			<?php get_job_manager_template( 'linkedin-import.php', '', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

		<?php endif; ?>

		<!-- Resume Fields -->
		<?php do_action( 'submit_resume_form_resume_fields_start' ); ?>

		<table>
		<?php foreach ( $resume_fields as $key => $field ) : ?>
			<tr class="fieldset-<?php esc_attr_e( $key ); ?>">
				<td width="300">
					<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_resume_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-job-manager-resumes' ) . '</small>', $field ); ?></label>
				</td>
				<td class="field">
					<?php $class->get_field_template( $key, $field ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>

		<?php do_action( 'submit_resume_form_resume_fields_end' ); ?>		
		<p>
			<?php wp_nonce_field( 'submit_form_posted' ); ?>
			<input type="hidden" name="resume_manager_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="resume_id" value="<?php echo esc_attr( $resume_id ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<input type="submit" name="submit_resume" class="button" value="<?php esc_attr_e( $submit_button_text ); ?>" />
		</p>

	<?php else : ?>

		<?php do_action( 'submit_resume_form_disabled' ); ?>

	<?php endif; ?>

	<?php do_action( 'submit_resume_form_end' ); ?>
</form>
