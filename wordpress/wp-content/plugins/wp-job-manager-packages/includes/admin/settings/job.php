<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Settings_Job
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Settings_Job extends WPJM_Pack_Admin_Settings {

	/**
	 * WPJM_Pack_Admin_Settings_Job constructor.
	 *
	 * @param $type WPJM_Pack_Job
	 * @param $admin WPJM_Pack_Admin_Job
	 */
	public function __construct( $type, $admin ){

		$this->admin = $admin;
		$this->type = $type;

		add_filter( 'job_manager_settings', array( $this, 'add_settings' ), 9999 );
		$this->add_custom_types();
	}

	/**
	 * Add Settings to core Job Manager Settings
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function add_settings( $settings ){

		wp_enqueue_script( 'wpjmpack_settings' );

		$singular = $this->get_label( 'job_listing', __( 'Job', 'wp-job-manager-packages' ) );
		$plural = $this->get_label( 'job_listing', __( 'Jobs', 'wp-job-manager-packages' ), FALSE );

		$apply_verb = $this->type->packages->get_package_type( 'apply', 'verb', true );
		$apply_label = $this->type->packages->get_package_type( 'apply', 'label' );

		$vis_settings = array(
			array(
				'name'       => 'job_manager_job_visibility_dashboard_my_account',
				'std'        => '1',
				'label'      => sprintf( __( 'User Packages List Table', 'wp-job-manager-packages' ), $singular ),
				'cb_label'   => sprintf( __( 'Yes, output the user\'s %1$s visibility packages list table on the My Account page', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'When enabled, this will output the current user packages on the My Account page. If you disable this setting, use the <code>%s</code> shortcode to output the list table elsewhere.', 'wp-job-manager-packages' ), '[job_visibility_dashboard]' ) . '<br />',
				'type'       => 'checkbox',
				'class'      => '',
			),
			array(
				'name'  => 'job_manager_job_visibility_settings_view_separator',
				'text'  => sprintf( __( 'View %s', 'wp-job-manager-packages' ), $singular ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_job_visibility_require_package_view',
				'std'        => '0',
				'label'      => sprintf( __( 'View %s', 'wp-job-manager-packages' ), $singular ),
				'cb_label'   => sprintf( __( 'Require %1$s package to view a single %1$s', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a %s package with view permissions, to view any single listing page.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'class'      => '',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-cshow' => 'wpjmpack-require-view', 'data-uchide' => 'wpjmpack-require-view' )
			),
			$this->get_redirect_setting( 'view' ),
			$this->get_ph_wp_editor_setting( 'view', 'content-single-job_listing.php', array( 'view_job_packages', 'view_job_packages_url' ) ),
			array(
				'name'  => 'job_manager_job_visibility_settings_browse_separator',
				'text'  => sprintf( __( 'Browse %s', 'wp-job-manager-packages' ), $plural ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_job_visibility_require_package_browse',
				'std'        => '0',
				'label'      => sprintf( __( 'Browse %s', 'wp-job-manager-packages' ), $plural ),
				'cb_label'   => sprintf( __( 'Require %s package to browse listings', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a package with browse permissions, to view the listing summary on the browse/list/search page.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-cshow' => 'wpjmpack-require-browse', 'data-uchide' => 'wpjmpack-require-browse' )
			),
			$this->get_redirect_setting( 'browse' ),
			$this->get_ph_wp_editor_setting( 'browse', 'job_listings.php', array( 'browse_job_packages', 'browse_job_packages_url' ) ),
			array(
				'name'  => 'job_manager_job_visibility_settings_apply_separator',
				'text'  => sprintf( __( '%1$s %2$s', 'wp-job-manager-packages' ), ucfirst( $apply_verb ), $singular ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_job_visibility_require_package_apply',
				'std'        => '0',
				'label'      => sprintf( __( '%1$s %2$s', 'wp-job-manager-packages' ), ucfirst( $apply_verb ), $singular ),
				'cb_label'   => sprintf( __( 'Require %1$s package to %2$s a %1$s', 'wp-job-manager-packages' ), $singular, $apply_verb ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a %1$s package with %2$s permissions, to %3$s a listing.', 'wp-job-manager-packages' ), $singular, $apply_label, $apply_verb ) . '<br />',
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-cshow' => 'wpjmpack-require-apply', 'data-uchide' => 'wpjmpack-require-apply' )
			),
			$this->get_ph_wp_editor_setting( 'apply', 'job-application-url.php and/or job-application-email.php', array( 'apply_job_packages', 'apply_job_packages_url' ) ),
		);

		$job_type_colors = array(
			array(
				'name'  => 'job_manager_job_visibility_settings_type_color_separator',
				'text'  => __( 'Type Colors', 'wp-job-manager-packages' ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'paint brush'
			),
			array(
				'name'       => 'job_manager_job_visibility_customize_type_colors',
				'std'        => '0',
				'label'      => __( 'Customize Type Colors', 'wp-job-manager-packages' ),
				'cb_label'   => __( 'Yes, enable custom type colors settings', 'wp-job-manager-packages' ),
				'desc'       => __( 'Enable to customize the type colors.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class'      => '',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-cshow' => 'wpjmpack-customize-typecolors', 'data-uchide' => 'wpjmpack-customize-typecolors' )
			),
			array(
				'name'    => 'job_manager_job_visibility_sui_view_color',
				'std'     => 'teal',
				'label'   => ' ↳ ' . __( 'View Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the view type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class'   => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
			array(
				'name'    => 'job_manager_job_visibility_sui_browse_color',
				'std'     => 'grey',
				'label'   => ' ↳ ' . __( 'Browse Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the browse type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class' => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
			array(
				'name'    => 'job_manager_job_visibility_sui_apply_color',
				'std'     => 'blue',
				'label'   => ' ↳ ' . sprintf( __( '%s Color', 'wp-job-manager-packages' ), ucfirst( $apply_label ) ),
				'desc'    => sprintf( __( 'Customize color to use for the the %1$s type', 'wp-job-manager-packages' ), strtolower( $apply_label ) ),
				'type'    => 'select',
				'class' => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			)
		);

		$settings['job_visibility'] = array(
			sprintf( __( '%s Visibility', 'wp-job-manager-packages' ), $singular ),
			array_merge( $vis_settings, $this->get_redirect_settings(), $this->get_sui_settings(), $job_type_colors )
		);

		$job_pages = array(
			array(
				'name'  => 'job_manager_job_visibility_settings_separator_pages',
				'label' => '',
				'desc'  => '',
				'text'  => sprintf( __( '%1$s Visibility Pages', 'wp-job-manager-packages' ), $singular ),
				'type'  => 'divider',
			    'icon'  => 'unhide'
			),
			array(
				'name'  => 'job_manager_job_visibility_packages_page_id',
				'std'   => '',
				'label' => __( 'Select Package Form Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[job_visibility_packages]</code> shortcode. This lets the plugin know where the form is located.', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'job_manager_browse_job_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . __( 'Browse Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[browse_job_packages]</code> shortcode, <strong>if you plan to use custom select browse package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'job_manager_view_job_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . __( 'View Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[view_job_packages]</code> shortcode, <strong>if you plan to use custom select view package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'job_manager_apply_job_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . sprintf( __( '%s Package Page', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label() ),
				'desc'  => sprintf( __( 'Select the page where you have placed the <code>[apply_job_packages]</code> shortcode, <strong>if you plan to use custom select %s package form page (optional).</strong>', 'wp-job-manager-packages' ), job_manager_packages_get_apply_label( true ) ),
				'type'  => 'page'
			),
		);

		$settings['job_pages'][1] = array_merge( $settings['job_pages'][1], $job_pages );

		return $settings;
	}

}
