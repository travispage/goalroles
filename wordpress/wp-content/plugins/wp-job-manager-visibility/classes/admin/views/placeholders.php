<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$ajax_nonce              = wp_create_nonce( "jmv_nonce" );
	$post_id = isset( $this->post ) && is_object( $this->post ) ? $this->post->ID : $this->post;
	//$single_table         = new WP_Job_Manager_Visibility_Admin_Table( $this->post );
?>
<div id="jmv-loader">
	<div id="jmv-loader-wrap">
		<i class="fa fa-cog fa-3x fa-spin"></i><br/><br/>
		<span><?php _e( 'Loading...', 'wp-job-manager-visibility' ); ?></span>
	</div>
	<div id="jmv-loader-close"><i class="fa fa-3x fa-times-circle-o"></i></div>
</div>
<div id="jmv-metabox">
	<?php
		echo "<input id=\"jmv-post-id\" type=\"hidden\" value=\"{$post_id}\">";
		echo "<input id=\"jmv-jmv_nonce\" type=\"hidden\" value=\"{$ajax_nonce}\">";
	?>
	<div id="jmv-mb-ph-table-wrap">
		<table class="" id="jmv-mb-ph-table">
			<tbody>
				<tr>
					<td id="jmv-mb-ph-field">
						<select data-placeholder="<?php _e( 'Select a field to hide', 'wp-job-manager-visibility' ); ?>" id="jmv-chosen-fields-add-ph" class="jmv-chosen-fields jmv-chosen-select" tabindex="2">
							<option value=""></option>
							<?php
								foreach ( $this->fields as $field_group => $fields ):
									if ( empty( $field_group ) ) continue;
							?>
									<optgroup label="<?php echo ucfirst( $field_group ); ?>">
								<?php
									foreach ( $fields as $field => $conf ) {
										echo "<option value=\"{$field}\" class=\"jmv-chosen-dd-hide\">{$field}</option>";
									}
								?>
									</optgroup>
							<?php
								endforeach;
							?>
						</select>
					</td>
					<td id="jmv-mb-ph-ph">
						<textarea rows="1" placeholder="<?php _e( 'Placeholder text or HTML to use instead of actual value', 'wp-job-manager-visibility' ); ?>" id="jmv-ph-add-ph-ta"></textarea>
					</td>
					<td id="jmv-mb-ph-actions" class="jmv-toggle-switch">
						<button id="jmv-ph-add-add-btn" class="jmv-mb-add-add-btn btn btn-primary" data-value="add" data-label="<?php _e( 'Add', 'wp-job-manager-visibility' ); ?>" type="button">
							<span class="fa fa-user-plus"></span>
						</button>
						<button id="jmv-ph-add-cancel-btn" class="jmv-mb-add-cancel-btn btn btn-danger" data-value="cancel" data-label="<?php _e( 'Cancel', 'wp-job-manager-visibility' ); ?>" type="button">
							<span class="fa fa-ban"></span>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="jmv-mb-list-wrap">
		<?php $this->placeholder_table( $this->post ); ?>
	</div>
</div>