<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function ntyp_debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

function ntyp_updatedb_ajax() {
  //wp_die();
  //load block only with cron job.comment wp_die to load blocks with request of nexty payment

	global $wpdb;
	$_nextypay_obj= new WC_Nextypay;

	$_db_prefix=$wpdb->prefix.'nextypay_';
	$_updatedb=new Nextypayupdatedb;
	$_blockchain= new Nextypayblockchain;
	$_functions= new Nextypayfunctions;

	$_updatedb->set_url($_nextypay_obj->url);
	$_updatedb->set_connection($wpdb);
	$_updatedb->set_includes($_blockchain,$_functions);
	$_updatedb->set_backend_settings($_db_prefix,$_nextypay_obj->store_currency_code,$_nextypay_obj->walletAddress,
				 $_SERVER['HTTP_HOST'],$_nextypay_obj->min_blocks_saved_db,$_nextypay_obj->max_blocks_saved_db,$_nextypay_obj->blocks_loaded_each_request);

	$_updatedb->updatedb();

	 // Always die in functions echoing ajax content
	wp_die();

}

function ntyp_updatedb_ajax_cronjob() {
	global $wpdb;
	$_nextypay_obj= new WC_Nextypay;

	$_db_prefix=$wpdb->prefix.'nextypay_';
	$_updatedb=new Nextypayupdatedb;
	$_blockchain= new Nextypayblockchain;
	$_functions= new Nextypayfunctions;

	$_updatedb->set_url($_nextypay_obj->url);
	$_updatedb->set_connection($wpdb);
	$_updatedb->set_includes($_blockchain,$_functions);
	$_updatedb->set_backend_settings($_db_prefix,$_nextypay_obj->store_currency_code,$_nextypay_obj->walletAddress,
				 $_SERVER['HTTP_HOST'],$_nextypay_obj->min_blocks_saved_db,$_nextypay_obj->max_blocks_saved_db,35);

	$_updatedb->updatedb();

	 // Always die in functions echoing ajax content
	wp_die();
}

function ntyp_get_order_status_ajax() {
	global $wpdb;
	// The $_REQUEST contains all the data sent via ajax
	if ( isset($_REQUEST) ) {
			 $_nextypay_obj= new WC_Nextypay;
			 $_db_prefix=$wpdb->prefix.'nextypay_';
			 $_updatedb=new Nextypayupdatedb;
			 $_blockchain= new Nextypayblockchain;
			 $_functions= new Nextypayfunctions;
			 $_updatedb->set_url($_nextypay_obj->url);
			 $_updatedb->set_connection($wpdb);
			 $_updatedb->set_includes($_blockchain,$_functions);
			 $_updatedb->set_backend_settings($_db_prefix,$_nextypay_obj->store_currency_code,$_nextypay_obj->walletAddress,
				 $_SERVER['HTTP_HOST'],$_nextypay_obj->min_blocks_saved_db,$_nextypay_obj->max_blocks_saved_db,$_nextypay_obj->blocks_loaded_each_request);

			$order_id=$_REQUEST['order_id'];//echo $order_id;
			echo $_updatedb->is_paid_sum_enough($order_id);
			if ($_updatedb->is_paid_sum_enough($order_id)) {
					 echo "1";
					// $_updatedb->order_status_to_complete($order_id);
					exit;
			}
			echo "0";
	}
	// Always die in functions echoing ajax content
	wp_die();
}

function ntyp_add_nextypay_js() {
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_js_url=$nextypay_url.'assets/js/';
	echo '<script type="text/javascript" src="'.$nextypay_js_url."nextypay.js".'"></script>';
}

function ntyp_add_ajax_js() {
	// Enqueue javascript on the frontend.
	wp_enqueue_script(
		'ajax-js',plugin_dir_url( __FILE__ ) . 'assets/js/ajax.js',array('jquery')
	);
	// The wp_localize_script allows us to output the ajax_url path for our script to use.
	wp_localize_script(
		'ajax-js','nextypay_ajax_obj',array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
	);
}

function ntyp_add_thankyou($order_id) {
  if ($order_id > 0) {
		$order = wc_get_order($order_id);
		$order_status = $order->get_status();// order status
		$order_total = $order->get_total(); // order total
		$order_id = $order->get_id(); // order id
		if (($order instanceof WC_Order) && ($order_status!='completed')) {
        ?>
        <script type="text/javascript">
              call_ajax(new Date(), <?php echo $order_id; ?>,600,3 );
        </script>
        <?php
    }
  }
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

function ntyp_error_notice_endPoint() {
    ?>
    <div class="error notice endPoint">
        <p><?php _e( 'EndPointAddress invalid!', 'my_plugin_textdomain' ); ?></p>
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

function ntyp_error_notice_blocks_min() {
    ?>
    <div class="error notice blocks_min">
        <p><?php _e( 'Mininum blocks number invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_error_notice_blocks_max() {
    ?>
    <div class="error notice blocks_max">
        <p><?php _e( 'Maxinum blocks number  invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_error_notice_blocks_load() {
    ?>
    <div class="error notice blocks_load">
        <p><?php _e( 'Loaded blocks number invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_error_notice_compare_min_max() {
    ?>
    <div class="error notice compare_min_max">
        <p><?php _e( 'Maxinum blocks number must be greater than minimum number', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function ntyp_validate_backend_inputs(){
	add_action( 'admin_notices', 'ntyp_error_notice_exchangeAPI' );
	add_action( 'admin_notices', 'ntyp_error_notice_wallet' );
	add_action( 'admin_notices', 'ntyp_error_notice_endPoint' );
	add_action( 'admin_notices', 'ntyp_error_notice_blocks_min' );
	add_action( 'admin_notices', 'ntyp_error_notice_blocks_max' );
	add_action( 'admin_notices', 'ntyp_error_notice_blocks_load' );
	add_action( 'admin_notices', 'ntyp_error_notice_compare_min_max' );
	add_action( 'admin_notices', 'ntyp_error_notice_link' );
}

function ntyp_hook_css(){
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_css_url=$nextypay_url.'assets/css/';
	//wp_enqueue_style( 'style', $nextypay_css_url . 'nextypay_styles.css');
}

//js backend
function ntyp_hook_js(){
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_js_url=$nextypay_url.'assets/js/';
	wp_enqueue_script( 'script', $nextypay_js_url . 'nextypay.js', array('jquery'), null, true);
}


function ntyp_checkout_field_display_admin_order_meta($order){
  $method = get_post_meta( $order->id, '_payment_method', true );
  if($method != 'nextypay') return;

	return; //disable callback for pending payment
}

function ntyp_update_order_meta( $order_id ) {
  if($_POST['payment_method'] != 'nextypay') return;
	return; //disable callback for pending payment
}

function ntyp_process(){
	return;
    if($_POST['payment_method'] != 'nextypay')
        return;
	//Valid inputs
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
