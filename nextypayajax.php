<?php

require_once(dirname( dirname(dirname( dirname(__FILE__) )))  . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

$nextypay_url			= dirname(__FILE__)."/";
$nextypay_js_url	= $nextypay_url.'assets/js/';
$nextypay_css_url	= $nextypay_url.'assets/css/';
$nextypay_lib_url = $nextypay_url.'lib/' ;

include_once $nextypay_url.'nextypay.php';
include_once $nextypay_url.'wp_add_scripts.php';

include_once $nextypay_lib_url.'nextypayblockchain.php';
include_once $nextypay_lib_url.'nextypayfunctions.php';
include_once $nextypay_lib_url.'nextypayexchange.php';
include_once $nextypay_lib_url.'nextypayupdatedb.php';

global $wpdb;
$_nextypay_obj= new WC_Nextypay;

$_db_prefix=$wpdb->prefix.'nextypay_';
$_updatedb=new Nextypayupdatedb;
$_blockchain= new Nextypayblockchain;
$_functions= new Nextypayfunctions;

$_updatedb->set_url($_nextypay_obj->url);
$_updatedb->set_connection($wpdb);
$_updatedb->set_includes($_blockchain,$_functions);
$_updatedb->set_backend_settings($_db_prefix,$_nextypay_obj->store_currency_code,$_nextypay_obj->walletAddress,
       $_SERVER['HTTP_HOST'],$_nextypay_obj->min_blocks_saved_db,$_nextypay_obj->max_blocks_saved_db,30);

$_updatedb->updatedb();

echo "Loading Blocks";


 // Always die in functions echoing ajax content
wp_die();
?>
