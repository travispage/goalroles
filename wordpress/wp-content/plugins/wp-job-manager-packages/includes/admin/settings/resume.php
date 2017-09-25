<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Settings_Resume
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Settings_Resume extends WPJM_Pack_Admin_Settings {

	/**
	 * WPJM_Pack_Admin_Settings_Resume constructor.
	 *
	 * @param $type  WPJM_Pack_Resume
	 * @param $admin WPJM_Pack_Admin_Resume
	 */
	public function __construct( $type, $admin ){

		$this->admin = $admin;
		$this->type  = $type;

		add_filter( 'resume_manager_settings', array( $this, 'add_settings' ), 9999 );
		$this->add_custom_types();
	}

	/**
	 * Add Visibility Settings
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

		$singular = $this->get_label( 'resume', __( 'Resume', 'wp-job-manager-packages' ) );
		$plural   = $this->get_label( 'resume', __( 'Resumes', 'wp-job-manager-packages' ), FALSE );

		$notice = '<strong>' . __( 'To override any package configuration above ... ', 'wp-job-manager-packages' ) . '</strong>';

		$vis_settings = array(
			array(
				'name'     => 'job_manager_resume_visibility_dashboard_my_account',
				'std'      => '1',
				'label'    => sprintf( __( 'User Packages List Table', 'wp-job-manager-packages' ), $singular ),
				'cb_label' => sprintf( __( 'Yes, output the user\'s %1$s visibility packages list table on the My Account page', 'wp-job-manager-packages' ), $singular ),
				'desc'     => sprintf( __( 'When enabled, this will output the current user packages on the My Account page. If you disable this setting, use the <code>%s</code> shortcode to output the list table elsewhere.', 'wp-job-manager-packages' ), '[resume_visibility_dashboard]' ) . '<br />',
				'type'     => 'checkbox',
				'class'    => '',
			),
			array(
				'name'  => 'job_manager_resume_visibility_settings_view_name_separator',
				'text'  => sprintf( __( 'View %s Name', 'wp-job-manager-packages' ), $singular ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_resume_visibility_require_package_view_name',
				'std'        => '0',
				'label'      => __( 'View Name Package', 'wp-job-manager-packages' ),
				'cb_label'   => sprintf( __( 'Require %s package to view resumes names', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a %1$s package with view names permissions, for %1$s names to be visible.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-related' => 'wpjmpack-require-view_name', 'data-check' => 'wpjmpack-view_name-cb_dynamic' )
			),
			$this->get_ph_wp_editor_setting( 'view_name', null, array( 'view_name_resume_packages', 'view_name_resume_packages_url' ) ),
			array(
				'name'  => 'job_manager_resume_visibility_settings_view_separator',
				'text'  => sprintf( __( 'View %s', 'wp-job-manager-packages' ), $singular ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_resume_visibility_require_package_view',
				'std'        => '0',
				'label'      => sprintf( __( 'View %s', 'wp-job-manager-packages' ), $singular ),
				'cb_label'   => sprintf( __( 'Require %1$s package to view a single %1$s', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a %s package with view permissions, to view any single listing page.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'class'      => '',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic wpjmpack-cbd_view', 'data-related' => 'wpjmpack-require-view', 'data-check' => 'wpjmpack-view-cb_dynamic' )
			),
			$this->get_redirect_setting( 'view' ),
			$this->get_ph_wp_editor_setting( 'view', 'access-denied-single-resume.php', array( 'view_resume_packages', 'view_resume_packages_url' ) ),
			array(
				'name'  => 'job_manager_resume_visibility_settings_browse_separator',
				'text'  => sprintf( __( 'Browse %s', 'wp-job-manager-packages' ), $plural ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_resume_visibility_require_package_browse',
				'std'        => '0',
				'label'      => sprintf( __( 'Browse %s', 'wp-job-manager-packages' ), $plural ),
				'cb_label'   => sprintf( __( 'Require %s package to browse listings', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a package with browse permissions, to view the listing summary on the browse/list/search page.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic wpjmpack-cbd_browse', 'data-related' => 'wpjmpack-require-browse', 'data-check' => 'wpjmpack-browse-cb_dynamic' )
			),
			$this->get_redirect_setting( 'browse' ),
			$this->get_ph_wp_editor_setting( 'browse', 'access-denied-browse-resumes.php', array( 'browse_resume_packages', 'browse_resume_packages_url' ) ),
			array(
				'name'  => 'job_manager_resume_visibility_settings_contact_separator',
				'text'  => sprintf( __( 'Contact %1$s', 'wp-job-manager-packages' ), $singular ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'shopping basket'
			),
			array(
				'name'       => 'job_manager_resume_visibility_require_package_contact',
				'std'        => '0',
				'label'      => sprintf( __( 'Contact %1$s', 'wp-job-manager-packages' ), $singular ),
				'cb_label'   => sprintf( __( 'Require contact package to contact a %1$s', 'wp-job-manager-packages' ), $singular ),
				'desc'       => sprintf( __( 'If enabled, visitors will be required to have a %1$s package with contact permissions, to contact any single %1$s.', 'wp-job-manager-packages' ), $singular ) . '<br />',
				'type'       => 'checkbox',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic wpjmpack-cbd_contact', 'data-related' => 'wpjmpack-require-contact', 'data-check' => 'wpjmpack-contact-cb_dynamic' )
			),
			$this->get_ph_wp_editor_setting( 'contact', 'access-denied-contact-details.php', array( 'contact_resume_packages', 'contact_resume_packages_url' ) ),
		);

		$resume_type_colors = array(
			array(
				'name'  => 'job_manager_resume_visibility_settings_type_color_separator',
				'text'  => __( 'Type Colors', 'wp-job-manager-packages' ),
				'desc'  => '',
				'label' => '',
				'type'  => 'divider',
				'icon'  => 'paint brush'
			),
			array(
				'name'       => 'job_manager_resume_visibility_customize_type_colors',
				'std'        => '0',
				'label'      => __( 'Customize Type Colors', 'wp-job-manager-packages' ),
				'cb_label'   => __( 'Yes, enable custom type colors settings', 'wp-job-manager-packages' ),
				'desc'       => __( 'Enable to customize the type colors.', 'wp-job-manager-packages' ),
				'type'       => 'checkbox',
				'class'      => '',
				'attributes' => array( 'class' => 'wpjmpack-cb_dynamic', 'data-cshow' => 'wpjmpack-customize-typecolors', 'data-uchide' => 'wpjmpack-customize-typecolors' )
			),
			array(
				'name'    => 'job_manager_resume_visibility_sui_browse_color',
				'std'     => 'grey',
				'label'   => ' ↳ ' . __( 'Browse Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the browse type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class'   => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
			array(
				'name'    => 'job_manager_resume_visibility_sui_view_color',
				'std'     => 'teal',
				'label'   => ' ↳ ' . __( 'View Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the view type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class'   => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
			array(
				'name'    => 'job_manager_resume_visibility_sui_view_name_color',
				'std'     => 'olive',
				'label'   => ' ↳ ' . __( 'View Name Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the view name type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class'   => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
			array(
				'name'    => 'job_manager_resume_visibility_sui_contact_color',
				'std'     => 'blue',
				'label'   => ' ↳ ' . __( 'Contact Color', 'wp-job-manager-packages' ),
				'desc'    => __( 'Customize color to use for the the contact type', 'wp-job-manager-packages' ),
				'type'    => 'select',
				'class'   => 'wpjmpack-customize-typecolors',
				'options' => $this->get_sui_colors(),
			),
		);

		$orig_vis_settings = array(
			array(
				'name'  => 'job_manager_resume_visibility_settings_separator',
				'label' => '',
				'desc'  => '',
				'text'  => __( 'Capability Override Settings', 'wp-job-manager-packages' ),
				'type'  => 'divider',
			    'icon'  => 'wordpress'
			),
			array(
				'name'  => 'resume_manager_view_name_capability',
				'std'   => '',
				'label' => __( 'View Resume name Capability', 'wp-job-manager-packages' ),
				'type'  => 'input',
				'desc'  => $notice . sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view resumes names. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-packages' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
			),
			array(
				'name'  => 'resume_manager_browse_resume_capability',
				'std'   => '',
				'label' => __( 'Browse Resume Capability', 'wp-job-manager-packages' ),
				'type'  => 'input',
				'desc'  => $notice . sprintf( __( 'Enter the <a href="%s">capability</a> required in order to browse resumes. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-packages' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
			),
			array(
				'name'  => 'resume_manager_view_resume_capability',
				'std'   => '',
				'label' => __( 'View Resume Capability', 'wp-job-manager-packages' ),
				'type'  => 'input',
				'desc'  => $notice . sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a single resume. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-packages' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
			),
			array(
				'name'  => 'resume_manager_contact_resume_capability',
				'std'   => '',
				'label' => __( 'Contact Details Capability', 'wp-job-manager-packages' ),
				'type'  => 'input',
				'desc'  => $notice . sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view contact details on a resume. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-packages' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
			),
		);

		$settings['resume_visibility'] = array(
			__( 'Resume Visibility', 'wp-job-manager-packages' ),
			array_merge( $vis_settings, $this->get_redirect_settings(), $this->get_sui_settings(), $resume_type_colors, $orig_vis_settings ),
		);

		$resume_pages = array(
			array(
				'name'  => 'job_manager_resume_visibility_settings_separator_pages',
				'label' => '',
				'desc'  => '',
				'text'  => sprintf( __( '%1$s Visibility Pages', 'wp-job-manager-packages' ), $singular ),
				'type'  => 'divider',
				'icon'  => 'unhide'
			),
			array(
				'name'  => 'resume_manager_resume_visibility_packages_page_id',
				'std'   => '',
				'label' => __( 'Select Package Form Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[resume_visibility_packages]</code> shortcode. This lets the plugin know where the form is located.', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'resume_manager_browse_resume_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . __( 'Browse Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[browse_resume_packages]</code> shortcode, <strong>if you plan to use custom select browse package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'resume_manager_view_resume_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . __( 'View Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[view_resume_packages]</code> shortcode, <strong>if you plan to use custom select view package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'resume_manager_view_name_resume_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' . __( 'View Name Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[view_name_resume_packages]</code> shortcode, <strong>if you plan to use custom select view name package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
			array(
				'name'  => 'resume_manager_contact_resume_packages_page_id',
				'std'   => '',
				'label' => ' ↳ ' .  __( 'Contact Package Page', 'wp-job-manager-packages' ),
				'desc'  => __( 'Select the page where you have placed the <code>[contact_resume_packages]</code> shortcode, <strong>if you plan to use custom select contact package form page (optional).</strong>', 'wp-job-manager-packages' ),
				'type'  => 'page'
			),
		);

		$settings['resume_pages'][1] = array_merge( $settings['resume_pages'][1], $resume_pages );

		return $settings;
	}
}
