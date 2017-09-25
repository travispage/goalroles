<?php
/**
 * Featured Job
 *
 * @package Jobify
 * @since 1.0.0
 * @version 3.8.0
 */
?>

<div class="job-spotlight">
	<div class="job-spotlight__featured-image">
		<a href="<?php the_permalink(); ?>" rel="bookmark">
			<?php echo jobify_get_the_featured_image(); ?>
		</a>
	</div>

	<div class="job-spotlight__content">
		<p><a href="<?php the_permalink(); ?>" rel="bookmark" class="job-spotlight__title"><?php the_title(); ?></a></p>

		<div class="job-spotlight__actions">
			<span class="job_listing-location"><?php echo jobify_get_formatted_address(); ?></span>

			<?php foreach( jobify_get_the_job_types() as $type ) : ?>
				<span class="job-type <?php echo esc_attr( sanitize_title( $type ? $type->slug : '' ) ); ?>"><?php echo $type->name; ?></span>
			<?php endforeach; ?>

		</div>

		<?php the_excerpt(); ?>
	</div>
</div>
