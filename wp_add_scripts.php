<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function ntyp_add_nextypay_js() {
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_js_url=$nextypay_url.'assets/js/';
	echo '<script type="text/javascript" src="'.$nextypay_js_url."nextypay.js".'"></script>';
}

function ntyp_error_notice_link() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Link Address invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_error_notice_exchangeAPI() {
    ?>
    <div class="error notice exchangeAPI">
        <p><?php _e( 'exchangeAPI Address invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_error_notice_wallet() {
    ?>
    <div class="error notice walletAddress">
        <p><?php _e( 'walletAddress invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}


function ntyp_validate_backend_inputs(){
	add_action( 'admin_notices', 'ntyp_error_notice_exchangeAPI' );
	add_action( 'admin_notices', 'ntyp_error_notice_wallet' );
	add_action( 'admin_notices', 'ntyp_error_notice_link' );
}

//js backend
function ntyp_hook_js(){
 	$nextypay_url = plugin_dir_url( __FILE__ ) ;
 	$nextypay_js_url=$nextypay_url.'assets/js/';
 	wp_enqueue_script( 'script', $nextypay_js_url . 'nextypay.js', array('jquery'), null, true);
}

function ntyp_update_order_meta( $order_id ) {
    
  if($_POST['payment_method'] != 'nextypay') return;
	return; //disable callback for pending payment
}

function ntyp_add_nextypay_class( $methods ) {
    $methods[] = 'WC_Nextypay';
    return $methods;
}

function ntyp_add_total_NTY(){
  $exchange= new Nextypayexchange;
  $nextypay= new WC_Nextypay;
  $exchange->set_exchangeAPI_url($nextypay->exchangeAPI);
  $store_currency_code= get_woocommerce_currency();
  $cart_total= WC()->cart->total;
  $exchange->set_store_currency_code($store_currency_code);
  $cart_total_NTY= $exchange->coinmarketcap_exchange($cart_total);
  $cart_total_NTY=number_format((float)$cart_total_NTY, 2, '.', '');
  ?>
    <tr class="order-total">
      <th><?php _e( 'Total NTY', 'woocommerce' ); ?></th>
      <td><strong>NTY <?php echo $cart_total_NTY ; ?></strong></td>
    </tr>
<?php

}

function ntyp_add_NTY_currency( $currencies ) {
     $currencies['NTY'] = __( 'Nexty Coin', 'woocommerce' );
     return $currencies;
}

function ntyp_add_NTY_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'NTY': $currency_symbol = 'NTY'; break;
     }
     return $currency_symbol;
}

function ntyp_plugin_add_settings_link( $links ) {
    $settings_link = array('<a href="admin.php?page=wc-settings&tab=checkout&section=nextypay">Settings</a>',);
    //return array_merge(  $settings_link, $links );
    return $settings_link+$links;
}

function nextypay_install(){

    // Require parent plugin
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Woocommerce Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
    $nexty_pay_setup= new Nextypaysetup;
    $nexty_pay_setup->install();
}

function nextypay_uninstall(){
    $nexty_pay_setup= new Nextypaysetup;
    $nexty_pay_setup->uninstall();
}
 ?>
