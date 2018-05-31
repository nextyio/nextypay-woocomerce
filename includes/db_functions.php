<?php
function strToHex($string){

	$hex = '';
	for ($i=0; $i<strlen($string); $i++){
		$ord = ord($string[$i]);
		$hexCode = dechex($ord);
		$hex .= substr('0'.$hexCode, -2);
	}
	return strToLower($hex);

}

function hexToStr($hex){

    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;

}

function create_order_total_in_coin_table_db($wpdb,$order_total_in_coin_table_name){
	$table_name = $order_total_in_coin_table_name;

	//if not exist
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		//table not in database. Create new table
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name
			(
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  order_id mediumint(9) NOT NULL,
			  store_currency text NOT NULL,
			  order_total text NOT NULL,
			  order_total_in_coin text NOT NULL,
			  placed_time DATETIME NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		debug_to_console( $sql);
	}
}

function create_transactions_table_db($wpdb,$transactions_table_name){
	$table_name = $transactions_table_name;

	//if not exist
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		//table not in database. Create new table
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name
			(
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  block_number mediumint(9) NOT NULL,
			  block_hash text NOT NULL,
			  hash text NOT NULL,
			  from_wallet text NOT NULL,
			  to_wallet text NOT NULL,
			  value text NOT NULL,
			  time DATETIME NOT NULL,
			  order_id text NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		debug_to_console( $sql);
	}
}

function create_blocks_table_db($wpdb,$blocks_table_name){

	$table_name = $blocks_table_name;
	//if not exist
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		//table not in database. Create new table
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name
		(
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			number mediumint(9) NOT NULL,
			hash text NOT NULL,
			header text NOT NULL,
			prev_header text NOT NULL,
			time text NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		debug_to_console( $sql);
	}
}

function key_filter($key){
      $delete_list=array('"','“','″','”',' ','{','}');
      return str_replace($delete_list, '',$key);
    }

function get_order_id_from_input($input_hash){

	//{“walletaddress”: “0x841A13DDE9581067115F7d9D838E5BA44B537A42″,”uoid”: “46”,”amount”: “80000”}
	$input=(hexToStr($input_hash));
	$input_arr=(explode(",",$input));

	$key='uoid';

	foreach($input_arr as $str)
	{
		$tmp= explode(":",$str,2);
		$get_key=key_filter($tmp[0]);
		if ($get_key==$key) {
			$get_value=key_filter($tmp[1]);
			$order_id=intval(explode("_",$get_value,2) [0]);
			return $order_id;
		}
	}
	return false;

}

function get_order_id_prefix_from_input($input_hash){
	//{“walletaddress”: “0x841A13DDE9581067115F7d9D838E5BA44B537A42″,”uoid”: “46”,”amount”: “80000”}
	$input=(hexToStr($input_hash));
	$input_arr=(explode(",",$input));

	$key='uoid';

	foreach($input_arr as $str)
	{
		$tmp= explode(":",$str,2);
		$get_key=key_filter($tmp[0]);
		if ($get_key==$key) {
			$get_value=key_filter($tmp[1]);
			$order_id_prefix=explode("_",$get_value,2) [1];
			return $order_id_prefix;
		}
	}
	return false;
}
/*
function get_order_id_from_input($input_hash){

	//{“walletaddress”: “0x841A13DDE9581067115F7d9D838E5BA44B537A42″,”uoid”: “46”,”amount”: “80000”}
	$input=(hexToStr($input_hash))."<br>";
	//echo $input;
	$input = str_replace(' ', '', $input);
	$input = str_replace('{', '', $input);
	$input = str_replace('}', '', $input);
	$input_arr=(explode(",",$input));

	$key='uoid';

	foreach($input_arr as $str)
	{
		//echo $str."<br>";
		$tmp= explode(":",$str);
		$delete_list=array('"','“','″','”');
		$get_key=str_replace($delete_list, '',$tmp[0]);
		//echo $get_key."<br>";
		if (str_replace($delete_list,'',$tmp[0])==$key) return str_replace($delete_list, '',$tmp[1]);
	}
	return false;

}*/

function transaction_exist($wpdb,$transactions_table_name,$hash){
	$sql= "SELECT hash FROM $transactions_table_name
			WHERE hash='$hash'";
	$result = $wpdb->get_var($sql);
	if ($result==$hash) return true;
	return false;
}

function order_total_in_coin_exist($wpdb,$order_total_in_coin_table_name,$order_id){
	$sql= "SELECT order_id FROM $order_total_in_coin_table_name
			WHERE order_id='$order_id'";
	$result = $wpdb->get_var($sql);
	if ($result==$order_id) return true;
	return false;
}

function get_order_total_in_coin_db($wpdb,$order_total_in_coin_table_name,$order_id){
	$sql= "SELECT order_total_in_coin FROM $order_total_in_coin_table_name
			WHERE order_id='$order_id'";
	$result = $wpdb->get_var($sql);
	return $result;
}

