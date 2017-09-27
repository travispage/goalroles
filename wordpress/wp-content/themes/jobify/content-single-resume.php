<?php
/**
 * Single Resume Content
 *
 * @package Jobify
 * @since 3.0.0
 * @version 3.8.0
 */

global $post;

$thispost = get_the_ID();
$is_visible = false;
if (isset($_GET['request'])) {
  $user_id = $_GET['request'];  
  $request_token = $_GET['token'];
  
  $find_token = get_post_meta( $thispost, $user_id );
  if ($request_token === $find_token[0]) {
  	add_post_meta($post->ID, 'visible_to', $user_id);
  	$is_visible = true;
  }  
  else {
  	var_dump('Token did not match.');
  }
} 

$skills     = wp_get_object_terms( $post->ID, 'resume_skill', array(
	'fields' => 'names',
) );
$education  = get_post_meta( $post->ID, '_candidate_education', true );
$experience = get_post_meta( $post->ID, '_candidate_experience', true );

$info            = get_theme_mod( 'resume-display-sidebar', 'top' );

$has_local_info  = is_array( $skills ) || $education || $experience;

$col_description = 'top' == $info ? '12' : ( $has_local_info ? '6' : '10' );
$col_info        = 'top' == $info ? '12' : ( 'side' == $info ? '4' : '6' );

$sex = get_post_meta( $post->ID, '_candidate_sex', true );
$age_range = get_post_meta( $post->ID, '_candidate_age_range', true );

$postcode = get_post_meta( $post->ID, '_candidate_postcode', true );

$current_status = get_post_meta( $post->ID, '_candidate_current_status', true );
$current_title = get_post_meta( $post->ID, '_candidate_current_title', true );
$current_time = get_post_meta( $post->ID, '_candidate_current_time', true );

$expected_status = get_post_meta( $post->ID, '_candidate_expected_status', true );
?>

<div class="page-header">
	<h2 class="page-title"><?php the_title(); ?></h2>
	<h3 class="page-subtitle">
		<ul>
			<?php do_action( 'single_resume_meta_start' ); ?>

			<li class="job-title"><?php the_candidate_title(); ?></li>
			<li class="location"><i class="icon-location"></i> <?php the_candidate_location( false ); ?></li>
			<li class="date-posted"><i class="icon-calendar"></i> <date><?php printf( __( 'Updated %s ago', 'jobify' ), human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></li>

			<?php do_action( 'single_resume_meta_end' ); ?>
		</ul>
	</h3>
</div>

<div id="content" class="container content-area" role="main">

	<?php do_action( 'single_resume_start' ); ?>

	<?php locate_template( array( 'sidebar-single-resume-top.php' ), true, false ); ?>

	<div class="resume-overview-basic">
		<div class="row">
			<div class="col-xs-12">Looking for: <b><?=$expected_status?></b></div> 			
		</div>
		<hr/>
		<div class="panel panel-default">
		 	<div class="panel-heading">Basic Information</div>
		 	<div class="panel-body">
		 		<div class="row">
			 		<div class="col-xs-3">Sex</div>
			 		<div class="col-xs-9"><?=$sex?></div>
			 	</div>
			 	<div class="row">
					<div class="col-xs-3">Age Range</div> 
					<div class="col-xs-9"><?=$age_range?></div>
				</div>
		 	</div>
		</div>

		<div class="panel panel-default">
		 	<div class="panel-heading">Location</div>
		 	<div class="panel-body">
		 		<div class="row">
					<div class="col-xs-3">Postcode:</div> 
					<div class="col-xs-9"><?=$postcode?></div>
				</div>
		 	</div>
		</div>

		<div class="panel panel-default">
		 	<div class="panel-heading">Employment</div>
		 	<div class="panel-body">
		 		<div class="row">
					<div class="col-xs-3">Current Status:</div> 
					<div class="col-xs-9"><?=$current_status?></div>
				</div>
				<div class="row">
					<div class="col-xs-3">Job Title</div> 
					<div class="col-xs-9"><?=$current_title?></div>
				</div>
				<div class="row">
					<div class="col-xs-3">Time in Role</div> 
					<div class="col-xs-9"><?=$current_time?></div>
				</div>
		 	</div>
		</div>
	</div>

	<?php if( in_array( get_current_user_id() , get_post_meta($thispost, 'visible_to', false) ) || $is_visible) { ?>
	<div class="resume-overview-detailed">
		CONFIDENTIAL DETAILS HERE
	</div>
	<?php } ?>

</div>
