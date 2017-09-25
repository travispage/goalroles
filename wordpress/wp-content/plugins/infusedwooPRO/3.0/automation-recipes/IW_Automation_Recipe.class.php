<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_Automation_Recipe {

	function __construct($recipe_id) {
		// get configs
		$this->config = get_post_meta( $recipe_id, 'iw_recipe_config',true );
		$recipe_post = get_post($recipe_id);

		// get actions
		$this->actions = get_post_meta( $recipe_id, 'iw_recipe_actions',true );
		$this->enabled = $recipe_post->post_status == 'iw-enabled';

		// get trigger
		$trigger_class = get_post_meta( $recipe_id, 'iw_trigger_class',true );
		$trigger = new $trigger_class($this->config, $this->actions);
		$this->trigger = $trigger;
	}

	function trigger_html() {
		$html = '<div class="iw-recipe-trigger">';
		$html .= '<div class="iw-trigger-icon">'.$this->trigger->get_icon() .'</div>';
		$html .= '<div class="iw-trigger-title">'.$this->trigger->get_desc() .'</div>';
		$html .= '</div>';

		return $html;
	}

	function action_html() {
		$num_actions = count($this->actions);
		$html = '<div class="iw-recipe-action"><div class="iw-action-icon num-actions-'.$num_actions.'">';
		

		if($num_actions > 5) {
			$html .= '<i class="fa fa-cog"></i>';
		} else {
			foreach($this->actions as $action) {
				$new_action = new $action;
				$html .= $new_action->get_icon();
			}
		}
		$html .= '</div>';

		$html .= '<div class="iw-action-title">';
		if($num_actions > 1) {
			$html .= 'Run ' . $num . ' actions';
		} else {
			foreach($this->actions as $action) {
				$new_action = new $action;
				$setting = isset($this->config['action_config'][$action]) ? isset($this->config['action_config'][$action]) : array();
				$html .= $new_action->get_desc($setting);
			}
		}
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}