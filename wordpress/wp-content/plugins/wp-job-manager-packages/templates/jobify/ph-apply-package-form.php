<?php
/**
 * Jobify specific Apply Package Form Template
 *
 * @var $form WPJM_Pack_Form
 */
$form_only = is_job_manager_packages_placeholder_form_only( 'apply', 'job' );
?>
<?php if( $form_only ): ?><h2 class="modal-title"><?php echo $form->header_text; ?></h2><?php endif; ?>
<div class="job_listing_packages">
	<?php get_job_manager_packages_form_template( $form->form_type, $form->type->slug, 'package-selection', array( 'form' => $form ) ); ?>
</div>
<p style="padding: 35px;">
	<input type="submit" name="continue" class="button" value="<?php echo $form->button_text; ?>"/>
</p>