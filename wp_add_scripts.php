<?php

function updatedb_ajax() {
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

function get_order_status_ajax() {
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

function nextypay_js() {
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_js_url=$nextypay_url.'assets/js/';
	echo '<script type="text/javascript" src="'.$nextypay_js_url."nextypay.js".'"></script>';
}

function add_ajax_js() {
	// Enqueue javascript on the frontend.
	wp_enqueue_script(
		'ajax-js',plugin_dir_url( __FILE__ ) . 'assets/js/ajax.js',array('jquery')
	);
	// The wp_localize_script allows us to output the ajax_url path for our script to use.
	wp_localize_script(
		'ajax-js','nextypay_ajax_obj',array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
	);
}

function add_nextypay_thankyou($order_id) {
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

function my_error_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Link Address invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_exchangeAPI() {
    ?>
    <div class="error notice exchangeAPI">
        <p><?php _e( 'exchangeAPI Address invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_endPoint() {
    ?>
    <div class="error notice endPoint">
        <p><?php _e( 'EndPointAddress invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_wallet() {
    ?>
    <div class="error notice walletAddress">
        <p><?php _e( 'walletAddress invalid!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_blocks_min() {
    ?>
    <div class="error notice blocks_min">
        <p><?php _e( 'Mininum blocks number invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_blocks_max() {
    ?>
    <div class="error notice blocks_max">
        <p><?php _e( 'Maxinum blocks number  invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_blocks_load() {
    ?>
    <div class="error notice blocks_load">
        <p><?php _e( 'Loaded blocks number invalid, only positive integer accepted!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function error_notice_compare_min_max() {
    ?>
    <div class="error notice compare_min_max">
        <p><?php _e( 'Maxinum blocks number must be greater than minimum number', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

function validate_backend_inputs(){
	add_action( 'admin_notices', 'error_notice_exchangeAPI' );
	add_action( 'admin_notices', 'error_notice_wallet' );
	add_action( 'admin_notices', 'error_notice_endPoint' );
	add_action( 'admin_notices', 'error_notice_blocks_min' );
	add_action( 'admin_notices', 'error_notice_blocks_max' );
	add_action( 'admin_notices', 'error_notice_blocks_load' );
	add_action( 'admin_notices', 'error_notice_compare_min_max' );
}

function hook_css(){
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_css_url=$nextypay_url.'assets/css/';
	//wp_enqueue_style( 'style', $nextypay_css_url . 'nextypay_styles.css');
}

//js backend
function hook_js(){
	$nextypay_url = plugin_dir_url( __FILE__ ) ;
	$nextypay_js_url=$nextypay_url.'assets/js/';
	wp_enqueue_script( 'script', $nextypay_js_url . 'nextypay.js', array('jquery'), null, true);
}


function nextypay_checkout_field_display_admin_order_meta($order){
  $method = get_post_meta( $order->id, '_payment_method', true );
  if($method != 'nextypay') return;

	return; //disable callback for pending payment
}

function nextypay_update_order_meta( $order_id ) {
  if($_POST['payment_method'] != 'nextypay') return;
	return; //disable callback for pending payment
}

function process_nextypay(){
	return;
    if($_POST['payment_method'] != 'nextypay')
        return;
	//Valid inputs
	return; //disable callback for pending payment
}

function add_nextypay_class( $methods ) {
    $methods[] = 'WC_Nextypay';
    return $methods;
}
 ?>
