<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Permalinks {

	/**
	 * WP_Job_Manager_Visibility_Permalinks constructor.
	 *
	 */
	public function __construct() {

		add_action( 'job_manager_save_job_listing', array( 'WP_Job_Manager_Visibility_Permalinks', 'update_post_permalink' ), 30, 2 );
		add_action( 'resume_manager_save_resume', array( 'WP_Job_Manager_Visibility_Permalinks', 'update_post_permalink' ), 30, 2 );

		add_filter( 'submit_job_form_save_job_data', array( 'WP_Job_Manager_Visibility_Permalinks', 'frontend_set_permalink' ), 9999999, 5 );
		add_filter( 'submit_resume_form_save_resume_data', array( 'WP_Job_Manager_Visibility_Permalinks', 'frontend_set_permalink' ), 9999999, 5 );
	}

	/**
	 * Update permalink when listing updated/saved from admin
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $post_id
	 * @param $post
	 */
	public static function update_post_permalink( $post_id, $post ){

		// If post type is resume, set that as type, otherwise should be job_listing so we set type to job
		$type = get_post_type( $post_id ) === 'resume' ? 'resume' : 'job';
		$enable = get_option( "jmv_{$type}_enable_custom_permalink", false );

		if( empty( $enable ) ){
			return;
		}

		// We have to remove our actions to prevent infinite loop
		remove_action( 'job_manager_save_job_listing', array(get_called_class(), 'update_post_permalink'), 30 );
		remove_action( 'resume_manager_save_resume', array(get_called_class(), 'update_post_permalink'), 30 );

		$permalink_type = $post->post_type === 'job_listing' ? 'job' : $post->post_type;

		$args = array(
			'ID' => $post_id,
			'post_name' => self::get_permalink_structure( $permalink_type, $post )
		);

		wp_update_post( $args );

		// Then add them back when we're done
		add_action( 'job_manager_save_job_listing', array(get_called_class(), 'update_post_permalink'), 30, 2 );
		add_action( 'resume_manager_save_resume', array(get_called_class(), 'update_post_permalink'), 30, 2 );

	}

	/**
	 * Update existing listings with permalink structure
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param string $post_type
	 *
	 * @return int|\WP_Query
	 */
	public static function update_existing( $post_type = 'job_listing' ){

		$args = array(
			'post_type'      => $post_type,
			'pagination'     => FALSE,
			'posts_per_page' => -1,
			'post_status'    => array( 'pending_payment', 'pending', 'hidden', 'expired', 'publish')
		);

		$listings = new WP_Query( $args );

		if( is_wp_error( $listings ) ) return $listings;

		$total = count( $listings->posts );

		foreach( $listings->posts as $listing ){

			self::update_post_permalink( $listing->ID, $listing );

		}

		return $total;
	}

	/**
	 * Return formatted permalink structure
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param        $type
	 * @param string $post
	 *
	 * @return mixed|string|void
	 */
	public static function get_permalink_structure( $type, $post = OBJECT ) {

		$structure = get_option( "jmv_{$type}_custom_permalink" );

		// { matches the opening brace, then \K tells the engine to abandon what was matched so far
		// [^}] negated character class represents one character that is not a closing brace
		// * quantifier matches that zero or more times
		// The lookahead (?=}) asserts that what follows is a closing brace
		$regex = '/{\K[^}]*(?=})/m';
		preg_match_all( $regex, $structure, $meta_keys );

		// If no matches were found for meta keys to use, generate a random string to use
		if ( ! isset( $meta_keys ) || empty( $meta_keys ) || empty( $meta_keys[ 0 ] ) ) {

			$structure = wp_generate_password( apply_filters( "job_manager_visibility_{$type}_permalink_empty_structure_chars", 40 ) );

		} else {

			$core = WP_Job_Manager_Visibility::get_instance();
			$all_fields = $core->integration()->get_all_fields();

			foreach( (array) $meta_keys[ 0 ] as $meta_key ) {

				$meta_key = strtolower( $meta_key );

				// Set value inside curly braces to replace, must be done before any handling of meta key
				$to_replace = '{' . $meta_key . '}';

				// If meta key has prepended exclamation point, we need to remove that before processing
				if( strpos( $meta_key, '!' ) !== FALSE ){
					$meta_key = str_replace( '!', '', $meta_key );
				}

				$field_value = self::get_field_value( $meta_key, $all_fields, $post );

				// Random characters
				if ( $meta_key === 'random' ) {

					$slug_part = wp_generate_password( apply_filters( "job_manager_visibility_{$type}_permalink_random_chars", 10, $structure ) );

				} elseif ( empty( $field_value ) || is_array( $field_value ) ) {

					/**
					 * Set slug part for field with no value, or that is an array of values
					 *
					 * If curly brace has exclamation point (!) before meta key, that means use empty string instead of random characters
					 */
					$slug_part = strpos( $to_replace, '{!' ) === FALSE ? wp_generate_password( apply_filters( "job_manager_visibility_admin_{$type}_permalink_empty_value_chars", 6, $meta_key, $structure ) ) : '';

				} else {

					// Only use the first word from value.  This is useful for instances like candidate_name where we only want the first name
					if ( in_array( $meta_key, apply_filters( 'job_manager_visibility_permalink_first_word', array('candidate_name') ) ) ) {
						// Split into an array, and get the first value
						$field_value = current( explode( ' ', $field_value ) );
					}

					// We only want the slug part to be a max of 20 characters at most
					$slug_part = substr( $field_value, 0, apply_filters( "job_manager_visibility_admin_{$type}_permalink_max_slug_chars", 20, $meta_key, $field_value, $structure ) );
				}

				// Filter the slug part
				$slug_part = apply_filters( "job_manager_visibility_admin_{$type}_permalink_slug_part", $slug_part, $meta_key, $post, $structure );

				// Replace {META_KEY} in structure with formatted slug part
				$structure = str_replace( $to_replace, sanitize_title( $slug_part ), $structure );

			}

		}

		$structure = sanitize_title( apply_filters( "job_manager_visibility_admin_{$type}_permalink_structure", $structure, $post, $meta_key, $field_value ) );

		return $structure;
	}

	/**
	 * Return meta key field value
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $meta_key
	 * @param $all_fields
	 * @param $post
	 *
	 * @return mixed|string
	 */
	public static function get_field_value( $meta_key, $all_fields, $post ){

		// Meta is saved to listing (and available from post object) with prepended underscore
		$is_hidden = apply_filters( "job_manager_visibility_permalink_get_field_value_{$meta_key}_is_hidden_meta", true, $all_fields, $post );
		// If is hidden meta key (should be by default), add prepended underscore, otherwise use passed meta key
		$_meta_key = $is_hidden ? "_{$meta_key}" : $meta_key;

		// Match meta keys to post object
		switch ( $meta_key ) {
			case 'job_title':
			case 'candidate_name':
				$_meta_key = 'post_title';
				break;
			case 'job_description':
			case 'resume_content':
				$_meta_key = 'post_content';
				break;
		}

		// Set field value from post object
		$field_value = apply_filters( "job_manager_visibility_permalink_get_post_field_value_{$meta_key}", $post->$_meta_key, $all_fields, $post );

		// Value found through post object
		if( ! empty( $field_value ) ){
			return $field_value;
		}

		$field_config = array();

		// Loops through field groups checking if meta key exists
		foreach( (array) $all_fields as $field_group => $fields ) {
			if ( array_key_exists( $meta_key, $all_fields[ $field_group ] ) ) {
				$field_config = $fields[ $meta_key ];
				// Break out of for loop once meta key is found
				break;
			}
		}

		// job_tags can also be text field which would not have taxonomy arg set
		if( $meta_key === 'job_tags' && empty( $field_config['taxonomy'] ) ){
			$field_config['taxonomy'] = 'job_listing_tag';
		}

		// Handle taxonomy items
		if ( ! empty( $field_config[ 'taxonomy' ] ) ) {

			$field_terms = get_the_terms( $post->ID, $field_config[ 'taxonomy' ] );

			if ( $field_terms && ! is_wp_error( $field_terms ) ) {

				$max_slugs = apply_filters( 'job_manager_visibility_admin_permalink_taxonomy_max_slugs', 2, $meta_key, $field_config );
				$added_slugs = 0;

				foreach( $field_terms as $field_term ) {

					if( $added_slugs >= $max_slugs ){
						break;
					}

					$field_value = $field_value . '-' . $field_term->slug;
					$added_slugs++;
				}
			}
		}

		$field_value = apply_filters( "job_manager_visibility_permalink_get_field_value_{$meta_key}", $field_value, $all_fields, $post );

		return $field_value;
	}

	/**
	 * Return formatted value for frontend permalink handling
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $values
	 * @param $meta_key
	 * @param $all_fields
	 *
	 * @return bool|string
	 */
	public static function frontend_get_value( $values, $meta_key, $all_fields ){

		if( $meta_key === 'random' || empty( $values[ $meta_key ] ) ) return false;

		$field_config = array();
		$field_value = $values[ $meta_key ];

		// Non array value handling
		if( ! is_array( $field_value ) ){

			// job_tags can be standard text field which is associated with a taxonomy
			if ( $meta_key === 'job_tags' ) {

				$added_slugs = 0;
				$max_slugs   = apply_filters( 'job_manager_visibility_permalink_job_tags_max_slugs', 2 );

				$job_tags      = array_filter( explode( ',', $field_value ) );
				$job_tag_slugs = '';

				foreach( (array) $job_tags as $job_tag ) {

					if ( $added_slugs >= $max_slugs ) {
						return $job_tag_slugs;
					}

					// Replace spaces with dash, and set to lowercase
					$job_tag = strtolower( str_replace( ' ', '-', $job_tag ) );

					$job_tag_slugs = $job_tag_slugs . ' ' . esc_attr( $job_tag );
					$added_slugs ++;
				}

				return apply_filters( 'job_manager_visibility_permalink_frontend_get_field_value_job_tags', $job_tag_slugs, $job_tags, $field_value, $all_fields, $values );

			} else {

				return apply_filters( "job_manager_visibility_permalink_frontend_get_field_value_{$meta_key}", $field_value, $all_fields, $values, $field_value );

			}

		}

		// Loops through field groups checking if meta key exists
		foreach( (array) $all_fields as $field_group => $fields ) {
			if ( array_key_exists( $meta_key, $all_fields[ $field_group ] ) ) {
				$field_config = $fields[ $meta_key ];
				// Break out of for loop once meta key is found
				break;
			}
		}

		// Handle taxonomy items
		if ( ! empty( $field_config[ 'taxonomy' ] ) ) {

			$added_slugs = 0;
			$max_slugs = apply_filters( 'job_manager_visibility_permalink_taxonomy_max_slugs', 2, $meta_key, $field_config );
			$tax_slugs = '';

			foreach( (array) $field_value as $field_term_id ) {

				if ( $added_slugs >= $max_slugs ) {
					break;
				}

				$field_term = get_term_by( 'ID', $field_term_id, $field_config['taxonomy'] );

				if( ! is_wp_error( $field_term ) ){
					$tax_slugs = $tax_slugs . ' ' . $field_term->slug;
				}

				$added_slugs ++;
			}

			return apply_filters( "job_manager_visibility_permalink_frontend_get_field_value_taxonomy_{$meta_key}", $tax_slugs, $field_value, $all_fields, $values );

		}

		// All else fails, means it's probably an array of values, that didn't match any of our handling above, as such, return empty value
		return apply_filters( "job_manager_visibility_permalink_frontend_get_field_value_{$meta_key}", '', $all_fields, $values, $field_value );

	}

	/**
	 * Customize Permalink Structure
	 *
	 * This method is called to format the slug to use for the post permalink based on
	 * configured structure in settings.
	 *
	 * @since 1.4.1
	 *
	 * @param $data             Array of data to pass to wp_insert_post or wp_update_post (post_title, etc)
	 * @param $post_title       Same value as key post_title in $data array
	 * @param $post_content     Same value as key post_content in $data array
	 * @param $status           Normally would be 'preview' by default, unless it's an update to the listing
	 * @param $values           Array of values from the submit listing page
	 *
	 * @return \Array
	 */
	public static function frontend_set_permalink( $data, $post_title, $post_content, $status, $values ) {

		if( ! is_array( $data ) || ! array_key_exists( 'post_type', $data ) ){
			return $data;
		}

		// If post type is resume, set that as type, otherwise should be job_listing so we set type to job
		$type = $data['post_type'] === 'resume' ? 'resume' : 'job';

		$enable    = get_option( "jmv_{$type}_enable_custom_permalink" );
		$structure = get_option( "jmv_{$type}_custom_permalink" );
		if ( empty( $enable ) ) {
			return $data;
		}

		// { matches the opening brace, then \K tells the engine to abandon what was matched so far
		// [^}] negated character class represents one character that is not a closing brace
		// * quantifier matches that zero or more times
		// The lookahead (?=}) asserts that what follows is a closing brace
		$regex = '/{\K[^}]*(?=})/m';
		preg_match_all( $regex, $structure, $meta_keys );

		// If no matches were found for meta keys to use, generate a random string to use
		if ( ! isset( $meta_keys ) || empty( $meta_keys ) || empty( $meta_keys[ 0 ] ) ) {
			$structure = wp_generate_password( apply_filters( "job_manager_visibility_{$type}_permalink_empty_structure_chars", 40 ) );
		} else {

			$vals = $type === 'resume' ? $values[ 'resume_fields' ] : array_merge( $values[ 'job' ], $values[ 'company' ] );

			$core       = WP_Job_Manager_Visibility::get_instance();
			$all_fields = $core->integration()->get_all_fields();

			foreach( (array) $meta_keys[ 0 ] as $meta_key ) {

				$meta_key = strtolower( $meta_key );

				// Set value inside curly braces to replace, must be done before any handling of meta key
				$to_replace = '{' . $meta_key . '}';

				// If meta key has prepended exclamation point, we need to remove that before processing
				if ( strpos( $meta_key, '!' ) !== FALSE ) {
					$meta_key = str_replace( '!', '', $meta_key );
				}

				$field_value = self::frontend_get_value( $vals, $meta_key, $all_fields );

				if ( $meta_key === 'random' ) {

					$slug_part = wp_generate_password( apply_filters( "job_manager_visibility_{$type}_permalink_random_chars", 10 ) );

				} elseif ( empty( $field_value ) ) {

					/**
					 * Set slug part for field with no value, or that is an array of values
					 *
					 * If curly brace has exclamation point (!) before meta key, that means use empty string instead of random characters
					 */
					$slug_part = strpos( $to_replace, '{!' ) === FALSE ? wp_generate_password( apply_filters( "job_manager_visibility_{$type}_permalink_empty_value_chars", 6, $meta_key, $structure ) ) : '';

				} else {

					// Only use the first word from value.  This is useful for instances like candidate_name where we only want the first name
					if ( in_array( $meta_key, apply_filters( 'job_manager_visibility_permalink_first_word', array('candidate_name') ) ) ) {
						// Split into an array, and get the first value
						$field_value = current( explode( ' ', $field_value ) );
					}

					// We only want the slug part to be a max of 20 characters at most
					$slug_part = substr( $field_value, 0, apply_filters( "job_manager_visibility_{$type}_permalink_max_slug_chars", 20, $meta_key, $field_value, $structure ) );
				}

				// Filter the slug part
				$slug_part = apply_filters( "job_manager_visibility_{$type}_permalink_slug_part", $slug_part, $meta_key, $vals, $structure );

				// Replace {META_KEY} in structure with formatted slug part
				$structure = str_replace( $to_replace, sanitize_title( $slug_part ), $structure );

			}

		}

		// Set post_name (used for slug) as our generated structure
		$data[ 'post_name' ] = sanitize_title( apply_filters( "job_manager_visibility_{$type}_permalink_structure", $structure, $values ) );

		return $data;
	}
}