<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypayfunctions{

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
