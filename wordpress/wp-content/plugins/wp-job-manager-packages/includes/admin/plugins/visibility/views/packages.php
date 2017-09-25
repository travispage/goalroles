<?php
/**
 * Package Selection Metabox
 *
 * @var $job_label               string   Post Label for Jobs
 * @var $resume_label            string   Post Label for Resumes
 * @var $job_packages            array    Available Job Packages
 * @var $wcpl_job_packages       array    Available WooCommerce Paid Listings Job Packages
 * @var $resume_packages         array    Available Resume Packages
 * @var $wcpl_resume_packages    array    Available WooCommerce Paid Listings Resume Packages
 * @var $selected_packages       array    Packages already selected and stored in meta
 */
if( ! defined( 'ABSPATH' ) ) exit;

$existing_selections = isset( $existing_selections ) && $existing_selections ? (array) $existing_selections : array();
$disable_selections  = isset( $disable_selections ) && $disable_selections ? (array) $disable_selections : array();

if( empty( $job_packages ) && empty( $resume_packages ) ):
	echo '<h4>' . __( 'No Packages Found', 'wp-job-manager-packages' ) . '</h4>';
else:
	echo "<input type=\"hidden\" name=\"jmv_selects[]\" value=\"group_packages\">";
	// ID set below to jmv-chosen-visible-default_visibilities to support including group label in selection (1.4.1 version on .jmv-chosen-user-visibility_groups did not have it included)
	// Can probably add back the 'jmv-chosen-user-visibility_groups' class once Visibility plugin > 1.4.1 is available/released
	?>
	<select name="group_packages[]" data-placeholder="<?php _e('Select a Package', 'wp-job-manager-packages' ); ?>" id="jmv-chosen-visible-default_visibilities" class="jmv-chosen-packages jmv-chosen-select" tabindex="1" multiple>
		<option value=""></option>

		<?php if( ! empty( $job_packages ) ): ?>
			<optgroup label="<?php printf( __( '%s Packages', 'wp-job-manager-packages' ), $job_label ); ?>">
				<?php
				foreach( $job_packages as $job_package => $job_package_display ) {

					$job_package_disable = in_array( $job_package, $disable_selections, false ) ? 'disabled' : '';
					$job_selected        = in_array( $job_package, $existing_selections, false ) ? 'selected' : '';

					echo "<option value=\"{$job_package}\" {$job_package_disable} {$job_selected}>{$job_package_display}</option>";
				}
				?>
			</optgroup>
		<?php endif; ?>

		<?php if( ! empty( $resume_packages ) ): ?>
			<optgroup label="<?php printf( __( '%s Packages', 'wp-job-manager-packages' ), $resume_label ); ?>">
				<?php
				foreach( $resume_packages as $resume_package => $resume_package_display ) {

					$resume_package_disable = in_array( $resume_package, $disable_selections, false ) ? 'disabled' : '';
					$resume_selected        = in_array( $resume_package, $existing_selections, false ) ? 'selected' : '';

					echo "<option value=\"{$resume_package}\" {$resume_package_disable} {$resume_selected}>{$resume_package_display}</option>";
				}
				?>
			</optgroup>
		<?php endif; ?>

		<?php if( ! empty( $wcpl_job_packages ) ): ?>
			<optgroup label="<?php printf( __( 'WC Paid Listings %s Packages', 'wp-job-manager-packages' ), $job_label ); ?>">
				<?php
				foreach( $wcpl_job_packages as $wcpl_job_package => $wcpl_job_package_display ) {

					$wcpl_job_package_disable = in_array( $wcpl_job_package, $disable_selections, false ) ? 'disabled' : '';
					$job_selected        = in_array( $wcpl_job_package, $existing_selections, false ) ? 'selected' : '';

					echo "<option value=\"{$wcpl_job_package}\" {$wcpl_job_package_disable} {$job_selected}>{$wcpl_job_package_display}</option>";
				}
				?>
			</optgroup>
		<?php endif; ?>

		<?php if( ! empty( $wcpl_resume_packages ) ): ?>
			<optgroup label="<?php printf( __( 'WC Paid Listings %s Packages', 'wp-job-manager-packages' ), $resume_label ); ?>">
				<?php
				foreach( $wcpl_resume_packages as $wcpl_resume_package => $wcpl_resume_package_display ) {

					$wcpl_resume_package_disable = in_array( $wcpl_resume_package, $disable_selections, false ) ? 'disabled' : '';
					$wcpl_resume_selected        = in_array( $wcpl_resume_package, $existing_selections, false ) ? 'selected' : '';

					echo "<option value=\"{$wcpl_resume_package}\" {$wcpl_resume_package_disable} {$wcpl_resume_selected}>{$wcpl_resume_package_display}</option>";
				}
				?>
			</optgroup>
		<?php endif; ?>

	</select>
<?php endif; ?>