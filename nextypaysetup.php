<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Nextypaysetup {
  //public $db_prefix=$wpdb->prefix;
  public $nexty_prefix='nextypay_';
  public $blocks_table_name  = "nextypay_blocks";
  public $transactions_table_name  = "nextypay_transactions";
  public $order_in_coin_table_name  = "nextypay_order_in_coin";

  public function create_order_in_coin_table_db(){
    global $wpdb;
    $table_name = $this->order_in_coin_table_name;
    $sql="
      CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "$table_name` (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        store_currency text NOT NULL,
        order_total text NOT NULL,
        order_total_in_coin text NOT NULL,
        to_walletAddress text NOT NULL,
        placed_time DATETIME NOT NULL,
        UNIQUE KEY id (id)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;";
    $wpdb->query($sql);
  }

  public function create_transactions_table_db(){
    global $wpdb;
    $table_name = $this->transactions_table_name;
    $sql="CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "$table_name` (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      block_number mediumint(9) NOT NULL,
      block_hash text NOT NULL,
      hash text NOT NULL,
      from_wallet text NOT NULL,
      to_wallet text NOT NULL,
      value text NOT NULL,
      time DATETIME NOT NULL,
      order_id text NOT NULL,
      UNIQUE KEY id (id)
    ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;";
    $wpdb->query($sql);
  }

  public function create_blocks_table_db(){
    global $wpdb;
  	$table_name = $this->blocks_table_name;
    $sql="
        CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "$table_name` (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
  			number mediumint(9) NOT NULL,
  			hash text NOT NULL,
  			header text NOT NULL,
  			prev_header text NOT NULL,
  			time text NOT NULL,
  			UNIQUE KEY id (id)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;";
    $wpdb->query($sql);
  }

  public function delete_blocks_table_db(){
    global $wpdb;
    $table_name = $this->blocks_table_name;
    $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "$table_name`;");
  }

  public function delete_transactions_table_db(){
    global $wpdb;
    $table_name = $this->transactions_table_name;
    $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "$table_name`;");
  }

  public function delete_order_in_coin_table_db(){
    global $wpdb;
    $table_name = $this->order_in_coin_table_name;
    $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "$table_name`;");
  }


  public function install() {
    $this->create_blocks_table_db();
    $this->create_transactions_table_db();
    $this->create_order_in_coin_table_db();
    add_filter( 'woocommerce_currencies', 'add_my_currency' );
    add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);
  }

  public function uninstall() {
    $this->delete_blocks_table_db();
    $this->delete_transactions_table_db();
    $this->delete_order_in_coin_table_db();
  }

}
?>
