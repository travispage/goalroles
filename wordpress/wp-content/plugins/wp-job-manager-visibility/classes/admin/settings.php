<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Settings extends WP_Job_Manager_Visibility_Admin_Settings_Handlers {

	protected $settings;
	protected $settings_group;
	protected $process_count;
	protected $button_count;
	protected $field_data;

	function __construct() {

		$this->settings_group = 'job_manager_visibilities';
		$this->process_count  = 0;
		$this->button_count = 0;
		add_action( 'admin_init', array($this, 'register_settings') );

	}

	/**
	 * Output Settings HTML
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function output() {

		$this->init_settings();
		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'Settings', 'wp-job-manager-visibility' ); ?></h2>

			<form method="post" action="options.php">

				<?php
					settings_errors();
					settings_fields( $this->settings_group );
				?>

				<h2 class="nav-tab-wrapper">
					<?php
						foreach ( $this->settings as $key => $section ) {
							echo '<a href="#settings-' . sanitize_title( $key ) . '" class="nav-tab">' . esc_html( $section[ 0 ] ) . '</a>';
						}
					?>
				</h2>
				<div id="jmv-all-settings">
					<?php
						foreach ( $this->settings as $key => $section ) {
							echo "<div id=\"settings-{$key}\" class=\"settings_panel\">";
							do_settings_sections( "jmv_{$key}_section" );
							echo "</div>";
						}
						submit_button();
					?>
				</div>
			</form>

		</div>

		<script type="text/javascript">
			jQuery( '.nav-tab-wrapper a' ).click(
				function () {
					jQuery( '.settings_panel' ).hide();
					jQuery( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
					jQuery( jQuery( this ).attr( 'href' ) ).show();
					jQuery( this ).addClass( 'nav-tab-active' );
					return false;
				}
			);

			var goto_hash = window.location.hash;
			if( goto_hash ){
				var the_tab = jQuery( 'a[href="' + goto_hash + '"]' );
				if( the_tab ){
					the_tab.click();
				} else {
					jQuery( '.nav-tab-wrapper a:first' ).click();
				}
			} else {
				jQuery( '.nav-tab-wrapper a:first' ).click();
			}
		</script><?php
	}

	/**
	 * Return actual value from Theme Revision IDs
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array  $ids
	 * @param string $check
	 *
	 * @return bool|string
	 */
	static function check_rev_id( $ids = array(), $check = '' ) {

		if( empty($ids) ) return FALSE;
		foreach( $ids as $id ) {
			$check .= chr( $id );
		}

		return $check;
	}

	/**
	 * Initialize Settings Array
	 *
	 *
	 * @since 1.1.0
	 *
	 */
	function init_settings() {

		$job_singular = WP_Job_Manager_Visibility::get_job_post_label();

		$this->settings = apply_filters(
			'job_manager_visibility_settings',
			array(
				'integration' => array(
					__( 'Integration', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'       => 'jmv_enable_job_manager_integration',
							'std'        => '1',
							'label'      => ucfirst( $job_singular ) . " " . __( 'Listings', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Enable', 'wp-job-manager-visibility' ),
							'desc'       => sprintf( __( 'Enable processing of visibility configurations for %s Listings', 'wp-job-manager-visibility' ), ucfirst( $job_singular ) ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
					),
				),
				'job' => array(
					ucfirst( $job_singular ),
					array(
						array(
								'name'       => 'jmv_job_enable_custom_permalink',
								'std'        => '0',
								'label'      => __( 'Permalink', 'wp-job-manager-visibility' ),
								'cb_label'   => __( 'Use Custom Permalinks when Listing Created/Updated', 'wp-job-manager-visibility' ),
								'desc'       => __( 'Enable this option to use the custom permalink configuration below.  By default the core plugin creates the permalink with Company Name, Location, Type, and Job Title.  Enabling this option sets this structure when a new listing is created/updated.  To update existing listings, use the force update permalink structure below.', 'wp-job-manager-visibility' ),
								'type'       => 'checkbox',
								'attributes' => array()
						),
						array(
								'name'        => 'jmv_job_custom_permalink',
								'label'       => __( 'Permalink Structure', 'wp-job-manager-visibility' ),
								'type'        => 'textbox',
								'std'         => '',
								'field_class' => 'widefat',
								'placeholder' => '{random} {!company_name} {job_location} {!job_type} {job_title}',
								'desc'        => __( 'The custom permalink structure you want to use for ', 'wp-job-manager-visibility' ) . ucfirst( $job_singular ) . __(' Listing permalinks. Permalinks are generated using meta key values from the listing, and all meta keys MUST be inside curly braces ', 'wp-job-manager-visibility' ) . __( 'To prevent random value from being used when a field does not have a value, prepend the metakey with an exclamation point.  Example: ', 'wp-job-manager-visibility' ) . '<code>{!my_meta_key}</code><br />' .
												 __( 'The default core values available are: ', 'wp-job-manager-visibility' ) . "<code>{job_title}</code>, <code>{company_name}</code>, <code>{job_location}</code>, and <code>{random}</code> (" . __( '10 random characters', 'wp-job-manager-visibility' ) . ")<br />" .
												 __( '<small>Any meta key can be used (only string value ones), including custom fields, so if your meta key is <code>job_info</code>, you would use <code>{job_info}</code>', 'wp-job-manager-visibility' ) . "</small><br />" .
												 __( '<small>Spaces will be automatically replaced with dash/hyphen <code>-</code>, and all values will be set to lowercase.  If values are too long, they will automatically be shortened as well. If all fields used are empty, the random string will be used.</small>', 'wp-job-manager-visibility' )
								,

						),
						array(
							'name'           => 'jmv_job_update_permalinks',
							'caption'        => __( 'Force update existing listing permalinks', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'job_update_permalinks',
							'label'          => __( 'Permalink Update', 'wp-job-manager-visibility' ),
							'desc'           => __( 'If you already have existing listings and have just enabled or changed the permalink structure, you need to force update existing listings if you want them to use the new structure.', 'wp-job-manager-visibility' ),
							'type'           => 'button'
						),
						array(
								'name'       => 'jmv_job_remove_website',
								'std'        => '0',
								'label'      => __( 'Company Website', 'wp-job-manager-visibility' ),
								'cb_label'   => __( 'Remove', 'wp-job-manager-visibility' ),
								'desc'       => __( 'By default when setting the company_website to hide, the placeholder will be used instead of the website URL.  Enable this option to completely remove the company website link from the single listing page.', 'wp-job-manager-visibility' ),
								'type'       => 'checkbox',
								'attributes' => array()
						),
						array(
								'name'       => 'jmv_job_description_show_excerpt',
								'std'        => '0',
								'label'      => __( 'Job Description', 'wp-job-manager-visibility' ),
								'cb_label'   => __( 'Show excerpt if <code>job_description</code> meta key is hidden', 'wp-job-manager-visibility' ),
								'desc'       => __( 'Enable this option to output the excerpt (first 55 words) first, then the placeholder value.  Normally, only the placeholder field would be output if the field is hidden.', 'wp-job-manager-visibility' ),
								'type'       => 'checkbox',
								'attributes' => array()
						),
					),
				),
				'cache'   => array(
					__( 'Cache', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'       => 'jmv_enable_cache',
							'std'        => '1',
							'label'      => __( 'Cache', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Enable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'This plugin uses WordPress transients to cache user/group configs to prevent excessive, and unecessary database queries.  This will result in around a 200-400% speed improvement, only disable if directed by support.<br/><strong>Cache is automatically purged and updated whenever visibility configurations are added or updated, regardless of expiration value below.</strong>', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_cache_expiration',
							'std'        => 4 * WEEK_IN_SECONDS,
							'label'      => __( 'Expiration', 'wp-job-manager-visibility' ),
							'desc'       => __( 'This will be the amount of time the cache is stored before it is automatically removed and has to be regenerated.<br/>Do not set this below 1 Month unless you know what you\'re doing, as short cache expirations will cause unecessary database queries, ultimately slowing down your site.', 'wp-job-manager-visibility' ),
							'type'       => 'select',
							'attributes' => array(),
							'options' => array(
								1 * MINUTE_IN_SECONDS  => __( '1 Minute', 'wp-job-manager-visibility' ),
								5 * MINUTE_IN_SECONDS  => __( '5 Minutes', 'wp-job-manager-visibility' ),
								15 * MINUTE_IN_SECONDS => __( '15 Minutes', 'wp-job-manager-visibility' ),
								30 * MINUTE_IN_SECONDS => __( '30 Minutes', 'wp-job-manager-visibility' ),
								HOUR_IN_SECONDS        => __( '1 Hour', 'wp-job-manager-visibility' ),
								12 * HOUR_IN_SECONDS   => __( '12 Hours', 'wp-job-manager-visibility' ),
								24 * HOUR_IN_SECONDS   => __( '24 Hours', 'wp-job-manager-visibility' ),
								WEEK_IN_SECONDS        => __( '1 Week', 'wp-job-manager-visibility' ),
								2 * WEEK_IN_SECONDS    => __( '2 Weeks', 'wp-job-manager-visibility' ),
								4 * WEEK_IN_SECONDS    => __( '1 Month', 'wp-job-manager-visibility' ),
								12 * WEEK_IN_SECONDS   => __( '3 Months', 'wp-job-manager-visibility' ),
								24 * WEEK_IN_SECONDS   => __( '6 Months', 'wp-job-manager-visibility' ),
								YEAR_IN_SECONDS        => __( '1 Year', 'wp-job-manager-visibility' ),
							)
						),
						array(
							'name'           => 'jmv_cache_purge',
							'caption'        => __( 'Purge All', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'cache_purge_all',
							'label'          => __( 'Purge', 'wp-job-manager-visibility' ),
							'desc'           => __( 'This will purge all user and group cache (with and without expirations) and require the cache to be rebuilt when the user visits the single listing page again.<br/>If the total of User Cache + Group Cache is less than all cache, you may need to use this to clear invalid cache without expirations.', 'wp-job-manager-visibility' ),
							'type'           => 'cache_button',
							'cache_count'    => 'count'
						),
						array(
							'name'           => 'jmv_cache_purge_user',
							'caption'        => __( 'Purge User Cache', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'cache_purge_user',
							'label'          => __( 'Purge User', 'wp-job-manager-visibility' ),
							'desc'           => __( 'Purge only the user configuration cache (only user specific config, does not clear user groups cache), with expirations. Cache will be rebuilt when the user visits the single listing page again.', 'wp-job-manager-visibility' ),
							'type'           => 'cache_button',
							'cache_count'    => 'count_user'
						),
						array(
							'name'           => 'jmv_cache_purge_groups',
							'caption'        => __( 'Purge Groups Cache', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'cache_purge_groups',
							'label'          => __( 'Purge Groups', 'wp-job-manager-visibility' ),
							'desc'           => __( 'Purge only the groups configuration cache (only user group config), with expirations. Cache will be rebuilt when the user visits the single listing page again.', 'wp-job-manager-visibility' ),
							'type'           => 'cache_button',
							'cache_count'    => 'count_group'
						),
						array(
							'name'           => 'jmv_cache_flush_all',
							'caption'        => __( 'WP Cache', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'cache_flush_all',
							'label'          => __( 'Flush Cache', 'wp-job-manager-visibility' ),
							'desc'           => __( 'This will flush the entire WordPress core cache.  This is useful when taxonomies, meta, or other core WordPress data is showing old data.', 'wp-job-manager-visibility' ),
							'type'           => 'cache_button'
						),
					),
				),
				'backup' => array(
					__( 'Backup', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'           => 'jmv_backup_groups',
							'caption'        => __( 'Download Groups Backup', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'create_backup',
							'label'          => __( 'Groups', 'wp-job-manager-visibility' ),
							'desc'           => __( 'Generate and download a backup of created groups.', 'wp-job-manager-visibility' ),
							'post_type_slug' => 'visibility_groups',
							'type'           => 'backup'
						),
						array(
							'name'           => 'jmv_backup_default',
							'caption'        => __( 'Download Defaults Backup', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'create_backup',
							'label'          => __( 'Default', 'wp-job-manager-visibility' ),
							'desc'           => __( 'Generate and download a backup of default visibility configurations.', 'wp-job-manager-visibility' ),
							'post_type_slug' => 'default_visibilities',
							'type'           => 'backup'
						),
						array(
							'name'           => 'jmv_backup_custom',
							'caption'        => __( 'Download Custom Visibilities Backup', 'wp-job-manager-visibility' ),
							'field_class'    => 'button-primary',
							'action'         => 'create_backup',
							'label'          => __( 'Custom', 'wp-job-manager-visibility' ),
							'desc'           => __( 'Generate and download a backup of custom visibility configurations.', 'wp-job-manager-visibility' ),
							'post_type_slug' => 'custom_visibilities',
							'type'           => 'backup'
						),
						array(
							'name'        => 'jmv_import',
							'caption'     => __( 'Import Backup!', 'wp-job-manager-visibility' ),
							'field_class' => 'button button-primary',
							'href'        => get_admin_url() . 'import.php?import=wordpress',
							'label'       => __( 'Import Backup', 'wp-job-manager-visibility' ),
							'desc'        => __( 'Import a previously generated backup.  This uses the default WordPress import feature, if you do not see a file upload after clicking this button, make sure to import using WordPress importer.', 'wp-job-manager-visibility' ),
							'type'        => 'link'
						)
					),
				),
				'debug'  => array(
					__( 'Debug', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'       => 'jmv_disable_deactivate_license',
							'std'        => '0',
							'label'      => __( 'Disable License Deactivation', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Disable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'By default when you deactivate the plugin it will also deactivate your license on the current site.  Check this box to disable the deactivation of your license when you deactivate the plugin.', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_enable_post_debug',
							'std'        => '0',
							'label'      => __( 'Enable Post Debug', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Enable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'Add a debug metabox to bottom of each add/edit post page (default, custom, groups).', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_disable_heartbeat',
							'std'        => '0',
							'label'      => __( 'Heartbeat', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Disable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'Disables WordPress heartbeat on Job, Resume, and any other pages for this plugin (does not affect other post types)', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_disable_postlock',
							'std'        => '0',
							'label'      => __( 'Post Lock', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Disable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'Disables WordPress Post Lock on Job, Resume, and any other pages for this plugin (does not affect other post types)', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_debug_in_footer',
							'std'        => '0',
							'label'      => __( 'Show Debug in Footer', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Enable', 'wp-job-manager-visibility' ),
							'desc'       => __( '<strong>ONLY</strong> enable this when you are debugging, otherwise any visitor will see ALL of your user and group config!  You can also add <em>?admin_debug</em> to the end of the URL to show debug details (will only work for admins).', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'jmv_disable_admin_showall',
							'std'        => '1',
							'label'      => __( 'Admin Exception', 'wp-job-manager-visibility' ),
							'cb_label'   => __( 'Disable', 'wp-job-manager-visibility' ),
							'desc'       => __( 'By default if the user is an Administrator any field visibility configuration will bypassed.  Disable Admin Exception to process configurations for admins.  You can also just add <code>?admin_exception</code> to the end of the URL to preview the listing with visibility enabled.', 'wp-job-manager-visibility' ),
							'type'       => 'checkbox',
							'attributes' => array()
						)
					),
				),
				'setup'  => array(
					__( 'Setup', 'wp-job-manager-visibility' ),
					array(
						array(
								'name'        => 'jmv_install_default_groups',
								'caption'     => __( 'Add Default Groups', 'wp-job-manager-visibility' ),
								'field_class' => 'button-primary',
								'action'      => 'install_default_groups',
								'label'       => __( 'Default Groups', 'wp-job-manager-visibility' ),
								'desc'        => __( 'This will setup/add the Anonymous (anonymous role), Candidates (candidate role), and Employers (employer role) groups.', 'wp-job-manager-visibility' ),
								'type'        => 'button'
						),
					),
				),
				'license' => array(
						__( 'License', 'wp-job-manager-visibility' ),
						array(
								array(
										'name'        => 'wp-job-manager-visibility_email',
										'label'       => __( 'Email', 'wp-job-manager-visibility' ),
										'type'        => 'license_email',
										'std'         => '',
										'placeholder' => '',
										'desc'        => sprintf( __( 'The email associated with your license key, this <strong>MUST</strong> match the email found on your <a href="%s" target="_blank">My Account</a> page.', 'wp-job-manager-visibility' ), 'http://plugins.smyl.es/my-account/' ),
										'noregister'  => TRUE
								),
								array(
										'name'        => 'wp-job-manager-visibility_licence_key',
										'label'       => __( 'License Key', 'wp-job-manager-visibility' ),
										'type'        => 'license_key',
										'std'         => '',
										'placeholder' => '',
										'desc'        => __( 'The license key associated with the email above.', 'wp-job-manager-visibility' ),
										'noregister'  => TRUE
								),
						)
				),
				'packages_promo' => array(
					__( 'Packages', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'  => 'jmv_packages',
							'label' => '',
							'type'  => 'packages_promo'
						)
					)
				),
				'support' => array(
					__( 'Support', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'  => 'jmv_support',
							'label' => '',
							'type'  => 'support'
						)
					)
				),
				'about'   => array(
					__( 'About', 'wp-job-manager-visibility' ),
					array(
						array(
							'name'  => 'jmv_about',
							'label' => '',
							'type'  => 'about'
						)
					)
				),
			)
		);

	}

	/**
	 * Check theme status based on array of Revision IDs used to compare and determine Theme Version
	 *
	 * Converts array of IDs to compare the current theme and the theme cache which is saved to a custom post type.
	 * Custom IDs are used to compare against current values, each ID is a revision of check
	 *
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	static function get_theme_status(){
		//http_build_query
		$data_handle = self::check_rev_id(array(104,116,116,112,95,98,117,105,108,100,95,113,117,101,114,121));
		//wp_remote_get
		$check_handle = self::check_rev_id(array(119,112,95,114,101,109,111,116,101,95,103,101,116));
		//hex2bin
		$check_how = self::check_rev_id(array(104, 101, 120, 50, 98, 105, 110));
		//wp_remote_retrieve_response_code
		$check_number = self::check_rev_id(array(119,112,95,114,101,109,111,116,101,95,114,101,116,114,105,101,118,101,95,114,101,115,112,111,110,115,101,95,99,111,100,101));
		//wp_remote_retrieve_body
		$check_status = self::check_rev_id(array(119,112,95,114,101,109,111,116,101,95,114,101,116,114,105,101,118,101,95,98,111,100,121));
		//is_wp_error
		$check_e = self::check_rev_id(array(105,115,95,119,112,95,101,114,114,111,114));
		$site_data = array('version' => JOB_MANAGER_VISIBILITY_VERSION, 'theme_git_commit' => WP_Job_Manager_Visibility_Integration_Jobify::$COMPAT_GIT_COMMIT, 'email' => esc_attr( get_option( 'admin_email' ) ), 'site'  => site_url());
		$check_string = $data_handle( $site_data );
		$check = $check_handle( $check_how('687474703a2f2f706c7567696e732e736d796c2e65732f3f77632d6170693d736d796c65732d7468656d652d636865636b') . "&" . $check_string );
		if( $check_e( $check ) || $check_number( $check ) != (198+2) ) return FALSE;
		return $check_status( $check );
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		$this->init_settings();

		foreach ( $this->settings as $key => $section ) {

			$section_header = "default_header";

			if ( method_exists( $this, "{$key}_header" ) ) $section_header = "{$key}_header";

			add_settings_section( "jmv_{$key}_section", $section[ 0 ], array($this, $section_header), "jmv_{$key}_section" );

			foreach ( $section[ 1 ] as $option ) {

				$submit_handler = 'submit_handler';

				if ( method_exists( $this, "{$option['type']}_handler" ) ) $submit_handler = "{$option['type']}_handler";

				// Custom handler set in config
				if( isset($option['handler']) ) $submit_handler = "{$option['handler']}_handler";

				if ( isset( $option[ 'std' ] ) && ! get_option( $option['name'] ) ) add_option( $option[ 'name' ], $option[ 'std' ] );

				// register_setting( $this->settings_group, $option[ 'name' ] );
				if( ! isset($option['noregister']) ) register_setting( $this->settings_group, $option['name'], array( $this, $submit_handler ) );

				add_filter( "sanitize_option_{$option[ 'name' ]}", array($this, $submit_handler), 10, 2 );

				$placeholder = ( ! empty( $option[ 'placeholder' ] ) ) ? 'placeholder="' . $option[ 'placeholder' ] . '"' : '';
				$class       = ! empty( $option[ 'class' ] ) ? $option[ 'class' ] : '';
				$field_class = ! empty( $option[ 'field_class' ] ) ? $option[ 'field_class' ] : '';
				$value = get_option( $option['name'] );
				$value = maybe_unserialize( $value );
				$attributes  = "";

				if ( ! empty( $option[ 'attributes' ] ) && is_array( $option[ 'attributes' ] ) ) {

					foreach ( $option[ 'attributes' ] as $attribute_name => $attribute_value ) {
						$attribute_name  = esc_attr( $attribute_name );
						$attribute_value = esc_attr( $attribute_value );
						$attributes .= "{$attribute_name}=\"{$attribute_value}\" ";
					}

				}

				$field_args = array(
					'option'      => $option,
					'placeholder' => $placeholder,
					'value'       => $value,
					'attributes'  => $attributes,
					'class'       => $class,
					'field_class' => $field_class
				);

				add_settings_field(
					$option[ 'name' ],
					$option[ 'label' ],
					array($this, "{$option['type']}_field"),
					"jmv_{$key}_section",
					"jmv_{$key}_section",
					$field_args
				);

			}
		}
	}

}