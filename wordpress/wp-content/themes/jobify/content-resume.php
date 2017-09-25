<?php
/**
 * Resume Content
 *
 * @package Jobify
 * @since 3.0.0
 * @version 3.8.0
 */
?>
<li id="resume-<?php the_ID(); ?>" <?php resume_class(); ?> <?php echo apply_filters( 'jobify_listing_data', '' ); ?>>
	<a href="<?php the_resume_permalink(); ?>" class="resume-clickbox"></a>

	<div class="resume-logo">
		<?php the_candidate_photo( 'large' ); ?>
	</div><div class="resume-about">
		<div class="resume-candidate resume__column">
			<h3 class="resume-title"><?= the_title(); ?></h3>

			<div class="resume-candidate-title">
				<?php echo get_post_meta( $post->ID, '_candidate_current_title', true ); ?>, <?php echo get_post_meta( $post->ID, '_candidate_current_time', true ); ?> now				
			</div>
		</div>

		<div class="resume-location resume__column">
			<?php echo get_post_meta( $post->ID, '_candidate_postcode', true ); ?>
		</div>

		<ul class="resume-meta resume__column">
			<li class="resume-date">Looking for:</li>
			<li class="resume-category"><?php echo get_post_meta( $post->ID, '_candidate_expected_status', true ); ?></li>
		</ul>
	</div>
</li>
