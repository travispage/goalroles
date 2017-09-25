<?php

if( ! function_exists( 'job_manager_packages_get_apply_label' ) ){
	/**
	 * Return Apply Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $lowercase
	 * @param string $append
	 * @param string $prepend
	 *
	 * @return mixed|void
	 */
	function job_manager_packages_get_apply_label( $lowercase = false, $append = '', $prepend = '' ){

		$label = __( 'Apply', 'wp-job-manager-packages' );

		if( $lowercase ){
			$label = strtolower( $label );
		}

		$label = $prepend . $label . $append;

		return apply_filters( 'job_manager_packages_get_apply_label', $label, $lowercase, $append, $prepend );

	}
}

if( ! function_exists( 'job_manager_packages_get_user_package' ) ){
	/**
	 * Get Job Manager Packages User Package Object
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $package
	 *
	 * @return \WPJM_Pack_User_Package
	 */
	function job_manager_packages_get_user_package( $package ){
		return new WPJM_Pack_User_Package( $package );
	}
}

if( ! function_exists( 'job_manager_get_job_post_type_label' ) ){
	/**
	 * Get Job Manager Job Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $plural
	 * @param string $fallback
	 *
	 * @return string|void
	 */
	function job_manager_get_job_post_type_label( $plural = false, $fallback = '' ){

		$fallback = ! empty( $fallback ) ? $fallback :  __( 'Job', 'wp-job-manager-packages' );

		$post_obj = get_post_type_object( 'job_listing' );

		if( $post_obj instanceof WP_Post_Type && $post_obj->labels ){
			$label = $plural ? $post_obj->labels->name : $post_obj->labels->singular_name;
		}

		$label = ! isset( $label ) ? $fallback : $label;

		return $label;

	}
}

if( ! function_exists( 'job_manager_get_post_type_label' ) ){
	/**
	 * Job Manager Get Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $post_type
	 * @param bool   $plural
	 * @param string $fallback
	 *
	 * @return string
	 */
	function job_manager_get_post_type_label( $post_type, $plural = false, $fallback = '' ){

		$post_obj = get_post_type_object( $post_type );

		if( $post_obj instanceof WP_Post_Type && $post_obj->labels ){
			$label = $plural ? $post_obj->labels->name : $post_obj->labels->singular_name;
		}

		$label = ! isset( $label ) ? ucfirst( $fallback ) : $label;

		return $label;

	}
}

if( ! function_exists( 'job_manager_get_resume_post_type_label' ) ){
	/**
	 * Get Resume Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $plural
	 * @param string $fallback
	 *
	 * @return string|void
	 */
	function job_manager_get_resume_post_type_label( $plural = false, $fallback = '' ){

		$fallback = ! empty( $fallback ) ? $fallback :  __( 'Resume', 'wp-job-manager-packages' );

		$post_obj = get_post_type_object( 'resume' );

		if( $post_obj instanceof WP_Post_Type && $post_obj->labels ){
			$label = $plural ? $post_obj->labels->name : $post_obj->labels->singular_name;
		}

		$label = ! isset( $label ) ? $fallback : $label;

		return $label;

	}
}

if( ! function_exists( 'get_job_manager_packages_template' ) ){
	/**
	 * Get Job Manager Packages Template
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return bool
	 */
	function get_job_manager_packages_template( $template_name, $args = array(), $template_path = 'jm_packages', $default_path = '' ){

		$template = locate_job_manager_packages_template( $template_name, $template_path, $default_path );

		if( ! $template || ! file_exists( $template )){
			return false;
		}

		if( $args && is_array( $args ) ){
			extract( $args );
		}

		include $template;
		return true;
	}
}

if( ! function_exists( 'locate_job_manager_packages_template' ) ){
	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *        yourtheme        /    $template_path    /    $template_name
	 *        $default_path    /    $theme_name       /    $template_name
	 *        $default_path    /    $template_name
	 *
	 * @param string      $template_name
	 * @param string      $template_path  (default: 'jm_packages')
	 * @param string|bool $default_path   (default: '') False to not load a default
	 * @param string      $theme_name     (default: '') Override theme name to use (leave blank to auto detect)
	 *
	 * @return string
	 */
	function locate_job_manager_packages_template( $template_name, $template_path = 'jm_packages', $default_path = '', $theme_name = '' ){

		// Look within passed path within the theme - this is priority
		$template = locate_template( trailingslashit( $template_path ) . $template_name );

		// Get default template
		if( ! $template && $default_path !== FALSE ){
			$default_path = $default_path ? $default_path : WP_Job_Manager_Packages::dir() . '/templates/';

			/**
			 * First let's check for a theme specific template override (auto detect if $theme_name not specified)
			 *
			 * Theme template structure should match /templates/themename/
			 */
			if( ! $theme_name && is_object( $theme = wp_get_theme() ) ){
				$theme_obj = $theme->parent() ? $theme->parent() : $theme;
				$name      = $theme_obj->get( 'Name' );
				// Use name if possible, otherwise fallback to textdomain
				$theme_name = ! empty( $name ) ? $name : $theme_obj->get( 'TextDomain' );
			}

			// Set $theme_template if $theme_name set and is valid string
			if( ! empty( $theme_name ) && is_string( $theme_name ) ){
				$theme_name     = strtolower( $theme_name );
				$theme_template = trailingslashit( $default_path ) . "{$theme_name}/{$template_name}";
				$template = file_exists( $theme_template ) ? $theme_template : false;
			}

			// If no theme template, set to default if one exists
			$template = ! $template && file_exists( trailingslashit( $default_path ) . $template_name ) ? trailingslashit( $default_path ) . $template_name : $template;
		}

		// Return what we found
		return apply_filters( 'job_manager_packages_locate_template', $template, $template_name, $template_path );
	}
}

