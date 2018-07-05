<?php
//This file do ajax request with php cron job
//Load 30 blocks each request. Cron job interval 1 min.
//Nexty blockchain creats 1 block every 2 seconds

function ntyp_get_ajax_request_url($url){
    $to_cut='wp-content/plugins/nextypay/nextypayajax.php';
    $base_url=explode($to_cut, $url);
    return $base_url[0].'wp-admin/admin-ajax.php';
}

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$url = ntyp_get_ajax_request_url($actual_link);
$postfields = array('action'=>'ntyp_updatedb_ajax_cronjob');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
$result = curl_exec($ch);
?>
