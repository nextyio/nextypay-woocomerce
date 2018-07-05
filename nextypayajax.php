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
$nextypay_obj= new WC_Nextypay;

$ntyp_db_prefix=$wpdb->prefix.'nextypay_';
$updatedb=new Nextypayupdatedb;
$blockchain= new Nextypayblockchain;
$functions= new Nextypayfunctions;

$updatedb->set_url($nextypay_obj->url);
$updatedb->set_connection($wpdb);
$updatedb->set_includes($blockchain,$functions);
$updatedb->set_backend_settings($ntyp_db_prefix,$nextypay_obj->store_currency_code,$nextypay_obj->walletAddress,
       $_SERVER['HTTP_HOST'],$nextypay_obj->min_blocks_saved_db,$nextypay_obj->max_blocks_saved_db,30);

$updatedb->updatedb();

echo "Loading Blocks";

 // Always die in functions echoing ajax content
wp_die();
?>
