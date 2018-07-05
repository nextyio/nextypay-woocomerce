<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypayupdatedb{
  public static $instance;
  //inputs list

  public $_url;
  public $_functions;
  public $_blockchain;

  public $_connection;

  public $_db_prefix;
  public $_store_currency;
  public $_admin_wallet_address;
  public $_order_id_prefix;
  public $_min_blocks_saved_db;
  public $_max_blocks_saved_db;
  public $_blocks_loaded_each_request;

  /**
    * @param  object  $registry  Registry Object
  */

  public static function get_instance($registry) {
    if (is_null(static::$instance)) {
      static::$instance   = new static($registry);
    }
    return static::$instance;
  }

  public function set_url($url){
    $this->_url=$url;
  }

  public function set_connection($connection){
    $this->_connection=$connection;
  }

  public function set_includes($obj_blockchain,$obj_functions){
    $this->_functions=$obj_functions;
    $this->_blockchain=$obj_blockchain;
  }

  public function set_backend_settings($db_prefix,$store_currency,$admin_wallet_address,$order_id_prefix,$min,$max,$blocks_loaded){
    $this->_db_prefix=$db_prefix;
    $this->_store_currency=$store_currency;
    $this->_admin_wallet_address=$admin_wallet_address;
    $this->_order_id_prefix=$order_id_prefix;
    $this->_min_blocks_saved_db=$min;
    $this->_max_blocks_saved_db=$max;
    $this->_blocks_loaded_each_request=$blocks_loaded;
  }

////////////////////sql query DB,depending on Framework

  private function query_db($sql){
    //global $wpdb;
    return $this->_connection->query($sql);
  }

  private function get_value_query_db($sql){
    //global $wpdb;
    $result= $this->_connection->get_var($sql);
    return $result;
  }

  private function get_values_query_db($sql){
    //global $wpdb;
    $results= $this->_connection->get_results($sql);
    return $results;
  }

//////////////////change order_status to Complete, depending on Framework
  public function order_status_to_complete($order_id){
    $order = new WC_Order($order_id);
    $order->update_status('completed');
    return;
  }

//GET Functions

  private function get_order_id_prefix(){
    return $this->_order_id_prefix;
  }

  private function get_transactions_table_name(){
    return $this->_db_prefix.'transactions';
  }

  public function get_blocks_table_name(){
    return $this->_db_prefix.'blocks';
  }

  private function get_order_in_coin_table_name(){
    return $this->_db_prefix.'order_in_coin';
  }

  private function get_max_block_number_db(){

    $table_name = $this->get_blocks_table_name();
    $sql="SELECT MAX(number) AS output FROM $table_name";
    $result = $this->get_value_query_db($sql);

    if (!$result) return 0;
    return $result;

  }

  private function get_order_id_from_input($input_hash){

    //{“walletaddress”: “0x841A13DDE9581067115F7d9D838E5BA44B537A42″,”uoid”: “46”,”amount”: “80000”}
    $input=($this->_functions->hexToStr($input_hash));
    $input_arr=(explode(",",$input));

    $key='uoid';

    foreach($input_arr as $str)
    {
      $tmp= explode(":",$str,2);
      $get_key=$this->_functions->key_filter($tmp[0]);
      if ($get_key==$key) {
        $get_value=$this->_functions->key_filter($tmp[1]);
        $order_id=intval(explode("_",$get_value,2) [0]);
        return $order_id;
      }
    }
    return false;

  }

  private function get_order_id_prefix_from_input($input_hash){

    //{“walletaddress”: “0x841A13DDE9581067115F7d9D838E5BA44B537A42″,”uoid”: “46”,”amount”: “80000”}
    $input=($this->_functions->hexToStr($input_hash));
    $input_arr=(explode(",",$input));

    $key='uoid';

    foreach($input_arr as $str){
      $tmp= explode(":",$str,2);
      $get_key=$this->_functions->key_filter($tmp[0]);
      if ($get_key==$key) {
        $get_value=$this->_functions->key_filter($tmp[1]);
        $order_id_prefix=explode("_",$get_value,2) [1];
        return $order_id_prefix;
      }
    }
    return false;

  }

  public function get_order_status_by_id($order_id){
    $table_name=$table_name=DB_PREFIX."order";
    $sql="SELECT MAX(order_status_id) as output FROM $table_name WHERE order_id='$order_id'";
    $result= $this->get_value_query_db($sql);
    return $result;
  }

  public function get_order_in_coin($order_id){

    $table_name=$this->get_order_in_coin_table_name();
    $sql = "SELECT MAX(order_total_in_coin) as output FROM $table_name
          WHERE order_id='$order_id'";
    $result=$this->get_value_query_db($sql);
    if ($result) return $result;
    return null;

  }

  public function get_paid_sum_by_order_id($order_id){

    $table_name=$this->get_transactions_table_name();
    $sql = "SELECT value FROM $table_name
          WHERE order_id='$order_id'";
    $results=$this->get_values_query_db($sql);
//return json_encode($results);
    $sum=0;
    foreach ($results as $result){
      //$value_hex=$result['value'];
      $value_hex=$result->value;
      $value=(hexdec($value_hex))*1e-18;
      $sum=$sum+$value;
      //return $value;
    }
    return $sum;

  }

  private function get_complete_status_id(){
    $table_name=DB_PREFIX."order_status";
    $key='Complete';
    $sql="SELECT MAX(order_status_id) as output FROM $table_name WHERE name='$key'";
    $result=$this->get_value_query_db($sql);
    return $result;
  }

//Check exist

  private function transaction_exist($hash){

    $table_name=$this->get_transactions_table_name();
    $sql= "SELECT hash FROM $table_name
          WHERE hash='$hash'";
    $results = $this->get_values_query_db($sql);
    if (count($results)>0) return true;
    return false;

  }

  private function block_exist($hash){

    $table_name=$this->get_blocks_table_name();
    $sql= "SELECT hash FROM $table_name
          WHERE hash='$hash'";
    $results = $this->get_values_query_db($sql);
    if (count($results)>0) return true;
    return false;

  }

  private function order_in_coin_exist($order_id){

    $table_name=$this->get_order_in_coin_table_name();
    $sql= "SELECT order_id FROM $table_name
          WHERE order_id='$order_id'";

    $results = $this->get_values_query_db($sql);
    //echo $sql;
    //echo json_encode($results);
    if (count($results)>0) return true;
    return false;

  }

//INSERT Functions

  public function insert_order_in_coin_db($order_id,$order_total,$order_total_in_coin,$placed_time,$walletAddress){

    $store_currency=$this->_store_currency;
    $table_name=$this->get_order_in_coin_table_name();
    if (!$this->order_in_coin_exist($order_id)) {
      $sql = "INSERT INTO " . $table_name . "(order_id, store_currency, order_total, order_total_in_coin, placed_time, to_walletAddress) VALUES
          ('$order_id', '$store_currency', '$order_total', '$order_total_in_coin', '$placed_time', '$walletAddress')";
      $this->query_db($sql);
    }

  }

  public function is_order_exist($order_id,$to_wallet){
    $table_name=$this->get_order_in_coin_table_name();
    $to_walletAddress=strtolower($to_wallet);
    $sql="SELECT MAX(order_id) as output FROM  $table_name WHERE order_id='$order_id' AND to_walletAddress='$to_walletAddress'";
    $result=$this->get_value_query_db($sql);
    if ($result) return true;
    return false;
  }

  public function is_transaction_correct($transaction){
    $table_name=$this->get_order_in_coin_table_name();

    $hash=strtolower($transaction['hash']);
    $extra_data=$transaction['input'];

    $order_id=$this->get_order_id_from_input($extra_data);
    $order_id_prefix_from_input=$this->_functions->key_filter($this->get_order_id_prefix_from_input($extra_data));
    $order_id_prefix=$this->_functions->key_filter($this->get_order_id_prefix());

    if (strtoupper($order_id_prefix)!=strtoupper($order_id_prefix_from_input)) return false;

    $to_wallet=$transaction['to'];
    return $this->is_order_exist($order_id, $to_wallet);
  }

  private function insert_transactions_db($transactions,$block_time){

    $table_name=$this->get_transactions_table_name();

    foreach ($transactions as $transaction)
    //if (($transaction['to']) && (strtolower($transaction['to'])==strtolower($this->_admin_wallet_address))){
    if ($this->is_transaction_correct($transaction)){

      $block_hash=$transaction['blockHash'];
      $block_number=$transaction['blockNumber'];
      $block_number_dec= hexdec($block_number);

      $from_wallet=$transaction['from'];
      $to_wallet=$transaction['to'];
      $value=$transaction['value'];
      $time=$block_time;

      $hash=strtolower($transaction['hash']);
      $extra_data=$transaction['input'];

      $order_id=$this->get_order_id_from_input($extra_data);
      $order_id_prefix_from_input=$this->_functions->key_filter($this->get_order_id_prefix_from_input($extra_data));
      $order_id_prefix=$this->_functions->key_filter($this->get_order_id_prefix());


      //if ((strtoupper($order_id_prefix)==strtoupper($order_id_prefix_from_input)) &&
      //(!$this->transaction_exist($hash))){
      if (!$this->transaction_exist($hash)){
        $sql = "INSERT INTO " . $table_name . "(block_number, block_hash, hash, from_wallet, to_wallet, value, time, order_id) VALUES
            ('$block_number_dec', '$block_hash', '$hash', '$from_wallet', '$to_wallet', '$value', '$time', '$order_id')";
        $this->query_db($sql);
        $this->is_paid_sum_enough($order_id);
      }
    }

  }

  private function insert_block_db($block_content){
    //if block still unavaiable
    if (!$block_content) return;

    $table_name=$this->get_blocks_table_name();

    $block_number=hexdec($block_content['number']);
    $block_hash=strtoupper($block_content['hash']);
    if ($this->block_exist($block_hash)) return;
    $block_header="";	/////////////////////////////////*****
    $block_prev_header=$block_content['parentHash'];
    $block_time=hexdec($block_content['timestamp']);
    $block_time= date("Y-m-d H:i:s", $block_time);
    $transactions=$block_content['transactions'];


    $sql = "INSERT INTO " . $table_name . "(number, hash, header, prev_header, time) VALUES
      ('$block_number', '$block_hash', '$block_header', '$block_prev_header','$block_time')";
    $this->query_db($sql);

    $this->insert_transactions_db($transactions,$block_time);

  }

//depending on Framework
  public function is_order_completed($order_id){
    $order=wc_get_order( $order_id );
    $order_status = wc_get_order( $order)->status;
    return ($order_status=='completed');
    $complete_status_id=$this->get_complete_status_id();
    $status=$this->get_order_status_by_id($order_id);
    return ($complete_status_id==$status);
  }

  public function is_paid_sum_enough($order_id){

    if ($this->is_order_completed($order_id)) return true;
    $order = wc_get_order( $order_id );
    $order_total = wc_get_order( $order)->total;
    if ($order_total==0) { //coupon, voucher
      $this->order_status_to_complete($order_id);
      return $paid_enough;
    }

    $paid_sum=$this->get_paid_sum_by_order_id($order_id);
    if (!is_numeric($paid_sum)) {$paid_sum=-1;$order_total_in_coin=0;}

    $order_total_in_coin=$this->get_order_in_coin($order_id);
    if (!is_numeric($order_total_in_coin)) {$paid_sum=-1;$order_total_in_coin=0;}
    //return $paid_sum."a". $order_total_in_coin;
//return true;
    //Set epsilon
    $epsilon=1e-10;

    //check if payment success
    $paid_enough= ($paid_sum+$epsilon>$order_total_in_coin);
    if  ($paid_enough) $this->order_status_to_complete($order_id);
    return $paid_enough;

  }

  private function is_order_in_coin_placed($order_id){

    $table_name = $this->get_order_in_coin_table_name();
    $sql="SELECT order_id FROM $table_name WHERE order_id='$order_id'";
    $results=$this->get_values_query_db($sql);
    if (count($results)>0) return true;
    return false;

  }

  public function count_total_blocks_db(){

    $table_name = $this->get_blocks_table_name();
    $sql="SELECT COUNT('id') AS output FROM $table_name";
    $result = $this->get_value_query_db($sql);
    //return $sql;
    return $result;

  }

  private function delete_old_blocks_db(){

    $table_name=$this->get_blocks_table_name();

    $bottom_limit=$this->_min_blocks_saved_db;
    $top_limit=$this->_max_blocks_saved_db;

    $total_blocks=$this->count_total_blocks_db();
    $total_blocks_to_delete=$total_blocks-$bottom_limit;

    if ($top_limit>$total_blocks) return;
    $sql="DELETE FROM $table_name LIMIT $total_blocks_to_delete";
    $this->query_db($sql);

  }

  public function init_blocks_table_db(){

    if ($this->count_total_blocks_db()>0) return;
    $max_block_number = $this->_blockchain->get_max_block_number($this->_url);
    $hex_max_block_number="0x".strval(dechex($max_block_number));
    $block=$this->_blockchain->get_block_by_number($this->_url,$hex_max_block_number);
    $block_content=$block['result'];
    $this->insert_block_db($block_content);

  }

  public function updatedb(){
    $this->init_blocks_table_db();

    $to_scan_number=$this->_blocks_loaded_each_request;
    //scan from this block number
    $start_number=$this->get_max_block_number_db()+1 ;
    //$start_number=2447115; //testing transaction at xxxxxxxx2419899 2447116

    for ($scanning_number=$start_number;
        $scanning_number<$start_number+$to_scan_number; //test
        $scanning_number++){

      $hex_scanning_number="0x".strval(dechex($scanning_number)); //convert to hex
      $block=$this->_blockchain->get_block_by_number($this->_url,$hex_scanning_number);	//get Block by number with API
      if (isset($block['result'])) $block_content=$block['result']; else exit;
      $this->insert_block_db($block_content);
    }

    $this->delete_old_blocks_db();

  }

  public function test_func(){
    $test=$this->_db_prefix."<br>".
    $this->_store_currency."<br>".
    $this->_admin_wallet_address."<br>".
    $this->_order_id_prefix."<br>".
    $this->_min_blocks_saved_db."<br>".
    $this->_max_blocks_saved_db."<br>".
    $this->_blocks_loaded_each_request;
    return $test;
  }

}
?>
