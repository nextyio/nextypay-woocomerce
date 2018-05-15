<?php
function coinmarketcap_id_to_usd($id,$amount){
	$str="https://api.coinmarketcap.com/v2/ticker/".$id."/?convert=usd";
	$result=json_decode((file_get_contents($str)),true);

	return $amount*$result['data']['quotes']['USD']['price'];
}

//http://free.currencyconverterapi.com
function wc_currency_to_usd($wc_currency,$amount){
	$str='http://free.currencyconverterapi.com/api/v5/convert?q='.$wc_currency .'_USD&compact=y';
	$result=json_decode((file_get_contents($str)),true);
	$key=$wc_currency.'_'."USD";
	return $amount*$result[$key]['val'];
}

function hex_to_coin($value){
	return $value*(1E-18);
}
?>