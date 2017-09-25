<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('iwpro_ready', 'ia_woo_gateway');

function ia_woo_gateway() {
	if (!class_exists('WC_Payment_Gateway')) return;

	if(class_exists('WC_Payment_Gateway_CC')) {
		class IW_Payment_Gateway extends WC_Payment_Gateway_CC { }
	} else {
		class IW_Payment_Gateway extends WC_Payment_Gateway { }
	}



	class IA_WooPaymentGateway extends IW_Payment_Gateway {
		public function __construct() { 
			global $iwpro;
	        $this->id			= 'infusionsoft';
	        $this->has_fields 	= false;
				
			// Load the form fields
			$this->init_form_fields();
			
			// Load the settings.
			$this->init_settings();

			// Get setting values
			$this->enabled 		= isset($this->settings['pgenabled']) ? $this->settings['pgenabled'] : '';
			$this->title 		= isset($this->settings['pgtitle']) ? $this->settings['pgtitle'] : "Infusionsoft";
			$this->description	= isset($this->settings['pgdescription']) ? $this->settings['pgdescription'] : '';
			$this->merchant		= isset($this->settings['pgmerchant']) ? $this->settings['pgmerchant'] : '';
			$this->cvv			= isset($this->settings['pgcvv']) ? $this->settings['pgcvv'] : '';
			$this->cardtypes	= isset($this->settings['pgcardtypes']) ? $this->settings['pgcardtypes'] : '';
			$this->remcc		= isset($this->settings['pgremcc']) ? $this->settings['pgremcc'] : '';
			$this->ti			= isset($this->settings['pgti']) ? $this->settings['pgti'] : '';
			$this->test			= isset($this->settings['pgtest']) ? $this->settings['pgtest'] : '';
			$this->icon			= isset($this->settings['pgicon']) ? (INFUSEDWOO_PRO_URL . 'images/' . $this->settings['pgicon']) : (INFUSEDWOO_PRO_URL . 'images/cards.png');
			$this->ui			= isset($this->settings['pgui']) ? $this->settings['pgui'] : '';

			// For InfusedWoo 2.0: Transaction ID support
			$appname = isset($iwpro->machine_name) ? $iwpro->machine_name : "";
			$this->view_transaction_url = "https://$appname.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=%s";

			add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );	
			add_action('woocommerce_get_customer_payment_tokens', array(&$this, 'token_saved_cc'),10,3);
			add_action('woocommerce_deposits_create_order', array($this, 'woo_deposits_pass_cc'), 10, 1 );
			
			// Hooks
			if( $this->enabled  == "yes" ) {
				add_action('woocommerce_receipt_authorize', array(&$this, 'receipt_page'));
				add_action('admin_notices', array(&$this,'ia_notices'));
			}

			$this->supports = array(
					'add_payment_method'
				);
	    }

	    public function get_icon() {
	    	$ext   = version_compare( WC()->version, '2.6', '>=' ) ? '.svg' : '.png';
			$style = version_compare( WC()->version, '2.6', '>=' ) ? 'style="margin-left: 0.3em"' : '';
	    	
	    	if(isset($this->settings['pgicon']) && $this->settings['pgicon'] == 'infusionsoft.png') {
	    		$icon  = '<img src="' . WC_HTTPS::force_https_url(INFUSEDWOO_PRO_URL . 'images/' . $this->settings['pgicon'] ) . '" alt="Visa" width="32" ' . $style . ' />';
	    	} else {
	    		$icon = '';
	    		if(is_array($this->cardtypes) && in_array("Visa", $this->cardtypes)) {
	    			$icon  .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext ) . '" alt="Visa" width="32" ' . $style . ' />';
	    		}

	    		if(is_array($this->cardtypes) && in_array("MasterCard", $this->cardtypes)) {
	    			$icon  .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext ) . '" alt="Visa" width="32" ' . $style . ' />';
	    		}

	    		if(is_array($this->cardtypes) && in_array("Discover", $this->cardtypes)) {
	    			$icon  .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover' . $ext ) . '" alt="Visa" width="32" ' . $style . ' />';
	    		}

	    		if(is_array($this->cardtypes) && in_array("American Express", $this->cardtypes)) {
	    			$icon  .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext ) . '" alt="Visa" width="32" ' . $style . ' />';
	    		}
			}

			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}

		function token_saved_cc($tokens, $customer_id, $gateway_id) {
			global $iwpro;

			if($gateway_id == 'infusionsoft' && empty($tokens)) {
				$tokens = $iwpro->ia_get_creditcards();
			} 

			return $tokens;
		}

		function ia_notices() {
			if(version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' )) 
				$pgurl = admin_url('admin.php?page=wc-settings&tab=checkout&section=ia_woopaymentgateway');
			else
				$pgurl = admin_url('admin.php?page=woocommerce_settings&tab=payment_gateways&section=IA_WooPaymentGateway');

		     if (get_option('woocommerce_force_ssl_checkout')=='no' && $this->enabled=='yes') :
		     	echo '<div class="error"><p>'.sprintf(__('Infusionsoft is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woothemes'), admin_url('admin.php?page=wc-settings&tab=checkout')).'</p></div>';
		     endif;
			 
			 if ($this->settings['pgtest'] == 'yes' && $this->enabled=='yes') :
		     	echo '<div class="error"><p>'.sprintf(__('<b>Infusionsoft Gateway Currently in Test Mode</b>: All orders will be approved without charging the Credit Card. Make sure to <a href="%s">turn this off</a> after debugging / testing', 'woothemes'), $pgurl).'</p></div>';
		     endif;

		}
				
		function init_form_fields() {
			global $iwpro;

	    	$this->form_fields = array(
				'pgenabled' => array(
								'title' => __( 'Enable/Disable', 'woothemes' ), 
								'label' => __( 'Enable Infusionsoft as Payment Gateway', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							), 
				'pgtitle' => array(
								'title' => __( 'Title', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'This controls the title which the user sees during checkout.', 'woothemes' ), 
								'default' => __( 'Credit card (Infusionsoft)', 'woothemes' )
							),
				'pgicon' => array(
								'title' => __( 'Icon', 'woothemes' ), 
								'type' => 'select', 
								'description' => __( 'Payment Gateway Icon to Show', 'woothemes' ), 
								'default' => __( 'cards.png', 'woothemes' ),
								'options' => array(
												'cards.png' 		=> __("Visa / MC / Amex / Disc", 'woothemes'),
												'infusionsoft.png' 	=> __("Infusionsoft Logo", 'woothemes')
											),								
							), 
				'pgdescription' => array(
								'title' => __( 'Description', 'woothemes' ), 
								'type' => 'textarea', 
								'description' => __( 'This controls the description which the user sees during checkout.', 'woothemes' ), 
								'default' => 'Pay with your credit card via Infusionsoft.'
							),  

				'pgmerchant' => array(
								'title' => __( 'Merchant Account ID', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( 'Merchant Account to Use <a target="_blank" href="http://infusedaddons.com/redir.php?to=merchantid">Click here for more info on how to get your merchant account ID.</a>', 'woothemes' ), 
								'default' => '',
								'class' => 'requirenum'
							), 
							
				'pgcardtypes'	=> array(
								'title' => __( 'Accepted Cards', 'woothemes' ), 
								'type' => 'multiselect', 
								'description' => __( 'Select which card types to accept.', 'woothemes' ), 
								'default' => '',
								'options' => array(
									'MasterCard'	=> 'MasterCard', 
									'Visa'			=> 'Visa',
									'Discover'		=> 'Discover',
									'American Express' => 'American Express'
									),
							),

				'pgcvv' => array(
								'title' => __( 'Require CVV on checkout?', 'woothemes' ), 
								'label' => __( 'Require CVV', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							), 							

							
				'pgremcc' => array(
								'title' => __( 'Allow customer to select saved credit cards from infusionsoft?', 'woothemes' ), 
								'label' => __( 'Remember Credit Cards?', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'yes'
							),
				'pgui' => array(
								'title' => __( 'Use advanced UI Elements?', 'woothemes' ), 
								'label' => __( 'If turned on, InfusedWoo will use advanced UI styles to make the checkout fields look better.<br><b> This may not be compatible to some wordpress themes.</b>', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							),

				'pgti' => array(
								'title' => __( 'Having issues with the theme?', 'woothemes' ), 
								'label' => __( 'Check if the Infusionsoft payment fields are not showing properly (old themes).', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							),
				'pgtest' => array(
								'title' => __( 'Turn on Test Mode?', 'woothemes' ), 
								'label' => __( 'If turned on, all orders will be approved (marked as paid in Infusionsoft) and credit card will not be charged.', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => '', 
								'default' => 'no'
							)

				);
	    }
		
		function validate_settings_fields( $form_fields = false ) { 
			 if ( ! $form_fields )
				 $form_fields = $this->form_fields;

			 $this->sanitized_fields = array();
			 $this->sanitized_fields = $this->settings;
	 
			 foreach ( $form_fields as $k => $v ) {
				 if ( ! isset( $v['type'] ) || ( $v['type'] == '' ) ) { $v['type'] == 'text'; } // Default to "text" field type.
	 
				if ( method_exists( $this, 'validate_' . $v['type'] . '_field' ) ) {
					 $field = $this->{'validate_' . $v['type'] . '_field'}( $k );
					$this->sanitized_fields[$k] = $field;
				 } else {
					 $this->sanitized_fields[$k] = $this->settings[$k];
				 }
			 }
			 
		 }
		
		
		public function admin_options() {
			if($this->settings['enabled'] != "yes") {
				?>
					<h3><?php _e('Infusionsoft','woothemes'); ?></h3>	
					<br>
					<div class="error" style="padding: 5px;">
						<?php _e('Infusionsoft Integration should be enabled to activate this payment gateway.', 'woothemes'); ?>
						<a target="_blank" href="<?php echo admin_url('admin.php?page=woocommerce_settings&tab=integration&section=infusionsoft'); ?>">
							<?php _e('Please enable infusionsoft integration','woothemes'); ?>
						</a>
						<?php echo _e('and refresh this page.', 'woothemes'); ?>
					</div>
				<?php
				return;
			}
			?>
			<h3><?php _e('Infusionsoft','woothemes'); ?></h3>	    	
	    	<p><?php _e( 'Infusionsoft works by adding credit card fields on the checkout and then sending the details to Infusionsoft for verification.', 'woothemes' ); ?></p>
	    	<table class="form-table">
	    		<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->    	
			<script>
				jQuery("form#mainform").submit(function() {
					if(jQuery("#woocommerce_infusionsoft_pgenabled").is(":checked")) {
						var errors = [];

						// check if credit card type selected
						if(jQuery("#woocommerce_infusionsoft_pgcardtypes").length > 0) {
							var selected_cards = jQuery("#woocommerce_infusionsoft_pgcardtypes").val();
							if(!selected_cards || selected_cards.length < 1) {
								errors.push('- Please select the type of the credit cards you would like to accept in the checkout page.');
							}
						}

						if(jQuery("#woocommerce_infusionsoft_pgmerchant").length > 0) {
							var merchant_id = jQuery("#woocommerce_infusionsoft_pgmerchant").val();
							if(merchant_id % 1 !== 0) {
								errors.push('- Merchant ID should be an integer. Contact Infusionsoft Support if you need help identifying your credit card processor\'s merchant ID.');
							}

							if(merchant_id == 0) {
								errors.push('- Merchant ID should be greater than 0');
							} 
						} 

						if(errors.length > 0) {
							var errormsg = "Cannot enable Infusionsoft Gateway due to errors: \n\n"+errors.join("\n");
							alert(errormsg);
							return false;
						}
					}	
				});
			</script>
	    	<?php
	    }
	
		function has_fields() {
			return true;
		}

		function payment_fields() {
			if( version_compare( WC()->version, '2.6', '>=' ) && $this->ti != 'yes') {
				global $iwpro;
				if ( $this->description ) {
					echo apply_filters( 'wc_infusionsoft_description', wpautop( wp_kses_post( $this->description ) ) );
				}

				if ( $this->remcc == 'yes' && !is_add_payment_method_page()) {
					$ccs = $iwpro->ia_get_creditcards();

					if(count($ccs) > 0) {
						$this->tokenization_script();
						$html = '<ul class="woocommerce-SavedPaymentMethods wc-saved-payment-methods" data-count="' . esc_attr( count( $ccs ) ) . '">';
						foreach($ccs as $k => $cc) {
							switch ($cc['CardType']) {
								case 'Visa': $icon_img = 'visa'; break;
								case 'MasterCard': $icon_img = 'mastercard'; break;
								case 'American Express': $icon_img = 'amex'; break;
								case 'Discover': $icon_img = 'discover'; break;
								default: $icon_img = ''; break;
								
							}

							if(!empty($icon_img)) {
								$icon = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/'.$icon_img.'.svg' ) . '" alt="'.$icon_img.'" width="32"  />';
							} else {
								$icon = $cc['CardType'];
							}
							$html .= sprintf(
								'<li class="woocommerce-SavedPaymentMethods-token">
									<input id="wc-%1$s-payment-token-%2$s" type="radio" name="wc-%1$s-payment-token" value="%2$s" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" %4$s />
									<label for="wc-%1$s-payment-token-%2$s">%3$s</label>
								</li>',
								esc_attr( $this->id ),
								esc_attr( $cc['Id'] ),
								($icon . __(" Card ending in ", 'woocommerce') . $cc['Last4'] . __(" expiring on ", 'woocommerce') . $cc['ExpirationMonth'] . '/' .$cc['ExpirationYear']  ),
								checked( $k == 0, true, false )
							);

						}
						$html .= $this->get_new_payment_method_option_html();
						$html .= '</ul>';

						echo $html;

					}
				}

				$this->form();
			} else {
				global $woocommerce;
				$pg = $this;
				
				include(INFUSEDWOO_PRO_DIR . 'modules/gatewayfields.php');
			}
		}

		public function validate_fields() {
			global $iwpro; 
			global $woocommerce;

			if($_POST) {

				if(session_id() == '') {
	            	session_start();
	        	}

				if($iwpro->ia_app_connect()) {
					global $woocommerce;		

					if(isset($_POST['infusionsoft-card-number'])) {
						$cardId 				= (int) $this->ia_get_post('wc-infusionsoft-payment-token');
						$cardNumber 			= trim(str_replace('+', '', $this->ia_get_post('infusionsoft-card-number')));
						switch (substr($cardNumber, 0, 1)) {
							case '4': $cardType = 'Visa'; break;
							case '5': $cardType = 'MasterCard'; break;
							case '3': $cardType = 'American Express'; break;
							case '6': $cardType = 'Discover'; break;
							default: $cardType = 'Other'; break;
						}

						$expiry = str_replace('+', '', $this->ia_get_post('infusionsoft-card-expiry'));
						$expiry = explode("/", $expiry);

						$cardExpirationMonth 	= isset($expiry[0]) ? trim($expiry[0]) : '';
						$cardExpirationYear 	= isset($expiry[1]) ? '20' . trim($expiry[1]) : '';

						$cardCSC 				= $this->ia_get_post('infusionsoft-card-cvc');
					} else {	
						$cardId 				= $this->ia_get_post('ia_cardId');
						$cardType 				= $this->ia_get_post('ia_cardtype');
						$cardNumber 			= $this->ia_get_post('ia_ccnum');
						$cardCSC 				= $this->ia_get_post('ia_cvv');
						$cardExpirationMonth 	= $this->ia_get_post('ia_expmonth');
						$cardExpirationYear 	= $this->ia_get_post('ia_expyear');
					}
						
					if( empty($cardId) || !empty($cardNumber) || $cardId == 0 ) { 			
						if ($this->cvv=='yes'){
							//check security code
							if(!ctype_digit($cardCSC)) {
								//$woocommerce->add_error(__('Card security code is invalid (only digits are allowed)', 'woocommerce'));
								wc_add_notice(__('Card security code is invalid (only digits are allowed)', 'woocommerce'),'error');
								return false;
							}
					
							if((strlen($cardCSC) != 3 && in_array($cardType, array('Visa', 'MasterCard', 'Discover'))) || (strlen($cardCSC) != 4 && $cardType == 'American Express')) {
								//$woocommerce->add_error(__('Card security code is invalid (wrong length)', 'woocommerce'));
								wc_add_notice(__('Card security code is invalid (wrong length)', 'woocommerce'),'error');
								return false;
							}
						}

						if(empty($cardType)) {
							//$woocommerce->add_error(__('Credit Card Type not specified.', 'woocommerce'));
							wc_add_notice(__('Credit Card Type not specified.', 'woocommerce'),'error');
							return false;
						}

						if(!in_array($cardType, $this->cardtypes)) {
							//$woocommerce->add_error(__('We only accept ' . implode(", ", $this->cardtypes) . __(' Card Types'), 'woocommerce'));
							wc_add_notice(__('We only accept ' . implode(", ", $this->cardtypes) . __(' Card Types'), 'woocommerce'),'error');
							return false;
						}
				
						//check expiration data
						$currentYear = date('Y');
						
						if(!ctype_digit($cardExpirationMonth) || !ctype_digit($cardExpirationYear) ||
							 $cardExpirationMonth > 12 ||
							 $cardExpirationMonth < 1 ||
							 $cardExpirationYear < $currentYear ||
							 $cardExpirationYear > $currentYear + 20
						) {
							//$woocommerce->add_error(__('Card expiration date is invalid', 'woocommerce'));
							wc_add_notice(__('Card expiration date is invalid', 'woocommerce'),'error');
							return false;
						}
				
						//check card number
						$cardNumber = str_replace(array(' ', '-'), '', $cardNumber);
				
						if(empty($cardNumber) || !ctype_digit($cardNumber)) {
							//$woocommerce->add_error(__('Card number is invalid', 'woocommerce'));
							wc_add_notice(__('Card number is invalid', 'woocommerce'),'error');
							return false;
						}	
						
						$this->ia_woocommerce_checkout_process();			
						$contactId = isset($_SESSION['ia_contactId']) ? (int) $_SESSION['ia_contactId'] : 0; 
						$card = array('CardType'		=>	$cardType,
									  'ContactId' 		=>	$contactId,
									  'CardNumber' 		=>	$cardNumber,
									  'ExpirationMonth' => 	$cardExpirationMonth,
									  'ExpirationYear' 	=> 	$cardExpirationYear,
									  'CVV2' 			=> 	$cardCSC );		
									  

						$result = $iwpro->app->validateCard($card);				

					
						if($result['Valid'] == 'false') {
							//$woocommerce->add_error(__(('Credit Card Error: ' . $result['Message']), 'woocommerce'));
							wc_add_notice(__(('Credit Card Error: ' . $result['Message']), 'woocommerce'),'error');
							return false;					
						} else {
							if(!is_add_payment_method_page()) {
								$this->app_generate_card($card);
							}	
						}
						
						return true;
					
					} else {
						$this->ia_woocommerce_checkout_process();
						$_SESSION['ia_cardId'] = (int) $cardId;
						return true;
					}
				}
			}
		}

		public function add_payment_method() {
			global $iwpro;

			if(!is_user_logged_in()) {
				wc_add_notice( __( 'There was a problem adding the card.', 'woocommerce' ), 'error' );
				return;
			} else {
				$cardNumber 			= trim(str_replace('+', '', $this->ia_get_post('infusionsoft-card-number')));
				switch (substr($cardNumber, 0, 1)) {
					case '4': $cardType = 'Visa'; break;
					case '5': $cardType = 'MasterCard'; break;
					case '3': $cardType = 'American Express'; break;
					case '6': $cardType = 'Discover'; break;
					default: $cardType = 'Other'; break;
				}

				$expiry = str_replace('+', '', $this->ia_get_post('infusionsoft-card-expiry'));
				$expiry = explode("/", $expiry);

				$cardExpirationMonth 	= isset($expiry[0]) ? trim($expiry[0]) : '';
				$cardExpirationYear 	= isset($expiry[1]) ? '20' . trim($expiry[1]) : '';

				$cardCSC 				= $this->ia_get_post('infusionsoft-card-cvc');

				$user_id = get_current_user_id();
				$wp_user = get_user_by( 'id', $user_id );

				$email = $wp_user->user_email;

				$contact = $iwpro->app->dsFind('Contact',1,0,'Email', $email, array('Id'));

				if(isset($contact[0]['Id'])) {
					$contactId = $contact[0]['Id'];
				} else {
					$contactId = $iwpro->app->dsAdd('Contact', array('Email' => $email));
				}

				if(!empty($contactId)) {
					$cc_fields = array(
						"ContactId" 		=> $contactId,
						"NameOnCard" 		=> get_user_meta( $user_id,'billing_first_name', true) . ' ' . get_user_meta( $user_id, 'billing_last_name', true),
						"FirstName" 		=> get_user_meta( $user_id, 'billing_first_name', true),
						"LastName" 			=> get_user_meta( $user_id, 'billing_last_name', true),
						"Email"				=> get_user_meta( $user_id, 'billing_email', true),		
						"CardType" 			=> $cardType,
						"CardNumber" 		=> $cardNumber,
						"ExpirationMonth" 	=> $cardExpirationMonth,
						"ExpirationYear" 	=> $cardExpirationYear,
						"BillName"			=> get_user_meta( $user_id,'billing_first_name', true) . ' ' . get_user_meta( $user_id, 'billing_last_name', true),
						"BillAddress1"		=> get_user_meta( $user_id,'billing_address_1',true),
						"BillAddress2"		=> get_user_meta( $user_id,'billing_address_2',true),
						"BillCity" 			=> get_user_meta( $user_id,'billing_city',true),
						"BillState"			=> get_user_meta( $user_id,'billing_state',true),
						"BillCountry"		=> iw_to_country(get_user_meta( $user_id,'billing_country',true)),
						"BillZip"			=> get_user_meta( $user_id,'billing_postcode',true),
						"ShipAddress1"		=> get_user_meta( $user_id,'shipping_address_1',true),
						"ShipAddress2"		=> get_user_meta( $user_id,'shipping_address_2',true),
						"ShipCity" 			=> get_user_meta( $user_id,'shipping_city',true),
						"ShipState"			=> get_user_meta( $user_id,'shipping_state',true),
						"ShipCountry"		=> iw_to_country(get_user_meta( $user_id,'shipping_country',true)),
						"ShipZip"			=> get_user_meta( $user_id,'shipping_postcode',true),
						"PhoneNumber"		=> get_user_meta( $user_id,'billing_phone',true),
						"CVV2"				=> $cardCSC,
						"Status"			=> 3			
					);
					

					$cc_id = $iwpro->app->dsAdd("CreditCard", $cc_fields);	
					do_action('iw_user_add_newcc', $cc_id, $user_id);
				}
			}

			return array(
				'result'   => 'success',
				'redirect' => wc_get_endpoint_url( 'payment-methods' ),
			);


		}

		function process_payment($order_id, $cardId = 0) {
			global $iwpro;
			global $woocommerce;

			if(session_id() == '') {
	            session_start();
	        }
			
			$order 		= new WC_Order( $order_id );			
			$inv_info 	= $this->app_generate_invoice($order);
			$merchant 	= (int) $this->merchant;
			$inv_id     = (int) $inv_info['inv_id'];

			$cardId		= empty($cardId) ? ((int) $_SESSION['ia_cardId']) : $cardId;
			
			
			if($this->test == "yes") {			
				$orderDate = date('Ymd\TH:i:s', current_time('timestamp'));			
				$totals = (float) $iwpro->app->amtOwed($inv_id);
				$iwpro->app->manualPmt($inv_id, $totals, $orderDate, 'Test Mode', "Woocommerce Checkout",false);
				$results['Code'] = "APPROVED";			
			}

			update_post_meta( $order_id, 'infusionsoft_cc_id', $cardId );

			$hasproduct = $inv_info['hasproduct'];

			update_post_meta( $order_id, 'infusionsoft_merchant_id', $merchant );
			if($this->test != "yes") {
				$results = $iwpro->app->chargeInvoice($inv_id,"Online Shopping Cart", $cardId, $merchant, false);
			}
			
			if(!is_array($results) && $hasproduct) {
				$errorText = (string) $results;
				$cancelNote = __($errorText, 'woocommerce');		
				$order->add_order_note( $cancelNote );				
				//$woocommerce->add_error(__($errorText, 'woocommerce'));
				wc_add_notice(__($errorText, 'woocommerce'),'error');
			} else if(((strtoupper($results['Code']) != "APPROVED") && ($results['Successful'] != true)) && $hasproduct && (strtoupper($results['Code']) != "SKIPPED")) {
				$errorText = "Ref# {$results['RefNum']} - {$results['Code']}: {$results['Message']}. ";
				$cancelNote = __($errorText, 'woocommerce');		
				$order->add_order_note( $cancelNote );
				update_post_meta($order_id, 'infusionsoft_merchant_refnum', $results['RefNum']); 					
				//$woocommerce->add_error(__($errorText, 'woocommerce'));
				wc_add_notice(__($errorText, 'woocommerce'),'error');
			} else {
				$subs 		= isset($_SESSION['ifs_woo_subs']) ? $_SESSION['ifs_woo_subs'] : "";
				$contactId 	= (int) $inv_info['contact_id'];
				$aff		= (int) $inv_info['is_aff']; 

				$subIds = array();

				if(!empty($subs)) {
					foreach($subs as $sub) {
						$nsubid = $iwpro->app->addRecurringAdv($contactId, true, $sub['id'], $sub['qty'], $sub['price'], false, $merchant, $cardId, $aff, $sub['nextbill']);

						if($sub['ncycles'] > 0) {
							if($sub['ncycles'] > 1) {
								$rcycles = $sub['ncycles']-1;
								switch($sub['cycle']) {						
									case 1: 
										$end_date =  date('Y-m-d', strtotime("+{$rcycles} years", $sub['nextbilldate'])); 
										break;						
									case 2: 
										$end_date =  date('Y-m-d', strtotime("+{$rcycles} months", $sub['nextbilldate'])); 
										break;							
									case 3: 
										$end_date =  date('Y-m-d', ($sub['nextbilldate']+$rcycles*7*24*3600)); 
										break;							
									case 6: 
										$end_date =  date('Y-m-d', ($sub['nextbilldate']+$rcycles*24*3600)); 
										break;
								}
							} else {
								$end_date = date('Y-m-d', $sub['nextbilldate']); 
							}

							$iwpro->app->dsUpdate('RecurringOrder', $nsubid, array('EndDate' => $end_date));
						}

						$subIds[] = $nsubid;
					}
					
					$subIdsText = implode(", ", $subIds);
					
					update_post_meta( $order->id, 'ia_subscriptions', $subs);
					unset($_SESSION['ifs_woo_subs']);	
				}

				if($hasproduct) $ordernote = "[INVOICE #{$inv_id}] Credit Card payment via infusionsoft completed";
				if(!empty($subs)) $ordernote .= " Subscriptions (IDs {$subIdsText}) successfully added and activated in Infusionsoft.";

				$refnum = isset($results['RefNum']) ? $results['RefNum'] : 0;
				update_post_meta($order_id, 'infusionsoft_invoice_id', $inv_id);
				update_post_meta($order_id, 'infusionsoft_merchant_refnum', $refnum); 			 
				
				$order->add_order_note( __($ordernote, 'woocommerce') );		
				
				//Add Order Notes				
				$jobid  = $iwpro->app->dsLoad("Invoice",$inv_id, array("JobId"));
				$jobid  = (int) $jobid['JobId'];
				$modify_order = array("JobNotes" => $order->customer_note, 'OrderType' => 'Online');

				if(!empty($order->shipping_first_name) && $order->shipping_first_name != $order->billing_first_name)
					$modify_order['ShipFirstName'] = $order->shipping_first_name;

				if(!empty($order->shipping_last_name) && $order->shipping_last_name != $order->billing_last_name)
					$modify_order['ShipLastName'] = $order->shipping_last_name;

				if(!empty($order->shipping_company) && $order->shipping_company != $order->billing_company)
					$modify_order['ShipCompany'] = $order->shipping_company;

				$modify_order = apply_filters( 'infusedwoo_modify_order', $modify_order, $order_id );
				$iwpro->app->dsUpdate("Job",$jobid, $modify_order);
				
				// Update Transaction ID in Woo
				update_post_meta($order_id, 'infusionsoft_order_id', $jobid);
				update_post_meta($order_id, '_transaction_id', $jobid);
				update_post_meta($order_id, 'infusionsoft_affiliate_id', $aff);
				$appname = isset($iwpro->machine_name) ? $iwpro->machine_name : "";
				update_post_meta($order_id, 'infusionsoft_view_order', "https://$appname.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=$jobid");
				
					
				$order->payment_complete();

				if($woocommerce->cart) {
					$woocommerce->cart->empty_cart();
				}

				// Empty awaiting payment session
				unset($_SESSION['order_awaiting_payment']);			
					
				// Return thank you redirect
				if(version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' )) {
					$tyredir = $this->get_return_url( $order );
				} else {
					$typageid = woocommerce_get_page_id('thanks');
					if($typageid == 0 || $typageid == -1) $typageid = get_option('woocommerce_thanks_page_id');
					$tyredir = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink($typageid))); 
				}

				do_action( 'infusedwoo_payment_complete_via_infusionsoft', $order->id, $jobid, $inv_id, $subIds, $contactId, $iwpro->app);
				
				return array(
					'result' 	=> 'success',
					'redirect'	=> $tyredir
				);
			}
		}

		function woo_deposits_pass_cc($order_id) {
			$orig_order_id = wp_get_post_parent_id($order_id);

			if($orig_order_id > 0) {
				$cc_id = get_post_meta( $orig_order_id, 'infusionsoft_cc_id', true );

				if(!empty($cc_id))
					update_post_meta( $order_id, 'infusionsoft_cc_id', $cc_id );
			}
		}

		#### HELPER FUNCTIONS #########

		function app_generate_invoice($order) {
			global $iwpro;
			global $woocommerce;

			if($iwpro->ia_app_connect()) {
				$email			= $order->billing_email;			
				$contact 		= $iwpro->app->dsFind('Contact',5,0,'Email',$email,array('Id')); 			
				
				if(is_array($contact) && count($contact) > 0) $contact 	= $contact[0];									
				
				if ($contact['Id'] != null && $contact['Id'] != 0 && $contact != false){				   
					$contactId = (int) $contact['Id']; 			
				} else {				
					$contactinfo	= array();				
					$contactinfo['Email'] = $email;				
					$contactId  = $iwpro->app->addCon($contactinfo);			
				}
				
				// CHECK AFFILIATE			
							
				$returnFields = array('AffiliateId');
				$referrals = $iwpro->app->dsFind('Referral',1000,0,'ContactId',(int) $contactId,$returnFields);
				$num = count($referrals);
				if($num > 0 && is_array($referrals)) $is_aff = $referrals[$num-1]['AffiliateId'];
				else $is_aff = 0;

				$is_aff = apply_filters( 'infusedwoo_get_affiliateid', $is_aff, $order);
				
				// CREATE INVOICE
				
				$orderDate = date('Ymd\TH:i:s', current_time('timestamp'));

				$order_num = $order->get_order_number();
				if(empty($order_num)) $order_num = $order->id;


				$order_title = apply_filters( 'infusedwoo_infusion_order_title', "Woocommerce Order # {$order_num}", $order );
				$inv_id = (int) $iwpro->app->blankOrder($contactId,$order_title,$orderDate,0,$is_aff);
				$calc_totals = 0;
				
				$products = $order->get_items(); 
				// PRODUCT LINE

				$subs	    = array();
				$hasproduct = false;

				foreach($products as $product) {
					global $woocommerce;
					$sku = "";
					$id  =  (int) $product['product_id'];
					$vid =  (int) $product['variation_id'];				
					
					$pid     = (int) get_post_meta($id, 'infusionsoft_product', true);
					$ifstype = get_post_meta($id, 'infusionsoft_type', true);
					$sdesc 	 = '';
					
					if( empty($pid) ) {
						if($vid != 0)   $sku = get_post_meta($vid, '_sku', true);
						if(empty($sku)) $sku = get_post_meta($id, '_sku', true);
						
						if(!empty($sku)) {
							$ifsproduct = $iwpro->app->dsFind('Product',1,0,'Sku',$sku, array('Id'));
							
							if(is_array($ifsproduct) && count($ifsproduct) > 0) $ifsproduct = $ifsproduct[0];
							
							if(!empty($ifsproduct)) $pid = (int) $ifsproduct['Id'];
							else if($this->settings['addsku'] == "yes") {
									$productname  = get_the_title($product['product_id']);
									$productprice = $product['line_subtotal'];								
									$newproduct = array('ProductName' 	=> $productname,
														'ProductPrice'  => $productprice,
														'Sku'     		=> $sku);
									$pid = (int) $iwpro->app->dsAdd("Product", $newproduct);
							} else $pid = 0;
						} else $pid = 0;						
					}

					// set product description
					$pdesc = ''; 
					if($pid == 0) $pdesc .= $product['name'] . " ";

					if($vid != 0) {
						$variation = wc_get_product($vid);

						$attribs = $variation->get_variation_attributes();
						$var_parent = wc_get_product($variation->get_parent_id());
						$all_attribs = $var_parent->get_attributes();

						$attribs_txt = array();

						foreach($attribs as $k => $v) {
							$key = str_replace('attribute_', '', $k);
							if(isset($all_attribs[$key])) {
								$label = isset($all_attribs[$key]['name']) ? $all_attribs[$key]['name'] : $all_attribs[$key];
								$attribs_txt[] = "$label: $v";
							}
						}

						$var_sku = get_post_meta($vid, '_sku', true);
						if(!empty($var_sku)) $attribs_txt[] = "SKU: $var_sku";
						
						$pdesc .= implode(", ", $attribs_txt);
					}

					$pdesc = apply_filters( 'infusedwoo_product_item_desc', $pdesc, $product['name'], $pid );

					$qty 	= (int) $product['qty'];
					$price 	= ((float) $product['line_total']) / ((float) $product['qty']);
					$price  = apply_filters( 'infusedwoo_product_price_calc', $price, $product );
	
					if(version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' )) {
						$sid   = (int) get_post_meta($id, 'infusionsoft_sub', true);
						if($sid > 0) {
							$subn = $iwpro->app->dsLoad('CProgram',$sid,array('ProductId'));
							$sub_prod_id = isset($subn['ProductId']) ? $subn['ProductId'] : 0;
							if($sub_prod_id != 0) $pid = (int) $sub_prod_id;
						}
						$iwpro->app->addOrderItem($inv_id, $pid, 4, round($price,2), $qty, $pdesc, $sdesc);
						$calc_totals += $qty * $price;		
						if($price > 0) $hasproduct = true;
					} else {	
						if($ifstype != 'Subscription') {
							$iwpro->app->addOrderItem($inv_id, $pid, 4, round($price,2), $qty, $pdesc, $sdesc);
							$calc_totals += $qty * $price;		
							if($price > 0) $hasproduct = true;
						} else {
							$packages 			= $woocommerce->shipping->packages;
							$selected_shipping 	= $order->shipping_method;
						
							$sid       = (int) get_post_meta($id, 'infusionsoft_sub', true);
							$trial     = (int) get_post_meta($id, 'infusionsoft_trial', true);					

							$returnFields = array('ProgramName','DefaultPrice','DefaultCycle','DefaultFrequency','ProductId');
							$sub 		  = $iwpro->app->dsLoad('CProgram',$sid,$returnFields);
							
							$sub_price = $iwpro->ia_get_sub_price($sid, $sub['DefaultPrice']);	
							$price 		  = $sub_price;	

							$sub_prod_id = isset($sub['ProductId']) ? $sub['ProductId'] : 0;
							if($sub_prod_id != 0) $pid = (int) $sub_prod_id;

							if($sid > 0) {
								
							
								if($trial == 0) {
									$iwpro->app->addOrderItem($inv_id, $pid, 4, round($product['line_subtotal'],2), $qty, $sub_prod_id, $sdesc);
									$calc_totals += $qty * $price;		
									
									switch($sub['DefaultCycle']) {						
										case 1: $nextbill = $sub['DefaultFrequency']*366; break;						
										case 2: $nextbill = $sub['DefaultFrequency']*30; break;						
										case 3: $nextbill = $sub['DefaultFrequency']*7; break;						
										case 6: $nextbill = $sub['DefaultFrequency']*1; break;					
									}

									$hasproduct = true;
								} else $nextbill = $trial;		

								$shipping_fee 	= 0;
								$tax_fee		= (float) $product['line_subtotal_tax'];
								
								foreach($packages as $package) {
									foreach($package['contents'] as $content) {
										if($content['product_id'] == $id) {
											if($package['trialdays'] > 0 && !empty($package['rates'][$selected_shipping]->subcost)) {
												$shipping_fee += $package['rates'][$selected_shipping]->subcost;
												foreach($package['rates'][$selected_shipping]->subtaxes as $tax) 
													$tax_fee += $tax;					
											} else {
												$shipping_fee += $package['rates'][$selected_shipping]->cost;
												foreach($package['rates'][$selected_shipping]->taxes as $tax) 
													$tax_fee += $tax;
											}
										}
									}
								}
								

								$sub_total = $price + ($shipping_fee + $tax_fee)/((float) $qty);

								$nsub = $iwpro->app->dsLoad('SubscriptionPlan', $sid, array('NumberOfCycles'));
								$ncycles = isset($nsub['NumberOfCycles']) ? $nsub['NumberOfCycles'] : 0;
								
								$subs[]  = array('id' 	 		=> (int) $sid,
												 'qty'	 		=> (int) $qty,
												 'nextbill' 	=> (int) $nextbill,
												 'program'		=> $sub['ProgramName'],
												 'price' 		=> (float) $sub_total, 
												 'nextbilldate' => (time() + 24*60*60*$nextbill),
												 'cycle'		=> $sub['DefaultCycle'],
												 'freq'			=> $sub['DefaultFrequency'],
												 'ncycles'		=> $ncycles
											 );						
							}

						}
					}
				}

				// SHIPPING LINE
				$s_method = (string) $order->get_shipping_method();  
				$s_total  = (float)  $order->get_total_shipping();
				if($s_total > 0.0) {
					$iwpro->app->addOrderItem($inv_id, 0, 14, round($s_total,2), 1, $s_method,$s_method);
					$calc_totals += $s_total;	
				}

				// Custom Fees
				$fees = $order->get_fees();
				if(count($fees) > 0) foreach($fees as $fee) {
					$f_a = isset($fee['line_subtotal']) ? $fee['line_subtotal'] : $fee['line_total'];
					$fee_amount = round((float) $f_a, 2);
					$iwpro->app->addOrderItem($inv_id, 0, 13, $fee_amount, 1, $fee['name'], $fee['name']);
					$calc_totals += $fee_amount;
				}

				// TAX LINE
				$cart_tax = (float) $order->get_total_tax();
				
				if(count($subs) > 0) {			
					foreach($woocommerce->cart->cart_contents as $item) {
						$ifstype  = get_post_meta($item['product_id'], 'infusionsoft_type', true);
						if($ifstype == 'Subscription') {
							$trial = (int) get_post_meta($item['product_id'], 'infusionsoft_trial', 	true);	
							
							if($trial > 0) {
								$cart_tax -= $item['line_subtotal_tax'];
							}						
						}
					}	
				
				}
				
				
				if($cart_tax > 0.0) {
					$iwpro->app->addOrderItem($inv_id, 0, 2, round($cart_tax,2), 1, 'Tax','');
					$calc_totals += $cart_tax;	
				}
				


				//coupon line
				$discount = (float) ($calc_totals - $order->get_total());
				if ( round($discount,2) > 0.00 ) {
				  $used_coup = $order->get_used_coupons();
				  $coupon_desc = "Discount";
			  	  if(is_array($used_coup)) $coupon_desc = implode(",", $used_coup);
				  $iwpro->app->addOrderItem($inv_id, 0, 7, -round($discount,2), 1, $coupon_desc, 'Woocommerce Coupon Code');
				  $calc_totals -= $discount;		  
				} 
						
				return array(
						'inv_id' => $inv_id,
						'contact_id' => $contactId,
						'hasproduct' => $hasproduct,
						'is_aff'	=> $is_aff
					);
			}
		}
		

		function ia_woocommerce_checkout_process() {
			global $iwpro;
			global $woocommerce;

			if($iwpro->ia_app_connect()) {					
				$returnFields 	= array('Id');	
				$shiptobilling 	= (int) $this->ia_get_post('shiptobilling');
				$shiptobilling  = $shiptobilling || !((int) ia_get_post('ship_to_different_address'));


				
				// GET COUNTRY
				$email			= $this->ia_get_post('billing_email');
				if(strpos($email, '@') === false || strpos($email, '.') === false) return;
				
				$contact 		= $iwpro->app->dsFind('Contact',5,0,'Email',$email,$returnFields); 
				if(is_array($contact) && count($contact) > 0) $contact = $contact[0];
					
				$firstName		= $this->ia_get_post('billing_first_name');
				$lastName		= $this->ia_get_post('billing_last_name');
				$phone			= $this->ia_get_post('billing_phone');
				
				$b_address1		= $this->ia_get_post('billing_address_1');
				$b_address2		= $this->ia_get_post('billing_address_2');
				$b_city			= $this->ia_get_post('billing_city');
				$b_state		= $this->ia_get_post('billing_state');
				$b_country		= iw_to_country($this->ia_get_post('billing_country'));
				$b_zip			= $this->ia_get_post('billing_postcode');
				$b_company		= $this->ia_get_post('billing_company');
				
				$s_address1		= $shiptobilling ?	$b_address1 : $this->ia_get_post('shipping_address_1');
				$s_address2		= $shiptobilling ? 	$b_address2	: $this->ia_get_post('shipping_address_2');
				$s_city			= $shiptobilling ? 	$b_city		: $this->ia_get_post('shipping_city');
				$s_state		= $shiptobilling ? 	$b_state	: $this->ia_get_post('shipping_state');
				$s_country		= $shiptobilling ? 	$b_country	: iw_to_country($this->ia_get_post('shipping_country'));
				$s_zip			= $shiptobilling ? 	$b_zip		: $this->ia_get_post('shipping_postcode');
				
				// Company Selector
				$compId = 0;
				if(!empty($b_company)) {
					$company 		= $iwpro->app->dsFind('Company',5,0,'Company',$b_company,array('Id')); 
					if(is_array($company) && count($company) > 0) $company 	= $company[0];
					
					if ($company['Id'] != null && $company['Id'] != 0 && $company != false){							
						$compId = $company['Id'];						
					} else {
						$companyinfo = array('Company' => $b_company);
						$compId = $iwpro->app->dsAdd("Company", $companyinfo);
					}
				}
				
				// CONTACT INFO
				$contactinfo = array();
				if(!empty($firstName)) $contactinfo['FirstName'] = stripslashes($firstName);
				if(!empty($lastName)) $contactinfo['LastName'] = stripslashes($lastName);
				if(!empty($phone)) $contactinfo['Phone1'] = stripslashes($phone);
				if(!empty($b_address1)) $contactinfo['StreetAddress1'] = stripslashes($b_address1);
				if(!empty($b_address2)) $contactinfo['StreetAddress2'] = stripslashes($b_address2);
				if(!empty($b_city)) $contactinfo['City'] = stripslashes($b_city);
				if(!empty($b_state)) $contactinfo['State'] = stripslashes($b_state);
				if(!empty($b_country)) $contactinfo['Country'] = stripslashes($b_country);
				if(!empty($b_zip)) $contactinfo['PostalCode'] = stripslashes($b_zip);
				if(!empty($s_address1)) $contactinfo['Address2Street1'] = stripslashes($s_address1);
				if(!empty($s_address2)) $contactinfo['Address2Street2'] = stripslashes($s_address2);
				if(!empty($s_city)) $contactinfo['City2'] = stripslashes($s_city);
				if(!empty($s_state)) $contactinfo['State2'] = stripslashes($s_state);
				if(!empty($s_country)) $contactinfo['Country2'] = stripslashes($s_country);
				if(!empty($s_zip)) $contactinfo['PostalCode2'] = $s_zip;
				if(!empty($b_company)) $contactinfo['Company'] = 	stripslashes($b_company);
				if(!empty($compId)) $contactinfo['CompanyID'] = $compId;
				$contactinfo['ContactType'] = 'Customer';
					
				if(isset($_SESSION['leadsource']) && !empty($_SESSION['leadsource'])) $contactinfo['Leadsource'] = 	$_SESSION['leadsource'];
			
				// GET CONTACT ID
				if ($contact['Id'] != null && $contact['Id'] != 0 && $contact != false){
					   $contactId = (int) $contact['Id']; 
					   $contactId = $iwpro->app->updateCon($contactId, $contactinfo);
				} else {
					$contactinfo['Email'] = $email;
					$contactId  = $iwpro->app->addCon($contactinfo);
					$iwpro->app->optIn($email,"API: User purchased from shop.");
				}
				
				// CREATE REFERRAL: CHECK AFFILIATE													
				$is_aff = isset($_COOKIE['is_aff']) ? (int) $_COOKIE['is_aff'] : "";				
				if( empty($is_aff) ) {					
					if(!empty( $_COOKIE['is_affcode'])) {						
						$returnFields 	= array('Id');						
						$affiliate 		= $iwpro->app->dsFind('Affiliate',1,0,'AffCode', $_COOKIE['is_affcode'], $returnFields);								
						
						if(is_array($affiliate) && count($affiliate) > 0) {
							$affiliate = $affiliate[0];
							$is_aff = (int) $affiliate['Id'];
						} else $is_aff = 0;							

					}							
				}							

				if( !empty($is_aff) ) {
					$iwpro->app->dsAdd('Referral', array(			
						'ContactId'   => $contactId,				
						'AffiliateId' => $is_aff,				
						'IPAddress'   => $_SERVER['REMOTE_ADDR'],		
						'Type'	  	  => 0,
						'DateSet'	  => date("Y-m-d")
						)					
					);								
				}
			
				$_SESSION['ia_contactId']  = $contactId;	
			}
		}

		function app_generate_card($card) {
			global $iwpro;
			global $woocommerce;

			if($iwpro->ia_app_connect()) {			
						
				$contactId = $card['ContactId'];

				//locatefirst if card exists.						
				$cardnum 	= 	$card['CardNumber'];
				$last4 		=	substr($cardnum, strlen($cardnum)-4, 4);
				$ccidcheck 	= 	$iwpro->app->locateCard($contactId, $last4);
				
				if(empty($ccidcheck) || $ccidcheck == 0) {
					$cc_fields = array(
						"ContactId" 		=> $contactId,
						"NameOnCard" 		=> $this->ia_get_post('billing_first_name') . ' ' . $this->ia_get_post('billing_last_name'),
						"FirstName" 		=> $this->ia_get_post('billing_first_name'),
						"LastName" 			=> $this->ia_get_post('billing_last_name'),
						"Email"				=> $this->ia_get_post('billing_email'),		
						"CardType" 			=> $card['CardType'],
						"CardNumber" 		=> $cardnum,
						"ExpirationMonth" 	=> $card['ExpirationMonth'],
						"ExpirationYear" 	=> $card['ExpirationYear'],
						"BillName"			=> $this->ia_get_post('billing_first_name') . ' ' . $this->ia_get_post('billing_last_name'),
						"BillAddress1"		=> $this->ia_get_post('billing_address_1'),
						"BillAddress2"		=> $this->ia_get_post('billing_address_2'),
						"BillCity" 			=> $this->ia_get_post('billing_city'),
						"BillState"			=> $this->ia_get_post('billing_state'),
						"BillCountry"		=> iw_to_country($this->ia_get_post('billing_country')),
						"BillZip"			=> $this->ia_get_post('billing_postcode'),
						"ShipAddress1"		=> $this->ia_get_post('shipping_address_1'),
						"ShipAddress2"		=> $this->ia_get_post('shipping_address_2'),
						"ShipCity" 			=> $this->ia_get_post('shipping_city'),
						"ShipState"			=> $this->ia_get_post('shipping_state'),
						"ShipCountry"		=> iw_to_country($this->ia_get_post('shipping_country')),
						"ShipZip"			=> $this->ia_get_post('shipping_postcode'),
						"PhoneNumber"		=> $this->ia_get_post('billing_phone'),
						"CVV2"				=> $card['CVV2'],
						"Status"			=> 3			
					);
					
					$cc_id = $iwpro->app->dsAdd("CreditCard", $cc_fields);	
					$_SESSION['ia_cardId'] = (int) $cc_id;	
					
				} else {
					$cc_fields = array(
						"NameOnCard" 		=> $this->ia_get_post('billing_first_name') . ' ' . $this->ia_get_post('billing_last_name'),
						"FirstName" 		=> $this->ia_get_post('billing_first_name'),
						"LastName" 			=> $this->ia_get_post('billing_last_name'),
						"Email"				=> $this->ia_get_post('billing_email'),	
						"ExpirationMonth" 	=> $card['ExpirationMonth'],
						"ExpirationYear" 	=> $card['ExpirationYear'],
						"BillName"			=> $this->ia_get_post('billing_first_name') . ' ' . $this->ia_get_post('billing_last_name'),
						"BillAddress1"		=> $this->ia_get_post('billing_address_1'),
						"BillAddress2"		=> $this->ia_get_post('billing_address_2'),
						"BillCity" 			=> $this->ia_get_post('billing_city'),
						"BillState"			=> $this->ia_get_post('billing_state'),
						"BillCountry"		=> iw_to_country($this->ia_get_post('billing_country')),
						"BillZip"			=> $this->ia_get_post('billing_postcode'),
						"ShipAddress1"		=> $this->ia_get_post('shipping_address_1'),
						"ShipAddress2"		=> $this->ia_get_post('shipping_address_2'),
						"ShipCity" 			=> $this->ia_get_post('shipping_city'),
						"ShipState"			=> $this->ia_get_post('shipping_state'),
						"ShipCountry"		=> iw_to_country($this->ia_get_post('shipping_country')),
						"ShipZip"			=> $this->ia_get_post('shipping_postcode'),
						"PhoneNumber"		=> $this->ia_get_post('billing_phone'),
						"CVV2"				=> $card['CVV2']
					);
					
					$iwpro->app->dsUpdate("CreditCard", (int) $ccidcheck, $cc_fields);
					$_SESSION['ia_cardId'] = (int) $ccidcheck;			
				}
			}		 
		}

		#### UTILITY FUNCTIONS #####

		function receipt_page( $order ) {
			echo '<p>'.__('Thank you for your order.', 'woocommerce').'</p>';		
		}

		function ia_get_post($name) {
			if(isset($_POST[$name])) {
				return $_POST[$name];
			}
			return NULL;
		}

		
		function testmailme($msg) {
			$to      = 'infusedmj@gmail.com';
			$subject = 'debug msg';
			$message = $msg;
			$headers = 'From: infusedmj@gmail.com';

			mail($to, $subject, $message, $headers);
		}  
	}
	
	/**
	 * Add the gateway to woocommerce
	 **/
	function add_infusionsoft_gateway( $methods ) {
		$methods[] = 'IA_WooPaymentGateway'; return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'add_infusionsoft_gateway' );
}

?>