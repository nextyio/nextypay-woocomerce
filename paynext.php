<?php
/**
 * Plugin Name: Nexty Payment
 * Plugin URI: 
 * Description: A payment gateway for Nexty.
 * Version: 1.0.0
 * Author: Thang Nguyen
 * Author URI:
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

function admin_options() {
 ?>
 <h2><?php _e('Settings Tab','woocommerce'); ?></h2>
 <table class="form-table">
 <?php $this->generate_settings_html(); ?>
 </table> <?php
 }

/**
 * Nexty Payment Gateway.
 *
 * Provides a Nexty Payment Gateway, mainly for testing purposes.
 */
 
 //add_action('woocommerce_after_checkout_validation', 'bbloomer_deny_checkout_user_pending_orders');
 
function bbloomer_deny_checkout_user_pending_orders( $posted ) {
global $woocommerce;

    wc_add_notice( 'Sorry, please pay your pending orders first by logging into your account', 'error');

}
 
 
function my_error_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Link Address invalid!!!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'my_error_notice' );

 
 add_action( 'wp_enqueue_scripts', function(){
   wp_enqueue_script( 'jquery' );
});
 
function hook_css(){
	
	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_css_url=$nexty_payment_url.'assets/css/';
	wp_enqueue_style( 'style', $nexty_payment_css_url . 'nexty_payment_styles.css');
	
}

function hook_js(){
	
	$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
	$nexty_payment_js_url=$nexty_payment_url.'assets/js/';
	wp_enqueue_script( 'script', $nexty_payment_js_url . 'nexty_payment.js', array('jquery'), null, true);
	
}
 
