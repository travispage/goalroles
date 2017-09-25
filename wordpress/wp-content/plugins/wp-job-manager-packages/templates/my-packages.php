<?php
/**
 * My Packages
 *
 * Shows packages on the account page
 *
 * @var $slug                string       Slug for type of current output (job or resume)
 * @var $sui                 string       Class used for main table, will be ui when Semantic UI is enabled, or no-ui when disabled
 * @var $table_color         string       Color to use for table (configured in settings)
 * @var $enable_sui          boolean      Whether Semantic UI is enabled (configured in settings)
 * @var $packages            array        Array of user packages to output
 * @var $post_type           string       Post type for current output (job_listing or resume)
 * @var $package_types       array        Package types configuration values
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<h2><?php echo apply_filters( 'woocommerce_my_account_job_manager_job_packages_title', sprintf( __( 'My %s Visibility Packages', 'wp-job-manager-packages' ), job_manager_get_post_type_label( $post_type, false, $slug ) ) ); ?></h2>

<table class="<?php echo esc_attr( $sui ); ?> <?php echo esc_attr( $table_color ); ?> striped compact table jmpack-my-packages-main-table jmpack-my-packages-job-table">
  <thead>
	<tr class="center aligned">
	  <th colspan="1"><?php _e( 'Package Name', 'wp-job-manager-packages' ); ?></th>
	  <th colspan="3"><?php _e( 'Package Features', 'wp-job-manager-packages' ); ?></th>
	</tr>
  </thead>
  <tbody>
	<?php foreach( (array) $packages as $package ) :
		$package = job_manager_packages_get_user_package( $package );
		$package_details = '';
		?>
		<tr class="jmpack-my-packages-main-row">
			<td>
				<?php //if( $package->is_subscription() ): ?>
				<?php //endif; ?>
				<?php echo $package->get_title(); ?>
			</td>
			<td class="ui right aligned">
				<?php
					foreach( (array) $package_types as $package_type => $ptcfg ){

						if( ! $package->allow_enabled( $package_type ) ){
							continue;
						}

						$icon  = ! empty( $ptcfg['icon'] ) ? esc_attr( $ptcfg['icon'] ) : '';
						$color = ! empty( $ptcfg['color'] ) ? esc_attr($ptcfg['color']) : '';
						$label = ! empty( $ptcfg['label'] ) ? esc_attr($ptcfg['label']) : '';

						// Opening type label DIV wrapper

						if( ! empty( $ptcfg['limit'] ) ){

							$package_type = esc_attr( $package_type );

							// Initially set to false
							$has_limit = FALSE;
							$limit_reached = '';

							$limit     = $package->get_limit( $package_type );
							$used      = $package->get_used( $package_type );

							if( (int) $limit === 0 ){
								$detail = __( 'unlimited', 'wp-job-manager-packages' );
							} else {
								$detail = "{$used} " . __( 'of', 'wp-job-manager-packages' ) . " {$limit}";

								// Set value of $limit_reached to style and show limit reached
								if( absint( $limit - $used ) === 0 ){
									$limit_reached = 'jmpack-type-detail-limit-reached';
								}

								$has_limit = true;
							}


							if( $has_limit ){
								// Type with limit
								echo "<a href='#' class='ui {$color} mini label jmpack-type-detail-toggle {$limit_reached}' data-status='up' data-type='{$package_type}'><i class='{$icon} icon'></i> {$label}";
								echo "<div class='detail'>{$detail}</div>";
								echo "<div class='detail' data-status='up' data-type='{$package_type}'><i class='jmpack-type-detail-toggle-icon toggle down icon'></i></div>";
								echo '</a>';

								ob_start();
								get_job_manager_packages_template(
									'my-packages-limit-details.php',
									array(
										'ptcfg'      => $ptcfg,
										'package_type' => $package_type,
										'package'     => $package,
										'used'     => $used,
										'limit'    => $limit,
										'icon'	   => $icon,
										'color'    => $color,
										'label'    => $label,
										'posts'    => $package->get_posts( $package_type ),
										'sui'	   => $sui
									),
									'jm_packages',
									WP_Job_Manager_Packages::dir( '/templates/' )
								);

								$package_details .= ob_get_clean();

							} else {
								// Unlimited Type
								echo "<div class='ui {$color} mini label'><i class='{$icon} icon'></i> {$label}<div class='detail'>{$detail}</div></div>";

							}

						} else {
							// Type without limits
							echo "<div class='ui {$color} mini label'><i class='{$icon} icon'></i> {$label}</div>";

						}

					 }
				?>
			</td>
		</tr>
		<?php if( ! empty( $package_details ) ): ?>
		<tr class="jmpack-package-details" style="display: none;" data-status="up">
			<td colspan="4">
				<?php echo $package_details; ?>
			</td>
		</tr>
		<?php endif; ?>

	<?php endforeach; ?>

  </tbody>
</table>