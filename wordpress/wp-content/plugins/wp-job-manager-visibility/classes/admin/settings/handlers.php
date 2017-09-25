<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Settings_Handlers extends WP_Job_Manager_Visibility_Admin_Settings_Fields {

	/**
	 * Settings Button Method Handler
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $input
	 *
	 * @return bool
	 */
	public function button_handler( $input ) {

		if( empty($_POST['button_submit']) || ($this->button_count > 0) ) return $input;

		$action = filter_input( INPUT_POST, 'button_submit', FILTER_SANITIZE_STRING );

		switch( $action ) {

			case 'install_default_groups':

				$result = WP_Job_Manager_Visibility_Install::add_default_groups();
				if( empty( $result ) ){
					$this->add_error_alert( __( 'Please make sure the default groups do not already exist, or there was an error creating them.', 'wp-job-manager-visibility' ) );
				} else {
					$added_groups = implode( ', ', $result );
					$this->add_updated_alert( __( 'These default groups were added succesfully: ', 'wp-job-manager-visibility' ) . $added_groups );
				}
				break;

			case 'job_update_permalinks':
				$result = WP_Job_Manager_Visibility_Permalinks::update_existing( 'job_listing' );

				if ( is_wp_error( $result ) ) {
					$this->add_error_alert( $result->get_error_message() );
				} else {
					$this->add_updated_alert( sprintf( __( 'Succesfully updated %s existing listing permalinks!', 'wp-job-manager-visibility' ), $result ) );
				}

				break;

			case 'resume_update_permalinks':
				$result = WP_Job_Manager_Visibility_Permalinks::update_existing( 'resume' );

				if ( is_wp_error( $result ) ) {
					$this->add_error_alert( $result->get_error_message() );
				} else {
					$this->add_updated_alert( sprintf( __( 'Succesfully updated %s existing resume listing permalinks!', 'wp-job-manager-visibility' ), $result ) );
				}

				break;
		}

		$this->button_count ++;

		return FALSE;

	}

	/**
	 * Settings Button Method Handler
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $input
	 * @param $option
	 *
	 * @return bool
	 */
	public function cache_button_handler( $input, $option ) {

		if ( empty( $_POST[ 'button_submit' ] ) || ( $this->process_count > 0 ) ) return $input;

		$action = filter_input( INPUT_POST, 'button_submit', FILTER_SANITIZE_STRING );

		switch ( $action ) {

			case 'cache_purge_all':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge();
				$user_cache->purge( FALSE );
				$this->add_updated_alert( __( 'All cache (with AND without expirations) has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_purge_user':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge_user();
				$this->add_updated_alert( __( 'All user config cache has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_flush_all':
				wp_cache_flush();
				wp_cache_init();
				$this->add_updated_alert( __( 'The core WordPress cache has been flushed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_purge_groups':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge_group();
				$this->add_updated_alert( __( 'All user group config cache has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

		}

		$this->process_count ++;

		return FALSE;

	}

	/**
	 * Add WP Updated Alert
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $message
	 */
	function add_updated_alert( $message ) {

		add_settings_error(
			$this->settings_group,
			esc_attr( 'settings_updated' ),
			$message,
			'updated'
		);

	}

	/**
	 * Add WP Error Alert
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $message
	 */
	function add_error_alert( $message ) {

		add_settings_error(
			$this->settings_group,
			esc_attr( 'settings_error' ),
			$message,
			'error'
		);

	}

	/**
	 * Settings Button Handler
	 *
	 * Default handler that gets executed whenever the options are saved
	 * as long a there isn't another method that matches {$field_type}_handler
	 *
	 * @since 1.1.0
	 *
	 * @param $input
	 * @param $option
	 *
	 * @return bool
	 */
	public function submit_handler( $input, $option ) {

		if( empty($input) ) return FALSE;

		return $input;

	}

	/**
	 * Checkboxes Handler
	 *
	 * We need to serialize the array before allowing it to save.
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function checkboxes_handler( $input ) {

		if( is_array( $input ) ) $input = maybe_serialize( array_values( $input ) );

		return $input;
	}
}