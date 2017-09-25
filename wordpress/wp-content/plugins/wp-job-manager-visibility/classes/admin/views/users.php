<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$display_users_as     = 'display_name';

	$existing_selections = isset( $existing_selections ) && $existing_selections ? (array) $existing_selections : array();
	$disable_selections = isset( $disable_selections ) && $disable_selections ? (array) $disable_selections : array();

	$multiple_select = isset( $multiple ) && $multiple ? 'multiple' : '';

	$select_id = isset( $this->post_type ) ? $this->post_type : 'default';
	$select_id_append = isset( $select_id_append ) && $select_id_append ? $select_id_append : '';
	$select_name = isset( $select_name ) && $select_name ? $select_name : "user";
	$select_placeholder = isset( $select_placeholder ) && $select_placeholder ? $select_placeholder : __( 'Select a User or Group', 'wp-job-manager-visibility' );

	// Handle arugments to prepend user/group values if required
	$user_prepend = isset( $user_prepend ) && $user_prepend ? $user_prepend : '';
	$group_prepend = isset( $group_prepend ) && $group_prepend ? $group_prepend : '';
?>
<?php
	if( isset( $exclude_users ) && $exclude_users && empty( $this->groups ) ):
		echo "<h4>" . __( "No Groups Found", 'wp-job-manager-visibility' ) . "</h4>";
	elseif( isset( $exclude_groups ) && $exclude_groups && empty( $this->users ) ):
		echo "<h4>" . __( "Error getting Users!", 'wp-job-manager-visibility' ) . "</h4>";
	else:
		if( $select_name ) echo "<input type=\"hidden\" name=\"jmv_selects[]\" value=\"{$select_name}\">";
		// Add array notation to select name if multiple select
		if ( ! empty( $multiple_select ) ) $select_name .= "[]";
?>
	<select name="<?php echo $select_name; ?>" data-placeholder="<?php echo $select_placeholder; ?>" width="" id="jmv-chosen-user-<?php echo $select_id . $select_id_append; ?>" class="jmv-chosen-user-<?php echo $select_id . $select_id_append; ?> jmv-chosen-user jmv-chosen-select" tabindex="1" <?php echo $multiple_select; ?>>
		<option value=""></option>
		<?php if( ! empty( $this->groups ) && ( ! isset( $exclude_groups ) || ! $exclude_groups ) ):  ?>
		<optgroup label="<?php _e( 'Groups', 'wp-job-manager-visibility' ); ?>">
			<?php
				foreach ( $this->groups as $group ) {
					$group_display = esc_html( $group['title'] );

					$group_disable = in_array( "{$group_prepend}{$group['ID']}", $disable_selections ) ? 'disabled' : '';
					$selected = in_array( "{$group_prepend}{$group['ID']}", $existing_selections ) ? 'selected' : '';

					echo "<option value=\"{$group_prepend}{$group['ID']}\" {$group_disable} {$selected}>{$group_display}</option>";
				}
			?>
		</optgroup>
		<?php endif; ?>
		<?php if ( ! empty( $this->users ) && ( ! isset( $exclude_users ) || ! $exclude_users ) ): ?>
		<optgroup label="<?php _e( 'Users', 'wp-job-manager-visibility' ); ?>">
			<?php
				foreach ( $this->users as $user ) {
					$user_display = esc_html( $user->$display_users_as );

					$user_disable = in_array( $user_prepend . $user->ID, $disable_selections ) ? 'disabled' : '';
					$selected = in_array( "{$user_prepend}{$user->ID}", $existing_selections ) ? 'selected' : '';

					echo "<option value=\"{$user_prepend}{$user->ID}\" {$user_disable} {$selected}>{$user_display}</option>";
				}
			?>
		</optgroup>
		<?php endif; ?>
	</select>
	<?php endif; ?>