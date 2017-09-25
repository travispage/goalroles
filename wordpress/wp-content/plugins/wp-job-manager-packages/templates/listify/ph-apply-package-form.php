<?php
/**
 * Listify specific Apply Package Form Template
 *
 * @var $form WPJM_Pack_Form
 */
?>
<h2 class="modal-title"><?php echo $form->header_text; ?></h2>
<div class="job_listing_packages" style="margin-bottom: 20px; border-bottom: 2px solid #e9edf2;">
	<?php get_job_manager_packages_form_template( $form->form_type, $form->type->slug, 'package-selection', array( 'form' => $form ) ); ?>
</div>
<p>
	<input type="submit" name="continue" class="button" value="<?php echo $form->button_text; ?>"/>
</p>