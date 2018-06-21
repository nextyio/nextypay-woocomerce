<?php
/**
 * Plugin Name: Nexty Payment
 * Plugin URI: https://github.com/nextyio/nextypay-woocomerce
 * Description: A payment gateway for Nexty (NTY).
 * Version: 2.0.0
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

add_filter( 'woocommerce_get_order_totals', 'bbloomer_add_recurring_row_email', 10, 2 );

function bbloomer_add_recurring_row_email( $total_rows, $myorder_obj ) {

$total_rows['recurr_not'] = array(
    'label' => __( 'Rec:', 'woocommerce' ),
    'value' => 'blabla'
);

return $total_rows;
}

//add_filter( 'woocommerce_get_order_item_totals', 'add_custom_order_totals_row', 30, 3 );
function add_custom_order_totals_row( $total_rows, $order, $tax_display ) {
    $costs = 1.01;

    // Set last total row in a variable and remove it.
    $gran_total = $total_rows['order_total'];
    unset( $total_rows['order_total'] );

    // Insert a new row
    $total_rows['recurr_not'] = array(
        'label' => __( 'Total HT :', 'woocommerce' ),
        'value' => wc_price( ( $order->get_total() - $order->get_total_tax() ) * $costs  ),
    );

    // Set back last total row
    $total_rows['order_total'] = $gran_total;

    return $total_rows;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol( $currency_symbol, $currency ) {
     /*switch( $currency ) {
          case 'NTY': $currency_symbol = 'NTY'; break;
     }*/
     $currency_symbol = 'NTY ';
     return $currency_symbol;
}

/**
 * Custom currency and currency symbol
 */
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
     $currencies['NTY'] = __( 'Nexty Coin', 'woocommerce' );
     return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'NTY': $currency_symbol = 'NTY'; break;
     }
     return $currency_symbol;
}

function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=nextypay">Settings</a>';
    array_push( $links, $settings_link );
   	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_action( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

$nextypay_url			= dirname(__FILE__)."/";
$nextypay_js_url	= $nextypay_url.'assets/js/';
$nextypay_css_url	= $nextypay_url.'assets/css/';
$nextypay_lib_url = $nextypay_url.'lib/' ;

include_once $nextypay_lib_url.'nextypayblockchain.php';
include_once $nextypay_lib_url.'nextypayfunctions.php';
include_once $nextypay_lib_url.'nextypayexchange.php';
include_once $nextypay_lib_url.'nextypayupdatedb.php';

include_once $nextypay_url.'nextypaysetup.php';
//$nexty_pay_setup= new Nextypaysetup;

 ////////////// activeable only if woocommerce installed!
register_activation_hook( __FILE__, 'nextypay_install' );
function nextypay_install(){

    // Require parent plugin
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Woocommerce Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
    $nexty_pay_setup= new Nextypaysetup;
    $nexty_pay_setup->install();
}

register_deactivation_hook( __FILE__, 'nextypay_uninstall' );
function nextypay_uninstall(){
    $nexty_pay_setup= new Nextypaysetup;
    $nexty_pay_setup->uninstall();
}

function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}
///////////////////////////////////////////////////////////////////
include_once $nextypay_url.'/wp_add_scripts.php';


//no need login
//add_action( 'wp_ajax_nopriv_get_order_status_ajax', 'get_order_status_ajax' );
//add_action( 'wp_ajax_nopriv_updatedb_ajax', 'updatedb_ajax' );

////////////wp_ajax_[nopriv_]...... function name


add_action( 'wp_ajax_updatedb_ajax', 'updatedb_ajax' );

add_action( 'wp_ajax_get_order_status_ajax', 'get_order_status_ajax' );

add_action( 'wp_enqueue_scripts', 'add_ajax_js' );


//add_action( 'wp_enqueue_scripts', 'add_scripts' );
add_action('wp_head', 'nextypay_js');
add_action("woocommerce_thankyou", "add_nextypay_thankyou", 20);

//Check invalid Links in Admin Settings
add_action( 'admin_notices', 'my_error_notice' );

//load jquery if not loaded
add_action( 'wp_enqueue_scripts', function(){
   wp_enqueue_script( 'jquery' );
});

//add_action('wp_head', 'hook_css');
add_action('admin_enqueue_scripts', 'hook_js');
//add_action('wp_enqueue_scripts', 'hook_js');

add_action('plugins_loaded', 'init_nexty_payment_class');
add_filter( 'woocommerce_payment_gateways', 'add_nextypay_class' );
add_action('woocommerce_checkout_process', 'process_nextypay');
add_action( 'woocommerce_checkout_update_order_meta', 'nextypay_update_order_meta' );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'nextypay_checkout_field_display_admin_order_meta', 10, 1 );

