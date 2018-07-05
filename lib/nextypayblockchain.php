<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypayblockchain
{

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

  public function get_max_block_number($url){
    $fields = array(
    'jsonrpc' => "2.0",
    'method' => 'eth_blockNumber',
    'params' => [],
    'id' => 100,
    );

    $data_string = json_encode($fields);

    $args = array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'httpversion' => '1.0',
            'sslverify' => false,
            'body' => $data_string,
        );

    $response = wp_remote_post(esc_url_raw( $url ), $args);
    $response_body=json_decode(wp_remote_retrieve_body( $response ));
    return hexdec($response_body->result);

  }


  public function get_block_by_hash($url,$block_hash){
		$fields = array(
			'jsonrpc' => "2.0",
			'method' => 'eth_getBlockByHash',
			'params' => [$block_hash,true],
			'id' => 1,
		);

    $data_string = json_encode($fields);

    $args = array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'httpversion' => '1.0',
            'sslverify' => false,
            'body' => $data_string,
        );

    $response = wp_remote_post(esc_url_raw( $url ), $args);
    $response_body=(wp_remote_retrieve_body( $response ));
		return $response_body;
	}

  public function get_block_by_number($url,$block_number){
    $fields = array(
      'jsonrpc' => "2.0",
      'method' => 'eth_getBlockByNumber',
      'params' => [$block_number,true],
      'id' => 1,
    );

    $data_string = json_encode($fields);

    $args = array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'httpversion' => '1.0',
            'sslverify' => false,
            'body' => $data_string,
        );

    $response = wp_remote_post(esc_url_raw( $url ), $args);
    $response_body=json_decode(wp_remote_retrieve_body( $response ),true);

    return $response_body;
  }


/*
	public function get_max_block_number($url){
		$fields = array(
		'jsonrpc' => "2.0",
		'method' => 'eth_blockNumber',
		'params' => [],
		'id' => 100,
		);
		$data_string = json_encode($fields);

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json')
		);

		$result = curl_exec($ch);
		$result= json_decode($result, true);
		return hexdec($result['result']);
	}

	public function get_block_by_hash($url,$block_hash){
		$fields = array(
			'jsonrpc' => "2.0",
			'method' => 'eth_getBlockByHash',
			'params' => [$block_hash,true],
			'id' => 1,
		);
		$data_string = json_encode($fields);

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		$json_result = json_encode($result);
		return $json_result;
	}


	public function get_block_by_number($url,$block_number){
		$fields = array(
			'jsonrpc' => "2.0",
			'method' => 'eth_getBlockByNumber',
			'params' => [$block_number,true],
			'id' => 1,
		);

		$data_string = json_encode($fields);
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		$json_result = json_decode($result,true);
		return $json_result;
	}
*/
}
?>
