<?php
/**
 * Plugin Name: Nexty Payment
 * Plugin URI: https://github.com/nextyio/nextypay-woocomerce
 * Description: A payment method with Nexty Coin (NTY).
 * Version: 1.0
 * Author: Thang Nguyen
 * Author URI: https://github.com/bestboyvn87
 * Copyright: © 2018 Fredo / Nexty.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-nextypay
 * Domain Path: /languages
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ntyp_ as prefix for the whole Project.Nextypay prefix and NTY is the crypto coin name

$plugin           = plugin_basename( __FILE__ );

$nextypay_url			= dirname(__FILE__).'/';
$nextypay_js_url	= $nextypay_url.'assets/js/';
$nextypay_css_url	= $nextypay_url.'assets/css/';
$nextypay_lib_url = $nextypay_url.'lib/' ;

//Add frontend styles for the calendar page:
$pluginURL = plugins_url("",__FILE__);
$CSSURL = "$pluginURL/assets/css/nextypay.css";//change to your filename and path
wp_register_style( 'frontend_CSS', $CSSURL);
wp_enqueue_style('frontend_CSS');

include_once $nextypay_lib_url.'nextypayblockchain.php';
include_once $nextypay_lib_url.'nextypayfunctions.php';
include_once $nextypay_lib_url.'nextypayexchange.php';
include_once $nextypay_lib_url.'nextypayupdatedb.php';

include_once $nextypay_url.'nextypaysetup.php';
include_once $nextypay_url.'wp_add_scripts.php';

// activeable only if woocommerce installed!
register_activation_hook( __FILE__, 'nextypay_install' );

register_deactivation_hook( __FILE__, 'nextypay_uninstall' );

//add nextypay setting link in plugins list
add_action( 'plugin_action_links_'.$plugin, 'ntyp_plugin_add_settings_link' );

/**
 * NTY currency and currency symbol
 */

add_action( 'woocommerce_review_order_after_order_total', 'ntyp_add_total_NTY' );

add_filter( 'woocommerce_currencies', 'ntyp_add_NTY_currency' );

add_filter( 'woocommerce_currency_symbol', 'ntyp_add_NTY_symbol', 10, 2 );

//no need login for ajax, to load blocks from Blockchain
add_action( 'wp_ajax_nopriv_ntyp_get_order_status_ajax', 'ntyp_get_order_status_ajax' );
add_action( 'wp_ajax_nopriv_ntyp_updatedb_ajax', 'ntyp_updatedb_ajax' );
add_action( 'wp_ajax_nopriv_ntyp_updatedb_ajax_cronjob', 'ntyp_updatedb_ajax_cronjob' );

////////////wp_ajax_[nopriv_]...... function name


add_action( 'wp_ajax_ntyp_updatedb_ajax', 'ntyp_updatedb_ajax' );

add_action( 'wp_ajax_ntyp_get_order_status_ajax', 'ntyp_get_order_status_ajax' );

add_action( 'wp_enqueue_scripts', 'ntyp_add_ajax_js' );

//add_action( 'admin_notices',...) Check invalid Links in Admin Settings
//ntyp_validate_backend_inputs();
add_action( 'admin_notices', 'ntyp_error_notice_exchangeAPI' );
add_action( 'admin_notices', 'ntyp_error_notice_wallet' );
add_action( 'admin_notices', 'ntyp_error_notice_endPoint' );
add_action( 'admin_notices', 'ntyp_error_notice_blocks_min' );
add_action( 'admin_notices', 'ntyp_error_notice_blocks_max' );
add_action( 'admin_notices', 'ntyp_error_notice_blocks_load' );
add_action( 'admin_notices', 'ntyp_error_notice_compare_min_max' );
add_action( 'admin_notices', 'ntyp_error_notice_link' );

//load jquery if not loaded
add_action( 'wp_enqueue_scripts', function(){
   wp_enqueue_script( 'jquery' );
});

//add_action( 'wp_enqueue_scripts', 'add_scripts' );
add_action( 'wp_head', 'ntyp_add_nextypay_js' );

