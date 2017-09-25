<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action("init","ia_ls_save", 10);
add_action("template_redirect", 'ia_save_sess_email',10);

function ia_ls_save() {
	global $iwpro;
	$siteurl = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	$siteurl = str_replace("http://","",$siteurl);
	$siteurl = str_replace("https://","",$siteurl);
	$siteurl = str_replace("www.","",$siteurl);
	$set_leadsource = (WC()->session) ? WC()->session->get('iw_leadsource') : "";

	if(!empty($_GET['leadsource'])) {
		$set_leadsource = $_GET['leadsource'];
	} else if(!empty($_GET['utm_source'])) {
		$set_leadsource = $_GET['utm_source'];				
	} else if(!empty($_GET['utm_campaign'])) {
		$set_leadsource = $_GET['utm_campaign'];				
	} else if(empty($set_leadsource) && !empty($_COOKIE['ia_leadsource'])) {
		$set_leadsource = $_COOKIE['ia_leadsource'];				
	} else if(empty($set_leadsource) && isset($_SERVER['HTTP_REFERER'])) {
		$set_leadsource = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
	}

	$set_leadsource = apply_filters( 'iw_set_leadsource', $set_leadsource );

	if(!empty($set_leadsource)) {
		setcookie("ia_leadsource", $set_leadsource, (time()+31*24*3600), "/", $siteurl, 0); 
		if(WC()->session) {
			WC()->session->set('iw_leadsource', $set_leadsource);
		}
	}
	
	if(isset($_GET['affiliate']) && !empty($_GET['affiliate'])) {
		setcookie("is_aff", $_GET['affiliate'], (time()+365*24*3600), "/", $siteurl, 0); 
		if(WC()->session) {
			WC()->session->set('iw_is_aff', $_GET['affiliate']);
		}
	}

	if(!empty($_GET['aff'])) {
		setcookie("is_affcode", $_GET['aff'], (time()+365*24*3600), "/", $siteurl, 0); 
		if(WC()->session) {
			WC()->session->set('iw_is_affcode', $_GET['aff']);
		}
	}

}

function ia_save_sess_email() {
	$email_source = array('Email','inf_field_Email','email','ContactOEmail','e-mail','E-mail','emailaddress','EmailAddress','saved_cart_loaded');
	$arrs = array($_GET,$_POST);

	foreach($arrs as $arr) {
		foreach($email_source as $param) {
			if(isset($arr[$param]) && !empty($arr[$param])) {
				$contact_email = urldecode($arr[$param]);
				WC()->session->set('session_email', $contact_email);
				break;
			}
		}
	}

}

?>