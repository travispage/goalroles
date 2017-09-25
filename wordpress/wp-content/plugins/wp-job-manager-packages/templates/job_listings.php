<?php if ( defined( 'DOING_AJAX' ) ) : ?>
	<li class="no_job_listings_found">
		<?php do_action( 'job_manager_packages_job_listings_ajax' ); ?>
	</li>
<?php else : ?>
	<p class="no_job_listings_found">
		<?php do_action( 'job_manager_packages_job_listings' ); ?>
	</p>
<?php endif; ?>