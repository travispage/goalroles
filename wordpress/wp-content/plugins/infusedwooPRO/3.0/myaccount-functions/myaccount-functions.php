<?php


// Display Credit Cards 
add_action( 'iwpro_ready', 'iw_enable_inf_cc_control', 10, 1 );

function iw_enable_inf_cc_control($iwpro) {
	if((isset($iwpro->settings['pgenabled']) && $iwpro->settings['pgenabled'] == 'yes')) {
		add_filter( 'woocommerce_saved_payment_methods_list', 'iw_display_infusionsoft_cards', 10, 2 );
		add_action( 'wp_ajax_iw_userdelete_cc', 'iw_userdelete_cc' );
		add_action( 'wp_ajax_nopriv_iw_userdelete_cc', 'iw_userdelete_cc' );
	}
}




function iw_display_infusionsoft_cards($payment_methods, $user_id) {
	global $iwpro;
	$displayed_cc = isset($payment_methods['cc']) ? $payment_methods['cc'] : array();

	$wp_user = get_user_by( 'id', $user_id );
	$user_email = $wp_user->user_email;

	if($ccs = $iwpro->ia_get_creditcards($user_email)) {
		foreach($ccs as $cc) {
			$displayed_cc[] = array(
					'method' => array(
						'gateway' => 'infusionsoft',
						'last4' => $cc['Last4'],
						'brand' => $cc['CardType'],
						),
					'expires' => $cc['ExpirationMonth'] . '/' . substr($cc['ExpirationYear'], -2),
					'actions' => array(array(
							'url'  => admin_url( 'admin-ajax.php?action=iw_userdelete_cc&id=' . $cc['Id'] ),
							'name' => __('Delete','woocommerce')
						)),
				);
		}
	}

	$payment_methods['cc'] = $displayed_cc;

	return $payment_methods;
}

function iw_userdelete_cc() {
	if(is_user_logged_in()) {
		global $iwpro;
		$ccs = $iwpro->ia_get_creditcards();

		foreach($ccs as $cc) {
			if($cc['Id'] == $_GET['id']) {
				$iwpro->app->deactivateCreditCard($cc['Id']);
				$user_id = get_current_user_id();
				do_action( 'iw_userdelete_cc', $cc['Id'],  $user_id);
				wc_add_notice( __("Successfully removed {$cc['CardType']} ending in {$cc['Last4']}", 'woocommerce'));
				break;
			}
		}
	}

	header('Location: ' . $_SERVER['HTTP_REFERER']);
	exit();
}
