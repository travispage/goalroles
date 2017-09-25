<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Settings
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Settings {

	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	protected $type;
	/**
	 * @var WPJM_Pack_Admin_Job|WPJM_Pack_Admin_Resume
	 */
	protected $admin;

	/**
	 * Add Custom Settings Types
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function add_custom_types(){

		if( ! has_action( 'wp_job_manager_admin_field_wp_editor' ) ){
			add_action( 'wp_job_manager_admin_field_wp_editor', array( $this, 'wp_editor_field' ), 99, 4 );
		}
		if( ! has_action( 'wp_job_manager_admin_field_separator' ) ){
			add_action( 'wp_job_manager_admin_field_separator', array( $this, 'separator' ), 99, 4 );
		}
		if( ! has_action( 'wp_job_manager_admin_field_divider' ) ){
			add_action( 'wp_job_manager_admin_field_divider', array( $this, 'divider' ), 99, 4 );
		}

	}

	/**
	 * Return Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type
	 * @param string $fallback
	 * @param bool   $singular
	 *
	 * @return mixed
	 */
	public function get_label( $post_type = '', $fallback = '', $singular = true ){

		if( empty( $fallback ) ){
			$fallback = ucfirst( $this->type->slug );

			if( ! $singular ){
				$fallback .= 's';
			}
		}

		if( empty( $post_type ) ){
			$post_type = $this->type->post_type;
		}

		$post_obj = get_post_type_object( $post_type );

		if( $post_obj instanceof WP_Post_Type && $post_obj->labels ){
			$label = $singular ? $post_obj->labels->singular_name : $post_obj->labels->name;
		}

		$label = ! isset( $label ) ? ucfirst( $fallback ) : $label;

		return $label;

	}

	/**
	 * Return Plural Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type
	 * @param string $fallback
	 *
	 * @return mixed
	 */
	public function get_label_plural( $post_type = '', $fallback = '' ){
		return $this->get_label( $post_type, $fallback, true );
	}

	/**
	 * WP Editor Field Type
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $option
	 * @param $attributes
	 * @param $value
	 * @param $placeholder
	 */
	public function wp_editor_field( $option, $attributes, $value, $placeholder ){

		wp_editor( $value, $option['name'] );

		if( ! empty ( $option['desc'] ) ){
			echo ' <p class="description">' . $option['desc'] . '</p>';
		}

	}

	/**
	 * Override File Code Block Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function override_file( $file ){
		if( empty( $file ) ) {
			return '';
		}
		return  __( ', or use a', 'wp-job-manager-packages' ) .  ' <span style="font-style: normal; font-weight: bold;">' . sprintf( __( '<a href="%2$s" target="_blank">Template Override</a>: <code>%1$s</code>.', 'wp-job-manager-packages' ), $file, 'https://plugins.smyl.es/docs/template-overrides/' ) . '</span>';
	}

	/**
	 * Separator Field Type
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $option
	 * @param $attributes
	 * @param $value
	 * @param $placeholder
	 */
	public function separator( $option, $attributes, $value, $placeholder ){
		echo '<hr />';
	}

	/**
	 * Divider Field Type
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $option
	 * @param $attributes
	 * @param $value
	 * @param $placeholder
	 */
	public function divider( $option, $attributes, $value, $placeholder ){

		wp_enqueue_style( 'wpjmpack-sui-divider' );

		$text = ! empty( $option['text'] ) ? $option['text'] : '';
		$icon = ! empty( $option['icon'] ) ? "<i style=\"display: inline-block; padding: 0; margin-right: .75rem; vertical-align: initial;\" class=\"{$option['icon']} icon\"></i>" : '';

		echo '<h4 class="ui horizontal divider header" style="text-transform: none;">' . $icon . $text . '</h4>';
	}

	/**
	 * Return Semantic UI Colors
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sui_colors(){

		$colors = array(
			''       => __( 'None', 'wp-job-manager-packages' ),
			'red'    => __( 'Red', 'wp-job-manager-packages' ),
			'orange' => __( 'Orange', 'wp-job-manager-packages' ),
			'yellow' => __( 'Yellow', 'wp-job-manager-packages' ),
			'olive'  => __( 'Olive', 'wp-job-manager-packages' ),
			'green'  => __( 'Green', 'wp-job-manager-packages' ),
			'teal'   => __( 'Teal', 'wp-job-manager-packages' ),
			'blue'   => __( 'Blue', 'wp-job-manager-packages' ),
			'violet' => __( 'Violet', 'wp-job-manager-packages' ),
			'purple' => __( 'Purple', 'wp-job-manager-packages' ),
			'pink'   => __( 'Pink', 'wp-job-manager-packages' ),
			'grey'   => __( 'Grey', 'wp-job-manager-packages' ),
			'black'  => __( 'Black', 'wp-job-manager-packages' ),
		);

		return $colors;
	}

	/**
	 * Return Redirect Customization Settings
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_redirect_settings(){

		$slug = $this->type->slug;

		$redirect_settings = array(
			array(
				'name'  => "job_manager_{$slug}_visibility_settings_redirect_separator",
				'text'  => __( 'Redirects', 'wp-job-manager-packages' ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'linkify'
			),
			array(
				'name'       => "job_manager_{$slug}_visibility_customize_redirect",
				'std'        => '0',
				'label'      =>  __( 'Customize Redirect', 'wp-job-manager-packages' ),
				'cb_label'   => __( 'Yes, enable custom redirect settings', 'wp-job-manager-packages' ),
				'desc'       => __( 'Enable to customize the redirect settings and configuration.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class'      => '',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-related' => 'wpjmpack-customize-redirect' )
			),
			array(
				'name'  => "job_manager_{$slug}_visibility_redirect_seconds",
				'std'   => '2',
				'label' => ' ↳ ' . __( 'Wait Time', 'wp-job-manager-packages' ),
				'desc'  => __( 'Amount of time (in seconds) to wait before executing redirect.  Set to 0 (zero) to immediately redirect.', 'wp-job-manager-packages' ),
				'type'  => 'number',
			    'class' => 'wpjmpack-customize-redirect'
			),
			array(
				'name'       => "job_manager_{$slug}_visibility_redirect_hide_all",
				'std'        => '0',
				'label'      => ' ↳ ' . __( 'Hide All', 'wp-job-manager-packages' ),
				'cb_label'   => __( 'Yes, hide all page contents while showing the dimmer.', 'wp-job-manager-packages' ),
				'desc'       => __( 'By default, when a redirect is enabled, a dimmer will be shown that covers the entire page contents with a loading icon in the middle, and notice text below it.  Enable this option to hide all content behind the dimmer (to only show loader and notice text).', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class' => 'wpjmpack-customize-redirect',
				'attributes' => array()
			),
			array(
				'name'       => "job_manager_{$slug}_visibility_redirect_dimmer_inverted",
				'std'        => '1',
				'label'      => ' ↳ ' . __( 'White Dimmer', 'wp-job-manager-packages' ),
				'cb_label'   => __( 'Yes, use the white (inverted) dimmer color.', 'wp-job-manager-packages' ),
				'desc'       => __( 'By default, the standard dimmer color is black, enable this setting to use the inverted color, white.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class' => 'wpjmpack-customize-redirect',
				'attributes' => array()
			),
		);

		return $redirect_settings;
	}

	/**
	 * Return Semantic UI Settings
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sui_settings(){

		$slug = $this->type->slug;

		$sui_settings = array(
			array(
				'name'  => "job_manager_{$slug}_visibility_settings_sui_separator",
				'text'  => __( 'Semantic UI', 'wp-job-manager-packages' ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'lab'
			),
			array(
				'name'       => "job_manager_{$slug}_visibility_use_semantic",
				'std'        => '1',
				'label'      => __( 'Semantic UI', 'wp-job-manager-packages' ),
				'cb_label'   => sprintf( __( 'Yes, enable <a href="%s" target="_blank">Semantic UI</a>', 'wp-job-manager-packages' ), 'http://semantic-ui.com/collections/table.html' ),
				'desc'       => __( 'Enable this to use and customize the Semantic UI styling.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic wpjmpack-cbd_sui', 'data-related' => 'wpjmpack-use-semantic' )
			),
			array(
				'name'       => "job_manager_{$slug}_visibility_sui_use_table_style",
				'std'        => '1',
				'label'      => ' ↳ ' . __( 'Table Style', 'wp-job-manager-packages' ),
				'cb_label'   => sprintf( __( 'Yes, enable <a href="%s" target="_blank">Semantic UI</a> table styling for the My Packages list', 'wp-job-manager-packages' ), 'http://semantic-ui.com/collections/table.html' ),
				'desc'       => __( 'If enabled, the frontend user package list tables will use the Semantic UI styling.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class'      => 'wpjmpack-use-semantic wpjmpack-sui-use-table-style',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic wpjmpack-sui_ts-cb_dynamic', 'data-related' => 'wpjmpack-sui-table-style' )
			),
			array(
				'name'    => "job_manager_{$slug}_visibility_sui_table_color",
				'std'     => 'black',
				'label'   => '   ┗→ ' . __( 'Table Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the table', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'options' => $this->get_sui_colors(),
				'class'   => 'wpjmpack-use-semantic wpjmpack-sui-table-style',
			),
			//array(
			//	'name'       => "job_manager_{$slug}_visibility_sui_table_color_details",
			//	'std'        => '1',
			//	'label'      => '   ┗→ ' . __( 'Details Background' ),
			//	'cb_label'   => __( 'Yes, set the details background color to match the table color.' ),
			//	'desc'       => __( 'This will enable jQuery on page load to get the table color, and set the details table wrapper background color to match it. <br/> Alternatively, you can set CSS background color for <code>jmpack-package-details</code>.' ),
			//	'type'       => 'checkbox',
			//	'class'      => 'wpjmpack-use-semantic wpjmpack-sui-table-style wpjmpack-sui-use-table-style',
			//	'attributes' => array()
			//),
		);

		return $sui_settings;
	}

	/**
	 * Return Formatted Shortcode Description Output
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $shortcodes
	 *
	 * @return string|void
	 */
	public function shortcodes_output( $shortcodes ){

		if( empty( $shortcodes ) ){
			return '';
		}

		$output = '<br /><span style="font-style: normal; font-weight: bold;">' . __( 'Available Shortcodes:', 'wp-job-manager-packages' );

		foreach( $shortcodes as $shortcode ){
			$output .= " <code>[{$shortcode}]</code>";
		}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Return Configured WP Editor Setting
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $type
	 * @param       $override_file
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_ph_wp_editor_setting( $type, $override_file, $shortcodes = array(), $args = array() ){

		$slug = $this->type->slug;
		$singular = $this->get_label();
		$verb = $this->type->packages->get_package_type( $type, 'verb' );

		$build = array(
			'name'       => "job_manager_{$slug}_visibility_require_package_{$type}_ph",
			'std'        => '',
			'label'      => ' ↳ ' . sprintf( __( '%1$s %2$s Placeholder', 'wp-job-manager-packages' ), ucfirst( $verb ), $singular ),
			'desc'       =>  __( 'The content above will be shown when the user does not have permissions and a package is required', 'wp-job-manager-packages' ) . $this->override_file( $override_file ) . $this->shortcodes_output( $shortcodes ),
			'type'       => 'wp_editor',
			'class'      => "wpjmpack-require-{$type} wpjmpack-wpe-{$type}",
		);

		return array_merge( $build, $args );
	}

	/**
	 * Return Configured Redirect Setting
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $type
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_redirect_setting( $type, $args = array() ){

		$slug = $this->type->slug;
		$singular = $this->get_label();
		$verb = $this->type->packages->get_package_type( $type, 'verb' );

		$build = array(
			'name'     => "job_manager_{$slug}_visibility_require_package_{$type}_redirect",
			'std'      => '0',
			'label'    => ' ↳ ' . sprintf( __( '%1$s %2$s Redirect', 'wp-job-manager-packages' ), ucfirst( $verb ), $singular ),
			'cb_label' => sprintf( __( 'Yes, redirect to the %1$s %2$s package select page', 'wp-job-manager-packages' ), strtolower( $verb ), $singular ),
			'desc'     => __( 'If enabled, users without permissions will be redirected to the page (configured in Pages tab) to purchase or select package to use.', 'wp-job-manager-packages' ) . '<br />',
			'type'     => 'checkbox',
			'class'    => "wpjmpack-require-{$type}",
			'attributes' => array( 'class' => "wpjmpack-{$type}-cb_dynamic wpjmpack-cb_dynamic", 'data-chide' => "wpjmpack-wpe-{$type}", 'data-ucshow' => "wpjmpack-wpe-{$type}", 'data-root' => "wpjmpack-cbd_{$type}" )
		);

		return array_merge( $build, $args );
	}

	/**
	 * Return Admin Override Setting
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $type
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_admin_require_setting( $type, $args = array() ){

		$slug = $this->type->slug;
		$singular = $this->get_label();
		$verb = $this->type->packages->get_package_type( $type, 'verb' );

		$build = array(
			'name'     => "job_manager_{$slug}_visibility_require_package_{$type}_admin",
			'std'      => '0',
			'label'    => ' ↳ ' . sprintf( __( '%1$s %2$s Admin', 'wp-job-manager-packages' ), ucfirst( $verb ), $singular ),
			'cb_label' => sprintf( __( 'Yes, require administrators to have package', 'wp-job-manager-packages' ), strtolower( $verb ), $singular ),
			'desc'     => __( 'If enabled, administrators will be required to have a package with permissions for this type.', 'wp-job-manager-packages' ) . '<br />',
			'type'     => 'checkbox',
			'class'    => "wpjmpack-require-{$type}",
			'attributes' => array()
		);

		return array_merge( $build, $args );
	}

	/**
	 *
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param       $type
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_redirect_use_ph_setting( $type, $args = array() ){

		$slug = $this->type->slug;
		$singular = $this->get_label();
		$verb = $this->type->packages->get_package_type( $type, 'verb' );

		$build = array(
			'name'     => "job_manager_{$slug}_visibility_{$type}_redirect_use_ph",
			'std'      => '0',
			'label'    => ' ↳ ' . sprintf( __( '%1$s %2$s Redirect Notice', 'wp-job-manager-packages' ), ucfirst( $verb ), $singular ),
			'cb_label' => sprintf( __( 'Yes, use the %1$s %2$s placeholder as redirect notice', 'wp-job-manager-packages' ), strtolower( $verb ), $singular ),
			'desc'     => __( 'If enabled, the placeholder value will be used as the notice shown before redirecting (instead of default one).', 'wp-job-manager-packages' ) . '<br />',
			'type'     => 'checkbox',
			'class'    => "wpjmpack-require-{$type} wpjmpack-{$type}-redirect-enabled",
			'attributes' => array( 'class' => "wpjmpack-cb_dynamic wpjmpack-cbd_{$type}", 'data-uchide' => "wpjmpack-wpe-{$type}", 'data-cshow' => "wpjmpack-wpe-{$type}" )
		);

		return array_merge( $build, $args );
	}
}
