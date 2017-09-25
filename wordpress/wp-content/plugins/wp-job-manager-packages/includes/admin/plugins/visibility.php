<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_Plugins_Visibility
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_Plugins_Visibility {

	/**
	 * @var \WPJM_Pack_Plugins_Visibility
	 */
	public $integration;

	/**
	 * @var array $packages All combined packages
	 */
	public $packages             = array();
	/**
	 * @var array $job_packages All formatted job packages
	 */
	public $job_packages         = array();
	/**
	 * @var array $resume_packages All formatted Resume Packages
	 */
	public $resume_packages      = array();
	/**
	 * @var array $wcpl_job_packages All formatted WCPL job packages
	 */
	public $wcpl_job_packages    = array();
	/**
	 * @var array $wcpl_resume_packages All formatted WCPL resume packages
	 */
	public $wcpl_resume_packages = array();
	/**
	 * @var $job_label Job Post Type Label
	 */
	public $job_label;
	/**
	 * @var $resume_label Resume Post Type Label
	 */
	public $resume_label;

	/**
	 * WPJM_Pack_Admin_Plugins_Visibility constructor.
	 *
	 * @param $integration \WPJM_Pack_Plugins_Visibility
	 */
	public function __construct( $integration ){

		$this->integration = $integration;

		add_filter( 'jmv_groups_metaboxes', array( $this, 'group_metaboxes' ) );
		add_filter( 'jmv_groups_listtable_columns', array( $this, 'add_column' ) );
		add_filter( 'job_manager_visibility_settings', array( $this, 'settings' ) );

		add_action( 'jmv_groups_listtable_before_column_values', array( $this, 'column_output' ), 10, 2 );
	}

	/**
	 * Remove Promo Tab from Settings
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function settings( $settings ){

		if( array_key_exists( 'packages_promo', $settings ) ){
			unset( $settings['packages_promo'] );
		}

		return $settings;
	}

	/**
	 * Add Packages Column to Groups List Table
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_column( $columns ){

		$new_column = array( 'packages' => __( 'Packages', 'wp-job-manager-packages' ) );

		// Should be index 5, but just in case let's search for it
		$offset = array_search( 'roles', array_keys( $columns ), false );

		if( empty( $offset ) ){
			return array_merge( $columns, $new_column );
		}

		$new_columns = array_merge(
			array_slice( $columns, 0, $offset ),
			$new_column,
			array_slice( $columns, $offset, null )
		);

		return $new_columns;
	}

	/**
	 * Output Packages Column Values on Groups List Table
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function column_output( $column, $post_id ){

		if( $column !== 'packages' ){
			return;
		}

		$packages = get_post_meta( $post_id, 'group_packages' );

		if( is_array( $packages ) && ! empty( $packages ) ){
			echo implode( ', ', array_map( array( $this, 'format_column_output' ), $packages ) );
		}

	}

	/**
	 * Format Packages Column Output on Groups List Table
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $package_id
	 *
	 * @return string
	 */
	public function format_column_output( $package_id ){

		if( array_key_exists( $package_id, $this->get_job_packages() ) ){
			$output = $this->get_job_label() . ' ' . $this->job_packages[ $package_id ];
			return $output;
		}

		if( array_key_exists( $package_id, $this->get_resume_packages() ) ){
			$output = $this->get_resume_label() . ' ' . $this->resume_packages[ $package_id ];
			return $output;
		}

		if( array_key_exists( $package_id, $this->get_wcpl_job_packages() ) ){
			$output = 'WCPL ' . $this->get_job_label() . ' ' . $this->wcpl_job_packages[ $package_id ];
			return $output;
		}

		if( array_key_exists( $package_id, $this->get_wcpl_resume_packages() ) ){
			$output = 'WCPL ' . $this->get_resume_label() . ' ' . $this->wcpl_resume_packages[ $package_id ];
			return $output;
		}
	}

	/**
	 * Add Packages MetaBox to Groups Post Type
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $metaboxes
	 *
	 * @return mixed
	 */
	public function group_metaboxes( $metaboxes ){

		$metaboxes['packages'] = array(
			'id'            => 'packages_mb',
			'title'         => __( 'Packages', 'wp-job-manager-packages' ),
			'callback'      => array( $this, 'groups_mb' ),
			'screen'        => 'visibility_groups',
			'context'       => 'normal',
			'priority'      => 'high',
			'callback_args' => NULL
		);

		return $metaboxes;
	}

	/**
	 * Output Packages MetaBox in Groups Post Type
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $post
	 * @param $metabox
	 */
	public function groups_mb( $post, $metabox ){

		$selected_packages = get_post_meta( $post->ID, 'group_packages' );

		$vars = array(
			'post'                => $post,
			'job_label'           => $this->get_job_label(),
			'resume_label'        => $this->get_resume_label(),
			'job_packages'        => $this->get_job_packages(),
			'resume_packages'     => $this->get_resume_packages(),
			'wcpl_job_packages'        => $this->get_wcpl_job_packages(),
			'wcpl_resume_packages'     => $this->get_wcpl_resume_packages(),
			'existing_selections' => $selected_packages,
		);

		$view = 'admin/plugins/visibility/views/packages';

		WP_Job_Manager_Packages::include_view( $view, $vars );

	}

	/**
	 * Return Job Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_job_label(){

		if( empty( $this->job_label ) ){
			$this->job_label = job_manager_get_job_post_type_label();
		}

		return $this->job_label;
	}

	/**
	 * Return Resume Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_resume_label(){

		if( empty( $this->resume_label ) ){
			$this->resume_label = job_manager_get_resume_post_type_label();
		}

		return $this->resume_label;
	}

	/**
	 * Get Job Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_job_packages(){

		if( ! empty( $this->job_packages ) ){
			return $this->job_packages;
		}

		$job_packages = $this->integration->core->job->handler->get_packages();
		$this->job_packages = $this->format_packages( $job_packages );

		return $this->job_packages;
	}

	/**
	 * Get Resume Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_resume_packages(){

		if( ! empty( $this->resume_packages ) ){
			return $this->resume_packages;
		}

		$resume_packages = array();

		if( $this->integration->core->resume instanceof WPJM_Pack_Resume ){
			$resume_packages = $this->integration->core->resume->handler->get_packages();
		}

		$this->resume_packages = $this->format_packages( $resume_packages );

		return $this->resume_packages;
	}

	/**
	 * Get WCPL Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_wcpl_job_packages(){

		if( ! empty( $this->wcpl_job_packages ) ){
			return $this->wcpl_job_packages;
		}

		$job_packages = get_posts( array(
			                'post_type'        => 'product',
			                'posts_per_page'   => - 1,
			                'order'            => 'asc',
			                'orderby'          => 'menu_order',
			                'suppress_filters' => FALSE,
			                'tax_query'        => array(
				                array(
					                'taxonomy' => 'product_type',
					                'field'    => 'slug',
					                'terms'    => array( 'job_package', 'job_package_subscription' )
				                )
			                ),
			                'meta_query'       => array(
				                array(
					                'key'     => '_visibility',
					                'value'   => array( 'visible', 'catalog' ),
					                'compare' => 'IN'
				                )
			                )
		                ) );

		$this->wcpl_job_packages = $this->format_packages( $job_packages );

		return $this->wcpl_job_packages;
	}

	/**
	 * Get Resume WCPL Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_wcpl_resume_packages(){

		if( ! empty( $this->wcpl_resume_packages ) ){
			return $this->wcpl_resume_packages;
		}

		$resume_packages = get_posts( array(
			                'post_type'        => 'product',
			                'posts_per_page'   => - 1,
			                'order'            => 'asc',
			                'orderby'          => 'menu_order',
			                'suppress_filters' => FALSE,
			                'tax_query'        => array(
				                array(
					                'taxonomy' => 'product_type',
					                'field'    => 'slug',
					                'terms' => array( 'resume_package', 'resume_package_subscription' )
				                )
			                ),
			                'meta_query'       => array(
				                array(
					                'key'     => '_visibility',
					                'value'   => array( 'visible', 'catalog' ),
					                'compare' => 'IN'
				                )
			                )
		                ) );

		$this->wcpl_resume_packages = $this->format_packages( $resume_packages );

		return $this->wcpl_resume_packages;
	}

	/**
	 * Get All Packages
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_packages(){

		$job = $this->get_job_packages();
		$resume = $this->get_resume_packages();
		$wcpl = $this->get_wcpl_packages();

		return $job + $resume + $wcpl;
	}

	/**
	 * Format Packages from Post Object/Array for Output
	 *
	 * This method takes a normal WP_Post object and returns an array with the key
	 * as the ID of the post, and value as the Post Title
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array $posts  Posts to format output for (should be WP_Post objects)
	 *
	 * @return array
	 */
	public function format_packages( $posts = array() ){

		$formatted_packages = array();

		if( ! empty( $posts ) ){

			foreach( (array) $posts as $post ) {
				$formatted_packages[$post->ID] = $post->post_title;
			}

		}

		return $formatted_packages;
	}
}