//add_action('wp_head', 'ntyp_hook_css');
add_action( 'admin_enqueue_scripts', 'ntyp_hook_js' );
//add_action('wp_enqueue_scripts', 'hook_js');

add_action( 'plugins_loaded', 'ntyp_init_nextypay_class' );

add_action( 'woocommerce_thankyou', 'ntyp_add_thankyou', 20 );
add_filter( 'woocommerce_payment_gateways', 'ntyp_add_nextypay_class' );
add_action( 'woocommerce_checkout_process', 'ntyp_process' );
add_action( 'woocommerce_checkout_update_order_meta', 'ntyp_update_order_meta' );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'ntyp_checkout_field_display_admin_order_meta', 10, 1 );

function ntyp_init_nextypay_class(){

  class WC_Nextypay extends WC_Payment_Gateway {

    public $domain;

    /**
    * Constructor for the gateway.
    */
    public function __construct() {

      $this->domain = 'nextypay';

      $this->id                 = 'nextypay';
      $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '' ); //?????
      $this->has_fields         = false;
      $this->method_title       = __( 'Nexty Payment', $this->domain );
      $this->method_description = __( 'Allows payments with NTY.', $this->domain );

      // Load the settings.
      $this->init_form_fields();
      $this->init_settings();

      //$isCheckOut = is_checkout();

      // Define user set variables
      //add logo
      $image_url=plugin_dir_url( __FILE__ ).'images/icon.jpeg';
      $this->title        = $this->get_option( 'title' );
      if ( is_checkout() )
      $this->title = '<img src="'. $image_url.'"  style="width:30px"> '.$this->get_option( 'title' );

      $this->description = $this->get_option( 'description' );
      $this->instruction = $this->get_option( 'instruction', $this->instruction );
      $this->order_status = $this->get_option( 'order_status', 'completed' );
			$this->walletAddress = $this->get_option( 'walletAddress' );
			$this->exchangeAPI = $this->get_option( 'exchangeAPI' );
			$this->endPointAddress = $this->get_option( 'endPointAddress' );
			$this->min_blocks_saved_db = $this->get_option( 'min_blocks_saved_db' );
			$this->max_blocks_saved_db = $this->get_option( 'max_blocks_saved_db' );
			$this->blocks_loaded_each_request = $this->get_option( 'blocks_loaded_each_request' );
      $this->store_currency_code = get_woocommerce_currency();
      $this->url = $this->get_option( 'endPointAddress' );

      // Actions
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'woo_change_order_received_text' ),10,2 );
      add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instruction' ), 10, 3 );
    }

    /**
     * Initialise Gateway Settings Form Fields.
     * With default values
     */
    public function init_form_fields() {

      $this->form_fields = array(
        'enabled' => array(
            'title'   => __( 'Enable/Disable', $this->domain ),
            'type'    => 'checkbox',
            'label'   => __( 'Enable Nexty Payment', $this->domain ),
            'default' => 'yes'
        ),
        'title' => array(
            'title'       => __( 'Title', $this->domain ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
            'default'     => __( 'Nexty Payment', $this->domain ),
            'desc_tip'    => true,
        ),
        'order_status' => array(
            'title'       => __( 'Order Status', $this->domain ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
            'default'     => 'wc-processing',
            'desc_tip'    => true,
            'options'     => wc_get_order_statuses()
        ),
        'description' => array(
            'title'       => __( 'Description', $this->domain ),
            'type'        => 'textarea',
            'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
            'default'     => __('Nexty payment default description', $this->domain),
            'desc_tip'    => true,
        ),
        'instruction' => array(
            'title'       => __( 'Instruction', $this->domain ),
            'type'        => 'textarea',
            'description' => __( 'Instruction that will be added to the thank you page and emails.', $this->domain ),
            'default'     => 'Nexty payment default instruction',
            'desc_tip'    => true,
        ),
        'walletAddress' => array(
            'title'       => __( 'WalletAddress', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Wallet Address description.', $this->domain ),
            'default'     => '0x3489fffae8ca8685dea7b7cd44b19b3d5fb9a5c6',
            'desc_tip'    => true,
        ),
        'exchangeAPI' => array(
            'title'       => __( 'ExchangeAPI', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Exchange API Description.', $this->domain ),
            'default'     => 'https://api.coinmarketcap.com/v2/ticker/',
            'desc_tip'    => true,
	          'class'    => 'valid_url',
	          'id'    => 'exchangeAPI',
        ),
        'endPointAddress' => array(
            'title'       => __( 'EndPointAddress', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Blockchain Endpoint Address Description.', $this->domain ),
            'default'     => 'http://13.228.68.50:8545/',
            'desc_tip'    => true,
	          'class'    => 'valid_url',
	          'id'    => 'endPointAddress',
        ),
        'min_blocks_saved_db' => array(
            'title'       => __( 'min_blocks_saved_db', $this->domain ),
            'type'        => 'number',
            'description' => __( 'Min total Blocks saved in Database.', $this->domain ),
            'default'     => '4000',
            'desc_tip'    => true,
        ),
        'max_blocks_saved_db' => array(
            'title'       => __( 'max_blocks_saved_db', $this->domain ),
            'type'        => 'number',
            'description' => __( 'Max total Blocks saved in Database.', $this->domain ),
            'default'     => '6000',
            'desc_tip'    => true,
        ),
        'blocks_loaded_each_request' => array(
            'title'       => __( 'blocks_loaded_each_request', $this->domain ),
            'type'        => 'number',
            'description' => __( 'Total Blocks loaded each request with Nexty included', $this->domain ),
            'default'     => '20',
            'desc_tip'    => true,
        ),
      );
    }

    private function test_function($data){
        ntyp_debug_to_console($data['QRtext'] .$data['QRtext_hex'] .$data['order_id_test'].$data['order_id_prefix_test'].$data['total_in_coin']);
/*
      echo wpautop( wptexturize( $data['QRtext']) );
      echo wpautop( wptexturize( $data['QRtext_hex']) );
      echo wpautop( wptexturize( $data['order_id_test']) );
      echo wpautop( wptexturize( $data['order_id_prefix_test']) );
      echo wpautop( wptexturize( $data['total_in_coin']) );
*/
    }

    private function add_NTY_to_order_details( $data ) {
      // Set last total row in a variable and remove it.
      $gran_total = $total_rows['order_total'];
      unset( $total_rows['order_total'] );

      // Insert a new row
      $total_rows['recurr_not'] = array(
          'label' => __( 'Total NTY :', 'woocommerce' ),
          'value' => __($data['total_in_coin']." NTY",'woocommerce' ),
      );

      $total_rows['recurr_not'] = array(
          'label' => __( 'Total :', 'woocommerce' ),
          'value' => wc_price( ( $order->get_total() - $order->get_total_tax() ) * $costs  ),
      );

      // Set back last total row
      $total_rows['order_total'] = $gran_total;

      return $total_rows;
    }


    /**
     * Output for the order received page.
    **/
    public function woo_change_order_received_text( $str, $order ){
      global $wpdb;
      $order_status = wc_get_order( $order)->status;
      echo wpautop( wptexturize("<h3>$this->instruction</h3>"));
      if ($order_status=='completed') {
        echo wpautop( wptexturize( 'Payment successed. Thank you and have fun with your Shopping!') );
      } else
      {
        $ntyp_db_prefix=$wpdb->prefix.'nextypay_';
        $updatedb=new Nextypayupdatedb;
        $blockchain= new Nextypayblockchain;
        $functions= new Nextypayfunctions;
        $exchange= new Nextypayexchange;

        $exchange->set_exchangeAPI_url($this->exchangeAPI);
        $exchange->set_store_currency_code($this->store_currency_code);

        $updatedb->set_url($this->url);
        $updatedb->set_connection($wpdb);
        $updatedb->set_includes($blockchain,$functions);
        $updatedb->set_backend_settings($ntyp_db_prefix,$this->store_currency_code,$this->walletAddress,
          $_SERVER['HTTP_HOST'],$this->min_blocks_saved_db,$this->max_blocks_saved_db,$this->blocks_loaded_each_request);

        $data['store_currency_code'] = get_woocommerce_currency();
        $data['order_id'] = wc_get_order( $order)->id;
        $data['order_status'] = wc_get_order( $order)->status;
        $data['order_id_with_prefix']= $data['order_id']."_".$_SERVER['HTTP_HOST'];
        $data['total'] = wc_get_order( $order)->total;
        $data['id']=2714; //NTY
        //$data['id']=1027; //ETH Testing
        //$this->url='https://rinkeby.infura.io/fNuraoH3vBZU8d4MTqdt'; $this->url='https://rinkeby.infura.io/fNuraoH3vBZU8d4MTqdt';

        $data['total_in_coin']=$updatedb->get_order_in_coin($data['order_id']);

        if (!$data['total_in_coin'])  {
          $data['total_in_coin']=$exchange->coinmarketcap_exchange($data['total']);
          $placed_time=date("Y-m-d H:i:s");
          if ($data['total_in_coin']>0)
          $updatedb->insert_order_in_coin_db($data['order_id'],$data['total'],$data['total_in_coin'],$placed_time,strtolower($this->walletAddress));
        }

        $data['QRtext']='{"walletaddress": "'.$this->walletAddress.'","uoid": "'.$data['order_id_with_prefix'].'","amount": "'.$data['total_in_coin'].'"}  ';
        $data['QRtext_hex']="0x".$functions->strToHex($data['QRtext']);
        $data['QRtextencode']= urlencode ( $data['QRtext'] );

        echo wpautop( wptexturize('Waiting for your Payment... Page will be redirected after the payment.'));
        //echo wpautop( wptexturize( "<img style ='width:30px; display: inline ' src = '".get_site_url()."/wp-content/plugins/nextypay/images/Loading.gif'/>" ) );
        echo wpautop( wptexturize( "<img style ='width:30px; display: inline ' src = 'wp-includes/js/tinymce/skins/lightgray/img/loader.gif'/>" ) );
        //Apps Link
        echo wpautop( wptexturize('<p><a href="https://play.google.com/store/apps/details?id=io.nexty.wallet">Click here to download Android payment app</a></p>'));
        echo wpautop( wptexturize('<p><a href="https://nexty.io/ios">Click here to download IOS payment app</a></p>'));

        //QR
        echo wpautop( wptexturize( '<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='
        .$data['QRtextencode'].'&choe=UTF-8" title="Link to Google.com" />' ) );
        add_action( 'woocommerce_get_order_item_totals', function() use ($data,$order) {
          // Set last total row in a variable and remove it.
          $gran_total = $total_rows['order_total'];
          //unset( $total_rows['order_total'] );

          // Insert a new row
          $total_rows['total'] = array(
            'label' => __( 'Total:', 'woocommerce' ),
            'value' => wc_price( $data['total']  ),
          );

          $arg['currency']='NTY';
          $total_rows['total_in_coin'] = array(
              'label' => __( 'Total NTY:', 'woocommerce' ),
              'value' => wc_price($data['total_in_coin'],$arg ),
              //'value' => __( $data['total_in_coin']." NTY", 'woocommerce' ),
          );

          // Set back last total row
          $total_rows['order_total'] = $gran_total;

          return $total_rows;
        } );
        $this->test_function($data);

      }
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instruction( $order, $sent_to_admin, $plain_text = false ) {
        //if ( $this->instruction && ! $sent_to_admin && 'custom' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
        //echo wpautop( wptexturize( $this->instruction ) ) . PHP_EOL;
        if ( $this->instruction && 'nextypay' === $order->payment_method  ) {
            echo wpautop( wptexturize( $this->instruction ) ) . PHP_EOL;
        }
    }

    public function payment_fields(){

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

        // Set order status
        $order->update_status( $this->order_status, __( 'Awaiting Nexty payment ', $this->domain ) );

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url( $order )
        );
    }

	  public function webhook() {
		return; //disable callback for pending payment
	  }

  }
}
