<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	$existing_selections = isset( $existing_selections ) && $existing_selections ? (array) $existing_selections : array();
	$disable_selections = isset( $disable_selections ) && $disable_selections ? (array) $disable_selections : array();

	$multiple_select = isset( $multiple ) && $multiple ? 'multiple' : '';

	$select_id = isset( $this->post_type ) ? $this->post_type : 'select';
	$select_id_append = isset( $select_id_append ) && $select_id_append ? $select_id_append : '';
	$select_name = isset( $select_name ) && $select_name ? $select_name : "select";
	$select_label = isset( $select_label ) ? $select_label : 'value';
	$select_placeholder = isset( $select_placeholder ) && $select_placeholder ? $select_placeholder : sprintf( __( 'Select a %s', 'wp-job-manager-visibility' ), ucfirst( $select_label ) );

	// Handle arugments to prepend values if required
	$field_prepend = isset( $field_prepend ) && $field_prepend ? $field_prepend : '';
	$select_fields = isset( $fields )  ? $fields : array();
?>
<?php
	if( empty( $select_fields ) ):
		echo "<h4>" . sprintf( __( "No %ss Found", 'wp-job-manager-visibility' ), ucfirst( $select_label ) ) . "</h4>";
	else:
		if( $select_name ) echo "<input type=\"hidden\" name=\"jmv_selects[]\" value=\"{$select_name}\">";
		// Add array notation to select name if multiple select
		if ( ! empty( $multiple_select ) ) $select_name .= "[]";
?>
	<select name="<?php echo $select_name; ?>" data-placeholder="<?php echo $select_placeholder; ?>" width="" id="jmv-chosen-<?php echo $select_label; ?>-<?php echo $select_id . $select_id_append; ?>" class="jmv-chosen-<?php echo $select_label; ?>-<?php echo $select_id . $select_id_append; ?> jmv-chosen-<?php echo $select_label; ?> jmv-chosen-select" tabindex="1" <?php echo $multiple_select; ?>>
		<option value=""></option>
		<?php
			foreach( $select_fields as $field_group => $fields ):
		?>
				<optgroup label="<?php echo ucfirst( $field_group ); ?>">
					<?php
						foreach ( $fields as $field => $field_config ) {

							$option_disable = in_array( "{$field_prepend}{$field}", $disable_selections ) ? 'disabled' : '';
							$selected = in_array( "{$field_prepend}{$field}", $existing_selections ) ? 'selected' : '';

							echo "<option value=\"{$field_prepend}{$field}\" {$option_disable} {$selected}>{$field}</option>";
						}
					?>
				</optgroup>
		<?php
			endforeach;
		?>
	</select>
	<?php endif; ?>