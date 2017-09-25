<?php if ( resume_manager_user_can_view_resume( $post->ID ) ) : ?>
	<div class="single-resume-content">

		<?php do_action( 'single_resume_start' ); ?>

		<div class="resume-aside">
			<?php the_candidate_photo(); ?>
			<?php the_resume_links(); ?>
			<p class="job-title"><?php the_candidate_title(); ?></p>
			<p class="location"><?php the_candidate_location(); ?></p>

			<?php the_candidate_video(); ?>
		</div>

		<div class="resume_description">
			<?php echo apply_filters( 'the_resume_description', get_the_content() ); ?>
		</div>

		<?php if ( ( $skills = wp_get_object_terms( $post->ID, 'resume_skill', array( 'fields' => 'names' ) ) ) && is_array( $skills ) ) : ?>
			<h2><?php _e( 'Skills', 'wp-job-manager-resumes' ); ?></h2>
			<ul class="resume-manager-skills">
				<?php echo '<li>' . implode( '</li><li>', $skills ) . '</li>'; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $items = get_post_meta( $post->ID, '_candidate_education', true ) ) : ?>
			<h2><?php _e( 'Education', 'wp-job-manager-resumes' ); ?></h2>
			<dl class="resume-manager-education">
			<?php
				foreach( $items as $item ) : ?>

					<dt>
						<small class="date"><?php echo esc_html( $item['date'] ); ?></small>
						<h3><?php printf( __( '%s at %s', 'wp-job-manager-resumes' ), '<strong class="qualification">' . esc_html( $item['qualification'] ) . '</strong>', '<strong class="location">' . esc_html( $item['location'] ) . '</strong>' ); ?></h3>
					</dt>
					<dd>
						<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
					</dd>

				<?php endforeach;
			?>
			</dl>
		<?php endif; ?>

		<?php if ( $items = get_post_meta( $post->ID, '_candidate_experience', true ) ) : ?>
			<h2><?php _e( 'Experience', 'wp-job-manager-resumes' ); ?></h2>
			<dl class="resume-manager-experience">
			<?php
				foreach( $items as $item ) : ?>

					<dt>
						<small class="date"><?php echo esc_html( $item['date'] ); ?></small>
						<h3><?php printf( __( '%s at %s', 'wp-job-manager-resumes' ), '<strong class="job_title">' . esc_html( $item['job_title'] ) . '</strong>', '<strong class="employer">' . esc_html( $item['employer'] ) . '</strong>' ); ?></h3>
					</dt>
					<dd>
						<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
					</dd>

				<?php endforeach;
			?>
			</dl>
		<?php endif; ?>

		<ul class="meta">
			<?php do_action( 'single_resume_meta_start' ); ?>

			<?php if ( get_the_resume_category() ) : ?>
				<li class="resume-category"><?php the_resume_category(); ?></li>
			<?php endif; ?>

			<li class="date-posted" itemprop="datePosted"><date><?php printf( __( 'Updated %s ago', 'wp-job-manager-resumes' ), human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></li>

			<?php do_action( 'single_resume_meta_end' ); ?>
		</ul>

		<?php get_job_manager_template( 'contact-details.php', array( 'post' => $post ), 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

		<?php do_action( 'single_resume_end' ); ?>
	</div>
<?php else : ?>
	<div class="single-resume-content">

		<?php do_action( 'single_resume_start' ); ?>

		<div class="resume-aside">
			<?php the_candidate_photo(); ?>
			<?php the_resume_links(); ?>
			<p class="job-title"><?php the_candidate_title(); ?></p>
			<p class="location"><?php the_candidate_location(); ?></p>			
		</div>

		<?php if ( $items = get_post_meta( $post->ID, '_candidate_experience', true ) ) : ?>
			<h2><?php _e( 'Experience', 'wp-job-manager-resumes' ); ?></h2>
			<dl class="resume-manager-experience">
			<?php
				foreach( $items as $item ) : ?>

					<dt>
						<small class="date"><?php echo esc_html( $item['date'] ); ?></small>
						<h3><?php printf( __( '%s at %s', 'wp-job-manager-resumes' ), '<span class="job_title">' . esc_html( $item['job_title'] ) . '</span>', '<span class="employer">' . esc_html( $item['employer'] ) . '</span>' ); ?></h3>
					</dt>
					<dd>
						<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
					</dd>

				<?php endforeach;
			?>
			</dl>
		<?php endif; ?>

		<?php if ( ( $skills = wp_get_object_terms( $post->ID, 'resume_skill', array( 'fields' => 'names' ) ) ) && is_array( $skills ) ) : ?>
			<h2><?php _e( 'Skills', 'wp-job-manager-resumes' ); ?></h2>
			<ul class="resume-manager-skills">
				<?php echo '<li>' . implode( '</li><li>', $skills ) . '</li>'; ?>
			</ul>
		<?php endif; ?>

		<br/>
		<?php
		global $resume_preview;

		if ( $resume_preview ) {
			return;
		}

		wp_enqueue_script( 'wp-resume-manager-resume-contact-details' );	
		?>
			<div class="resume_contact">
				<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'wp-job-manager-resumes' ); ?>" />

				<div class="resume_contact_details">
					Choose Job:
					<select>
						<?php foreach ( $jobs as $job ) : ?>
							<option><?php wpjm_the_job_title( $job ); ?></option>
						<?php endforeach; ?>
					</select>
					<a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $email; ?>&su=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Send</a>
				</div>
			</div>

		<?php do_action( 'single_resume_end' ); ?>
	</div>
	
	<!-- <!-- <?php get_job_manager_template_part( 'access-denied', 'single-resume', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

<?php endif; ?>