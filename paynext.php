<?php
/**
 * Plugin Name: Nexty Payment
 * Plugin URI:
 * Description: A payment gateway for Nexty.
 * Version: 2.0.0
 * Author: Thang Nguyen
 * Author URI: https://github.com/bestboyvn87/paynext
 * Copyright: © 2018 Fredo / Nexty.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-paynext
 * Domain Path: /languages
 * WC tested up to: 3.3
 * WC requires at least: 2.6
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

 ////////////// activeable only if woocommerce installed!
register_activation_hook( __FILE__, 'child_plugin_activate' );
function child_plugin_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Woocommerce Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}

/**
 * Nexty Payment Gateway.
 *
 * Provides a Nexty Payment Gateway, mainly for testing purposes.
 */

////////////////////////TESTING////////////////////////////////////
/*
add_filter('woocommerce_thankyou_order_received_text', 'woo_change_order_received_text', 10, 2 );
function woo_change_order_received_text( $str, $order ) {
    $new_str = $str . ' We have emailed the purchase receipt to you.';
    return $new_str;
}
*/
function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}
///////////////////////////////////////////////////////////////////

function add_scripts() {
	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_js_url=$nexty_payment_url.'assets/js/';
	wp_enqueue_script( 'app', $nexty_payment_js_url . 'nexty_payment.js', array('jquery'), null, true);
    //wp_enqueue_script( 'app', get_template_directory_uri() . '/assets/js/build.min.js', array(), '1.0.0', true );

    wp_localize_script( 'app', 'my_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'add_scripts' );

function my_custom_js() {
	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_js_url=$nexty_payment_url.'assets/js/';
    echo '<script type="text/javascript" src="'.$nexty_payment_js_url."nexty_payment.js".'"></script>';
}
add_action('wp_head', 'my_custom_js');

function ajax_action() {
	global $wpdb;
  //////////////////Include///////////////////////
	$nexty_payment_url			= dirname(__FILE__);
	$nexty_payment_js_url		= $nexty_payment_url.'/assets/js/';
	$nexty_payment_css_url		= $nexty_payment_url.'/assets/css/';
	$nexty_payment_includes_url = $nexty_payment_url.'/includes/' ;
	include_once $nexty_payment_includes_url.'blockchain.php';
	include_once $nexty_payment_includes_url.'db_functions.php';
	include_once $nexty_payment_includes_url.'exchange.php';
	$blocks_table_name 		= $wpdb->prefix.'woocommerce_nexty_payment_blocks';
	$transactions_table_name= $wpdb->prefix.'woocommerce_nexty_payment_transactions';
	$order_total_in_coin_table_name	= $wpdb->prefix.'woocommerce_nexty_payment_order_total_in_coin';
  //update_nexty_db();
  ////////////////////////////////////////////////

  $order_id = $_POST['order_id'];
	//$order_total= $_POST['order_total'];
	//$wc_currency=get_woocommerce_currency();

	//order just paid
	$order = wc_get_order($order_id);
	$order_status = $order->get_status();
	if ($order_status==='completed') {
		echo "2"; //sum checked before
		exit;
	}
	$paid_sum_hex=get_paid_sum_by_order_id($wpdb,$transactions_table_name,$order_id);
	$paid_sum_coin=number_format(hex_to_coin($paid_sum_hex),15);
	$paid=false;
	$epsilon=1E-5;
	$order_total_in_coin=get_order_total_in_coin_db($wpdb,$order_total_in_coin_table_name,$order_id);

	//echo "paid sum coin= $paid_sum_coin order total in coin = $order_total_in_coin <br>";
	if ($paid_sum_coin+$epsilon>=$order_total_in_coin) $paid=true; //test
	if ($paid){
		$order->update_status( "completed", __( 'Paid Nexty payment ', $WC->domain ) );
		echo "1";
	} else
	{
		echo "0";
	}
    wp_die();
}

add_action('wp_ajax_my_action',        'ajax_action');
add_action('wp_ajax_nopriv_my_action', 'ajax_action');
////////////////////////////////////////////////////////////////////

function update_nexty_db($admin_wallet_address,$min_blocks_saved_db,$max_blocks_saved_db,$blocks_loaded_each_request){
	global $wpdb;
  //////////////////Include///////////////////////
	$nexty_payment_url			= dirname(__FILE__);
	$nexty_payment_js_url		= $nexty_payment_url.'/assets/js/';
	$nexty_payment_css_url		= $nexty_payment_url.'/assets/css/';
	$nexty_payment_includes_url = $nexty_payment_url.'/includes/' ;
	include_once $nexty_payment_includes_url.'blockchain.php';
	include_once $nexty_payment_includes_url.'db_functions.php';
	include_once $nexty_payment_includes_url.'exchange.php';
	$blocks_table_name 		= $wpdb->prefix.'woocommerce_nexty_payment_blocks';
	$transactions_table_name= $wpdb->prefix.'woocommerce_nexty_payment_transactions';
	$order_total_in_coin_table_name	= $wpdb->prefix.'woocommerce_nexty_payment_order_total_in_coin';
  ////////////////////////////////////////////////

	//Create table to save Blocks on the first loading of Admin
	create_blocks_table_db($wpdb,$blocks_table_name);
	//Create table to save Transactions on the first loading of Admin
	create_transactions_table_db($wpdb,$transactions_table_name);
	//Create table to exchange order total from store currency to coin
	create_order_total_in_coin_table_db($wpdb,$order_total_in_coin_table_name);

	//API to get Informations of Blocks, Transactions
	$url = 'https://rinkeby.infura.io/fNuraoH3vBZU8d4MTqdt';

	//insert latest Block on the first loading of Admin, ignore all Blocks before
	init_blocks_table_db($wpdb,$url,$blocks_table_name,$transactions_table_name,$admin_wallet_address);

	//scan from this block number
	$start_block_number=get_max_block_number_db($wpdb,$blocks_table_name) +1;
	//$start_block_number=2378736   ; //testing transaction at 2378737
	for ($scan_block_number=$start_block_number;
		//$scan_block_number<=$start_block_number+$blocks_loaded_each_request;
		$scan_block_number<=$start_block_number+$blocks_loaded_each_request; //test
		$scan_block_number++)
	{
		$hex_scan_block_number="0x".strval(dechex($scan_block_number)); //convert to hex
		$block=get_block_by_number($url,$hex_scan_block_number);	//get Block by number with API
		$block_content=$block['result'];
		if (!$block_content) break;	//Stop scanning at a empty block, still not avaiable
		//put Block to Database, table $blocks_table_name
		insert_block_db($wpdb,$block_content,$blocks_table_name,$transactions_table_name,$admin_wallet_address);
	}

	// keep $min_blocks_saved_db Blocks, and delete the oldest blocks, in Admin Setting
	delete_old_blocks_db($wpdb,$blocks_table_name,$min_blocks_saved_db,$max_blocks_saved_db);
}

// Request AJAX when order placed
add_action("woocommerce_thankyou", "add_custom_action_thankyou", 20);
if(!function_exists('add_custom_action_thankyou')) {
    function add_custom_action_thankyou($order_id) {
        if ($order_id > 0) {
		$order = wc_get_order($order_id);
		$order_status = $order->get_status();// order status
		$order_total = $order->get_total(); // order total
		$order_id = $order->get_id(); // order id
		if (($order instanceof WC_Order) && ($order_status!='completed')) {
		/**
		* full list methods and property that can be accessed from $order object
		* https://docs.woocommerce.com/wc-apidocs/class-WC_Order.html
		*/
                ?>
                <script type="text/javascript">
                  call_ajax(new Date(),<?php echo $order_total; ?>,<?php echo $order_id; ?>,600,3 );
                </script>
                <?php
            }
        }
    }
}

//Check invalid Links in Admin Settings
function my_error_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Link Address invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'my_error_notice' );

//load jquery if not loaded
add_action( 'wp_enqueue_scripts', function(){
   wp_enqueue_script( 'jquery' );
});

function hook_css(){

	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_css_url=$nexty_payment_url.'assets/css/';
	//wp_enqueue_style( 'style', $nexty_payment_css_url . 'nexty_payment_styles.css');
}

function hook_js(){
	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_js_url=$nexty_payment_url.'assets/js/';
	wp_enqueue_script( 'script', $nexty_payment_js_url . 'nexty_payment.js', array('jquery'), null, true);
}

//add_action('wp_head', 'hook_css');
add_action('admin_enqueue_scripts', 'hook_js');
//add_action('wp_enqueue_scripts', 'hook_js');

add_action('plugins_loaded', 'init_custom_gateway_class');
function init_custom_gateway_class(){

    class WC_Gateway_Custom extends WC_Payment_Gateway {


        public $domain;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {

            $this->domain = 'custom_payment';

            $this->id                 = 'custom';
            $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __( 'Nexty Payment', $this->domain );
            $this->method_description = __( 'Allows payments with custom gateway.', $this->domain );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );
            $this->order_status = $this->get_option( 'order_status', 'completed' );
      			$this->walletAddress = $this->get_option( 'walletAddress');
      			$this->exchangeAPI = $this->get_option( 'exchangeAPI');
      			$this->endPointAddress = $this->get_option( 'endPointAddress');
      			$this->min_blocks_saved_db = $this->get_option( 'min_blocks_saved_db');
      			$this->max_blocks_saved_db = $this->get_option( 'max_blocks_saved_db');
      			$this->blocks_loaded_each_request = $this->get_option( 'blocks_loaded_each_request');

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'woo_change_order_received_text' ),10,2 );
			      update_nexty_db($this->walletAddress,$this->min_blocks_saved_db,$this->max_blocks_saved_db,$this->blocks_loaded_each_request);
            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
      			// You can also register a webhook here
      			//add_action( 'woocommerce_api_nextyapi', array( $this, 'webhook' ) );
        }

        /**
         * Initialise Gateway Settings Form Fields.
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
                    'default'     => 'https://wallet-api.nexty.io/api/exchange/NTYUSD',
                    'desc_tip'    => true,
					          'class'    => 'valid_url',
					          'id'    => 'exchangeAPI',
                ),
				        'endPointAddress' => array(
                    'title'       => __( 'EndPointAddress', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Blockchain Endpoint Address Description.', $this->domain ),
                    'default'     => 'https://wallet-api.nexty.io:8545',
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

        /**
         * Output for the order received page.
         */
        public function woo_change_order_received_text( $str, $order ){
          global $wpdb;
          $order_status = wc_get_order( $order)->status;
          //echo wpautop( wptexturize( $this->instructions ) );
          if ($order_status=='completed') {
            echo wpautop( wptexturize( 'Payment successed. Thank you and have fun with your Shopping!') );
          } else
          {
            ////////////////////////////////
            $nexty_payment_url			= dirname(__FILE__);
            $nexty_payment_js_url		= $nexty_payment_url.'/assets/js/';
            $nexty_payment_css_url		= $nexty_payment_url.'/assets/css/';
            $nexty_payment_includes_url = $nexty_payment_url.'/includes/' ;
            include_once $nexty_payment_includes_url.'blockchain.php';
            include_once $nexty_payment_includes_url.'db_functions.php';
            include_once $nexty_payment_includes_url.'exchange.php';
            $blocks_table_name 		= $wpdb->prefix.'woocommerce_nexty_payment_blocks';
            $transactions_table_name= $wpdb->prefix.'woocommerce_nexty_payment_transactions';
            $order_total_in_coin_table_name	= $wpdb->prefix.'woocommerce_nexty_payment_order_total_in_coin';

            $store_currency = get_woocommerce_currency();
            $order_id = wc_get_order( $order)->id;
            $order_id_with_prefix= $order_id."_".$_SERVER['HTTP_HOST'];
            $order_total = intval(wc_get_order( $order)->total);
            $id=2714; //NTY
            $id=1027; //ETH
            $order_total_in_coin = $order_total/coinmarketcap_coin_to_store_currency($id,$store_currency);
            //$order_status = wc_get_order( $order)->status;
            $placed_time = date("Y-m-d H:i:s");
            $QRtext='{"walletaddress": "'.$this->walletAddress.'","uoid": "'.$order_id_with_prefix.'","amount": "'.$order_total.'"}  ';
            $QRtext_hex="0x".strToHex($QRtext);
            $QRtextencode= urlencode ( $QRtext );
            insert_order_total_in_coin_db($wpdb,$order_total_in_coin_table_name,$order_id,$store_currency,$order_total,$order_total_in_coin,$placed_time);
            $order_id_test= get_order_id_from_input($QRtext_hex);
            $order_id_prefix_test= get_order_id_prefix_from_input($QRtext_hex);

            echo wpautop( wptexturize( $QRtext) );
            echo wpautop( wptexturize( $QRtext_hex) );
            echo wpautop( wptexturize( $order_id_test) );
            echo wpautop( wptexturize( $order_id_prefix_test) );
            echo wpautop( wptexturize( $order_total_in_coin) );
            //Informations of Backend
            /*echo wpautop( wptexturize( $wc_currency ) );
            echo wpautop( wptexturize( $this->walletAddress ) );
            echo wpautop( wptexturize( $this->exchangeAPI ) );
            echo wpautop( wptexturize( $this->endPointAddress ) );
            echo wpautop( wptexturize( $this->endPointAddress ) );
            echo wpautop( wptexturize($order_id ) );
            echo wpautop( wptexturize($order_total ) );
            echo wpautop( wptexturize($order_status ) );
            echo wpautop( wptexturize($QRtext ) );
            echo wpautop( wptexturize($QRtext_hex ) );
            echo str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'nextyapi', home_url( '/' ) ) );
            */

            //require_once ($nexty_payment_qr_url);
            // outputs QR code image directly into browser, as PNG stream

            echo wpautop( wptexturize( 'Waiting for your Payment... Page will be redirected after the payment.' ) );
            echo wpautop( wptexturize( '<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='
            .$QRtextencode.'&choe=UTF-8" title="Link to Google.com" />' ) );
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
            //$order->reduce_order_stock();

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

add_filter( 'woocommerce_payment_gateways', 'add_custom_gateway_class' );
function add_custom_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Custom';
    return $methods;
}

add_action('woocommerce_checkout_process', 'process_custom_payment');
function process_custom_payment(){
	return;
    if($_POST['payment_method'] != 'custom')
        return;
	//Valid inputs
	return; //disable callback for pending payment
}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'custom_payment_update_order_meta' );
function custom_payment_update_order_meta( $order_id ) {

    if($_POST['payment_method'] != 'custom')
        return;

	return; //disable callback for pending payment
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'custom_checkout_field_display_admin_order_meta', 10, 1 );
function custom_checkout_field_display_admin_order_meta($order){
    $method = get_post_meta( $order->id, '_payment_method', true );
    if($method != 'custom') return;

	return; //disable callback for pending payment
}
