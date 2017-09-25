<?php
/**
 * Template Name: Page: Home
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<div id="primary" role="main">

		<div id="homepage">
			<div id="homepage-top">
				<img src="http://localhost/goalroles/wordpress/wp-content/uploads/2017/09/cropped-office.jpg">	
				<div id="homepage-banner" class="container">
					<div class="col-sm-12 col-md-4">	
						<div><h4>I'M LOOKING FOR OPPORTUNITIES</h4></div>
						<div><a href="<?=site_url( '/sign-up-candidate/');?>">Login to create my resume.</a></div>
					</div>
					<div class="col-sm-12 col-md-4">				
						<div><h4>I HAVE OPPORTUNITIES AVAILABLE</h4></div>
						<div><a href="<?=site_url( '/sign-up-employer/');?>">Log in to begin my search for candidates.</a></div>
					</div>
					<div class="col-sm-12 col-md-4">				
						<div><h4>SEARCH JOB OPPORTUNITIES</h4></div>
						<div><a href="<?=site_url( '/jobs/');?>">Browse and apply online.</a></div>
					</div>
				</div>			
			</div>			
		</div>		

		<!--
		<div id="homepage-content">
			<?php if ( jobify()->get( 'woocommerce' ) ) : ?>
				<?php wc_print_notices(); ?>
			<?php endif; ?>

			<?php
			if ( ! dynamic_sidebar( 'widget-area-front-page' ) ) :
				the_widget(
					'Jobify_Widget_Jobs',
					array(
						'title' => 'Recent Jobs',
						'filters' => 0,
						'number' => 5,
						'spotlight' => 1,
						'spotlight-title' => 'Job Spotlight',
					),
					array(
					'before_widget' => '<section class="widget widget--home jobify_widget_jobs">',
					'after_widget'  => '</section>',
					'before_title'  => '<h3 class="widget-title widget-title--home">',
					'after_title'   => '</h3>',
					)
				);
				endif;
			?>
		</div>
		-->

	</div><!-- #primary -->

<?php get_footer(); ?>
