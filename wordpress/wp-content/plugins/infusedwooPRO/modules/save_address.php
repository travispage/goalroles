<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('woocommerce_customer_save_address', 'infusedwoo_customer_edit_address',10, 2);

function infusedwoo_customer_edit_address($user_id, $load_address) {
	global $iwpro;

	if($iwpro->autosave_address == "yes") {
		if(!$iwpro->ia_app_connect()) return false;

		$upd = array(
				'FirstName' => get_user_meta( $user_id, 'billing_first_name', true ),
				'LastName' => get_user_meta( $user_id, 'billing_last_name', true ),
				'StreetAddress1' => get_user_meta( $user_id, 'billing_address_1', true ),
				'StreetAddress2' => get_user_meta( $user_id, 'billing_address_2', true ),
				'City' => get_user_meta( $user_id, 'billing_city', true ),
				'State' => get_user_meta( $user_id, 'billing_state', true ),
				'PostalCode' => get_user_meta( $user_id, 'billing_postcode', true ),
				'Address2Street1' => get_user_meta( $user_id, 'shipping_address_1', true ),
				'Address2Street2' => get_user_meta( $user_id, 'shipping_address_2', true ),
				'City2' => get_user_meta( $user_id, 'shipping_city', true ),
				'State2' => get_user_meta( $user_id, 'shipping_state', true ),
				'PostalCode2' => get_user_meta( $user_id, 'shipping_postcode', true ),
				'Email' => get_user_meta( $user_id, 'billing_email', true )
			);

		$b_company		= get_user_meta( $user_id, 'billing_company', true );
		$compId 		= 0;

		if(!empty($b_company)) {
			$company 		= $iwpro->app->dsFind('Company',5,0,'Company',$b_company,array('Id')); 
			$company 		= $company[0];
			
			if ($company['Id'] != null && $company['Id'] != 0 && $company != false){							
				$compId = $company['Id'];						
			} else {
				$companyinfo = array('Company' => $b_company);
				$compId = $iwpro->app->dsAdd("Company", $companyinfo);
			}
		}

		$upd['Company'] = 	stripslashes($b_company);
		$upd['CompanyID'] = $compId;
		

		$iwpro->app->addWithDupCheck($upd, 'Email');
	}
}