<?php
class Nextypaysuccess {
  public $nextypay_code="payment_nextypay_";
  private $_updatedb;
  private $_functions;
  private $_blockchain;
  private $_exchange;
  private $_url = 'https://rinkeby.infura.io/fNuraoH3vBZU8d4MTqdt';

  private function get_lib(){
    //Get help-functions
    $this->load->library('nextypayblockchain');
    $this->_blockchain = Nextypayblockchain::get_instance($this->registry);

    $this->load->library('nextypayexchange');
    $this->_exchange = Nextypayexchange::get_instance($this->registry);

    $this->load->library('nextypayfunctions');
    $this->_functions = Nextypayfunctions::get_instance($this->registry);

    $this->load->library('nextypayupdatedb');
    $this->_updatedb = Nextypayupdatedb::get_instance($this->registry,$this->_blockchain,$this->_functions);
  }

//anti ddos! build later TODOOOOOOOOOOOOO
//hash by Min
  private function get_ajax_key(){
    $key="1234";
    return $key;
  }

  private function get_backend_settings(&$data){
    $data['walletAddress']=$this->config->get($this->nextypay_code.'walletAddress');
    $data['exchangeAPI']=$this->config->get($this->nextypay_code.'exchangeAPI');
    $data['min_blocks_saved_db']=$this->config->get($this->nextypay_code.'min_blocks_saved_db');
    $data['max_blocks_saved_db']=$this->config->get($this->nextypay_code.'max_blocks_saved_db');
    $data['blocks_loaded_each_request']=$this->config->get($this->nextypay_code.'blocks_loaded_each_request');
  }

  private function get_order_details(&$data){
    $data['orderDetails'] = $this->model_checkout_order->getOrder($data['order_id']);
    $data['orderDetails_json'] = json_encode($this->model_checkout_order->getOrder($data['order_id']));

    $data['currency_code']=$data['orderDetails']['currency_code'];
    $data['total']=$data['orderDetails']['total'];
    $data['store_name']=$data['orderDetails']['store_name'];
    $data['store_url']=$data['orderDetails']['store_url'];
    $data['order_id_prefix']=$data['orderDetails']['store_url'].$data['orderDetails']['store_name'];
    $data['uoid']=$data['order_id']."_".$data['order_id_prefix'];
    $data['test']=$data['currency_code']." ".$data['total']." ".$data['uoid'];
  }

  private function set_navi(&$data){
    $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('extension/payment/nextypaysuccess')
		);
  }

  private function set_template(&$data){

    $data['response']=$this->language->get('payment_waiting');
    $data['android_mobile']=$this->language->get('android_mobile_app_link');
    $data['ios_mobile']=$this->language->get('ios_mobile_app_link');
    $data['entry_android']=$this->language->get('entry_android');
    $data['entry_ios']=$this->language->get('entry_ios');
    $data['showQR_status']=true;
    if ($this->_updatedb->is_order_completed($data['order_id'])) {
      $data['response']=$this->language->get('payment_success');
      $data['showQR_status']=false;
    }
    $this->document->setTitle($this->language->get('heading_title'));
    $this->set_navi($data);

    if ($this->customer->isLogged()) {
      $data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
    } else {
      $data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
    }

    $data['continue'] = $this->url->link('common/home');

    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
  }

  private function unset_session(&$data){
    $this->cart->clear();
/*
    unset($this->session->data['shipping_method']);
    unset($this->session->data['shipping_methods']);
    unset($this->session->data['payment_method']);
    unset($this->session->data['payment_methods']);
    unset($this->session->data['guest']);
    unset($this->session->data['comment']);
    unset($this->session->data['order_id']);
    unset($this->session->data['coupon']);
    unset($this->session->data['reward']);
    unset($this->session->data['voucher']);
    unset($this->session->data['vouchers']);
    unset($this->session->data['totals']);
    */
  }

  public function ajax_updatedb(){

    $ajax_key=$this->get_ajax_key();
    if (!isset($_GET['ajax_key'])) exit;
    if ($ajax_key!=$_GET['ajax_key']) exit;
    $this->index();

  }

  public function ajax_get_order_status(){

    $ajax_key=$this->get_ajax_key();
    if (!isset($_GET['ajax_key'])) exit;
    if ($ajax_key!=$_GET['ajax_key']) exit;
    if (!isset($_POST['order_id'])) exit;
    $this->index();

  }

  public function ajax_response(){
    if ((isset($_POST['action'])) && ($_POST['action']=='ajax_get_order_status')) {
      $order_id=$_POST['order_id'];

      if ($this->_updatedb->is_paid_sum_enough($order_id)) {
        echo "1.".$order_id;
        $this->_updatedb->order_status_to_complete($order_id);
        exit;
      }
      //$response=$this->_updatedb->updatedb();
      echo "0.".$order_id;
      //echo $this->_updatedb->get_complete_status_id();
      //$this->_updatedb->order_status_to_complete($order_id);
    } else {
      $this->_updatedb->updatedb();
    }

  }

  public function get_QR_code(&$data){

    $QRtext='{"walletaddress":"'.$data['walletAddress'].'","uoid":"'.$data['uoid'].'","amount":"'.$data['total_in_coin'].'"}';
    $QRtext_hex="0x".$this->_functions->strToHex($QRtext);
    $QRtextencode= urlencode ( $QRtext );
    $data['QRtextencode']=$QRtextencode;
    $data['QRtext']=$QRtext;
    $data['QRtext_hex']=$QRtext_hex;

  }

  private function get_total_in_coin(&$data){
    $this->_exchange->set_exchangeAPI_url($data['exchangeAPI']);
    $this->_exchange->set_store_currency_code($data['currency_code']);
    $data['total_in_coin']=$this->_updatedb->get_order_in_coin($data['order_id']);
    if (!$data['total_in_coin'])  {
      $data['total_in_coin']=$this->_exchange->coinmarketcap_exchange($data['total']);
      $placed_time=date("Y-m-d H:i:s");
      $this->_updatedb->insert_order_in_coin_db($data['order_id'],$data['total'],$data['total_in_coin'],$placed_time);
    }
  }

  private function set_updatedb(&$data){
    $this->_updatedb->set_url($this->_url);
    $this->_updatedb->set_connection($this->db);
    $this->_updatedb->set_includes($this->_blockchain,$this->_functions);
    $this->_updatedb->set_backend_settings(DB_PREFIX."nextypay_",$data['currency_code'],$data['walletAddress'],$data['order_id_prefix'],
                                          $data['min_blocks_saved_db'],$data['max_blocks_saved_db'],  $data['blocks_loaded_each_request']);
  }

	public function index() {
		$this->load->language('extension/payment/nextypaysuccess');
    $this->load->model('checkout/order');

    if (!isset($this->session->data['order_id'])) exit;
    $data['order_id']=$this->session->data['order_id'];


    $this->get_order_details($data);
    $this->get_backend_settings($data);
    $this->get_order_details($data);
    $this->get_lib();

    $this->set_updatedb($data);

    $this->get_total_in_coin($data);
    $this->get_QR_code($data);

    $data['ajax_key']=$this->get_ajax_key();
    if (isset($_GET['ajax_key'])) {
      $this->ajax_response();
      exit;
    }


		if (isset($this->session->data['order_id'])) $this->unset_session($data);

    $this->set_template($data);

		$this->response->setOutput($this->load->view('extension/payment/nextypaysuccess', $data));
	}

}
