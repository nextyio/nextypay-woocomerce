<?php
function coinmarketcap_coin_to_store_currency($id,$store_currency){
	$curr_upper=strtoupper($store_currency);
	$str="https://api.coinmarketcap.com/v2/ticker/".$id."/?convert=".$curr_upper;
	$result=json_decode((file_get_contents($str)),true);

	return $result['data']['quotes']["$curr_upper"]['price'];
}

function hex_to_coin($value){
	return $value*(1E-18);
}
?>
