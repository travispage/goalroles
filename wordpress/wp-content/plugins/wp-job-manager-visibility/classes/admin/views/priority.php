<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$priority = isset( $priority ) && $priority ? $priority : 10;
	wp_enqueue_script( 'jquery-ui-core' );
?>
<script>
	jQuery( function($){ $('#priority_spinner' ).spinner(); });
</script>
<p>
  <label for="spinner" style="font-size: 18px;"><?php _e( 'Priority: ', 'wp-job-manager-visibility' ); ?></label>
</p>
<p>
	<div id="jmv-priority-spinner-wrap">
		<input id="priority_spinner" name="priority" value="<?php echo $priority; ?>" style="font-size: 18px; text-align: center;">
	</div>
</p>
<p><?php _e('Default priority is 10', 'wp-job-manager-visibility'); ?></p>
<p><?php _e('Lower number equals higher priority', 'wp-job-manager-visibility'); ?></p>
<p><em><?php _e('See the help menu in top right corner for more details on priorities.', 'wp-job-manager-visibility'); ?></em></p>