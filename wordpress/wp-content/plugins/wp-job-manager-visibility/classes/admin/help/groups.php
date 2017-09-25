<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Help_Groups extends WP_Job_Manager_Visibility_Admin_Help {


	/**
	 * WP_Job_Manager_Visibility_Admin_Help_Default constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'groups', 'post_type' );
		parent::__construct();

	}

	function init_config(){

		$this->tabs = array(
			'overview' => array(
				'title' => __( 'Overview', 'wp-job-manager-visibility' ),
			),
			'priority' => array(
				'title' => __( 'Priority', 'wp-job-manager-visibility' ),
			),
			'users' => array(
				'title' => __( 'Users', 'wp-job-manager-visibility' ),
			),
			'groups' => array(
				'title' => __( 'Groups', 'wp-job-manager-visibility' ),
			),
			'roles' => array(
				'title' => __( 'Roles', 'wp-job-manager-visibility' ),
			)
		);

		$this->screens = array(
			'new' => true,
			'edit' => true,
			'list' => false
		);

	}

	function overview(){
	?>
		<p>
			<?php _e('Groups can be used to create many different setups based on your specific needs. Groups will always have a lower priority than user specific configurations, so a group will never override a specific user configuration.', 'wp-job-manager-visibility'); ?>
		</p>
		<p>
			<?php _e( 'You can include multiple users, other groups, roles, or even any other addon configurations (such as packages, etc).', 'wp-job-manager-visibility' ); ?>
		</p>
	<?php
	}

	function priority(){
	?>
		<p>
			<strong><?php _e( 'Default priority is 10 (if not set).  The lower the number the higher the priority ( 1 is a higher priority than 5 )', 'wp-job-manager-visibility' ); ?></strong>
		</p>
		<p>
			<?php _e( 'Priorities are specifically used for groups to determine what group should take priority whenever there is a coflict.', 'wp-job-manager-visibility' ); ?>
		</p>
		<p>
			<?php _e( 'As an example, you have 2 different groups ( Employers [Priority 5], and Gold Package [Priority 1] ):', 'wp-job-manager-visibility' ); ?>
		</p>
		<p>
			<strong><?php _e( 'Employer Group (Priority 5) has these fields set to hide:', 'wp-job-manager-visibility' ); ?></strong> <em>candidate_phone, candidate_title, candidate_email</em>
		</p>
		<p>
			<strong><?php _e( 'Gold Package (Priority 1) has these fields set to show:', 'wp-job-manager-visibility' ); ?></strong> <em>candidate_phone, candidate_title</em>
		</p>
		<p>
			<?php _e('When someone visits your site and is a member of both of those groups, because Gold Package has a higher priority than Employer Group, the final result would be:', 'wp-job-manager-visibility'); ?>
		</p>
		<p>
			<strong><?php _e( 'Show Fields:', 'wp-job-manager-visibility' ); ?></strong> candidate_phone, candidate_title
		</p>
		<p>
			<strong><?php _e( 'Hide Fields:', 'wp-job-manager-visibility' ); ?></strong> candidate_email
		</p>
	<?php
	}

	function users(){
	?>
		<p>
			<?php _e( 'Include as many users as you want into a group.  Even if users are included in this group, if you have any user specific configurations they will take priority over this group configuration.', 'wp-job-manager-visibility' ); ?>
		</p>
		<p>
			<?php _e( '', 'wp-job-manager-visibility' ); ?>
		</p>
	<?php
	}

	function groups(){
	?>
		<p>
			<?php _e( 'Coming Soon...', 'wp-job-manager-visibility' ); ?>
		</p>
	<?php
	}

	function roles() {
	?>
		<p>
			<?php _e( 'You can include any registered roles into a group.  A special group has also been added called Anonymous which is used for any users that are not logged in.', 'wp-job-manager-visibility' ); ?>
		</p>
	<?php
	}
}