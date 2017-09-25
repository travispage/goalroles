<?php
/**
 * @var $form WPJM_Pack_Form
 */
?>
<div class="job_listing_packages_title job_manager_visibility_packages_title">
	<input type="submit" name="continue" class="button" value="<?php echo $form->button_text; ?>"/>
	<h2><?php echo $form->header_text; ?></h2>
</div>
<div class="job_listing_packages">
	<?php get_job_manager_packages_form_template( $form->form_type, $form->type->slug, 'package-selection', array( 'form' => $form ) ); ?>
</div>