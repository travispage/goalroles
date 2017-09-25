<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$no_records = FALSE;
?>
<div id="jmv-edit-loader">
	<div id="jmv-edit-loader-wrap">
		<i class="fa fa-pencil-square-o fa-3x"></i><br/><br/>
		<span><?php _e( 'Edit in Progress...', 'wp-job-manager-visibility' ); ?></span>
	</div>
</div>
<table class="wp-list-table widefat striped" id="jmv-ph-list-table">
<thead>
	<tr id="jmv-list-row-th">
		<th id="jmv-list-ph-field-th"><strong><?php _e( 'Field', 'wp-job-manager-visibility' ); ?></th>
		<th id="jmv-list-ph-ph-th"><strong><?php _e( 'Placeholder', 'wp-job-manager-visibility' ); ?></strong></th>
		<th id="jmv-list-ph-action-th"><strong><?php _e( 'Action', 'wp-job-manager-visibility' ); ?></strong></th>
	</tr>
</thead>
<tbody>
<?php
	$placeholders = is_object( $this->post ) ? get_post_meta( $this->post->ID, 'placeholders', true ) : array();
	if ( ! empty( $placeholders ) ):
		foreach ( $placeholders as $field => $conf ):
			$placeholder = isset( $conf['placeholder'] ) ? $conf[ 'placeholder' ] : '';
			?>
			<tr>
				<td class="jmv-list-fields-td">
				<?php
					$fields_data = '';
					echo "<span data-value=\"{$field}\" class=\"jmv-list-fields-box-hide jmv-list-fields-box\">{$field}</span>";
				?>
				</td>
				<td class="jmv-list-ph-td">
					<?php echo html_entity_decode( $placeholder ); ?>
				</td>
				<?php
					$post_id  = isset( $conf[ 'post_id' ] ) ? $conf[ 'post_id' ] : '';
					$meta_key = $field;
				?>
					<td class="jmv-ph-actions-td" data-meta_key="<?php echo $meta_key; ?>" data-post_id="<?php echo $post_id; ?>" data-placeholder="<?php echo htmlspecialchars( $placeholder ); ?>">
					<a class="jmv-ph-edit" href="#"><span class="dashicons dashicons-admin-tools"></span></a>
					<a class="jmv-ph-remove" href="#"><span class="dashicons dashicons-dismiss"></span></a>
				</td>
			</tr>
<?php
		endforeach;
	else:
		$no_records = TRUE;
	endif;
?>
</tbody>
</table>
<?php if ( $no_records ): ?><div id="jmv-no-records"><?php _e( 'No Records', 'wp-job-manager-visibility' ); ?></div><?php endif; ?>