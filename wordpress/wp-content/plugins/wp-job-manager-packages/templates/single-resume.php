<?php
/**
 * The Template for displaying a single listing.
 *
 * This is a standard template layout, but we have to use this as the only core WordPress filter available
 * is on this template file.  As such, we include this standard file, but instead of using the core get_template_part(),
 * we use our own get_job_manager_template_part(), which allows us to filter and handle it as we need.
 */
global $post;

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_job_manager_packages_template_part( 'content', 'single-resume' ); ?>

	<?php endwhile; ?>

<?php get_footer(); ?>
