<h5>SEND JOB DESCRIPTION</h5>

<?php 
$resume_id = get_the_ID();
$jobs = get_job_listings( array(
	'search_location'   => '',
	'search_keywords'   => '',	
	'orderby'           => 'date',
	'order'             => 'DESC',
) );
?>
<ul class="job_listings">

	<?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>

		<li id="job_listing-<?php the_ID(); ?>" <?php jobify_listing_html_class(); ?> <?php echo apply_filters( 'jobify_listing_data', '' ); ?> post_id="<?php the_ID(); ?>" resume_id="<?=$resume_id;?>">
			<!-- <?php 
			$subject = "Good day! <br/><br/>"
					. "Please apply at " . jobify_listing_permalink() . "<br/><br/>"
					. "Thanks!";
			printf( __( '<a class="job_application_email job_listing-clickbox" href="mailto:%1$s%2$s"></a>', 'wp-job-manager-resumes' ), $email, '?subject=' . rawurlencode( $subject ) ); 
			?> -->
			<!-- <a href="<?php jobify_listing_permalink(); ?>" class="job_listing-clickbox"></a> -->

			<div class="job_listing-logo">
				<?php jobify_the_company_logo( 'fullsize' ); ?>
			</div><div class="job_listing-about">

				<div class="job_listing-position job_listing__column">
					<h3 class="job_listing-title"><?php the_title(); ?></h3>					
				</div>

				<div class="job_listing-location job_listing__column">
					<?php echo jobify_get_formatted_address(); ?>
				</div>

				<ul class="job_listing-meta job_listing__column">
					<?php do_action( 'job_listing_meta_start' ); ?>

					<?php foreach( jobify_get_the_job_types() as $type ) : ?>
						<li class="job_listing-type job-type <?php echo esc_attr( sanitize_title( $type ? $type->slug : '' ) ); ?>"><?php echo $type->name; ?></li>
					<?php endforeach; ?>					

					<?php do_action( 'job_listing_meta_end' ); ?>
				</ul>

			</div>
		</li>

	<?php endwhile; ?>

</ul>

<!-- <p><?php printf( __( '<button class="job_application_email" href="mailto:%1$s%2$s">SEND</button>', 'wp-job-manager-resumes' ), $email, '?subject=' . rawurlencode( $subject ) ); ?></p> -->

<!-- <p>
	<?php _e( 'Contact using webmail: ', 'wp-job-manager-resumes' ); ?>

	<a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $email; ?>&su=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Gmail</a> / 
	
	<a href="http://webmail.aol.com/Mail/ComposeMessage.aspx?to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">AOL</a> / 
	
	<a href="http://compose.mail.yahoo.com/?to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Yahoo</a> / 
	
	<a href="http://mail.live.com/mail/EditMessageLight.aspx?n=&to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Outlook</a>
</p> -->

<script>
$(".job_listing").click(function () {
    var post_id = $(this).attr('post_id');
    var resume_id = $(this).attr('resume_id');
    $.ajax({
        type: 'POST',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        data: {
            'post_id': post_id,
            'resume_id': resume_id,
            'action': 'send_job_description' //this is the name of the AJAX method called in WordPress
        }, 
        success: function (result) {
        	alert('Email successfully sent!');
        	console.log(result);
        	// location.reload();
        },
        error: function () {
            console.log("error");
        }
    });

});
</script>