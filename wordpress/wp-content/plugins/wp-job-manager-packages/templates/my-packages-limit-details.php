<!-- START FEATURE DETAILS -->
<table class="<?php echo esc_attr($sui); ?> striped <?php echo esc_attr( $color ); ?> mini celled table jmpack-<?php echo $package_type; ?>-detail-table jmpack-detail-table" style="display: none;">
	<thead>
		<tr class="ui center aligned">
			<th><?php _e('Feature', 'wp-job-manager-packages'); ?></th>
			<th><?php _e('Remaining', 'wp-job-manager-packages'); ?></th>
			<th><?php _e('Used', 'wp-job-manager-packages'); ?></th>
			<th><?php _e('Limit', 'wp-job-manager-packages'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="ui center aligned">
			<td>
				<div class="ui <?php echo esc_attr( $color ); ?> mini label">
					<i class="<?php echo esc_attr( $icon ); ?> icon"></i> <?php echo esc_html( $label ); ?> <div class="detail"></div>
				</div>
			</td>
			<td><?php echo absint( $limit - $used ); ?></td>
			<td><?php echo absint( $used ); ?></td>
			<td><?php echo absint( $limit ); ?></td>
		</tr>

		<?php if( ! empty( $posts ) ): ?>
			<!-- START FEATURE LISTING DETAILS -->
			<tr class="ui center aligned inverted jmpack-detail-listings-header-row">
				<td colspan="4">
					<div class="ui center aligned icon">
						<i class="list icon"></i>
						<?php _e('Listings', 'wp-job-manager-packages'); ?>
					</div>
				</td>
			</tr>
			<?php foreach( $posts as $postid => $postcfg ): ?>
				<tr>
					<td colspan="2">
						<a href="<?php echo get_permalink( $postid ); ?>" class="jmpack-detail-listings-listing-link" target="_blank">
							<?php echo get_the_title( $postid ); ?>
							<i class="external icon"></i>
						</a>
					</td>
					<td colspan="2" class="ui center aligned">
						<i class="icon wait"></i>
						<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $postcfg['added'] ); ?>
					</td>
				</tr>
			<?php endforeach; ?>

		<?php endif; ?>

	</tbody>
</table>
<!-- END FEATURE DETAILS -->