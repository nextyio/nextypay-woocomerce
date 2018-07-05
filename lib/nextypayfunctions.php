<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypayfunctions{
  public static $instance;

/**
 * @param  object  $registry  Registry Object
 */

  public static function get_instance($registry) {
    if (is_null(static::$instance)) {
      static::$instance = new static($registry);
    }

    return static::$instance;
  }

  public function getQRCode($walletAddress,$order_id,$order_total)
  {
      $QRtext='{"walletaddress": "'.$walletAddress.'","uoid": "'.$order_id.'","amount": "'.$order_total.'"}  ';
      $QRtext_hex="0x".$this->strToHex($QRtext);
      $QRtextencode= urlencode ( $QRtext_hex );
      return $QRtextencode;
  }

  public function strToHex($string){

  	$hex = '';
  	for ($i=0; $i<strlen($string); $i++){
  		$ord = ord($string[$i]);
  		$hexCode = dechex($ord);
  		$hex .= substr('0'.$hexCode, -2);
  	}
  	return strToLower($hex);

  }

  public function hexToStr($hex){

      $string='';
      for ($i=0; $i < strlen($hex)-1; $i+=2){
          $string .= chr(hexdec($hex[$i].$hex[$i+1]));
      }
      return $string;

  }

  public function key_filter($key){
    $delete_list=array('"','“','″','”',' ','{','}');
    return str_replace($delete_list, '',$key);
  }
}
?>
