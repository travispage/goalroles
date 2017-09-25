<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Help_Default extends WP_Job_Manager_Visibility_Admin_Help {


	/**
	 * WP_Job_Manager_Visibility_Admin_Help_Default constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'default', 'post_type' );
		parent::__construct();

	}

	function init_config(){

		$this->tabs = array(
			'overview' => array(
				'title' => __( 'Overview', 'wp-job-manager-visibility' ),
			),
			'visible_fields' => array(
				'title' => __( 'Visible Fields', 'wp-job-manager-visibility' ),
			),
			'hidden_fields' => array(
				'title' => __( 'Hidden Fields', 'wp-job-manager-visibility' ),
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
		<p><?php _e( 'Default Visibilities are configurations you create for any user or group, that will be used for every listing on your site.  This allows you to configure fields you want visible or hidden to specific users or groups.  You can create unlimited visibility configurations to fine tweak everything exactly how you want it. Here\'s a few things to note:', 'wp-job-manager-visibility' ); ?></p>
		<ul>
			<li><strong><?php _e( 'User default visibility configurations ALWAYS take priority over any group configurations', 'wp-job-manager-visibility' ); ?></strong></li>
			<li><strong><?php _e( 'Group configurations are processed in order based on their priority', 'wp-job-manager-visibility' ); ?></strong></li>
		</ul>
		<p><?php _e('As a quick example, if you have a default visibility configuration for the user <code>JohnSmith</code>, and you set the <code>job_description</code> field as hidden...if you also have this user in a group, that has the <code>job_description</code> visible, because user configurations take priority, that user will <strong>NOT</strong> be able to see <code>job_description</code>.', 'wp-job-manager-visibility'); ?></p>

	<?php
	}

	function visible_fields(){
	?>
		<p><strong><?php _e( 'Visible Fields are fields that you want to be shown for this specific user or group.', 'wp-job-manager-visibility'); ?></strong></p>
		<p><?php _e('This is useful in situations where you have a user that is, as an example, apart of two groups, one with priority of 5 and one with priority of 10. Say the group with priority of 10 has the <code>job_description</code> field hidden ... if the group with priority of 5 has that same field set as visible, because the priority is lower than the group that hides the field, the <code>job_description</code> field will be shown.', 'wp-job-manager-visibility'); ?></p>

	<?php
	}

	function hidden_fields(){
	?>
		<p><strong><?php _e( 'Hidden Fields are fields that you do NOT want shown for a specific user or group', 'wp-job-manager-visibility' ); ?></strong></p>
		<p><?php _e( 'Setting hidden fields will probably be used the most, as this allows you to set fields to hide based on the user or group.  Hidden fields support what is called a <code>placeholder</code>.  The placeholder is the value you want to be shown instead of the actual value.', 'wp-job-manager-visibility' ); ?></p>
		<p><em><?php _e( 'HTML IS supported in placeholders!', 'wp-job-manager-visibility' ); ?></em></p>
		<p><strong><?php _e( 'Specific Meta Key Information:', 'wp-job-manager-visibility' ); ?></strong></p>
		<p><?php _e('Due to the way the core of WP Job Manager is coded, as well as how some meta keys are handled in templates and the core plugin, there are some caveots and neat ways that I have coded my plugin to handle specific meta keys.', 'wp-job-manager-visibility'); ?></p>

		<p><code>application</code> - <?php _e( 'If you do not enter a placeholder value, the <strong>Apply Now</strong> button will be hidden.  If you enter a placeholder, it will be shown, but when the user clicks the button, they will see your placehoolder value.', 'wp-job-manager-visibility' ); ?></p>
		<p><code>job_description</code> - <?php _e( 'By default the placeholder value will be shown instead of actual value ... if you want to output an excerpt of this field, go to the settings and enable excerpt for this meta key.  Any placeholder value will be appended (added to the end) of the excerpt.', 'wp-job-manager-visibility' ); ?></p>

	<?php
	}

}