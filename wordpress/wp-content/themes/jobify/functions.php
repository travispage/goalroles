<?php
/**
 * Jobify
 *
 * Do not modify this file. Place all modifications in a child theme.
 *
 * @package Jobify
 * @category Theme
 * @since 1.0.0
 */
class Jobify {

	/**
	 * The single instance of the Jobify object.
	 *
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * @var object $activation
	 */
	public $activation;

	/**
	 * @var object $setup
	 */
	public $setup;

	/**
	 * @var object $integrations
	 */
	public $integrations;

	/**
	 * @var object $template
	 */
	public $template;

	/**
	 * @var object $widgets
	 */
	public $widgets;

	/**
	 * Find the single instance of the class.
	 *
	 * @since 3.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Jobify ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->includes();
		$this->setup();
	}

	/**
	 * Integration getter helper.
	 *
	 * @since 3.0.0
	 *
	 * @param string $integration The name of the integration to load.
	 * @return object $integration
	 */
	public function get( $integration ) {
		return $this->integrations->get( $integration );
	}

	/**
	 * Load the necessary files.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function includes() {
		$this->files = array(
			'customizer/class-customizer.php',

			'class-deprecated.php',
			'class-helpers.php',

			'activation/class-activation.php',

			'setup/class-setup.php',
			'pages/class-page-settings.php',
			'pages/class-page-header.php',

			'listing/class-listing-factory.php',
			'listing/class-listing.php',
			'listing/template-tags.php',

			'integrations/class-integrations.php',
			'integrations/class-integration.php',

			'template/class-template.php',

			'widgets/class-widgetized-pages.php',
			'widgets/class-widgets.php',
			'widgets/class-widget.php',
		);

		foreach ( $this->files as $file ) {
			require_once( get_template_directory() . '/inc/' . $file );
		}
	}

	/**
	 * Instantiate necessary classes and assign them to relevant
	 * class properties.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function setup() {
		$this->activation = Jobify_Activation::init();
		$this->integrations = new Jobify_Integrations();
		$this->template = new Jobify_Template();
		$this->widgets = new Jobify_Widgets();

		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
	}

	/**
	 * Standard WordPress theme setup
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function setup_theme() {
		// set the content width
		$GLOBALS['content_width'] = apply_filters( 'jobify_content_width', 680 );

		// load translations
		$locale = apply_filters( 'plugin_locale', get_locale(), 'jobify' );
		load_textdomain( 'jobify', WP_LANG_DIR . "/jobify-$locale.mo" );
		load_theme_textdomain( 'jobify', get_template_directory() . '/languages' );

		// load editor-style.css
		add_editor_style();

		// theme supports
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		add_theme_support( 'custom-background', array(
			'default-color'    => '#ffffff',
		) );

		add_theme_support( 'custom-header', array(
			'default-text-color'     => '666666',
			'height'                 => 44,
			'width'                  => 200,
			'flex-width'             => true,
			'flex-height'            => true,
			'wp-head-callback'       => array( jobify()->template->header, 'custom_header_style' ),
		) );

		add_theme_support( 'customize-selective-refresh-widgets' );

		// nav menus
		register_nav_menus( array(
			'primary'       => __( 'Navigation Menu', 'jobify' ),
			'footer-social' => __( 'Footer Social', 'jobify' ),
		) );

		// images
		add_image_size( 'content-grid', 400, 200, true );
		add_image_size( 'content-job-featured', 1350, 525, true );

		// extras
		add_filter( 'excerpt_more', '__return_false' );
		add_filter( 'widget_text', 'do_shortcode' );
	}

}

/**
 * Helper function for accessing the main `Jobify` class.
 *
 * @since 3.0.0
 *
 * @return object Jobify The single instance of the `Jobify` class.
 */
function jobify() {
	return Jobify::instance();
}

// Oh get a job? Just get a job?
jobify();


/*
 * MODIFICATIONS
 */

// Remove default fields
add_filter( 'submit_resume_form_fields', 'remove_submit_resume_form_fields' );
function remove_submit_resume_form_fields( $fields ) {

	unset( $fields['resume_fields']['candidate_title'] );
	unset( $fields['resume_fields']['candidate_photo'] );
	unset( $fields['resume_fields']['candidate_video'] );		
	unset( $fields['resume_fields']['links'] );
	unset( $fields['resume_fields']['candidate_education'] );
	unset( $fields['resume_fields']['candidate_experience'] );

	return $fields;
}

// Add your own function to filter the fields
add_filter( 'submit_resume_form_fields', 'custom_submit_resume_form_fields' );
function custom_submit_resume_form_fields( $fields ) {
    
    $fields['resume_fields']['candidate_name']['label'] = "Full Name";
    $fields['resume_fields']['candidate_name']['priority'] = 1;

    $fields['resume_fields']['candidate_email']['label'] = "Email";
    $fields['resume_fields']['candidate_email']['priority'] = 2;

    $fields['resume_fields']['candidate_location']['label'] = "Location";
    $fields['resume_fields']['candidate_location']['priority'] = 3;

    $fields['resume_fields']['resume_skills']['label'] = "Notable Qualifications";
    $fields['resume_fields']['resume_skills']['priority'] = 8;

    $fields['resume_fields']['resume_content']['label'] = "Short Description";
    $fields['resume_fields']['resume_content']['required'] = "False";
    $fields['resume_fields']['resume_content']['priority'] = 20;

    // And return the modified fields
    return $fields;
}

// Add field to frontend
add_filter( 'submit_resume_form_fields', 'wpjms_frontend_resume_form_fields' );
function wpjms_frontend_resume_form_fields( $fields ) {

	$fields['resume_fields']['candidate_postcode'] = array(
	    'label' => __( 'Residential Postcode', 'job_manager' ),
	    'type' => 'text',
	    'required' => true,
	    'placeholder' => '',
	    'priority' => 4
	);
	
	$fields['resume_fields']['candidate_sex'] = array(
	    'label'    => __( 'Sex', 'wp-job-manager' ),
 		'type'     => 'radio',
 		'required' => true,
 		'default'  => 'option1',
 		'priority' => 5,
 		'options'  => array(
 			'Male' => 'Male',
 		 	'Female' => 'Female'
 		)
	);

	$fields['resume_fields']['candidate_age_range'] = array(
	    'label' => __( 'Age Range', 'job_manager' ),
	    'type' => 'text',
	    'required' => true,
	    'placeholder' => '',
	    'priority' => 7
	);	

	$fields['resume_fields']['candidate_current_status'] = array(
	    'label'    => __( 'Current Employment Status', 'job_manager' ),
 		'type'     => 'select',
 		'required' => true,
 		'default'  => 'option1',
 		'priority' => 15,
 		'options'  => array(
 			'Employed' => 'Employed',
 		 	'Unemployed' => 'Unemployed',
 		 	'Contractual' => 'Contractual',
 		 	'Part Time' => 'Part Time'
 		)
	);

	$fields['resume_fields']['candidate_industry'] = array(
	    'label'    => __( 'Industry', 'job_manager' ),
 		'type'     => 'select',
 		'required' => true,
 		'default'  => 'option1',
 		'priority' => 16,
 		'options'  => array(
 			'Accounting' => 'Accounting', 		 	
 		 	'Human Resource' => 'Human Resource',
 		 	'Information Technology' => 'Information Technology', 		 	
 		 	'Retail' => 'Retail'
 		)
	);

	$fields['resume_fields']['candidate_current_title'] = array(
	    'label' => __( 'Job Title', 'job_manager' ),
	    'type' => 'text',
	    'required' => true,
	    'placeholder' => '',
	    'priority' => 17
	);

	$fields['resume_fields']['candidate_current_time'] = array(
	    'label' => __( 'Time in Role', 'job_manager' ),
	    'type' => 'text',
	    'required' => true,
	    'placeholder' => '',
	    'priority' => 18
	);

	$fields['resume_fields']['candidate_expected_status'] = array(
	    'label'    => __( 'Looking For', 'job_manager' ),
 		'type'     => 'select',
 		'required' => true,
 		'default'  => 'option1',
 		'priority' => 19,
 		'options'  => array(
 			'Full Time' => 'Full Time', 
 			'Part Time' => 'Part Time',		 	
 		 	'Contractua' => 'Contractual' 		 	
 		)
	);

	return $fields;
	
}

// Add field to admin
add_filter( 'resume_manager_resume_fields', 'wpjms_admin_resume_form_fields' );
function wpjms_admin_resume_form_fields( $fields ) {

	$fields['_candidate_sex'] = array(
	    'label'    => __( 'Sex', 'wp-job-manager' ),
 		'type'     => 'radio', 		
 		'default'  => 'option1',
 		'priority' => 1,
 		'options'  => array(
 			'Male' => 'Male',
 		 	'Female' => 'Female'
 		)
	);

	$fields['_candidate_age_range'] = array(
	    'label' => __( 'Age Range', 'job_manager' ),
	    'type' => 'text',
	    'placeholder' => '',
	    'priority' => 2
	);

	$fields['_candidate_postcode'] = array(
	    'label' => __( 'Residential Postcode', 'job_manager' ),
	    'type' => 'text',
	    'placeholder' => '',
	    'priority' => 3
	);

	$fields['_candidate_current_status'] = array(
	    'label'    => __( 'Current Employment Status', 'job_manager' ),
 		'type'     => 'select', 		
 		'default'  => 'option1',
 		'priority' => 5,
 		'options'  => array(
 			'Employed' => 'Employed',
 		 	'Unemployed' => 'Unemployed',
 		 	'Contractual' => 'Contractual',
 		 	'Part Time' => 'Part Time'
 		)
	);

	$fields['_candidate_industry'] = array(
	    'label'    => __( 'Industry', 'job_manager' ),
 		'type'     => 'select', 		
 		'default'  => 'option1',
 		'priority' => 6,
 		'options'  => array(
 			'Accounting' => 'Accounting', 		 	
 		 	'Human Resource' => 'Human Resource',
 		 	'Information Technology' => 'Information Technology', 		 	
 		 	'Retail' => 'Retail'
 		)
	);

	$fields['_candidate_current_title'] = array(
	    'label' => __( 'Job Title', 'job_manager' ),
	    'type' => 'text',	    
	    'placeholder' => '',
	    'priority' => 7
	);

	$fields['_candidate_current_time'] = array(
	    'label' => __( 'Time in Role', 'job_manager' ),
	    'type' => 'text',	    
	    'placeholder' => '',
	    'priority' => 8
	);

	$fields['_candidate_expected_status'] = array(
	    'label'    => __( 'Looking For', 'job_manager' ),
 		'type'     => 'select', 		
 		'default'  => 'option1',
 		'priority' => 9,
 		'options'  => array(
 			'Full Time' => 'Full Time', 
 			'Part Time' => 'Part Time',		 	
 		 	'Contractual' => 'Contractual' 		 	
 		)
	);

	return $fields;	
}
// // Add a line to the notifcation email with custom field
// add_filter( 'apply_with_resume_email_message', 'wpjms_color_field_email_message', 10, 2 );
// function wpjms_color_field_email_message( $message, $resume_id ) {
//   $message[] = "\n" . "Sex: " . get_post_meta( $resume_id, '_candidate_sex', true );  
//   return $message;
// }

/**
 * Remove the preview step. Code goes in theme functions.php or custom plugin.
 * @param  array $steps
 * @return array
 */
function wpjm_remove_resume_preview_step( $steps ) {
	unset( $steps['preview'] );
	return $steps;
}
add_filter( 'submit_resume_steps', 'wpjm_remove_resume_preview_step' );

/**
 * Change button text (won't work until v1.16.2)
 */
function wpjm_change_resume_button_text() {
	return __( 'Submit Resume' );
}
add_filter( 'submit_resume_form_submit_button_text', 'wpjm_change_resume_button_text' );

/**
 * Since we removed the preview step and it's handler, we need to manually publish resumes
 * @param  int $resume_id
 */
function wpjm_manually_publish_resume( $resume_id ) {

	$resume = get_post( $resume_id );

	if ( in_array( $resume->post_status, array( 'preview', 'expired' ) ) ) {

		// Reset expirey
		delete_post_meta( $resume->ID, '_resume_expires' );

		// Update resume listing
		$update_resume                  = array();
		$update_resume['ID']            = $resume->ID;
		$update_resume['post_status']   = apply_filters( 'submit_resume_post_status', get_option( 'resume_manager_submission_requires_approval' ) ? 'pending' : 'publish', $resume );
		wp_update_post( $update_resume );
	}
}
add_action( 'resume_manager_resume_submitted', 'wpjm_manually_publish_resume' );


/*
 * Send Job Description AJAX
 */
function custom_send_job_description_callback () {
	// retrieve post_id, and sanitize it to enhance security
    $post_id = intval($_POST['post_id'] );
    $resume_id = intval($_POST['resume_id'] );    

    // Check if the input was a valid integer
    if ( $post_id == 0 ) {
        $response['error'] = 'true';
        $response['result'] = 'Invalid Input';
    } else {
        // get the post
        $thispost = get_post( $post_id );
        $resume = get_post( $resume_id );
        // check if post exists
        if ( !is_object( $thispost ) ) {
            $response['error'] = 'true';
            $response['result'] =  'There is no post with the ID ' . $post_id;
        } else {
        	$candidate_id = $resume->post_author;
			$candidate = get_user_by('id', $candidate_id);
			$employer_id = get_current_user_id();

			$request_token = bin2hex(random_bytes(16));
			$confirmation_link = get_permalink($resume_id) . "?request=" . $employer_id . "&token=" . $request_token;
						
			if (!get_post_meta( $post_id, $employer_id )) {
				add_post_meta( $resume_id, $employer_id, $request_token );
			}
			else {
				update_post_meta( $resume_id, $employer_id, $request_token );
			}

            $response['error'] = 'false';
            $response['result'] = wpautop( $thispost->post_content );                          
            $response['candidate'] = $candidate->user_email;                        
                        
            $content = $thispost->post_title . "<br/>";
            $content .= $thispost->post_content;
            $content .= "<br/><br/>";
            $content .= "If you would like continue, please click the link below.<br/>";
            $content .= $confirmation_link;

            $to = $candidate->user_email;
			$subject = 'Job Invitation';
			$body = $content;
			$headers = array('Content-Type: text/html; charset=UTF-8');
			 
			wp_mail( $to, $subject, $body, $headers );      
        }
    }
    wp_send_json( $response );
}
add_action( 'wp_ajax_send_job_description', 'custom_send_job_description_callback' );