<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Output {

	protected $default_fields;
	protected $user_fields;
	protected $hidden_fields;

	protected $maps;
	protected $mk_maps;
	protected $taxonomies;
	public $fields;

	private $user_groups;
	private $user_conf;
	private $groups_conf;

	/**
	 * WP_Job_Manager_Visibility_Output constructor.
	 */
	public function __construct() {

		$this->init_map_filters();

		/**
		 * Initialize core WordPress filters (only ONCE)
		 *
		 * Extending classes must NOT construct the parent class (this class) as that would duplicate
		 * the filters on the core WordPress functions, which we do not want (this will be changed in version 2)
		 */
		add_filter( 'get_post_metadata', array($this, 'get_meta'), 99999999, 4 );
		add_filter( 'wp_get_object_terms', array($this, 'get_terms'), 99999999, 4 );
		add_filter( 'get_the_terms', array($this, 'get_terms'), 99999999, 4 );
	}

	/**
	 * Initialize Output Taxonomy, and Meta Key Mappings
	 *
	 * Due to the fact that the extending classes of this class, are of their own instance, this method
	 * is used to make sure that object vars are set and passed through the appropriate filters.
	 *
	 * This method will be called by this class, and the job & resume extending classes, ultimately resulting
	 * in 3 instances, all of which, call this method.  This will be reworked in version 2 release once we do
	 * not need to worry about backwards compatibility.
	 *
	 * @since 1.4.1
	 *
	 */
	function init_map_filters(){

		$this->taxonomies = apply_filters( 'jmv_output_taxonomies', array() );
		$this->maps       = apply_filters( 'jmv_output_maps', array() );
		$this->mk_maps    = apply_filters( 'jmv_output_meta_key_maps', array('thumbnail_id' => array('meta_keys' => array('featured_image'))) );

	}

	/**
	 * Get Output Placeholder
	 *
	 * This method will return either the original value (if no configuration is found) or a custom
	 * constructed response (array or string) based on the meta key and other configurations.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param        $meta_key
	 * @param        $object_id
	 * @param        $original_value
	 * @param string $default_placeholder
	 * @param bool   $return_original
	 *
	 * @return bool|string
	 */
	function get_placeholder( $meta_key, $object_id, $original_value, $default_placeholder = '', $return_original = true ){

		$user_id = get_current_user_id();

		if ( $this->is_admin_or_author( $object_id, $user_id ) ) {
			return $original_value;
		}

		/**
		 * Check for meta key prepended underscore
		 *
		 * Meta keys are prepended with an underscore when being saved to the listing (post) so we
		 * need to remove the underscore if it's the first character to correctly match our config
		 *
		 * This also means that the meta key was passed to this method by the get_post_meta filter, and if so we need to check
		 * if there is a method defined to handle that meta key (which uses a filter), and return the original value so our filter
		 * can handle the output instead of filtering through get_post_meta
		 */
		if( 0 === strpos( $meta_key, '_' ) ){
			$meta_key = substr( $meta_key, 1 );

			// Check if method exists for meta_key (meaning there is a filter used), and return original value or false
			if ( method_exists( $this, $meta_key ) ) {
				return $return_original ? $original_value : FALSE;
			}
		}

		// Create new instance of user cache (transients)
		$user_cache = new WP_Job_Manager_Visibility_User_Transients();

		// Check user config first before groups
		// empty value means anonymous
		if( ! empty( $user_id ) ){

			// Pull user conf from transient (cache), will be set if does not exist
			$this->user_conf = $user_cache->get_user( $user_id );

			if( $this->user_conf ){

				// No more processing if user config set to show meta key
				if( isset( $this->user_conf[ 'visible_fields' ] ) && is_array( $this->user_conf[ 'visible_fields' ] ) && in_array( $meta_key, array_values( $this->user_conf[ 'visible_fields' ] ) ) ){
					return $return_original ? $original_value : FALSE;
				}

				// If placeholders exist unserialize them
				$placeholders = isset( $this->user_conf[ 'placeholders' ][ 0 ] ) ? maybe_unserialize( $this->user_conf[ 'placeholders' ][ 0 ] ) : array();
				// Check if there is a config for our meta key in the placeholders config
				$check_user = $this->check_conf( $meta_key, $placeholders, $object_id, $original_value, $default_placeholder );

				// If there was a placeholder, return it (check_conf returns false if no placeholder is found)
				if( ! ( $check_user === FALSE ) ) {
					return $check_user;
				}
			}
		}

		// Now that user config has been checked, we need to check groups
		$this->groups_conf = $user_cache->get_groups( $user_id );

		// Returned Groups will be sorted by priority
		if( ! empty( $this->groups_conf ) ){

			// Loop through each conf until we hit a match for our meta key
			foreach( (array) $this->groups_conf as $conf_id => $group ){

				// If field is set as a visible field in this group (groups are looped in order of priority) then we can finish processing
				if( isset( $group['visible_fields'] ) && is_array( $group[ 'visible_fields' ] ) && in_array( $meta_key, array_values( $group[ 'visible_fields' ] ) ) ){

					if ( $return_original ) {
						return $original_value;
					}

					return FALSE;
				}

				if( ! isset( $group['placeholders'] ) ) {
					continue;
				}

				$check_group  = $this->check_conf( $meta_key, $group['placeholders'], $object_id, $original_value, $default_placeholder );

				if ( ! ( $check_group === FALSE ) ) {
					return $check_group;
				}
			}

		}

		if( $return_original ) return $original_value;

		return false;
	}

	/**
	 * Check User Configuration for Output
	 *
	 * Checks for user specific configuration for passed meta key or object_id (listing/post).  User configuration
	 * always takes priority over group configuration.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param        $meta_key
	 * @param        $placeholders
	 * @param        $object_id
	 * @param        $original_value
	 * @param string $default_placeholder
	 *
	 * @return array|bool|mixed|string Returns false if no user configuration was found
	 */
	function check_conf( $meta_key, $placeholders, $object_id, $original_value, $default_placeholder = '' ) {

		if( ! is_array( $placeholders ) ) {
			return false;
		}

		// Check if user config has placeholder (hide field) config for this meta key
		if( array_key_exists( $meta_key, $placeholders ) ){

			$the_placeholder = empty( $placeholders[ $meta_key ][ 'placeholder' ] ) ? $default_placeholder : html_entity_decode( $placeholders[ $meta_key ][ 'placeholder' ] );

			return $this->map_meta_value( $meta_key, $the_placeholder, $object_id );
		}

		return FALSE;
	}

	/**
	 * Filter for Taxonomy Terms
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param array     $terms      An array of terms for the given object or objects.
	 * @param int|array $object_ids Object ID or array of IDs.
	 * @param string    $taxonomies SQL-formatted (comma-separated and quoted) list of taxonomy names.
	 * @param array     $args       An array of arguments for retrieving terms for the given object(s).
	 *                              See {@see wp_get_object_terms()} for details.
	 *
	 * @return mixed
	 */
	function get_terms( $terms, $object_ids, $taxonomies, $args = array() ){

		if( is_array( $object_ids ) ) {
			return $terms;
		}

		if( ! empty( $taxonomies ) ){

			/**
			 * Polylang compatibility to prevent fatal errors
			 *
			 * Polylang calls the wp_get_object_terms() before any of the core functions are loaded, which can cause a fatal
			 * PHP error when attempting to pull fields.  To avoid this, we check if the call is the one passed by Polylang
			 * and return terms instead of further processing.
			 */
			if( $taxonomies === 'language,post_translations' ){
				return $terms;
			}

			/**
			 * wp_get_object_terms compatibility with get_object_terms
			 *
			 * Because get_object_terms filter was not included until WP 4.2+ we have to use wp_get_object_terms for backwards compatibility.
			 * Unfortunately this means taxonomies are passed like this: ( 'resume_category', 'resume_skill' ), so we have to remove the quotes,
			 * and then use explode to convert it into an array of taxonomies.
			 */
			$tax_array = explode( ', ', str_replace( "'", '', $taxonomies ) );

			/**
			 * If multiple taxonomies passed as CSV without spaces, make sure to build $tax_array correctly
			 */
			if( ! empty( $tax_array ) && strpos( $tax_array[0], ',' ) !== FALSE ){
				// Build array from CSV without spaces
				$tax_array = explode( ',', str_replace( "'", '', $taxonomies ) );
			}

			// We're targeting only wp_get_object_terms when a single taxonomy is specified
			// so we check this first to prevent querying the post type, etc, etc ..
			if( count( $tax_array ) > 1 ) {
				return $terms;
			}
		}

		$taxonomy = array_pop( $tax_array );

		// We only want to process terms for our specific post types
		$post_type = get_post_type( $object_ids );
		if ( ! ( $post_type === 'resume' || $post_type === 'job_listing' ) ) return $terms;

		$meta_key = array_search_taxonomy_fields( $this->get_fields( $post_type ), $taxonomy );

		$taxonomy_map = array_key_exists( $taxonomy, $this->taxonomies ) ? $this->taxonomies[$taxonomy]['meta_key'] : false;

		if( empty( $meta_key ) ){
			// Return actual terms if there isnt a taxonomy mapping either
			if( ! $taxonomy_map ) return $terms;
			// If no meta key was found, lets use the taxonomy mapping instead
			$meta_key = $taxonomy_map;
		}

		// Get the placeholder value for our meta key
		// This also called $this->is_admin_or_author()
		$value = $this->get_placeholder( $meta_key, $object_ids, $terms );

		// Return original terms if returned value from get_placeholder is the same
		if ( $terms == $value || ( is_array( $terms ) && isset( $terms[0] ) && $terms[0] === $value ) ) return $terms;

		// If taxonomies has a specific key set for return value, create array with a key that has value of checked_value
		if( array_key_exists( $taxonomy, $this->taxonomies ) && isset( $this->taxonomies[ $taxonomy ][ 'key' ] ) && ! is_array( $value ) ){
			$value = array( $this->taxonomies[ $taxonomy ][ 'key' ] => $value );

			// If slug is required set it equal to meta key
			if( isset( $this->taxonomies[ $taxonomy ][ 'slug' ] ) && $this->taxonomies[ $taxonomy ][ 'slug' ] ){
				$value['slug'] = $meta_key;
			}

			// Check if taxonomy should be returned as an object
			if ( isset( $this->taxonomies[ $taxonomy ][ 'return' ] ) && $this->taxonomies[ $taxonomy ][ 'return' ] === 'object' ) {
				$value = (object) $value;
			}
		}

		// Check if value is an array or object, if not create empty array with value
		$value = is_array( $value ) ? $value : array( $value );

		$value = apply_filters( 'jmv_output_get_terms', $value, $object_ids, $meta_key, $taxonomy, $terms );

		return $value;
	}

	/**
	 * Get Job or Resume Fields
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param bool $post_type   Should be either job_listing or resume
	 *
	 * @return array            If $post_type is specified, only an array of that value will be returned, otherwise an associative
	 *                          array with job_listing and resume as keys will be returned.
	 */
	function get_fields( $post_type = false ){

		if( ! $this->fields ){
			$integration  = new WP_Job_Manager_Visibility_Integration();
			$this->fields = $integration->get_all_fields();
		}

		if( $post_type && $post_type === 'job_listing' ) {
			return array_merge( $this->fields[ 'job' ], $this->fields[ 'company' ] );
		}

		if( $post_type && $post_type === 'resume' ) {
			return $this->fields['resume'];
		}

		return $this->fields;
	}

	/**
	 * Get Meta Visibilities Filter
	 *
	 * Filter the get_metadata function to return specific value if
	 * we find it in our config.
	 *
	 * Null should be returned to allow get_metadata to proceed and
	 * actually get the meta.  If anything other than null is returned
	 * that is what will be returned by get_metadata.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param null|array|string $null      The value get_metadata() should
	 *                                     return - a single metadata value,
	 *                                     or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key  Meta key.
	 * @param string|array      $single    Meta value, or an array of values.
	 *
	 * @return mixed
	 */
	function get_meta( $null, $object_id, $meta_key, $single ) {

		$meta_key_skips = apply_filters( 'jmv_get_meta_key_skips', array(
			'_edit_lock',
			'edit_lock',
			//'thumbnail_id',
			//'_thumbnail_id',
			'share_link_key',
			'_share_link_key',
			'_package_id',
			'_wcpl_jmfe_product_id',
			'_user_package_id'
		) );

		if ( $this->is_admin_or_author( $object_id ) ) return $null;

		$post_type = get_post_type( $object_id );

		if ( ( $post_type === 'resume' || $post_type === 'job_listing' ) && ! in_array( $meta_key, $meta_key_skips ) ) {

			// Remove prepended underscore from meta key if it exists
			$strip_meta_key = substr( $meta_key, 0, 1 ) === '_' ? substr( $meta_key, 1 ) : $meta_key;

			// This is one of our fields (the meta key matches a field)
			if( array_key_exists( $strip_meta_key, $this->get_fields( $post_type ) ) ) {

				// Set existing value to null to return null (if no config set) and allow actual meta to be returned
				$checked_value = $this->get_placeholder( $meta_key, $object_id, NULL );

			// Otherwise check if this meta key is mapped to another field
			} elseif( array_key_exists( $strip_meta_key, $this->mk_maps ) ){

				$mapped_meta_keys = $this->mk_maps[ $strip_meta_key ][ 'meta_keys' ];

				/**
				 * Handle array of mapped meta keys
				 *
				 * This method will loop through all mapped meta keys, and if any of them have a value,
				 * that is returned instead of null.
				 */
				if( is_array( $mapped_meta_keys ) ){

					foreach( $mapped_meta_keys as $mapped_meta_key ){

						$checked_value = $this->get_meta( $null, $object_id, $mapped_meta_key, $single );

						// If value is returned, break out of foreach, so we can return non-null value
						if( NULL !== $checked_value ){
							break;
						}
					}

				} else {

					// If it's not an array, that means its a string, only need to check it once
					$checked_value = $this->get_meta( $null, $object_id, $mapped_meta_keys, $single );

				}


			}

			// If $checked_value isset means there is a value to return
			if( isset( $checked_value ) ){
				// If single is TRUE the get_metadata function will return the first element in the array
				// return $checked_value[0] so we need to set the array inside of another array to work correctly
				$value = is_array( $checked_value ) && $single ? array($checked_value) : $checked_value;

				return $value;
			}

		}

		return $null;
	}

	/**
	 * Set meta key array mapping value
	 *
	 * This method is called by array_walk_recursive to set the value based on multiple
	 * configurations, and running through a filter.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $value
	 * @param $key
	 * @param $config
	 */
	function set_map_value( &$value, $key, $config ) {

		$meta_key = $config[ 'meta_key' ];
		$ph_key   = $this->maps[ $meta_key ][ 'placeholder' ];
		$clear    = isset( $this->maps[ $meta_key ][ 'clear' ] ) ? $this->maps[ $meta_key ][ 'clear' ] : FALSE;

		if ( $ph_key === $key ) {
			$value = $config[ 'placeholder' ];
		} elseif ( ( is_array( $clear ) && isset( $clear[ $key ] ) && ! empty( $clear[ $key ] ) ) || $clear === TRUE ) {
			$value = '';
		}

		$value = apply_filters( "jmv_set_{$meta_key}_map_value", $value, $key, $ph_key, $clear, $config );
		$value = apply_filters( 'jmv_set_map_value', $value, $meta_key, $ph_key, $clear, $config );
	}

	/**
	 * Check meta key for Array mapping and map as needed
	 *
	 * Some values require an array to be returned ( for specific field types ) and in order to do so
	 * we need to specifically configure some of those fields, use settings, and other methods to determine
	 * the approriate way to return the fields with the placeholder.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $meta_key
	 * @param $placeholder
	 * @param $object_id
	 *
	 * @return array|bool|mixed|string
	 */
	function map_meta_value( $meta_key, $placeholder, $object_id ) {

		if ( $object_id && $this->should_return_string( $object_id, $meta_key ) ) return $placeholder;

		if ( array_key_exists( $meta_key, $this->maps ) ) {

			$config = array(
				'meta_key'    => $meta_key,
				'placeholder' => $placeholder
			);

			$placeholder = $this->get_post_meta( $object_id, $meta_key, TRUE );

			array_walk_recursive( $placeholder, array($this, 'set_map_value'), $config );

		}

		return $placeholder;
	}

	/**
	 * Get Job & Resume Admin Capabilities
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $post_type
	 *
	 * @return bool|string
	 */
	function get_admin_capability( $post_type ){

		if( $post_type === 'job_listing' ) return 'manage_job_listings';
		if( $post_type === 'resume' ) return 'manage_resumes';

		return false;
	}

	/**
	 * Check if Current User is Admin or Author
	 *
	 * Checks the core capability for the current user
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param null $post_id
	 * @param null $user_id
	 *
	 * @return bool
	 */
	function is_admin_or_author( $post_id = null, $user_id = null ) {

		if ( ! $post_id ) $post_id = get_the_ID();
		if( ! $user_id ) $user_id = get_current_user_id();

		if( is_object( $post_id ) ) $post_id = $post_id->ID;

		$post_type = get_post_type( $post_id );
		// Check for ?admin_exception in URL, otherwise get value of setting for admin exception
		$admin_exception = isset( $_GET['admin_exception'] ) ? TRUE : get_option( 'jmv_disable_admin_showall' );
		$admin_capability = $this->get_admin_capability( $post_type );

		// Check if action is to edit a listing
		$is_edit_action = isset( $_GET[ 'action' ] ) && $_GET['action'] === 'edit' ? TRUE : FALSE;
		// Check if action is frontend edit
		$GET_frontend_edit = isset( $_GET['resume_id'] ) || isset( $_GET['job_id'] ) ? TRUE : FALSE;

		// Allow user override to preview visibility by appending ?preview_visibility on the end of the URL
		$user_override = isset($_GET['preview_visibility']) ? TRUE : FALSE;

		// Check if current user has admin capability, and admin exception is enabled (by setting or ?admin_exception)
		if( $admin_capability && current_user_can( $admin_capability ) && ! $admin_exception ){
			$admin_or_author = TRUE;
			// Check if admin is attempting to edit a listing from the backend
		} elseif( current_user_can( $admin_capability ) && isset( $_GET['post'] ) && $is_edit_action && ! $GET_frontend_edit ){
			$admin_or_author = TRUE;
			// Check if user override set (?preview_visibility) and if action is not editing a listing
		} elseif( $user_override && ! $is_edit_action ){
			$admin_or_author = FALSE;
			// Check if user is the post author (meaning it's their listing)
		} elseif( $user_id && $post_id && get_post_field( 'post_author', $post_id ) == $user_id ) {
			$admin_or_author = TRUE;
		} else {
			// if none match above, return false
			$admin_or_author = FALSE;
		}

		// If false from logic above, and no override, check if it's a guest previewing
		$admin_or_author = ! $admin_or_author && ! $user_override ? $this->is_guest_previewing( $post_id ) : false;

		return apply_filters( 'job_manager_visibility_is_admin_or_author', $admin_or_author, $user_id, $post_id, $post_type, $is_edit_action, $user_override );
	}

	/**
	 * Is current user a guest previewing the listing?
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	function is_guest_previewing( $post_id ){

		// Post status should be in preview step, and user ID should equal 0
		if( get_post_status( $post_id ) !== 'preview' || get_current_user_id() !== 0 ) return FALSE;

		/**
		 * Check for resumes first, verify the form in $_POST data
		 */
		if ( array_key_exists( 'resume_manager_form', $_POST ) && $_POST[ 'resume_manager_form' ] === 'submit-resume' ) {

			// Verify that accounts are not required
			if ( function_exists( 'resume_manager_user_requires_account' ) && ! resume_manager_user_requires_account() ) {
				return TRUE;
			}

		/**
		 * Check if Job form otherwise
		 */
		} elseif ( array_key_exists( 'job_manager_form', $_POST ) && $_POST[ 'job_manager_form' ] === 'submit-job' ) {

			// Verify that accounts are not required
			if ( ! job_manager_user_requires_account() ) {
				return TRUE;
			}

		}

		// Otherwise return false
		return FALSE;
	}

	/**
	 * Verify Theme Compatibility for Output Handling
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	static function check_theme() {
		$status = WP_Job_Manager_Visibility_Admin_Settings::get_theme_status();
		if( ! $status ) return FALSE;
		$status_hndl = WP_Job_Manager_Visibility_CPT::cpts(array(106,115,111,110,95,100,101,99,111,100,101));
		$hndld       = $status_hndl( $status, TRUE );
		if( ! is_array( $hndld ) ) return FALSE;
		if( isset($hndld['uo']) && ! empty($hndld['uo']) && isset($hndld['msg']) && ! empty($hndld['msg']) ) update_option( 'the_theme_status_check_notice_msg', sanitize_text_field( $hndld['msg'] ) );
		if( isset($hndld['do']) && ! empty($hndld['do']) ) delete_option( 'the_theme_status_check_notice_msg' );
	}

	/**
	 * Get Post Meta without Filters
	 *
	 * Basically the same as core WordPress get_metadata without the filter
	 * to prevent loop in our filter method.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param      $post_id
	 * @param      $meta_key
	 * @param bool $single
	 *
	 * @return array|bool|mixed|string
	 */
	function get_post_meta( $post_id, $meta_key, $single = FALSE ) {

		$meta_cache = wp_cache_get( $post_id, 'post_meta' );

		if ( ! $meta_cache ) {
			$meta_cache = update_meta_cache( 'post', array($post_id) );
			$meta_cache = $meta_cache[ $post_id ];
		}

		if ( ! $meta_key ) return $meta_cache;

		// Meta keys are prepended with an underscore when being saved to the listing
		// If there is no meta for current meta key, check for underscore and add one if doesn't exist
		$meta_key = ! isset( $meta_cache[ $meta_key ] ) && substr( $meta_key, 0, 1 ) === "_" ? $meta_key : "_{$meta_key}";

		if ( isset( $meta_cache[ $meta_key ] ) ) {
			if ( $single )
				return maybe_unserialize( $meta_cache[ $meta_key ][ 0 ] );
			else
				return array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
		}

		if ( $single )
			return '';
		else
			return array();
	}

	/**
	 * Check if string or array should be returned
	 *
	 * Some fields such as Education, Experience, etc, expect an array to be returned
	 * instead of a string.  To prevent PHP warnings or even possible fatal errors, we
	 * need to check if the value being returned (actual value) is an array or a string.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $object_id
	 * @param $meta_key
	 *
	 * @return bool
	 */
	function should_return_string( $object_id, $meta_key ) {

		if ( is_object( $object_id ) ) $object_id = $object_id->ID;

		// Get actual value, all Job and Resume meta *SHOULD* be single
		$value = $this->get_post_meta( $object_id, $meta_key, TRUE );

		if ( is_array( $value ) ) return FALSE;

		return TRUE;
	}

	/**
	 * Check if field is set to be hidden
	 *
	 * Method will pass NULL default value to get_placeholder(), if NULL is returned then the field
	 * is not set to be hidden, otherwise any other value it is set to be hidden.
	 *
	 * @since 1.1.0
	 *
	 * @param        $meta_key
	 * @param string $post_id
	 *
	 * @return bool
	 */
	function field_hidden( $meta_key, $post_id = '' ) {

		$post_id    = empty($post_id) ? get_the_ID() : $post_id;
		$test_value = $this->get_placeholder( $meta_key, $post_id, NULL );

		if( ! is_null( $test_value ) ) return TRUE;

		return FALSE;
	}

	/**
	 * Check if field is set to be visible
	 *
	 * Method will pass NULL default value to get_placeholder()
	 *
	 * @since 1.1.0
	 *
	 * @param        $meta_key
	 * @param string $post_id
	 *
	 * @return bool
	 */
	function field_visible( $meta_key, $post_id = '' ) {

		$post_id    = empty($post_id) ? get_the_ID() : $post_id;
		$test_value = $this->get_placeholder( $meta_key, $post_id, NULL );

		if( is_null( $test_value ) ) return TRUE;

		return FALSE;
	}
}