<?php global $post; ?>
<div id="primary" class="container">
	<div class="row content-area">
		<div class="entry-content">
			<?php do_action( 'job_manager_packages_template_content-single-job_listing_before' ); ?>
			<div class="single_job_listing" itemscope itemtype="http://schema.org/JobPosting">
				<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />
				<div class="job-manager-packages-single-job-require-package"><?php do_action( 'job_manager_packages_single_job_listing' ); ?></div>
			</div>
			<?php do_action( 'job_manager_packages_template_content-single-job_listing_after' ); ?>
		</div>
	</div>
</div>
