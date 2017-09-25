<?php
/**
 * Template Name: Sign Up
 *
 * @package Jobify
 * @since Jobify 3.0.0
 */

get_header(); ?>

	<?php if ( Jobify_Page_Header::show_page_header() ) : ?>
	<header class="page-header">
		<h2 class="page-title">Create your account</h2>
	</header>
	<?php endif; ?>

	<div id="primary" class="content-area container" role="main">
		<div class="row">
			<div id="signup">				
							
			</div>
		</div>

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>