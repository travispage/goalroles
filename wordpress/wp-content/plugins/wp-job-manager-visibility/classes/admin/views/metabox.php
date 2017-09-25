<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$fields_to_mask_ph = __( 'Select Fields to Mask', 'wp-job-manager-visibility' );
	$select_user_or_group_ph = __( 'Select a User or Group', 'wp-job-manager-visibility' );
	$select_visibility_ph = __( 'Select Visibility Fields', 'wp-job-manager-visibility' );
	$ajax_nonce = wp_create_nonce( "jmrv_nonce" );

	$post_id = isset( $post ) && is_object( $post ) ? $post->ID : $post;

	$single_table = new WP_Job_Manager_Visibility_Admin_Table( $post );
	$existing_user_config = $single_table->getExistingUsers();
	$display_users_as = WP_Job_Manager_Visibility_Admin_Settings::display_users_as();
?>
<div id="jmv-loader">
	<div id="jmv-loader-wrap">
		<i class="fa fa-cog fa-3x fa-spin"></i><br/><br/>
		<span><?php _e('Loading...', 'wp-job-manager-visibility'); ?></span>
		<div id="jmv-loader-close"><i class="fa fa-3x fa-times-circle-o"></i></div>
	</div>
</div>
<div id="jmv-metabox">
	<?php
		echo "<input id=\"jmv-resume-id\" type=\"hidden\" value=\"{$post_id}\">";
		echo "<input id=\"jmv-jmrv_nonce\" type=\"hidden\" value=\"{$ajax_nonce}\">";
	?>
	<div id="jmv-mb-add" class="clearfix">
		<div id="jmv-mb-add-user" class="jmv-mb-add-div">
			<select data-placeholder="<?php echo $select_user_or_group_ph; ?>" width="" id="jmv-chosen-user-add" class="jmv-chosen-select" tabindex="1">
				<option value=""></option>
				<optgroup label="<?php _e( 'Groups', 'wp-job-manager-visibility' ); ?>">
					<?php
						foreach ( $this->groups as $group ) {
							$group_disable = in_array( $group, $existing_user_config ) ? 'disabled' : '';
							$group_display = esc_html( $group->$display_users_as );
							echo "<option value=\"{$group->ID}\" {$group_disable}>{$group_display}</option>";
						}
					?>
				</optgroup>
				<optgroup label="<?php _e( 'Users', 'wp-job-manager-visibility' ); ?>">
					<?php
						foreach ( $this->users as $user ) {
							$user_disable = in_array( $user->ID, $existing_user_config ) ? 'disabled' : '';
							$user_display = esc_html( $user->$display_users_as );
							echo "<option value=\"{$user->ID}\" {$user_disable}>{$user_display}</option>";
						}
					?>
				</optgroup>
			</select>
		</div>
		<div id="jmv-mb-add-fields" class="jmv-mb-add-div">
			<select data-placeholder="<?php echo $select_visibility_ph; ?>" id="jmv-chosen-fields-add" class="jmv-chosen-fields jmv-chosen-select" multiple tabindex="3">
				<option value=""></option>
				<optgroup label="<?php _e( 'Select Fields to Hide on Listing', 'wp-job-manager-visibility' ); ?>">
					<?php foreach( $this->fields as $field => $config ) echo "<option value=\"{$field}_hide\" class=\"jmv-chosen-dd-hide\">{$field}</option>"; ?>
				</optgroup>
				<optgroup label="<?php _e( 'Select Fields to Show on Listing', 'wp-job-manager-visibility' ); ?>">
					<?php foreach ( $this->fields as $field => $config ) echo "<option value=\"{$field}_show\" class=\"jmv-chosen-dd-show\">{$field}</option>"; ?>
				</optgroup>
			</select>
		</div>
		<div id="jmv-mb-add-ph" class="jmv-mb-add-div">
			<textarea placeholder="<?php _e( 'Placeholder text or HTML to use instead of actual value', 'wp-job-manager-visibility' ); ?>" id="jmv-mb-add-ph-ta"></textarea>
		</div>
		<div id="jmv-mb-add-add" class="jmv-mb-add-div jmv-toggle-switch">
			<button id="jmv-mb-add-add-btn" class="jmv-mb-add-add-btn btn btn-primary" data-value="add" data-label="<?php _e( 'Add', 'wp-job-manager-visibility' ); ?>" type="button">
				<span class="fa fa-user-plus"></span>
			</button>
			<button id="jmv-mb-add-cancel-btn" class="jmv-mb-add-cancel-btn btn btn-danger" data-value="cancel" data-label="<?php _e( 'Cancel', 'wp-job-manager-visibility' ); ?>" type="button">
				<span class="fa fa-ban"></span>
			</button>
		</div>
	</div>
	<div id="jmv-mb-list-wrap">
		<?php $single_table->output_table(); ?>
	</div>
</div>