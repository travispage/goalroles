<?php
/**
 * Plugin name: RCP - WP Job Manager Bridge
 * Plugin URI: https://restrictcontentpro.com/downloads/wp-job-manager/
 * Description: Limit job submission and / or application to paid subscribers in Restrict Content Pro
 * Author: Restrict Content Pro Team
 * Contributors: mordauk, mindctrl
 * Version: 2.1.3
 */


class RCP_WP_Job_Manager {

	/**
	 * @var RCP_WP_Job_Manager The one true RCP_WP_Job_Manager
	 * @since 1.0
	 */
	private static $instance;


	/**
	 * Main RCP_WP_Job_Manager Instance
	 *
	 * Insures that only one instance of RCP_WP_Job_Manager exists in memory at any one
	 * time.
	 *
	 * @var object
	 * @access public
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RCP_WP_Job_Manager ) ) {
			self::$instance = new RCP_WP_Job_Manager;
			self::$instance->init();
		}
		return self::$instance;
	}


	/**
	 * Setup filters and actions
	 *
	 * @access private
	 * @since 1.0
	 */
	private function init() {
		// Check for RCP and WP Job Manager
		if( ! function_exists( 'rcp_is_active' ) || ! class_exists( 'WP_Job_Manager' ) )
			return;


		// Require users to have an account to post a job
		add_filter( 'job_manager_user_requires_account', '__return_true' );

		// Users must register through RCP
		add_filter( 'job_manager_enable_registration', '__return_false' );

		// Check if user can post a job
		add_filter( 'job_manager_user_can_post_job', array( $this, 'user_can_post' ) );

		// Check if user can view resume contact info
		add_filter( 'resume_manager_user_can_view_resume', array( $this, 'user_can_view_resume' ), 10, 2 );
		add_filter( 'resume_manager_user_can_view_resume_name', array( $this, 'user_can_view_resume' ) );
		add_filter( 'resume_manager_user_can_view_contact_details', array( $this, 'user_can_view_resume' ), 10, 2 );

		// Show a message to users that don't have submission access
		add_action( 'submit_job_form_disabled', array( $this, 'disabled_submission_message' ) );

		// Add an input field to the Subscription levels Edit / Add screens to set the number of jobs allowed
		add_action( 'rcp_edit_subscription_form', array( $this, 'jobs_number_input' ) );
		add_action( 'rcp_add_subscription_form', array( $this, 'jobs_number_input' ) );

		// Save the jobs allowed subscription metadata
		add_action( 'rcp_edit_subscription_level', array( $this, 'save_jobs_input' ), 10, 2 );
		add_action( 'rcp_add_subscription', array( $this, 'save_jobs_input' ), 10, 2 );

		// Set the number of jobs the user has submitted
		add_action( 'job_manager_job_submitted', array( $this, 'set_jobs_submitted_count' ) );

		// Reset the number of jobs submitted when a payment is recorded
		add_action( 'rcp_insert_payment', array( $this, 'reset_jobs_submitted_count' ), 10, 3 );

	}

	/**
	 * Can the current user post jobs?
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function user_can_post() {

		if( $this->is_edit_screen() ) {
			return job_manager_user_can_edit_job( $_REQUEST['job_id'] );
		}

		return rcp_is_active() && ! $this->is_at_jobs_limit();
	}

	/**
	 * Can the current user view resume contact information?
	 *
	 * @param bool $can_view  Whether or not the user can view the resume.
	 * @param int  $resume_id The ID of the resume being checked.
	 *
	 * @access public
	 * @since 2.1
	 * @return bool
	 */
	public function user_can_view_resume( $can_view, $resume_id = 0 ) {

		if ( empty( $resume_id ) && isset( $_REQUEST['resume_id'] ) ) {
			$resume_id = $_REQUEST['resume_id'];
		}

		if ( ! empty( $resume_id ) && resume_manager_user_can_edit_resume( $resume_id ) ) {
			return true;
		}

		return rcp_is_active();
	}

