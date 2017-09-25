<?php
/**
 * Template Name: My Account
 *
 * @package Jobify
 * @since Jobify 3.0.0
 */

get_header(); ?>

	<?php if ( Jobify_Page_Header::show_page_header() ) : ?>
	<header class="page-header">
		<h2 class="page-title"><?php the_post();
		the_title();
		rewind_posts(); ?></h2>
	</header>
	<?php endif; ?>

	<div id="primary" class="content-area container" role="main">
		<div class="row">
			<div id="myaccount">
				<?php if ( !is_user_logged_in() ) { ?>					
					<div class="col-sm-offset-3 col-sm-6">			
						<?php echo do_shortcode("[woocommerce_my_account]"); ?>
					</div>
				<?php } else { echo do_shortcode("[woocommerce_my_account]"); } ?>				
			</div>
		</div>

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>