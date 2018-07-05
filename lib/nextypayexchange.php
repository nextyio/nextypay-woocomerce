<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypayexchange{
    private static $instance;

    public $_exchangeAPI_url;
    public $_coin_id=2714;
    public $_store_currency_code;
    public $_key_text="/?convert=";
    //Ether 1027 Nexty 2714
    //$_exchangeAPI_url="https://api.coinmarketcap.com/v2/ticker/";
    //$_key_text ="/?convert="

  /**
   * @param  object  $registry  Registry Object
   */

  public static function get_instance($registry) {
    if (is_null(static::$instance)) {
        static::$instance = new static($registry);
    }
    return static::$instance;
  }

  public function set_exchangeAPI_url($exchangeAPI_url){
    $this->_exchangeAPI_url=$exchangeAPI_url;
  }

  public function set_coin_id($coin_id){
    $this->_coin_id=$coin_id;
  }

  public function set_store_currency_code($store_currency_code){
    $this->_store_currency_code=$store_currency_code;
  }

  public function set_key_text($key_text){
    $this->_key_text=$key_text;
  }

/*
	public function coinmarketcap_exchange($amount){

		$str=$this->_exchangeAPI_url.$this->_coin_id.$this->_key_text.$this->_store_currency_code;
		$result=json_decode((file_get_contents($str)),true);
		$upper_code=strtoupper($this->_store_currency_code);

		return $amount/$result['data']['quotes'][$upper_code]['price'];
	}
  */

  public function coinmarketcap_exchange($amount){

  $str=$this->_exchangeAPI_url.$this->_coin_id.$this->_key_text.$this->_store_currency_code;
  $result=json_decode((file_get_contents($str)),true);
  $upper_code=strtoupper($this->_store_currency_code);
  if (!isset($result['data']['quotes'][$upper_code]['price'])) return 0;
  if ($result['data']['quotes'][$upper_code]['price']<=0) return 0;
  return $amount/$result['data']['quotes'][$upper_code]['price'];

  }

  public function ping_API(){
    $str=$this->_exchangeAPI_url.$this->_coin_id.$this->_key_text.$this->_store_currency_code;
    if(@file_get_contents($str)){
     return true;
    }
    return false;
  }

}
?>