	/**
	 * Display a message to users that can't post jobs
	 *
	 * @access public
	 * @since 1.0
	 */
	public function disabled_submission_message() {
		global $rcp_options;

		echo '<div class="rcp_wp_job_manager_submission_disabled">';

		if( $this->is_at_jobs_limit() ) {

			_e( 'You have submitted the maximum number of jobs for your current subscription period.' );

		} else {

			printf( __( 'You must have an active subscription to post jobs. <a href="%s">Register or upgrade an account</a>.' ), get_permalink( $rcp_options['registration_page'] ) );

		}

		echo '</div>';
	}

	/**
	 * Adds an input field to Add / Edit subscription level forms for inputting the number of jobs allowed
	 *
	 * @access public
	 * @since 2.0
	 */
	public function jobs_number_input( $level = false ) {

		if( ! empty( $level ) ) {
			$jobs_allowed = get_option( 'rcp_subscription_jobs_' . $level->id, 0 );
		} else {
			$jobs_allowed = 0;
		}
?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-jobs"><?php _e( 'Jobs Limit', 'rcp' ); ?></label>
			</th>
			<td>
				<input type="number" min="0" step="1" id="rcp-jobs" name="jobs" value="<?php echo esc_attr( $jobs_allowed ); ?>" style="width: 60px;"/>
				<p class="description"><?php _e( 'The number of jobs a member is allowed to submit per subscription period.', 'rcp' ); ?></p>
			</td>
		</tr>
<?php
	}

	/**
	 * Saves the job submission limit per period
	 *
	 * @access public
	 * @since 2.0
	 */
	public function save_jobs_input( $level_id = 0, $args = array() ) {

		if( ! isset( $_POST['jobs'] ) ) {
			return;
		}

		$jobs_allowed = absint( $_POST['jobs'] );
		update_option( 'rcp_subscription_jobs_' . $level_id, $jobs_allowed );

	}

	/**
	 * Increments the number of jobs a user has submitted
	 *
	 * @access public
	 * @since 2.0
	 */
	public function set_jobs_submitted_count( $job_id = 0 ) {

		$count = $this->get_job_count_for_period();
		$count++;
		update_user_meta( get_current_user_id(), 'rcp_jobs_submitted', $count );

	}

	/**
	 * Gets the number of jobs a user has submitted for the current period
	 *
	 * @access public
	 * @since 2.0
	 */
	public function get_job_count_for_period( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$count = get_user_meta( $user_id, 'rcp_jobs_submitted', true );

		return absint( $count );
	}

	/**
	 * Resets the number of jobs a user has submitted
	 *
	 * @access public
	 * @since 2.0
	 */
	public function reset_jobs_submitted_count( $payment_id = 0, $args = array(), $amount ) {

		delete_user_meta( $args['user_id'], 'rcp_jobs_submitted' );

	}

	/**
	 * Checks if the user is at their submission limit
	 *
	 * @access public
	 * @since 2.0
	 */
	public function is_at_jobs_limit( $user_id = 0 ) {

		$at_limit = false;

		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$subscription_id = rcp_get_subscription_id( $user_id );
		if( $subscription_id ) {

			$max_jobs  = absint( get_option( 'rcp_subscription_jobs_' . $subscription_id, 0 ) );
			$submitted = $this->get_job_count_for_period( $user_id );

			if( $max_jobs >= 0 && $submitted >= $max_jobs ) {
				$at_limit = true;
			}

		}

		return $at_limit;

	}

	/**
	 * Checks if the user is on the edit job screen
	 *
	 * @access public
	 * @since 2.0
	 */
	public function is_edit_screen() {

		$is_edit = ! empty( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'];

		return $is_edit;

	}

}
add_action( 'plugins_loaded', array( 'RCP_WP_Job_Manager', 'get_instance' ) );
