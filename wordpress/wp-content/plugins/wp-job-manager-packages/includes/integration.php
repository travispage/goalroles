<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Integration
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Integration {

	/**
	 * @var string Check if cache is clean or needs to be cleaned
	 */
	protected $cache_is_clean = 'QtQ94qBOyFSweNLTGua4HgEE';
	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	protected $type;
	/**
	 * @var boolean
	 */
	protected $force_no_results;

	/**
	 * WPJM_Pack_Integration constructor.
	 *
	 * @param $type WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	public function __construct( $type ) {

		$this->type = $type;
		add_filter( 'job_manager_locate_template', array( $this, 'filter_template' ), 99999999, 3 );
		add_filter( 'template_include', array( $this, 'template_include' ), 9999 );

		$this->hooks();
	}

	/**
	 * Check if Placeholder should Redirect
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function should_redirect( $type ){
		$slug = $this->type->slug;
		$do_redirect = get_option( "job_manager_{$slug}_visibility_require_package_{$type}_redirect", FALSE );
		$permalink = $this->type->shortcodes->get_permalink( $type );

		// If redirect not enabled or error getting permalink, don't try to redirect
		if( ! $do_redirect || empty( $permalink ) ){
			return false;
		}

		$white_dimmer = 'inverted';
		$seconds      = 1;
		$hide_all     = '';

		// If custom redirect settings enabled, overwrite defaults
		if( get_option( "job_manager_{$slug}_visibility_customize_redirect", true ) ){

			$white_dimmer = get_option( "job_manager_{$slug}_visibility_redirect_dimmer_inverted", TRUE ) ? 'inverted' : '';
			$seconds      = absint( get_option( "job_manager_{$slug}_visibility_redirect_seconds", 1 ) );

			if( get_option( "job_manager_{$slug}_visibility_redirect_hide_all", FALSE ) ){
				$hide_all = '<style>html { visibility: hidden; } div#jmpack-redirect-dimmer-wrap * { display: block;} div#jmpack-redirect-notice a, span.jmpack_redirect_in { display: inline !important; }</style>';
				echo $hide_all;
			}

		}

		// Notice to display under spinning loader
		$notice = apply_filters( "job_manager_packages_output_{$slug}_placeholder_redirect_notice", sprintf( __( 'This page should redirect in <span class="jmpack_redirect_in">%1$d</span> seconds, if not, please <a href="%2$s">click here</a>.', 'wp-job-manager-packages' ), $seconds, $permalink ), $type, $seconds, $permalink );

		wp_enqueue_style( 'jmpack-sui-full' );
		wp_enqueue_script( 'jmpack-sui-full' );

		// Redirect handling for when JavaScript is disabled in browser
		echo "<noscript><style>html { display: none; }</style><meta http-equiv=\"refresh\" content=\"0.0;url={$permalink}\"></noscript>";
		get_job_manager_packages_form_template( $type, $this->type->slug, 'package-redirect', array( 'permalink' => $permalink, 'type' => $type, 'slug' => $this->type->slug, 'white_dimmer' => $white_dimmer, 'seconds' => $seconds, 'hide_all' => $hide_all, 'notice' => $notice ) );

		$seconds = absint( $seconds );
		// Redirect handling and dimmer with jQuery
		$milliseconds = absint( $seconds * 1000 );
		echo "<script type=\"text/javascript\">jQuery( function ( $ ) { $( '#jmpack-redirect-dimmer' ).dimmer( {'closable': false} ); var redirect_count = '{$seconds}'; var redirect_countdown=setInterval(jmpack_countdown, 1000); var countdown_elem=$('.jmpack_redirect_in');function jmpack_countdown(){return redirect_count-=1,redirect_count<0?void clearInterval(redirect_countdown):void countdown_elem.html(redirect_count)}} ); window.setTimeout( function () { window.location.href = \"{$permalink}\"; }, {$milliseconds} );</script>";

		return true;
	}

	/**
	 *
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $post_type
	 *
	 * @return string
	 */
	public function get_admin_capability( $post_type ){

		if( $post_type === 'job_listing' ) {
			return 'manage_job_listings';
		}

		if( $post_type === 'resume' ) {
			return 'manage_resumes';
		}

		return 'manage_options';
	}

	/**
	 * Check if Current User is Admin or Author
	 *
	 * Checks the core capability for the current user
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param null $post_id
	 * @param null $user_id
	 *
	 * @return bool
	 */
	public function is_admin_or_author( $post_id = NULL, $user_id = NULL ){

		if( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if( is_object( $post_id ) ) {
			$post_id = $post_id->ID;
		}

		// Allow previews
		if( $post_id && get_post_status( $post_id ) === 'preview' ){
			return TRUE;
		}

		$post_type = get_post_type( $post_id );
		$admin_exception  = isset( $_GET['admin_exception'] ) ? TRUE : apply_filters( 'job_manager_packages_admin_required_packages_frontend', FALSE );
		$admin_capability = ! empty( $post_type ) ? $this->get_admin_capability( $post_type ) : false;

		if( ! $admin_exception && $admin_capability && current_user_can( $admin_capability ) ) {
			return TRUE;
		}

		// Check if admin is attempting to edit a listing
		$is_edit_action    = isset( $_GET['action'] ) && $_GET['action'] === 'edit' ? TRUE : FALSE;
		$GET_frontend_edit = isset( $_GET['resume_id'] ) || isset( $_GET['job_id'] ) ? TRUE : FALSE;

		if( $admin_capability && current_user_can( $admin_capability ) && isset( $_GET['post'] ) && $is_edit_action && ! $GET_frontend_edit ) {
			return TRUE;
		}

		// Check if user is the post author (meaning it's their listing)
		if( in_array( $post_type, array( 'job_listing', 'resume' ), false ) && $user_id && $post_id && get_post_field( 'post_author', $post_id ) == $user_id ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check if placeholder output is only shortcode for form
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function is_ph_form_only( $type ){

		$output = get_option( "job_manager_{$this->type->slug}_visibility_require_package_{$type}_ph" );

		if( $output === "[{$this->type->slug}_visibility_packages]" || $output === "[{$type}_{$this->type->slug}_packages]" ){
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get Placeholder Template/HTML Value
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 * @param $default
	 *
	 * @return string
	 */
	public function get_placeholder( $type, $default ){

		$this->start_ph();

		$output = get_option( "job_manager_{$this->type->slug}_visibility_require_package_{$type}_ph", $default );
		$output = $this->check_link_shortcode( $output, $type );
		$output = apply_filters( "job_manager_{$this->type->slug}_visibility_require_package_{$type}_ph_output", $output );

		$output = do_shortcode( $output );

		if( apply_filters( "job_manager_packages_{$this->type->slug}_get_placeholder_{$type}_output_wpautop", TRUE ) ){
			$output = $this->autop( $output, false );
		}

		$this->end_ph();

		return $output;
	}

	/**
	 * Output Placeholder Template/HTML
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 * @param $default
	 */
	public function output_placeholder( $type, $default ){

		// If redirect enabled, no need to output anything
		if( $this->should_redirect( $type ) ){
			return;
		}

		echo $this->get_placeholder( $type, $default );
	}

	/**
	 * Check User Capabilities
	 *
	 * This method checks if setting is enabled, and if so, if the user has a user package with permissions
	 * to view the listing (either unlimited, or a limit package with current post already approved).
	 *
	 * @since 1.0.0
	 *
	 * @param string $what  What capability to check (view, browse, view_name, apply, contact, etc)
	 *
	 * @return bool
	 */
	public function user_can( $what ){

		$user_can = false;
		$req_check = "require_{$what}_package";
		$package_required = $this->$req_check();
		$has_user_perms = $this->type->packages->user->can( $what );
		$is_admin_or_author = $this->is_admin_or_author();

		if( ! $package_required || $has_user_perms || $is_admin_or_author ){
			$user_can = TRUE;
		}

		return apply_filters( "job_manager_packages_{$this->type->slug}_user_can_{$what}", $user_can, $what, $package_required, $has_user_perms, $is_admin_or_author );
	}

	/**
	 * Filter WP Job Manager Templates
	 *
	 * This is just a placeholder, and should be overridden in extending class
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return mixed
	 */
	public function filter_template( $template, $template_name, $template_path ){

		return $template;
	}

	/**
	 * Filter core WordPress Templates
	 *
	 * This is just a placeholder, and should be overridden in extending class
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $template
	 *
	 * @return mixed
	 */
	public function template_include( $template ){
		return $template;
	}

	/**
	 * Locate Template File
	 *
	 * This method uses the core WP Job Manager locate_job_manager_template() function with a custom
	 * default path, to allow users to override template files exactly like template overrides in
	 * core WP Job Manager.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $template
	 * @param string $template_path
	 *
	 * @return string
	 */
	public function locate_template( $template, $template_path = 'jm_packages' ){

		// Look for placeholder template first
		$the_template = locate_job_manager_packages_template( "ph-{$template}.php", $template_path );

		if( empty( $the_template ) ){
			$the_template = locate_job_manager_packages_template( "{$template}.php", $template_path );
		}

		return $the_template;
	}

	/**
	 * Check Placeholder Output Links
	 *
	 * This method checks the passed output HTML (for use as placeholder), to make sure that the core WordPress
	 * link feature from the TinyMCE editor didn't automatically add http:// or https:// before the shortcode,
	 * and if it does, it's removed, and the option is updated with the new values.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $output
	 * @param $type
	 *
	 * @return mixed
	 */
	public function check_link_shortcode( $output, $type ){

		$slug = $this->type->slug;

		// Check for http:// in link before shortcode for URL output
		if( strpos( $output, "href=\"http://[{$type}_{$slug}_packages_url]" ) !== FALSE ){
			$output = str_replace( "href=\"http://[{$type}_{$slug}_packages_url]", "href=\"[{$type}_{$slug}_packages_url]", $output );
			update_option( "job_manager_{$slug}_visibility_require_package_{$type}_ph", $output );
		}

		// Check for http:// in link before shortcode for URL output
		if( strpos( $output, "href=\"https://[{$type}_{$slug}_packages_url]" ) !== FALSE ){
			$output = str_replace( "href=\"https://[{$type}_{$slug}_packages_url]", "href=\"[{$type}_{$slug}_packages_url]", $output );
			update_option( "job_manager_{$slug}_visibility_require_package_{$type}_ph", $output );
		}

		return $output;
	}

	/**
	 * Hooks Placeholder
	 *
	 * Extending method should override this method to execute specific hooks on construct
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function hooks(){ }

	/**
	 * Start output of placeholder
	 *
	 * Called before start of placeholder output, to set placeholder
	 * output value in form as true.
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function start_ph(){
		$this->type->form->setPlaceholder( true );
	}

	/**
	 * End output of placeholder
	 *
	 * Call this method after placeholder output is complete, to set
	 * form placeholder value back to false.
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function end_ph(){
		$this->type->form->setPlaceholder( FALSE );
	}

	/**
	 * Return False
	 *
	 * Anonymous function capability (for those idiots who still use PHP 5.2)
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function return_false(){

		return FALSE;
	}

	/**
	 * Replaces double line-breaks with paragraph elements.
	 *
	 * A group of regex replaces used to identify text formatted with newlines and
	 * replace double line-breaks with HTML paragraph tags. The remaining line-breaks
	 * after conversion become <<br />> tags, unless $br is set to '0' or 'false'.
	 *
	 * @since 0.71
	 *
	 * @param string $pee The text which has to be formatted.
	 * @param bool   $br  Optional. If set, this will convert all remaining line-breaks
	 *                    after paragraphing. Default true.
	 *
	 * @return string Text which has been converted into correct paragraph tags.
	 */
	public function autop( $pee, $br = TRUE ){

		$pre_tags = array();

		if( trim( $pee ) === '' )
			return '';

		// Just to make things a little easier, pad the end.
		//$pee = $pee . "\n";

		/*
		 * Pre tags shouldn't be touched by autop.
		 * Replace pre tags with placeholders and bring them back after autop.
		 */
		if( strpos( $pee, '<pre' ) !== FALSE ){
			$pee_parts = explode( '</pre>', $pee );
			$last_pee  = array_pop( $pee_parts );
			$pee       = '';
			$i         = 0;

			foreach( $pee_parts as $pee_part ) {
				$start = strpos( $pee_part, '<pre' );

				// Malformed html?
				if( $start === FALSE ){
					$pee .= $pee_part;
					continue;
				}

				$name            = "<pre wp-pre-tag-$i></pre>";
				$pre_tags[$name] = substr( $pee_part, $start ) . '</pre>';

				$pee .= substr( $pee_part, 0, $start ) . $name;
				$i ++;
			}

			$pee .= $last_pee;
		}
		// Change multiple <br>s into two line breaks, which will turn into paragraphs.
		$pee = preg_replace( '|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee );

		$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

		// Add a double line break above block-level opening tags.
		$pee = preg_replace( '!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee );

		// Add a double line break below block-level closing tags.
		$pee = preg_replace( '!(</' . $allblocks . '>)!', "$1\n\n", $pee );

		// Standardize newline characters to "\n".
		$pee = str_replace( array( "\r\n", "\r" ), "\n", $pee );

		// Find newlines in all elements and add placeholders.
		$pee = wp_replace_in_html_tags( $pee, array( "\n" => " <!-- wpnl --> " ) );

		// Collapse line breaks before and after <option> elements so they don't get autop'd.
		if( strpos( $pee, '<option' ) !== FALSE ){
			$pee = preg_replace( '|\s*<option|', '<option', $pee );
			$pee = preg_replace( '|</option>\s*|', '</option>', $pee );
		}

		/*
		 * Collapse line breaks inside <object> elements, before <param> and <embed> elements
		 * so they don't get autop'd.
		 */
		if( strpos( $pee, '</object>' ) !== FALSE ){
			$pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
			$pee = preg_replace( '|\s*</object>|', '</object>', $pee );
			$pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
		}

		/*
		 * Collapse line breaks inside <audio> and <video> elements,
		 * before and after <source> and <track> elements.
		 */
		if( strpos( $pee, '<source' ) !== FALSE || strpos( $pee, '<track' ) !== FALSE ){
			$pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
			$pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
			$pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
		}

		/*
		 * Collapse line breaks inside <form> elements,
		 * before and after <input> elements.
		 */
		if( strpos( $pee, '<input' ) !== FALSE ){
			$pee = preg_replace( '%([<\[](?:form)[^>\]]*[>\]])\s*%', '$1', $pee );
			$pee = preg_replace( '%\s*([<\[]/(?:form)[>\]])%', '$1', $pee );
			$pee = preg_replace( '%\s*(<(?:input)[^>]*>)\s*%', '$1', $pee );
		}

		// Remove more than two contiguous line breaks.
		$pee = preg_replace( "/\n\n+/", "\n\n", $pee );

		// Split up the contents into an array of strings, separated by double line breaks.
		$pees = preg_split( '/\n\s*\n/', $pee, - 1, PREG_SPLIT_NO_EMPTY );

		// Reset $pee prior to rebuilding.
		$pee = '';

		// Rebuild the content as a string, wrapping every bit with a <p>.
		foreach( $pees as $tinkle ) {
			$pee .= '<p>' . trim( $tinkle, "\n" ) . "</p>\n";
		}

		// Under certain strange conditions it could create a P of entirely whitespace.
		$pee = preg_replace( '|<p>\s*</p>|', '', $pee );

		// Add a closing <p> inside <div>, <address>, or <form> tag if missing.
		$pee = preg_replace( '!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee );

		// If an opening or closing block element tag is wrapped in a <p>, unwrap it.
		$pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee );

		// In some cases <li> may get wrapped in <p>, fix them.
		$pee = preg_replace( "|<p>(<li.+?)</p>|", "$1", $pee );

		// If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
		$pee = preg_replace( '|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee );
		$pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );

		// If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
		$pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee );

		// If an opening or closing block element tag is followed by a closing <p> tag, remove it.
		$pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee );

		// Optionally insert line breaks.
		if( $br ){
			// Replace newlines that shouldn't be touched with a placeholder.
			$pee = preg_replace_callback( '/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee );

			// Normalize <br>
			$pee = str_replace( array( '<br>', '<br/>' ), '<br />', $pee );

			// Replace any new line characters that aren't preceded by a <br /> with a <br />.
			$pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee );

			// Replace newline placeholders with newlines.
			$pee = str_replace( '<WPPreserveNewline />', "\n", $pee );
		}

		// If a <br /> tag is after an opening or closing block tag, remove it.
		$pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee );

		// If a <br /> tag is before a subset of opening or closing block tags, remove it.
		$pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );
		$pee = preg_replace( "|\n</p>$|", '</p>', $pee );

		// Replace placeholder <pre> tags with their original content.
		if( ! empty( $pre_tags ) )
			$pee = str_replace( array_keys( $pre_tags ), array_values( $pre_tags ), $pee );

		// Restore newlines in all elements.
		if( FALSE !== strpos( $pee, '<!-- wpnl -->' ) ){
			$pee = str_replace( array( ' <!-- wpnl --> ', '<!-- wpnl -->' ), "\n", $pee );
		}

		return $pee;
	}
}