add_action('wp_head', 'hook_css');
add_action('wp_footer', 'hook_js');
add_action('admin_enqueue_scripts', 'hook_js');
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

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_custom', array( $this, 'thankyou_page' ) );

            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

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
                    'default'     => __('', $this->domain),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
				'walletAddress' => array(
                    'title'       => __( 'WalletAddress', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Wallet Address description.', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
				'exchangeAPI' => array(
                    'title'       => __( 'ExchangeAPI', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Exchange API Description.', $this->domain ),
                    'default'     => 'https://wallet-api.nexty.io/api/exchange/NTYUSD',
                    'desc_tip'    => true,
					'class'    => 'valid_url',
                ),
				'endPointAddress' => array(
                    'title'       => __( 'EndPointAddress', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Blockchain Endpoint Address Description.', $this->domain ),
                    'default'     => 'https://wallet-api.nexty.io:8545',
                    'desc_tip'    => true,
					'class'    => 'valid_url',
                ),
            );
        }
		
		public function nexty_payment_form(){
			           ?>
					   <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
            <div id="custom_input">
                <p class="form-row form-row-wide">
                    <label for="mobile" class=""><?php _e('Mobile Number', $this->domain); ?></label>
                    <input type="text" class="mobile" name="mobile" id="mobile" placeholder="" value="">
                </p>
                <p class="form-row form-row-wide">
                    <label for="transaction" class=""><?php _e('Transaction ID', $this->domain); ?></label>
                    <input type="text" class="" name="transaction" id="transaction" placeholder="" value="">
                </p>
            </div>
			</fieldset>
            <?php 
		}
		
		public function elements_form() {
		?>
		<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
			<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>

			<?php if ( $this->inline_cc_form ) { ?>
				<label for="card-element">
					<?php esc_html_e( 'Credit or debit card', 'woocommerce-gateway-stripe' ); ?>
				</label>

				<div id="stripe-card-element" style="background:#fff;padding:0 1em;border:1px solid #ddd;margin:5px 0;padding:10px 5px;">
				<!-- a Stripe Element will be inserted here. -->
				</div>
			<?php } else { ?>
				<div class="form-row form-row-wide">
					<label><?php esc_html_e( 'Card Number', 'woocommerce-gateway-stripe' ); ?> <span class="required">*</span></label>
					<div class="stripe-card-group">
						<div id="stripe-card-element" style="background:#fff;padding:0 1em;border:1px solid #ddd;margin:5px 0;padding:10px 5px;">
						<!-- a Stripe Element will be inserted here. -->
						</div>

						<i class="stripe-credit-card-brand stripe-card-brand" alt="Credit Card"></i>
					</div>
				</div>

				<div class="form-row form-row-first">
					<label><?php esc_html_e( 'Expiry Date', 'woocommerce-gateway-stripe' ); ?> <span class="required">*</span></label>

					<div id="stripe-exp-element" style="background:#fff;padding:0 1em;border:1px solid #ddd;margin:5px 0;padding:10px 5px;">
					<!-- a Stripe Element will be inserted here. -->
					</div>
				</div>

				<div class="form-row form-row-last">
					<label><?php esc_html_e( 'Card Code (CVC)', 'woocommerce-gateway-stripe' ); ?> <span class="required">*</span></label>
				<div id="stripe-cvc-element" style="background:#fff;padding:0 1em;border:1px solid #ddd;margin:5px 0;padding:10px 5px;">
				<!-- a Stripe Element will be inserted here. -->
				</div>
				</div>
				<div class="clear"></div>
			<?php } ?>

			<!-- Used to display form errors -->
			<div class="stripe-source-errors" role="alert"></div>
			<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
			<div class="clear"></div>
		</fieldset>
		<?php
	}
        /**
         * Output for the order received page.
         */
        public function thankyou_page($order) {
            if ( $this->instructions ){
				//echo wpautop( wptexturize( $this->instructions ) );
				{
					//Informations of Backend
					echo wpautop( wptexturize( $this->walletAddress ) );
					echo wpautop( wptexturize( $this->exchangeAPI ) );
					echo wpautop( wptexturize( $this->endPointAddress ) );
					$order_id = wc_get_order( $order)->id;
					$order_total = intval(wc_get_order( $order)->total);
					$order_status = wc_get_order( $order)->status;
					echo wpautop( wptexturize($order_id ) );
					echo wpautop( wptexturize($order_total ) );
					echo wpautop( wptexturize($order_status ) );
					$QRtext='{"walletaddress": "'.$this->walletAddress.'","uoid": "'.$order_id.'","amount": "'.$order_total.'"}  ';
					$QRtextencode= urlencode ( $QRtext );
					echo wpautop( wptexturize($QRtext ) );
					
					//$nexty_payment_url = plugin_dir_url( __FILE__ ) ;
					//$nexty_payment_qr_url=$nexty_payment_url.'includes/phpqrcode/qrlib.php';
					    //require_once ($nexty_payment_qr_url); 
     
    // outputs image directly into browser, as PNG stream 
   echo wpautop( wptexturize( '<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$QRtextencode.'&choe=UTF-8" title="Link to Google.com" />' ) );
					//echo wc_get_order( $order);
				}
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
			$this->nexty_payment_form();
           /* ?>
            <div id="custom_input">
                <p class="form-row form-row-wide">
                    <label for="mobile" class=""><?php _e('Mobile Number', $this->domain); ?></label>
                    <input type="text" class="variations" name="mobile" id="fname" placeholder="" value="">
                </p>
                <p class="form-row form-row-wide">
                    <label for="transaction" class=""><?php _e('Transaction ID', $this->domain); ?></label>
                    <input type="text" class="" name="transaction" id="transaction" placeholder="" value="">
                </p>
            </div>
            <?php */
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
    }
}

add_filter( 'woocommerce_payment_gateways', 'add_custom_gateway_class' );
function add_custom_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Custom'; 
    return $methods;
}

add_action('woocommerce_checkout_process', 'process_custom_payment');
function process_custom_payment(){

    if($_POST['payment_method'] != 'custom')
        return;
	//Valid inputs
    if( !isset($_POST['mobile']) || empty($_POST['mobile']) )
        wc_add_notice( __( 'Please add your mobile number', $this->domain ), 'error' );


    if( !isset($_POST['transaction']) || empty($_POST['transaction']) )
        wc_add_notice( __( 'Please add your transaction ID', $this->domain ), 'error' );

}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'custom_payment_update_order_meta' );
function custom_payment_update_order_meta( $order_id ) {

    if($_POST['payment_method'] != 'custom')
        return;

    // echo "<pre>";
    // print_r($_POST);
    // echo "</pre>";
    // exit();

    update_post_meta( $order_id, 'mobile', $_POST['mobile'] ."test");
    update_post_meta( $order_id, 'transaction', $_POST['transaction'] );
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'custom_checkout_field_display_admin_order_meta', 10, 1 );
function custom_checkout_field_display_admin_order_meta($order){
    $method = get_post_meta( $order->id, '_payment_method', true );
    if($method != 'custom')
        return;

    $mobile = get_post_meta( $order->id, 'mobile', true );
    $transaction = get_post_meta( $order->id, 'transaction', true );

    echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile .  '</p>';
    echo '<p><strong>'.__( 'Transaction ID').':</strong> ' . $transaction . '</p>';
}