<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Output_RM extends WP_Job_Manager_Visibility_Output {

	/**
	 * WP_Job_Manager_Visibility_Output_RM constructor.
	 */
	public function __construct() {

		add_filter( 'job_manager_visibility_settings', array( $this, 'settings' ) );

		if ( ! get_option( 'jmv_enable_resume_manager_integration' ) ) return;

		$this->init_map_filters();

		// Specific fields with filters
		add_filter( 'the_candidate_title', array( $this, 'candidate_title' ), 9999999, 2 );
		add_filter( 'the_candidate_location', array( $this, 'candidate_location' ), 9999999, 2 );
		add_filter( 'the_candidate_photo', array( $this, 'candidate_photo' ), 9999999, 2 );
		add_filter( 'the_candidate_video', array( $this, 'candidate_video' ), 9999999, 2 );
		add_filter( 'the_resume_description', array( $this, 'resume_content' ), 9999999, 2 );
		add_filter( 'the_title', array( $this, 'candidate_name' ), 9999999, 2 );
		add_filter( 'single_post_title', array( $this, 'candidate_name' ), 9999999, 2 );
		add_filter( 'resume_manager_user_can_download_resume_file', array( $this, 'resume_file' ), 9999999, 2 );

		// Removed in 1.4.1 to move setting permalink from permalinks class
		//add_filter( 'submit_resume_form_save_resume_data', array( $this, 'save_resume' ), 9999999, 6 );
		add_filter( 'jmv_set_links_map_value', array( $this, 'links_map_value' ), 10, 5 );

		add_filter( 'resume_manager_user_can_view_contact_details', array( $this, 'candidate_email_check' ), 1, 2 );

		add_filter( 'jmv_output_taxonomies', array($this, 'init_taxonomies') );
		add_filter( 'jmv_output_maps', array($this, 'init_maps') );

	}

	/**
	 * Check for candidate_email field handling
	 *
	 * To handle the candidate_email field and output our placeholder, we first have to hook into the filter to
	 * check if user can view contact details.  We then check if the candidate_email field is hidden, if so, we
	 * remove all actions/filters from the contact details output, and add our own, to output the placeholder,
	 * by calling the candidate_email method in this class.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param boolean        $can_view
	 * @param string|integer $resume_id
	 *
	 * @return boolean
	 */
	function candidate_email_check( $can_view, $resume_id ){

		if( $this->field_hidden( 'candidate_email', $resume_id ) ) {

			remove_all_filters( 'resume_manager_contact_details' );
			add_action( 'resume_manager_contact_details', array( $this, 'candidate_email' ), 1 );

		}

		return $can_view;
	}

	/**
	 * Output candidate_email placeholder value
	 *
	 * This method is called by action {@see candidate_email_check} to output contact details,
	 * or in our case, the placeholder for this field.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	function candidate_email() {
		echo $this->get_placeholder( 'candidate_email' );
	}

	/**
	 * Filtered Save Resume Data
	 *
	 * This method is called by the `submit_resume_form_save_resume_data` filter which is executed by the Resumes
	 * plugin when it is creating a new resume.  This allows us to edit or modify any data or information required.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $data             Array of data to pass to wp_insert_post or wp_update_post (post_title, etc)
	 * @param $post_title       Same value as key post_title in $data array
	 * @param $post_content     Same value as key post_content in $data array
	 * @param $status           Normally would be 'preview' by default, unless it's an update to the resume
	 * @param $values           Array of values from the submit listing page
	 * @param $rmobj            Object for the WP_Resume_Manager_Form_Submit_Resume class
	 *
	 * @return \Array
	 */
	function save_resume( $data, $post_title, $post_content, $status, $values, $rmobj ){
		return $data;
	}

	/**
	 * Set Links Values based on Mapping
	 *
	 * This method is called by filter which is called dynamically `jmv_set_{$meta_key}_map_value` from
	 * parent class.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $value
	 * @param $key
	 * @param $ph_key
	 * @param $clear
	 * @param $config
	 *
	 * @return string
	 */
	function links_map_value( $value, $key, $ph_key, $clear, $config ){

		// If configured to use placeholder for URL
		if( $key === $ph_key ) $value = $config[ 'placeholder' ];

		if( $key === 'url' ){
			if ( ! empty( $clear[ 'url' ] ) ) $value = $clear[ 'url_placeholder' ];
			// Hack to check if URL has already been prepended
			$value = ! empty( $clear[ 'url_prepend' ] ) && strpos( $value, $clear['url_prepend'] ) === FALSE ? "{$clear['url_prepend']}{$value}" : $value;
		}

		return $value;
	}

	/**
	 * Initialize Resume Meta Key Taxonomies
	 *
	 * This method is called through the `jmv_output_taxonomies` filter, and adds
	 * specific resume meta key taxonomy configuration to the parent variable.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $taxonomies
	 *
	 * @return array
	 */
	function init_taxonomies( $taxonomies ) {

		$add_taxes = apply_filters( 'jmv_resume_taxonomies', array(
				'resume_skill' => array(
					'meta_key' => 'resume_skills'
				)
			)
		);

		return array_merge( $taxonomies, $add_taxes );
	}

	/**
	 * Initialize Resume Meta Key Mappings
	 *
	 * Some values require an array to be returned ( for specific field types ) and in order to do so
	 * we need to specifically configure some of those fields, use settings, and other methods to determine
	 * the approriate way to return the fields with the placeholder.
	 *
	 * This method gets called through the `jmv_output_maps` filter
	 *
	 * See map_meta_value() in parent class
	 *
	 * @since 1.1.0
	 *
	 * @param $maps
	 *
	 * @return array
	 */
	function init_maps( $maps ) {

		$add_maps = apply_filters( 'jmv_resume_maps', array(
				'candidate_education'  => array(
					'placeholder' => get_option( 'jmv_resume_maps_education_placeholder', 'notes' ),
					'clear'       => array(
						'location'      => get_option( 'jmv_resume_maps_education_clear_location', FALSE ),
						'qualification' => get_option( 'jmv_resume_maps_education_clear_qualification', FALSE ),
						'date'          => get_option( 'jmv_resume_maps_education_clear_date', FALSE ),
						'notes'         => get_option( 'jmv_resume_maps_education_clear_notes', FALSE ),
					)
				),
				'candidate_experience' => array(
					'placeholder' => get_option( 'jmv_resume_maps_experience_placeholder', 'notes' ),
					'clear'       => array(
						'employer'  => get_option( 'jmv_resume_maps_experience_clear_employer', FALSE ),
						'job_title' => get_option( 'jmv_resume_maps_experience_clear_job_title', FALSE ),
						'date'      => get_option( 'jmv_resume_maps_experience_clear_date', FALSE ),
						'notes'     => get_option( 'jmv_resume_maps_experience_clear_notes', FALSE ),
					)
				),
				'links'                => array(
					'placeholder' => get_option( 'jmv_resume_maps_links_placeholder', 'url' ),
					'clear'       => array(
						'url'             => get_option( 'jmv_resume_maps_links_clear_url', TRUE ),
						'url_placeholder' => get_option( 'jmv_resume_maps_links_clear_url_placeholder', '#' ),
						'url_prepend'     => get_option( 'jmv_resume_maps_links_clear_url_prepend', '' ),
					)
				)
			)
		);

		return array_merge( $maps, $add_maps );
	}

	/**
	 * Add Resume Tab to Visibility Settings
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	function settings( $settings ){

		$settings['integration'][1][] = array(
			'name'       => 'jmv_enable_resume_manager_integration',
			'std'        => '1',
			'label'      => __( 'Resume Listings', 'wp-job-manager-visibility' ),
			'cb_label'   => __( 'Enable', 'wp-job-manager-visibility' ),
			'desc'       => __( 'Enable processing of visibility configurations for Resume Manager.', 'wp-job-manager-visibility' ),
			'type'       => 'checkbox',
			'attributes' => array()
		);

		// Get all settings before index 2 in array (should be integration, then job)
		$settings_before = array_slice( $settings, 0, 2 );
		// Get all settings after index 2 in array
		$settings_after = array_slice( $settings, 2 );

		// Set resume settings as 3rd tab (3rd element in array)
		$settings_before['resume'] = array(
			__( 'Resumes', 'wp-job-manager-visibility' ),
			array(
				array(
					'name'       => 'jmv_resume_enable_custom_permalink',
					'std'        => '0',
					'label'      => __( 'Permalink', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Use Custom Permalinks', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Enable this option to use the custom permalink configuration below.  By default the Resumes plugin creates the permalink with Candidate Name, Title, and Location. .  By default the core plugin creates the permalink with Company Name, Location, Type, and Job Title.  Enabling this option sets this structure when a new listing is created/updated.  To update existing listings, use the force update permalink structure below.', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'        => 'jmv_resume_custom_permalink',
					'label'       => __( 'Permalink Structure', 'wp-job-manager-visibility' ),
					'type'        => 'textbox',
					'std'         => '',
					'field_class' => 'widefat',
					'placeholder' => '{random} {candidate_name} {!candidate_title} {candidate_location}',
					'desc'        => __( 'The custom permalink structure you want to use for Resume permalinks. Permalinks are generated using meta key values from the listing, and all meta keys MUST be inside curly braces ', 'wp-job-manager-visibility') . __( 'To prevent random value from being used when a field does not have a value, prepend the metakey with an exclamation point.  Example: ', 'wp-job-manager-visibility' ) . '<code>{!my_meta_key}</code><br />' .
					                 __( 'The default core values available are: ', 'wp-job-manager-visibility' ) . "<code>{candidate_name}</code>, <code>{candidate_title}</code>, <code>{candidate_location}</code>, and <code>{random}</code> (" . __( '10 random characters', 'wp-job-manager-visibility' ) . ")<br />" .
				                     __( '<small>Any meta key can be used (only string value ones), including custom fields, so if your meta key is <code>candidate_state</code>, you would use <code>{candidate_state}</code>', 'wp-job-manager-visibility' ) . "</small><br />" .
				                     __( '<small>Spaces will be automatically replaced with dash/hyphen <code>-</code>, and all values will be set to lowercase.  If values are too long, they will automatically be shortened as well. If all fields used are empty, the random string will be used.</small>', 'wp-job-manager-visibility' )
									,

				),
				array(
					'name'        => 'jmv_resume_update_permalinks',
					'caption'     => __( 'Force update existing listing permalinks', 'wp-job-manager-visibility' ),
					'field_class' => 'button-primary',
					'action'      => 'resume_update_permalinks',
					'label'       => __( 'Permalink Update', 'wp-job-manager-visibility' ),
					'desc'        => __( 'If you already have existing listings and have just enabled or changed the permalink structure, you need to force update existing listings if you want them to use the new structure.', 'wp-job-manager-visibility' ),
					'type'        => 'button'
				),
				array(
					'name'       => 'jmv_resume_maps_education_placeholder',
					'std'        => 'notes',
					'label'      => __( 'Education Placeholder', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Select what field for Education should be replaced with a placeholder (if configured)', 'wp-job-manager-visibility' ),
					'type'       => 'select',
					'attributes' => array(),
					'options'    => array(
						'location' => __( 'School name', 'wp-job-manager-visibility' ),
						'qualification' => __( 'Qualification(s)', 'wp-job-manager-visibility' ),
						'date' => __( 'Start/end date', 'wp-job-manager-visibility' ),
						'notes' => __( 'Notes', 'wp-job-manager-visibility' ),
					)
				),
				array(
					'name'       => 'jmv_resume_maps_education_clear_location',
					'std'        => '0',
					'label'      => __( 'School Name', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_education_clear_qualification',
					'std'        => '0',
					'label'      => __( 'Qualification(s)', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_education_clear_date',
					'std'        => '0',
					'label'      => __( 'Start/End Date', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_education_clear_notes',
					'std'        => '0',
					'label'      => __( 'Notes', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_experience_placeholder',
					'std'        => 'notes',
					'label'      => __( 'Experience Placeholder', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Select what field for Experience should be replaced with a placeholder (if configured)', 'wp-job-manager-visibility' ),
					'type'       => 'select',
					'attributes' => array(),
					'options'    => array(
						'location'      => __( 'Employer', 'wp-job-manager-visibility' ),
						'qualification' => __( 'Job Title', 'wp-job-manager-visibility' ),
						'date'          => __( 'Start/end date', 'wp-job-manager-visibility' ),
						'notes'         => __( 'Notes', 'wp-job-manager-visibility' ),
					)
				),
				array(
					'name'       => 'jmv_resume_maps_experience_clear_employer',
					'std'        => '0',
					'label'      => __( 'Employer', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_experience_clear_job_title',
					'std'        => '0',
					'label'      => __( 'Job Title', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_experience_clear_date',
					'std'        => '0',
					'label'      => __( 'Start/End Date', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_experience_clear_notes',
					'std'        => '0',
					'label'      => __( 'Notes', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Clear', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Should this field be cleared when a placeholder is used? Blank value will be used (unless selected for placeholder)', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmv_resume_maps_links_placeholder',
					'std'        => 'url',
					'label'      => __( 'Links Placeholder', 'wp-job-manager-visibility' ),
					'desc'       => __( 'Select what field for Links should be replaced with a placeholder (if configured).', 'wp-job-manager-visibility' ),
					'type'       => 'select',
					'attributes' => array(),
					'options'    => array(
						'url'      => __( 'URL', 'wp-job-manager-visibility' ),
						'name' => __( 'Name', 'wp-job-manager-visibility' ),
					)
				),
				array(
					'name'       => 'jmv_resume_maps_links_clear_url',
					'std'        => '0',
					'label'      => __( 'URL', 'wp-job-manager-visibility' ),
					'cb_label'   => __( 'Replace the URL with value from below', 'wp-job-manager-visibility' ),
					'desc'       => __( 'If Name is selected above, and this is checked, this will replace the URL in the HTML link tag with the value from below.', 'wp-job-manager-visibility' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'        => 'jmv_resume_maps_links_clear_url_placeholder',
					'std'         => '#',
					'label'       => __( 'URL Placeholder', 'wp-job-manager-visibility' ),
					'placeholder' => "#",
					'desc'        => __( 'This value will be used instead of the actual URL if enabled (checked) above and the placeholder is set as Name', 'wp-job-manager-visibility' ),
					'type'        => 'textbox',
					'attributes'  => array()
				),
				array(
					'name'        => 'jmv_resume_maps_links_clear_url_prepend',
					'std'         => '',
					'label'       => __( 'URL Prepend', 'wp-job-manager-visibility' ),
					'placeholder' => "//mysite.com/link-out.php?url=",
					'desc'        => __( 'If you want to prepend the URL with any value, set it here.  This will be used regardless of settings above, and only if there is a value set.', 'wp-job-manager-visibility' ),
					'type'        => 'textbox',
					'attributes'  => array()
				),
			)
		);

		// Merge everything back together
		$settings = array_merge( $settings_before, $settings_after );

		return $settings;
	}

	/**
	 * Resume File Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $allowed
	 * @param $post
	 *
	 * @return bool
	 */
	function resume_file( $allowed, $post ){

		if ( get_post_type( $post ) !== 'resume' ) return $allowed;

		$check_resume_file = $this->get_placeholder( 'resume_file', $post, $allowed );
		// If $allowed is TRUE and there is no config for resume_file, it will be passed
		// back to us.  This checks for anything other than true and returns false
		if( $check_resume_file !== TRUE ) return false;

		return true;
	}

	/**
	 * Resume Candidate Name Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param      $name
	 * @param null $post
	 *
	 * @return bool|string
	 */
	function candidate_name( $name, $post = null) {

		if( get_post_type( $post ) !== 'resume' ) return $name;

		return $this->get_placeholder( 'candidate_name', $post, $name, __( 'Candidate', 'wp-job-manager-visibility' ) );
	}

	/**
	 * Resume Candidate Title Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $title
	 * @param $resume
	 *
	 * @return bool|string
	 */
	function candidate_title( $title, $resume ){

		return $this->get_placeholder( 'candidate_title', $resume, $title );
	}

	/**
	 * Resume Candidate Location Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $location
	 * @param $resume
	 *
	 * @return bool|string
	 */
	function candidate_location( $location, $resume ) {

		return $this->get_placeholder( 'candidate_location', $resume, $location );
	}

	/**
	 * Resume Candidate Photo Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $photo
	 * @param $resume
	 *
	 * @return bool|string
	 */
	function candidate_photo( $photo, $resume ) {

		return $this->get_placeholder( 'candidate_photo', $resume, $photo );
	}

	/**
	 * Resume Candidate Video Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $video
	 * @param $resume
	 *
	 * @return bool|string
	 */
	function candidate_video( $video, $resume ) {

		return $this->get_placeholder( 'candidate_video', $resume, $video );
	}

	/**
	 * Resume Content Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $content
	 *
	 * @return bool|string
	 */
	function resume_content( $content ) {

		return $this->get_placeholder( 'resume_content', get_the_ID(), $content );
	}
}