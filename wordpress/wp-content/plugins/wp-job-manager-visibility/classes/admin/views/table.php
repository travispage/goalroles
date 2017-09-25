<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$no_records = false;
?>
<div id="jmv-edit-loader">
	<div id="jmv-edit-loader-wrap">
		<i class="fa fa-pencil-square-o fa-3x"></i><br/><br/>
		<span><?php _e( 'Edit in Progress...', 'wp-job-manager-visibility' ); ?></span>
	</div>
</div>
<table class="wp-list-table widefat striped" id="jmv-mb-list-table">
	<thead>
		<tr id="jmv-list-row-th">
			<th id="jmv-list-user-th"><strong><?php _e( 'User or Group', 'wp-job-manager-visibility' ); ?></th>
			<th id="jmv-list-fields-th"><strong><?php _e( 'Show/Hide Fields', 'wp-job-manager-visibility' ); ?></strong></th>
			<th id="jmv-list-marked-th"><strong><?php _e( 'Placeholder', 'wp-job-manager-visibility' ); ?></strong></th>
			<th id="jmv-list-action-th"><strong><?php _e( 'Action', 'wp-job-manager-visibility' ); ?></strong></th>
		</tr>
	</thead>
	<tbody>
	<?php
		if ( ! empty( $this->posts ) ):
			foreach ( $this->posts as $post => $conf ):
				?>
				<tr>
			<td class="jmv-list-user-td">
				<?php echo $conf[ 'user' ]; ?>
			</td>
			<td class="jmv-list-fields-td">
			<?php
				$fields_data = '';
				if ( ! empty( $conf[ 'show' ] ) ):
					foreach ( $conf[ 'show' ] as $field ) {
						echo "<span data-value=\"{$field}_show\" class=\"jmv-list-fields-box-show jmv-list-fields-box\">{$field}</span>";
						if ( $fields_data ) $fields_data .= ",";
						$fields_data .= "{$field}_show";
					}
				endif;
				$fields_hide = false;
				if ( ! empty( $conf[ 'hide' ] ) ):
					foreach ( $conf[ 'hide' ] as $field ) {
						echo "<span data-value=\"{$field}_hide\" class=\"jmv-list-fields-box-hide jmv-list-fields-box\">{$field}</span>";
						if ( $fields_data ) $fields_data .= ",";
						$fields_data .= "{$field}_hide";
					}
				endif;
			?>
			</td>
			<td class="jmv-list-ph-td">
				<?php if( isset( $conf['placeholder'] ) ) echo esc_textarea( $conf['placeholder'] ); ?>
			</td>
			<?php

				$resume_id = isset( $conf['resume_id'] ) ? $conf['resume_id'] : '';
				$post_id = isset( $conf['post_id'] ) ? $conf['post_id'] : '';
				$user = isset( $conf['user'] ) ? $conf['user'] : '';
				$user_id = isset( $conf['user_id'] ) ? $conf['user_id'] : '';
				$placeholder = isset( $conf['user_id'] ) ? $conf['user_id'] : '';
			?>
			<td class="jmv-list-actions-td" data-resume_id="<?php echo $resume_id; ?>" data-post_id="<?php echo $post_id; ?>" data-user="<?php echo $user; ?>" data-user_id="<?php echo $user_id; ?>" data-fields="<?php echo $fields_data; ?>">
				<a class="jmv-list-edit" href="#"><span class="dashicons dashicons-admin-tools"></span></a>
				<a class="jmv-list-remove" href="#"><span class="dashicons dashicons-dismiss"></span></a>
			</td>
		</tr>
				<?php
			endforeach;
		else:
			$no_records = true;
		endif;
	?>
	</tbody>
</table>
<?php if( $no_records ): ?>
<div id="jmv-no-records"><?php _e( 'No Records', 'wp-job-manager-visibility' ); ?></div>
<?php endif; ?>