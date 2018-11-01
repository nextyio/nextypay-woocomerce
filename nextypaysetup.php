<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypaysetup {

  public function install() {
    add_filter( 'woocommerce_currencies', 'add_my_currency' );
    add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);
  }

  public function uninstall() {
  }

}
?>