function init_nexty_payment_class(){

    class WC_Nextypay extends WC_Payment_Gateway {


        public $domain;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {

            $this->domain = 'nextypay';

            $this->id                 = 'nextypay';
            $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __( 'Nexty Payment', $this->domain );
            $this->method_description = __( 'Allows payments with NTY.', $this->domain );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            //add logo
            $isCheckOut = is_checkout();
            $image_url=plugin_dir_url( __FILE__ )."images/icon.jpeg";
            //$this->title      = '<img src="'. $image_url.'"  style="width:30px"> '.$this->get_option( 'title' );
            $this->title        = $this->get_option( 'title' );
            if ($isCheckOut) $this->title      = '<img src="'. $image_url.'"  style="width:30px"> '.$this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instruction = $this->get_option( 'instruction', $this->instruction );
            $this->order_status = $this->get_option( 'order_status', 'completed' );
      			$this->walletAddress = $this->get_option( 'walletAddress');
      			$this->exchangeAPI = $this->get_option( 'exchangeAPI');
      			$this->endPointAddress = $this->get_option( 'endPointAddress');
      			$this->min_blocks_saved_db = $this->get_option( 'min_blocks_saved_db');
      			$this->max_blocks_saved_db = $this->get_option( 'max_blocks_saved_db');
      			$this->blocks_loaded_each_request = $this->get_option( 'blocks_loaded_each_request');
            $this->store_currency_code = get_woocommerce_currency();
            //$this->url='https://rinkeby.infura.io/fNuraoH3vBZU8d4MTqdt';
            $this->url='13.228.68.50:8545';

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'woo_change_order_received_text' ),10,2 );
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
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
                    'default'     => 'wc-completed',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
                'description' => array(
                    'title'       => __( 'Description', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
                    'default'     => __('nexty payment default description', $this->domain),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                    'default'     => 'nexty payment default instructions',
                    'desc_tip'    => true,
                ),
				        'walletAddress' => array(
                    'title'       => __( 'WalletAddress', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Wallet Address description.', $this->domain ),
                    'default'     => '0x841A13DDE9581067115F7d9D838E5BA44B537A42',
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
                    'default'     => '13.228.68.50:8545',
                    'desc_tip'    => true,
					          'class'    => 'valid_url',
					          'id'    => 'endPointAddress',
                ),
				        'min_blocks_saved_db' => array(
                    'title'       => __( 'min_blocks_saved_db', $this->domain ),
                    'type'        => 'number',
                    'description' => __( 'Max total Blocks saved in Database.', $this->domain ),
                    'default'     => '40000',
                    'desc_tip'    => true,
                ),
				        'max_blocks_saved_db' => array(
                    'title'       => __( 'max_blocks_saved_db', $this->domain ),
                    'type'        => 'number',
                    'description' => __( 'Max total Blocks saved in Database.', $this->domain ),
                    'default'     => '60000',
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
          debug_to_console($data['QRtext'] .$data['QRtext_hex'] .$data['order_id_test'].$data['order_id_prefix_test'].$data['total_in_coin']);
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
         */
        public function woo_change_order_received_text( $str, $order ){
          global $wpdb;
          $order_status = wc_get_order( $order)->status;
          if ($order_status=='completed') {
            echo wpautop( wptexturize( 'Payment successed. Thank you and have fun with your Shopping!') );
          } else
          {
            $_db_prefix=$wpdb->prefix.'nextypay_';
            $_updatedb=new Nextypayupdatedb;
            $_blockchain= new Nextypayblockchain;
            $_functions= new Nextypayfunctions;
            $_exchange= new Nextypayexchange;

            $_exchange->set_exchangeAPI_url($this->exchangeAPI);
            $_exchange->set_store_currency_code($this->store_currency_code);

            $_updatedb->set_connection($wpdb);
            $_updatedb->set_includes($_blockchain,$_functions);
            $_updatedb->set_backend_settings($_db_prefix,$this->store_currency_code,$this->walletAddress,
              $_SERVER['HTTP_HOST'],$this->min_blocks_saved_db,$this->max_blocks_saved_db,$this->blocks_loaded_each_request);

            $data['store_currency_code'] = get_woocommerce_currency();
            $data['order_id'] = wc_get_order( $order)->id;
            $data['order_status'] = wc_get_order( $order)->status;
            $data['order_id_with_prefix']= $data['order_id']."_".$_SERVER['HTTP_HOST'];
            $data['total'] = intval(wc_get_order( $order)->total);
            $data['id']=2714; //NTY
            //$data['id']=1027; //ETH Testing

            $data['total_in_coin']=$_updatedb->get_order_in_coin($data['order_id']);
            if (!$data['total_in_coin'])  {
              $data['total_in_coin']=$_exchange->coinmarketcap_exchange($data['total']);
              $placed_time=date("Y-m-d H:i:s");
              $_updatedb->insert_order_in_coin_db($data['order_id'],$data['total'],$data['total_in_coin'],$placed_time);
            }

            $data['QRtext']='{"walletaddress": "'.$this->walletAddress.'","uoid": "'.$data['order_id_with_prefix'].'","amount": "'.$data['total_in_coin'].'"}  ';
            $data['QRtext_hex']="0x".$_functions->strToHex($data['QRtext']);
            $data['QRtextencode']= urlencode ( $data['QRtext'] );

            echo wpautop( wptexturize( "<img style ='width:30px; display: inline ' src = '".get_site_url()."/wp-content/plugins/nextypay/images/Loading.gif'/>".' Waiting for your Payment... Page will be redirected after the payment.' ) );
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
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $this->instructions && ! $sent_to_admin && 'custom' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
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
            $order->update_status( "on-hold", __( 'Awaiting Nexty payment ', $this->domain ) );

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
