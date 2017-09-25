<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_ShowPopup_Action extends IW_Automation_Action {
	function get_title() {
		return "Show popup message";
	}

	function allowed_triggers() {
		return array(
				'IW_PageVisit_Trigger'			
			);
	}

	function on_class_load() {
		add_action( 'adm_automation_recipe_after', array($this, 'show_popup_admin_script'));
		add_action( 'wp_ajax_iwar_popup_submit', array($this,'popup_submit'), 10, 0);
		add_action(	'wp_ajax_nopriv_iwar_popup_submit', array($this,'popup_submit'), 10, 0);
	}

	function display_html($config = array()) {
		$title = isset($config['title']) ? $config['title'] : '';
		$notice = isset($config['notice']) ? $config['notice'] : '';
		$collect_email = isset($config['collect_email']) ? $config['collect_email'] : 'off';
		$post_url = isset($config['post_url']) ? $config['post_url'] : '';
		$submit_msg= isset($config['submit_msg']) ? $config['submit_msg'] : 'Thank you. Please check your emails.';


		$html = '<hr><span>Popup Title : <input autocomplete="off" type="text" name="title" value="'.$title.'" placeholder="Title" class="iwar-mergeable" style="width: 200px; margin-top: 5px;" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></span>';
		$html .= '<br><span>Message : <br> <input autocomplete="off" type="text" name="notice" value="'.$notice.'" placeholder="Message" class="iwar-mergeable" style="width: 380px; margin-top: 5px;" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></span>';
		$html .= '<hr><input type="hidden" name="collect_email" value="off" /><input autocomplete="off" type="checkbox" class="collect_email" name="collect_email"'.($collect_email == 'on' ? ' checked' : '').'/> Collect Customer Email';
		$html .= '<div class="post_url" '. ($collect_email == 'on' ? '': 'style="display:none;"' ).'>POST URL : <input type="text" name="post_url" value="'.$post_url.'" placeholder="http://" class="iwar-mergeable" style="width: 250px; margin-top: 5px;" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></span>';
		$html .= '<div style="margin-top: 8px;">Thank you message : <br><input type="text" name="submit_msg" value="'.$submit_msg.'" placeholder="Thank you..." class="iwar-mergeable" style="width: 350px; margin-top: 5px;" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i></span>';
		$html .= '</div></div>';
		$html .= '<div style="margin-top: 8px;"><a class="prev_popup" href="#" style="font-size: 10pt">Preview Popup</a></div>';
		return $html;
	}

	function show_popup_admin_script() {
		?>
		<script>
				jQuery("body").on("change",".collect_email", function(){
				if(jQuery(this).is(':checked')) {
					jQuery(this).parent().children('.post_url').show();
				} else {
					jQuery(this).parent().children('.post_url').hide();
				}
			});

			jQuery("body").on('click',".prev_popup", function(e) {
				e.preventDefault();
				<?php $this->popup_script('preview'); ?>
			});
		</script>
		<?php
	}

	function popup_script($set) {
		if($set == 'config') {
			$title = json_encode($this->trigger->merger->merge_text($this->config['title']));
			$msg = json_encode($this->trigger->merger->merge_text($this->config['notice']));
			$post_url = json_encode($this->trigger->merger->merge_text($this->config['post_url']));
			$submit_msg = isset($this->config['submit_msg']) ? json_encode($this->trigger->merger->merge_text($this->config['submit_msg'])) : '""';
		}
		?>
		

			var settings = {};

			<?php if($set == 'config') { ?>
				settings.title = <?php echo $title; ?>;
				settings.text = <?php echo $msg; ?>;
			<?php } else { ?>
				settings.title = jQuery(this).closest('form').find('[name=title]').val();
				if(settings.title == '') settings.title = 'Sample Title';

				settings.text = jQuery(this).closest('form').find('[name=notice]').val();
				if(settings.text == '') settings.text = 'Sample Text';
			<?php } ?>
			var fcn = null;

			settings.animation = 'slide-from-bottom';
			settings.allowOutsideClick = true;
			settings.confirmButtonColor = '#2CBB0F';

			<?php if($set != 'config') { ?>
				if(jQuery(this).closest('form').find('.collect_email').is(":checked")) {
			<?php } ?>

			<?php if($set != 'config' || $this->config['collect_email'] == 'on') { ?>
				settings.inputPlaceholder =  "Email Address";
				settings.showCancelButton = false;
				settings.closeOnConfirm = false;
				settings.confirmButtonText = 'Submit';
				settings.type = 'input';

				<?php if($set == 'config') { ?>
					settings.submit_msg = <?php echo $submit_msg; ?>;
					var submit_url = <?php echo $post_url; ?>;
				<?php } else { ?>
					settings.submit_msg = jQuery(this).closest('form').find('[name=submit_msg]').val();
					if(settings.submit_msg == '') settings.submit_msg = 'Thank you. Please check your email.';
					var submit_url = jQuery(this).closest('form').find('[name=post_url]').val();
				<?php } ?>

				fcn = function(inputValue) {
					 if (inputValue === false) return false;      
					 if (inputValue.indexOf('@') == -1) {     
					 	swal.showInputError("Please enter your email address");     
					 	return false;
					 }

					 jQuery.post('<?php echo admin_url('admin-ajax.php?action=iwar_popup_submit'); ?>', {email: inputValue, url: submit_url}, function() {

					 }); 
					 jQuery(document).ready(function() {
					 	swal("", settings.submit_msg, "success");
					 });
				}
			<?php } ?>

			<?php if($set != 'config') { ?>
				}
			<?php } ?>
			jQuery(document).ready(function() {
				swal(settings, fcn); 
			 });
		<?php
	}

	function validate_entry($config) {
		if(empty($config['title'])) return "Please enter title";
		if(empty($config['notice'])) return "Please enter message";
		if($config['collect_email'] == 'on' && empty($config['post_url'])) return "Please enter POST URL";

	}

	function process($config, $trigger) {
		$this->config = $config;
		$this->trigger = $trigger;

		add_action( 'wp_footer', array($this, 'show_popup'), 10, 0 );
		add_filter( 'iw_page_live_actions', array($this,'show_popup_live'), 10, 1 );
		add_filter('iwar_live_ran', array($this, 'iwar_live_ran'), 10, 1);
	}

	function show_popup_live($fcns) {
		ob_start();
		$this->popup_script('config');
		$eval = ob_get_clean();

		$fcns[] = array(
				'id'	=> $this->recipe_id_proc . '-' . $this->sequence_id,
				'to_eval' => 1,
				'eval' => $eval
			);

		return $fcns;
	}

	function show_popup() {
		wp_enqueue_style( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.css" );
		wp_enqueue_script( 'iw_sweetalert', INFUSEDWOO_PRO_URL . "assets/sweetalert/sweetalert.min.js", array('jquery'));
		ob_start();
		?>
		<script>
		<?php $this->popup_script('config'); ?>
		</script>
		<?php

		echo ob_get_clean();
	}

	function iwar_live_ran($ran) {
		$ran[] = $this->recipe_id_proc . '-' . $this->sequence_id;
		return $ran;
	}

	function popup_submit() {
		$fields_string = 'inf_field_Email=' . $_POST['email'];
		$url = $_POST['url'];

		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		curl_close($ch);
		echo $result;
		exit();
	}
}

iw_add_action_class('IW_ShowPopup_Action');