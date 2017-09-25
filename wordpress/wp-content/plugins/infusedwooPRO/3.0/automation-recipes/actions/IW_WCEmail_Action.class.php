<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WCEmail_Action extends IW_Automation_Action {
	function get_title() {
		return "Send Email (using Woocommerce Emailer)";
	}

	function allowed_triggers() {
		return array(
				'IW_AddToCart_Trigger',
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_PageVisit_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_WishlistEvent_Trigger',
				'IW_WooSubEvent_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		add_action( 'adm_automation_recipe_after', array($this, 'wc_email_tester'));
		add_action( 'wp_ajax_iwar_preview_email', array($this,'ajax_iwar_preview_email'));
		add_action( 'wp_ajax_iwar_send_test_email', array($this,'ajax_iwar_send_test_email'));

	}

	function display_html($config = array()) {
		global $iwpro;

		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-color' );
		wp_enqueue_script('editor');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('utils');

		$name = isset($config['name']) ? $config['name'] : '{{InfContact:FirstName}} {{InfContact:LastName}}';
		$email = isset($config['email']) ? $config['email'] : '{{InfContact:Email}}';
		$content = isset($config['content']) ? $config['content'] : '';
		$subject = isset($config['subject']) ? $config['subject'] : '';



		$html = '<table style="margin-top: 5px;">';
		$html .= '<tr>';
		$html .= '<td>Recipient Email </td>';
		$html .= '<td><input name="email" type="text" value="'.$email.'" placeholder="Email Address" style="width: 280px;" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field" ></i></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>Email Subject </td>';
		$html .= '<td><input name="subject" type="text" value="'.$subject.'" placeholder="Email Subject" style="width: 280px;" class="iwar-mergeable" />';
		$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field" ></i></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td><br>Email Content </td>';
		$html .= '<td><br><a href="#" class="tincymce_expand">Click to edit email content</a> <input type="hidden" value="'.$content.'" name="content" /></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>Preview Email </td>';
		$html .= '<td><a href="#" class="iwar_preview_email">Preview</a> | <a href="#" class="iwar_send_test">Send test email</a></td>';
		$html .= '</tr>';
		$html .= '</table>';

		return $html;
	}

	function validate_entry($config) {
		if(empty($config['email'])) {
			return "Please enter the recipient's email";
		} else if(empty($config['subject'])) {
			return "Please enter the email subject";
		}

	}

	function process($config, $trigger) {
		$email = $trigger->merger->merge_text($config['email']);
		$subject = $trigger->merger->merge_text($config['subject']);
		$content = str_replace( "\n", '<br />', $config['content'] ); 
		$content = $trigger->merger->merge_text($content);

		if(strpos($email, '@') !== false) {
			$headers = "Content-Type: text/html\r\n";
			$mailer = WC()->mailer();
			$mail = $mailer->emails['WC_Email_InfusedWoo'];
			$mail->trigger($email, $subject, $content, $headers);
		}
	}

	function wc_email_tester() {
		?>
		<script>
		jQuery('body').on('click',".iwar_preview_email", function(e) {
			$iwar_clicked = jQuery(this);
			e.preventDefault();
			swal({   
				title: "Preview Email",   
				text: "Email Address to test",   
				type: "input",   
				showCancelButton: true,   
				closeOnConfirm: false,   
				animation: "slide-from-top",   
				inputPlaceholder: "Email Address" 
			}, function(inputValue) {   
				if (inputValue === false) return false;      
				if (inputValue.indexOf('@') === -1) {     
					swal.showInputError("Enter a valid email");     
					return false;
				}
				var content = $iwar_clicked.closest('form').find('[name=content]').val();
				var subject = $iwar_clicked.closest('form').find('[name=subject]').val();
				swal.close();

				var admin_ajax = '<?php echo admin_url('admin-ajax.php') ?>';
				jQuery.post(admin_ajax + '?action=iwar_preview_email', {content: content,email:inputValue,subject:subject}, function(data){
					window.open(admin_ajax + '?action=iwar_preview_email','_blank');
				});
			});
		});

		jQuery('body').on('click',".iwar_send_test", function(e) {
			$iwar_clicked = jQuery(this);
			e.preventDefault();
			swal({   
				title: "Send Test Email",   
				text: "Email Address to test",   
				type: "input",   
				showCancelButton: true,   
				closeOnConfirm: false,   
				animation: "slide-from-top",   
				inputPlaceholder: "Email Address" 
			}, function(inputValue) {   
				if (inputValue === false) return false;      
				if (inputValue.indexOf('@') === -1) {     
					swal.showInputError("Enter a valid email");     
					return false;
				}
				var content = $iwar_clicked.closest('form').find('[name=content]').val();
				var subject = $iwar_clicked.closest('form').find('[name=subject]').val();
				swal("Sent!", "Test Email Sent.", "success")

				var admin_ajax = '<?php echo admin_url('admin-ajax.php') ?>';
				jQuery.post(admin_ajax + '?action=iwar_send_test_email', {content: content,email:inputValue,subject:subject}, function(data){
					
				});
			});
		});
		</script>
		<?php
	}

	function ajax_iwar_preview_email() {
		if(isset($_GET['add-to-cart'])) {
			header("Location: " . wc_get_cart_url());
			exit();
		} else if(is_admin()) {
			if($_POST) {
				set_transient( 'iwar_preview_email', $_POST, 3600 );
			} else {
				$iwar_preview_email = get_transient('iwar_preview_email');
				$trigger = new IW_Automation_Trigger;
				$trigger->user_email = $iwar_preview_email['email'];
				$trigger->merger = new IW_Automation_MergeFields($trigger);
				$content = $trigger->merger->merge_text($iwar_preview_email['content']);
				$subject = $trigger->merger->merge_text($iwar_preview_email['subject']);
				$mailer = WC()->mailer();
				$mail = $mailer->emails['WC_Email_InfusedWoo'];
				$mail->content = $content;
				$mail->subject = $subject;
				echo $mail->style_inline($mail->get_content());
			}
			exit();
		}
	}

	function ajax_iwar_send_test_email() {
		if(is_admin()) {
			if($_POST) {
				$iwar_preview_email = $_POST;
				$trigger = new IW_Automation_Trigger;
				$trigger->user_email = $iwar_preview_email['email'];
				$trigger->merger = new IW_Automation_MergeFields($trigger);
				$content = $trigger->merger->merge_text($iwar_preview_email['content']);
				$subject = $trigger->merger->merge_text($iwar_preview_email['subject']);
				$mailer = WC()->mailer();
				$mail = $mailer->emails['WC_Email_InfusedWoo'];
				$mail->content = $content;
				$mail->subject = $subject;
				$headers = "Content-Type: text/html\r\n";
				$mail->trigger($iwar_preview_email['email'], $subject, $content, $headers);
				echo 'sent: ' . print_r($iwar_preview_email);
			} 
		}
		exit();
	}

}

iw_add_action_class('IW_WCEmail_Action');