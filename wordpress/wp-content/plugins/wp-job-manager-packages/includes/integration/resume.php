<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Integration_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Integration_Resume extends WPJM_Pack_Integration {

	/**
	 * @var array $template_actions     Template actions to output inside template overrides
	 */
	public $template_actions = array(
		'job_manager_packages_access_denied_single_resume' => 'single_resume_listing',
	    'job_manager_packages_access_denied_browse_resumes' => 'resume_listings',
	    'job_manager_packages_access_denied_contact_details' => 'contact_resume'
	);

	/**
	 * @var string $view_name_placeholder Value of view name placeholder for object caching
	 */
	public $view_name_placeholder;

	/**
	 * Construct Hooks
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function hooks(){

		add_filter( 'resume_manager_user_can_view_resume', array( $this, 'can_view_resume' ), 999999, 2 );
		add_filter( 'resume_manager_user_can_view_resume_name', array( $this, 'can_view_resume_name' ), 999999, 2 );

		add_filter( 'resume_manager_user_can_browse_resumes', array( $this, 'can_browse' ), 999999 );
		add_filter( 'resume_manager_user_can_view_contact_details', array( $this, 'can_contact' ), 999999, 2 );

		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_popup' ), 20 );

		add_filter( 'the_title', array( $this, 'resume_title' ), 1, 2 );
		add_filter( 'single_post_title', array( $this, 'resume_title' ), 1, 2 );

		add_filter( 'single_post_title', array( $this, 'strip_title_popup' ), 99, 2 );

		/**
		 * Add template actions after checking filter to enable them.
		 *
		 * This allows you to prevent the default action from firing (if you want to use your own action to do so),
		 * all you need to do is return false on the filter with the name of the action and an appended _enable.
		 */
		foreach( $this->template_actions as $template_action => $method ){

			if( apply_filters( "{$template_action}_enable", true ) ){
				add_action( $template_action, array( $this, $method ) );
			}

		}

	}

	/**
	 * Enqueue Popup Config and Script
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function enqueue_popup(){

		if( wp_script_is( 'jmpack-popup-js', 'enqueued' ) ){
			return;
		}

		if( empty( $this->view_name_placeholder ) ){
			$this->view_name_placeholder = $this->get_placeholder( 'view_name', sprintf( __( 'A <a href="%s">package</a> is required to view the full name on this listing.', 'wp-job-manager-packages' ), '[view_name_resume_packages_url]' ) );
		}

		// @see http://semantic-ui.com/modules/popup.html#/settings
		$popup_data = array(
			'exclusive' => true,
			'inline' => false,
			'hoverable' => true,
			'preserve' => true,
			'debug'    => true,
			//'movePopup' => false,
			'position' => 'bottom left',
			'lastResort' => 'right center',
			'variation' => 'flowing',
			'maxSearchDepth' => '10',
			//'prefer'    => 'opposite',
			'delay' => array( 'show' => 100, 'hide' => 250 ),
			'html' => $this->view_name_placeholder
		);

		$popup_data = apply_filters( 'job_manager_packages_resume_view_name_popup_data', $popup_data );

		wp_localize_script( 'jmpack-popup-js', 'jmpack_popupdata', $popup_data );
		wp_enqueue_script( 'jmpack-popup-js' );
		wp_enqueue_style( 'jmpack-sui-popup' );

	}

	/**
	 * Filter Resume Title
	 *
	 * This method/filter is called before the core Resume Manager filter runs, so we can check
	 * if we need to add filters on the Resume Manager filters to handle correctly.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param      $title
	 * @param null $post_or_id
	 *
	 * @return mixed
	 */
	public function resume_title( $title, $post_or_id = NULL ){

		if( ! $post_or_id || 'resume' !== get_post_type( $post_or_id ) ){
			return $title;
		}

		// Return title back if user has capability, is the author, status is preview, is share link URL, or user has package permissions
		if( $this->can_with_cap( 'view_name' ) || $this->is_post_author( $post_or_id ) || get_post_status( $post_or_id ) === 'preview' || $this->is_share_link( $post_or_id ) || $this->user_can( 'view_name' ) ){
			return $title;
		}

		$this->enqueue_popup();

		// Add filters to cause Resume Manager to process as no permissions to view name
		add_filter( 'packages_resume_manager_user_can_view_resume_name', array( $this, 'return_false' ), 999999 );
		add_filter( 'resume_manager_hidden_resume_title', array( $this, 'single_resume_listing_name' ), 999999, 3 );

		add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_popup' ) );

		// Still return title after adding our filters
		return $title;
	}

	/**
	 * Can Browse Resume Filter Handling
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $can_browse
	 *
	 * @return bool
	 */
	public function can_browse( $can_browse ){

		if( $this->can_with_cap( 'browse' ) || $this->user_can( 'browse' ) ){
			// For browse page
			$this->enqueue_popup();
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Can View Resume Filter Handling
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $can_view
	 * @param $resume_id
	 *
	 * @return bool
	 */
	public function can_view_resume( $can_view, $resume_id ){

		// If not post author, not share link, and no user permissions, return FALSE
		if( ! $this->is_post_author( $resume_id ) && ! $this->is_share_link( $resume_id ) && ! $this->user_can( 'view' ) ){
			// Add filter on template?
			$can_view = FALSE;
		}

		return $can_view;
	}
	/**
	 * Can View Resume Name Filter Handling
	 *
	 *
	 * @since @@since
	 *
	 * @param $can_view
	 * @param $resume_id
	 *
	 * @return bool
	 */
	public function can_view_resume_name( $can_view, $resume_id = 0 ){

		if( empty( $resume_id ) ){
			// Empty ID means probably old version of Resume Manager which does not pass $resume_id (1.15.3 and older)
			// To get around this, we call our own filter (to prevent returning the default true value), which would be added in $this->resume_title method
			return apply_filters( 'packages_resume_manager_user_can_view_resume_name', $can_view );
		}

		if ( ! $this->is_post_author( $resume_id ) && ! $this->is_share_link( $resume_id ) && ! $this->user_can( 'view_name' ) && ! $this->can_with_cap( 'view_name' ) ) {
			$can_view = FALSE;
		}

		return $can_view;
	}

	/**
	 * Can Contact Resume Filter Handling
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $can_contact
	 * @param $resume_id
	 *
	 * @return bool
	 */
	public function can_contact( $can_contact, $resume_id ){

		if( ! $this->is_post_author( $resume_id ) && ! $this->is_share_link( $resume_id ) && ! $this->user_can( 'contact' ) ){
			// Add filter on template?
			$can_contact = FALSE;
		}

		return $can_contact;
	}

	/**
	 * Filter core WordPress Template Include
	 *
	 * This method is required as the only filter available in core WordPress is this one, and as such, we have to
	 * filter on template files as early as possible, to return our own template file, as theme templates always
	 * take priority (this method overrides that).
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $full_template_path
	 *
	 * @return string
	 */
	public function template_include( $full_template_path ){

		return $full_template_path;

		$template = basename( $full_template_path );

		switch ( $template ) {

			/**
			 * Filter single-resume.php template file
			 *
			 * We have to filter this template file and return our own (if required), otherwise theme template files would be loaded
			 * instead, and chances are (99%) that this would bypass any integration we have to show our own template file.
			 */
			case 'single-resume.php':
				if( ! $this->user_can( 'view' ) ){
					// This template file uses a standard structure, but instead of calling core WordPress get_template_part() which
					// does not have a filter, it uses our custom get_job_manager_packages_template_part() to allow filtering as needed.
					$full_template_path = $this->locate_template( 'single-resume' );
				}

				break;

			default:
				break;
		}

		return $full_template_path;
	}

	/**
	 * Filter Core WP Job Manager Templates
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return string
	 */
	public function filter_template( $template, $template_name, $template_path ){

		// No need to filter on our own template path
		if( $template_path === 'jm_packages' ){
			return $template;
		}

		switch ( $template_name ) {

			case 'access-denied-single-resume.php':
				if( ! $this->user_can( 'view' ) ){
					// Load out custom template
					$template = $this->locate_template( 'access-denied-single-resume' );
				}

				break;

			case 'access-denied-browse-resumes.php':
				if( ! $this->user_can( 'browse' ) ){
					// Load out custom template
					$template = $this->locate_template( 'access-denied-browse-resumes' );
				}

				break;

			case 'access-denied-contact-details.php':
				if( ! $this->user_can( 'contact' ) ){
					// Load out custom template
					$template = $this->locate_template( 'access-denied-contact-details' );
				}

				break;

			default:
				break;
		}

		return $template;
	}

	/**
	 * Browse Resume Listings Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function resume_listings(){

		$default = sprintf( __( 'Please <a href="%s">select a package</a> to browse listings.', 'wp-job-manager-packages' ), '[browse_resume_packages_url]' );

		$this->output_placeholder( 'browse', $default );
	}

	/**
	 * Single Resume Listing Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function single_resume_listing(){

		$default = sprintf( __( 'Please <a href="%s">select a package</a> to view this listing\'s details.', 'wp-job-manager-packages' ), '[view_resume_packages_url]' );

		$this->output_placeholder( 'view', $default );
	}

	/**
	 * Strip Popup HTML from Title
	 *
	 * This method strips the popup HTML from the title to prevent outputting
	 * HTML inside the main <title></title> area.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param      $title
	 * @param null $post_or_id
	 *
	 * @return mixed
	 */
	public function strip_title_popup( $title, $post_or_id = NULL ){

		if( ! $post_or_id || 'resume' !== get_post_type( $post_or_id ) ){
			return $title;
		}

		if( $post_or_id instanceof WP_Post ){
			$post_or_id = $post_or_id->ID;
		}

		if( strpos( $title, 'jmpack-popup' ) !== FALSE ){
			// Remove opening popup wrapper
			$title = str_replace( '<span class="ui jmpack-popup" style="position: relative;" data-listing_id="' . $post_or_id . '">', '', $title );
			// Remove closing wrapper
			$title = str_replace( '</span>', '', $title );
		}

		return $title;
	}

	/**
	 * Single Resume Listing Name Popover Handling
	 *
	 * This method adds the popover wrapping element to the title, allowing the popup
	 * to show with the placeholder value inside it.
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function single_resume_listing_name( $title, $orig_title, $post_or_id ){

		if( $post_or_id instanceof WP_Post ){
			$post_or_id = $post_or_id->ID;
		}

		$name = explode( ' ', $title );

		// Place popup wrapper around the hidden part of the name
		if( ! empty( $name ) ){
			// Remove first name from title
			$title = str_replace( $name[0], '', $title );
			// Rebuild title with popup wrapper around hidden part
			$title = $name[0] . '<span class="ui jmpack-popup" style="position: relative;" data-listing_id="' . $post_or_id . '">' . $title . '</span>';
		}

		return $title;
	}

	/**
	 * Contact Resume Placeholder Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function contact_resume(){

		$default = sprintf( __( 'Please <a href="%1$s">select a package</a> to %2$s this listing.', 'wp-job-manager-packages' ), '[contact_resume_packages_url]', $this->type->packages->get_package_type( 'contact', 'verb', TRUE ) );

		$this->output_placeholder( 'contact', $default );

	}

	/**
	 * Check if View Package Required
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_view_package(){

		return get_option( 'job_manager_resume_visibility_require_package_view', FALSE );

	}

	/**
	 * Check if View Name Package Required
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_view_name_package(){

		return get_option( 'job_manager_resume_visibility_require_package_view_name', FALSE );

	}

	/**
	 * Check if Browse Package Required
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_browse_package(){

		return get_option( 'job_manager_resume_visibility_require_package_browse', FALSE );

	}

	/**
	 * Check if Contact Package Required
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	public function require_contact_package(){

		return get_option( 'job_manager_resume_visibility_require_package_contact', FALSE );

	}

	/**
	 * Check if User has Permissions with Capability Overrides
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function can_with_cap( $type ){

		$can_view = FALSE;
		$option   = $type === 'view_name' ? $type : "{$type}_resume";

		// Allow capability overrides
		$caps = array_filter( array_map( 'trim', array_map( 'strtolower', explode( ',', get_option( "resume_manager_{$option}_capability" ) ) ) ) );

		if( $caps ){

			foreach( $caps as $cap ) {
				if( current_user_can( $cap ) ){
					$can_view = TRUE;
					break;
				}
			}

		}

		return $can_view;
	}

	/**
	 * Check if Share Link Used to Access Resume
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $resume_id
	 *
	 * @return mixed|void
	 */
	public function is_share_link( $resume_id ){

		if( is_object( $resume_id ) && $resume_id->ID ){
			$resume_id = $resume_id->ID;
		}

		$key = get_post_meta( $resume_id, 'share_link_key', TRUE );

		if( $key && ! empty( $_GET['key'] ) && $key == $_GET['key'] ){
			$is_share_link = TRUE;
		} else {
			$is_share_link = FALSE;
		}

		return apply_filters( 'job_manager_packages_resume_is_share_link', $is_share_link, $resume_id, $key );
	}

	/**
	 * Check if Current User is Post Author of Resume
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $resume_id
	 *
	 * @return mixed|void
	 */
	public function is_post_author( $resume_id ){

		$resume = get_post( $resume_id );

		if( $resume->post_author > 0 && $resume->post_author == get_current_user_id() ){
			$is_post_author = TRUE;
		} else {
			$is_post_author = FALSE;
		}

		return apply_filters( 'job_manager_packages_resume_is_post_author', $is_post_author, $resume_id, $resume );
	}
}
