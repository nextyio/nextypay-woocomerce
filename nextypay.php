<?php
/**
 * Plugin Name: Nexty Payment
 * Plugin URI: https://github.com/nextyio/nextypay-woocomerce
 * Description: A payment method with Nexty Coin (NTY)1.
 * Version: 1.1
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
define('WP_DEBUG', true); 
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

include_once $nextypay_lib_url.'nextypayfunctions.php';
include_once $nextypay_lib_url.'nextypayexchange.php';

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

add_action( 'admin_notices', 'ntyp_error_notice_exchangeAPI' );
add_action( 'admin_notices', 'ntyp_error_notice_wallet' );
add_action( 'admin_notices', 'ntyp_error_notice_link' );

//load jquery if not loaded
add_action( 'wp_enqueue_scripts', function(){
   wp_enqueue_script( 'jquery' );
});

add_action( 'admin_enqueue_scripts', 'ntyp_hook_js' );

add_action( 'plugins_loaded', 'ntyp_init_nextypay_class' );

add_filter( 'woocommerce_payment_gateways', 'ntyp_add_nextypay_class' );
add_action( 'woocommerce_checkout_update_order_meta', 'ntyp_update_order_meta' );

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

        // Define user set variables
        //add logo
        $image_url=plugin_dir_url( __FILE__ ).'images/icon.jpeg';
        $this->title        = $this->get_option( 'title' );
        if (is_checkout())
        $this->title = '<img src="'. $image_url.'"  style="width:30px"> '.$this->get_option( 'title' );

        $this->description = $this->get_option( 'description' );
        $this->instruction = $this->get_option( 'instruction', $this->instruction );
        $this->order_status = $this->get_option( 'order_status', 'completed' );
        $this->walletAddress = $this->get_option( 'walletAddress' );
        $this->mid = $this->get_option( 'mid' );
        $this->shopId = $this->get_option( 'shopId' );
        $this->apiUrl = $this->get_option( 'apiUrl' );
        $this->apiKey = $this->get_option( 'apiKey' );
        $this->secretKey = $this->get_option( 'secretKey' );
        $this->exchangeAPI = $this->get_option( 'exchangeAPI' );
        $this->store_currency_code = get_woocommerce_currency();
        $this->url = $this->get_option( 'endPointAddress' );

      // Actions
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      add_action( 'woocommerce_api_nextypaycallback', array( $this, 'webhook' ) );
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
        'mid' => array(
            'title'       => __( 'Merchant ID', $this->domain ),
            'type'        => 'number',
            'description' => __( 'Merchant ID.', $this->domain ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'shopId' => array(
            'title'       => __( 'Shop ID', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Shop ID.', $this->domain ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'walletAddress' => array(
            'title'       => __( 'WalletAddress', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Wallet Address description.', $this->domain ),
            'default'     => '0x915584799f4a52da3807aef514d06e6a952808de',
            'desc_tip'    => true,
        ),
        'apiUrl' => array(
            'title'       => __( 'Gateway API Url', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Gateway API Url.', $this->domain ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'apiKey' => array(
            'title'       => __( 'API Key', $this->domain ),
            'type'        => 'text',
            'description' => __( 'API Key.', $this->domain ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'secretKey' => array(
            'title'       => __( 'Secret Key', $this->domain ),
            'type'        => 'text',
            'description' => __( 'Secret Key.', $this->domain ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'exchangeAPI' => array(
            'title'       => __( '', $this->domain ),
            'type'        => 'hidden',
            'default'     => 'https://api.coinmarketcap.com/v2/ticker/',
            'desc_tip'    => true,
	          'class'    => 'valid_url',
	          'id'    => 'exchangeAPI',
        )
      );
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
        $wpRootFolder = get_option( 'home' );
        $callbackUrl = $wpRootFolder. '/?wc-api=nextypaycallback/';
        $returnUrl = $order->get_view_order_url();
        $shopId = $this->shopId;
        $orderId = $order_id;
        $mid = $this->mid;
        $apiKey = $this->apiKey;
        $amount = $order->get_total();
        $currency = get_woocommerce_currency();
        $toWallet = $this->walletAddress;

        $payload = array(
            "callbackUrl" => $callbackUrl, 
            "returnUrl" => $returnUrl,
            "mid" => $mid,
            "toWallet" => $toWallet,
            "shopId" => $shopId,
            "orderId" => $orderId,
            "apiKey" => $apiKey,
            "amount" => $amount,
            "currency" => $currency,
        );
        
        //use this if you need to redirect the user to the payment page of the bank.
        $querystring = http_build_query( $payload );
        // return your form with the needed parameters
        return array(
            'result'    => 'success',
            'redirect' => $this->apiUrl. 'request.php?' . $querystring,
        );
    }
    private function order_status_to_complete($order_id){
        $order = new WC_Order($order_id);
        $order->update_status('completed');
        return;
      }

	  public function webhook() {

        $orderId = $_POST['orderId'];
        $mid = $this->mid;
        $shopId = $this->shopId;
        $apiKey = $this->apiKey;
        $apiUrl = 'http://gateway.nexty.io/api/payments/capture/&';
        $captureUrl = $apiUrl."mid=$mid&shopId=$shopId&orderId=$orderId&apiKey=$apiKey";

        $request = wp_remote_get( $captureUrl );

        if( is_wp_error( $request ) ) {
            return false; // Bail early
        }
        
        $body = wp_remote_retrieve_body( $request );
        
        $data = json_decode( $body );
        if ($data->status == 'success') {
            header("Content-Type: text/plain");
            print_r( 'Ok' );
            $this->order_status_to_complete($_POST["orderId"]);
            //
            exit();
        }
            wp_send_json_error( $response );
	  }

  }
}