function insert_order_total_in_coin_db($wpdb,$order_total_in_coin_table_name,$order_id,$store_currency,$order_total,$order_total_in_coin,$placed_time){
		if (!order_total_in_coin_exist($wpdb,$order_total_in_coin_table_name,$order_id))
		{
			$arr=
			array(
			'order_id' 	=> $order_id,
			'store_currency' 	=> $store_currency,
			'order_total' 			=> $order_total,
			'order_total_in_coin' 	=> $order_total_in_coin,
			'placed_time' 	=> $placed_time,
		);
		echo json_encode($arr);
			$wpdb->insert("$order_total_in_coin_table_name", $arr);
		}
}

function insert_transactions_db($wpdb,$transactions,$transactions_table_name,$admin_wallet_address,$block_time){

	foreach ($transactions as $transaction)
	if (strtolower($transaction['to'])==strtolower($admin_wallet_address))
	{
		$block_hash=$transaction['blockHash'];
		$block_number=$transaction['blockNumber'];
		$from_wallet=$transaction['from'];
		$to_wallet=$transaction['to'];
		$value=$transaction['value'];
		$time=$block_time;
		$hash=$transaction['hash'];
		$extra_data=$transaction['input'];
		$order_id=get_order_id_from_input($extra_data);
		$order_id_prefix=get_order_id_prefix_from_input($extra_data);

		if ((!transaction_exist($wpdb,$transactions_table_name,$hash)) && (strtoupper($order_id_prefix)==strtoupper($_SERVER['HTTP_HOST']))){
			$wpdb->insert("$transactions_table_name", array(
			'block_number' 	=> hexdec($block_number),
			'block_hash' 	=> $block_hash,
			'hash' 			=> $hash,
			'from_wallet' 	=> $from_wallet,
			'to_wallet' 	=> $to_wallet,
			'value' 		=> $value,
			'time' 			=> $time,
			'order_id' 		=> $order_id,
			));
		}
	}

}

function insert_block_db($wpdb,$block_content,$blocks_table_name,$transactions_table_name,$admin_wallet_address){

	//if block still unavaiable
	if (!$block_content) return;
	$block_number=hexdec($block_content['number']);
	$block_hash=$block_content['hash'];
	$block_header="";	/////////////////////////////////TODO
	$block_prev_header=$block_content['parentHash'];
	$block_time=hexdec($block_content['timestamp']);
	$block_time= date("Y-m-d H:i:s", $block_time);
	$transactions=$block_content['transactions'];

	$wpdb->insert("$blocks_table_name", array(
    'number' 		=> $block_number,
    'hash' 			=> $block_hash,
    'header' 		=> $block_header,
    'prev_header' 	=> $block_prev_header,
    'time' 			=> $block_time
	));

	insert_transactions_db($wpdb,$transactions,$transactions_table_name,$admin_wallet_address,$block_time);

}

function count_total_blocks_db($wpdb,$blocks_table_name){

	$table_name = $blocks_table_name;
	$sql="SELECT COUNT('id') AS count FROM $table_name";
    $result = $wpdb->get_var($sql);
	return $result;

}

function get_first_id_db($wpdb,$table_name){

	$sql="SELECT MAX(number) AS max FROM $table_name";
    $result = $wpdb->get_var($sql);

}

function delete_old_blocks_db($wpdb,$blocks_table_name,$bottom_limit,$top_limit){

	$total_blocks=count_total_blocks_db($wpdb,$blocks_table_name);
	$total_blocks_to_delete=$total_blocks-$bottom_limit;
	//echo $total_blocks;
	if ($top_limit>$total_blocks) return;
	$sql="DELETE FROM $blocks_table_name LIMIT $total_blocks_to_delete";
	$wpdb->query($sql );

}

function is_table_empty_db($wpdb,$table_name){

	$sql="SELECT * FROM $table_name";
    $result = $wpdb->get_results($sql);
    return(count($result) == 0);

}

function get_max_block_number_db($wpdb,$blocks_table_name){

	$table_name = $blocks_table_name;
	$sql="SELECT MAX(number) AS max FROM $table_name";
    $result = $wpdb->get_var($sql);
	return $result;

}

function get_paid_sum_by_order_id($wpdb,$transactions_table_name,$order_id){
	$sql = "SELECT value FROM $transactions_table_name
			WHERE order_id=$order_id";
	$results=$wpdb->get_results($sql);
	$sum=0;
	foreach ($results as $key){
		$value=hexdec($key->value);
		//echo $value;
		$sum=$sum+$value;
	}
return $sum;
}

function init_blocks_table_db($wpdb,$url,$blocks_table_name,$transactions_table_name,$admin_wallet_address){

	if (!is_table_empty_db($wpdb,$blocks_table_name)) return ;
	$max_block_number = get_max_block_number($url);
	//$max_block_number = 2258373;
	$hex_max_block_number="0x".strval(dechex($max_block_number));
	$block=get_block_by_number($url,$hex_max_block_number);
	$block_content=$block['result'];
	insert_block_db($wpdb,$block_content,$blocks_table_name,$transactions_table_name,$admin_wallet_address);

}

function get_last_update_exchange_db($wpdb,$wc_currency,$exchange_table_name){

	$table_name = $exchange_table_name;
	$sql="SELECT MAX(time) AS max FROM $table_name";
    $result = $wpdb->get_var($sql);
	return $result;
}
?>