if( ! function_exists( 'get_job_manager_packages_template_part' ) ){
	/**
	 * Get template part (for templates in loops).
	 *
	 * @param string      $slug
	 * @param string      $name          (default: '')
	 * @param string      $template_path (default: 'jm_packages')
	 * @param string|bool $default_path  (default: '') False to not load a default
	 */
	function get_job_manager_packages_template_part( $slug, $name = '', $template_path = 'jm_packages', $default_path = '' ){

		$template = '';

		if( $name ){
			$template = locate_job_manager_packages_template( "{$slug}-{$name}.php", $template_path, $default_path );
		}

		// If template file doesn't exist, look in yourtheme/jm_packages/slug.php
		if( ! $template ){
			$template = locate_job_manager_packages_template( "{$slug}.php", $template_path, $default_path );
		}

		if( $template ){
			load_template( $template, FALSE );
		}
	}
}

if( ! function_exists( 'get_job_manager_packages_form_template' ) ){
	/**
	 * Get form template
	 *
	 * This function checks the following template hierarchy, starting from top to bottom, and loads
	 * the first one it finds.  This includes support for theme specific templates as well.
	 *
	 * This is the load order:
	 *
	 *        Theme Directory:
	 *        -----------------
	 *        yourtheme / $template_path / $type-$slug-$template_name.php
	 *        yourtheme / $template_path / $type-$template_name.php
	 *        yourtheme / $template_path / $slug-$template_name.php
	 *        yourtheme / $template_path / $template_name.php
	 *
	 *        Default Path:
	 *        * $default_path if not specified is packages template directory
	 *        * examples below using `apply` for $type, `job` for $slug, and `package-form` for $template_name
	 *        ------------------
	 *        (apply-job-package-form.php)
	 *        $default_path / $theme_name / $type-$slug-$template_name.php
	 *        $default_path / $type-$slug-$template_name.php
	 *
	 *        (apply-package-form.php)
	 *        $default_path / $theme_name / $type-$template_name.php
	 *        $default_path / $type-$template_name.php
	 *
	 *        (job-package-form.php)
	 *        $default_path / $theme_name / $slug-$template_name.php
	 *        $default_path / $slug-$template_name.php
	 *
	 *        (package-form.php)
	 *        $default_path / $theme_name / $template_name.php
	 *        $default_path / $template_name.php
	 *
	 *
	 * @param             $type
	 * @param string      $slug
	 * @param string      $template_name
	 * @param array       $args
	 * @param string      $template_path (default: 'jm_packages')
	 * @param string|bool $default_path  (default: '') False to not load a default
	 *
	 * @return bool
	 */
	function get_job_manager_packages_form_template( $type, $slug, $template_name, $args = array(), $template_path = 'jm_packages', $default_path = '' ){

		if( array_key_exists( 'placeholder', $args ) && ! empty( $args['placeholder'] ) ){
			// Remove placeholder from args to prevent infinite loop
			unset( $args['placeholder'] );
			// Call back on self with added `ph-` in front of type
			if( get_job_manager_packages_form_template( "ph-{$type}", $slug, $template_name, $args, $template_path, $default_path ) ){
				// Return true if custom placeholder template found
				return true;
			}

			// Otherwise continue like normal ...
		}

		// First check for template with type and slug (ie apply-job-package-form.php)
		$template = locate_job_manager_packages_template( "{$type}-{$slug}-{$template_name}.php", $template_path, $default_path );

		// If template file doesn't exist, check for template file without slug (ie apply-package-form.php)
		if( ! $template ){
			$template = locate_job_manager_packages_template( "{$type}-{$template_name}.php", $template_path, $default_path );
		}

		// If template file doesn't exist, check for template file without type (ie job-package-form.php)
		if( ! $template ){
			$template = locate_job_manager_packages_template( "{$slug}-{$template_name}.php", $template_path, $default_path );
		}

		// Last resort, check for template without slug (ie package-form.php)
		if( ! $template ){
			$template = locate_job_manager_packages_template( "{$template_name}.php", $template_path, $default_path );
		}

		if( $template && file_exists( $template ) ){

			if( $args && is_array( $args ) ){
				extract( $args );
			}

			include $template;
			// Return true to confirm template was included
			return true;
		}
	}
}
if( ! function_exists( 'is_job_manager_packages_placeholder_form_only' ) ){
	/**
	 * Check if placeholder output is only the form shortcode
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $type
	 * @param $slug
	 *
	 * @return bool
	 */
	function is_job_manager_packages_placeholder_form_only( $type, $slug ){

		$output = get_option( "job_manager_{$slug}_visibility_require_package_{$type}_ph" );

		if( $output === "[{$slug}_visibility_packages]" || $output === "[{$type}_{$slug}_packages]" ){
			return TRUE;
		} else {
			return FALSE;
		}

	}
}