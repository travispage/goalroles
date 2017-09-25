<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	$existing_selections = isset( $existing_selections ) && $existing_selections ? (array) $existing_selections : array();
	$disable_selections = isset( $disable_selections ) && $disable_selections ? (array) $disable_selections : array();

	$multiple_select = isset( $multiple ) && $multiple ? 'multiple' : '';

	$select_id = isset( $this->post_type ) ? $this->post_type : 'default';
	$select_id_append = isset( $select_id_append ) && $select_id_append ? $select_id_append : '';
	$select_name = isset( $select_name ) && $select_name ? $select_name : "user";
	$select_placeholder = isset( $select_placeholder ) && $select_placeholder ? $select_placeholder : __( 'Select a Role', 'wp-job-manager-visibility' );

	// Handle arugments to prepend user/group values if required
	$role_prepend = isset( $role_prepend ) && $role_prepend ? $role_prepend : '';
?>
<?php
	if( empty( $this->roles ) ):
		echo "<h4>" . __( "No Roles Found", 'wp-job-manager-visibility' ) . "</h4>";
	else:
		if( $select_name ) echo "<input type=\"hidden\" name=\"jmv_selects[]\" value=\"{$select_name}\">";
		// Add array notation to select name if multiple select
		if ( ! empty( $multiple_select ) ) $select_name .= "[]";
?>
	<select name="<?php echo $select_name; ?>" data-placeholder="<?php echo $select_placeholder; ?>" width="" id="jmv-chosen-user-<?php echo $select_id . $select_id_append; ?>" class="jmv-chosen-user-<?php echo $select_id . $select_id_append; ?> jmv-chosen-roles jmv-chosen-select" tabindex="1" <?php echo $multiple_select; ?>>
		<option value=""></option>
		<?php if( ! empty( $this->roles ) ):  ?>
		<optgroup label="<?php _e( 'Roles', 'wp-job-manager-visibility' ); ?>">
			<?php
				foreach ( $this->roles as $role => $role_display ) {

					$role_disable = in_array( "{$role_prepend}{$role}", $disable_selections ) ? 'disabled' : '';
					$selected = in_array( "{$role_prepend}{$role}", $existing_selections ) ? 'selected' : '';

					echo "<option value=\"{$role_prepend}{$role}\" {$role_disable} {$selected}>{$role_display}</option>";
				}
			?>
		</optgroup>
		<?php endif; ?>
	</select>
	<?php endif; ?>