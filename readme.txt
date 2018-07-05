=== Nextypay ===
Contributors: thangnguyen87
Donate link: nexty.io
Tags: blockchain, payment, ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, downloadable, downloads, storefront, woo commerce
Requires at least: 2.6
Tested up to: 4.9.5
Stable tag: 4.9.5
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Nextypay is one of our targets in Nexty e-commerce system to widen the payment with Nexty Coin (NTY), one of the best crypto currencies on the world at the moment.

== Description ==

The Nextypay plugin allows you to accept NTY payment via Nextypay gateway on your WordPress site easily.

One click payment via Nextypay with a remember me feature. Responsive design so it is compatible with all devices and browsers.

Your customers will be redirected to the "Thank You" page after the payment. This page shows them a QR code and links to download mobile app
(the order that they just placed).These apps help your customers to transfer NTY over Blockchain, from customer wallet to your wallet, which is settingable in backend.
its just a option to make the transfer easier with hexed code from the QR code. This plugin load full Blocks from the Blockchain and save any Transaction, which content your Wallet as to_wallet. For the Blocks
loading, a short guide for linux cronjob added in the root folder of plugin (30 blocks loaded every minute, because Nexty blockchain create Blocks every 2 seconds).
How many blocks to save is settingable too in backend (to prevent a block revert). Once the order enough paid, the plugin changes order status to completed, and notifies to customer.
For more details, you can see these Videos below.

= Setup and Usage Video =

Coming soon

= Checkout Demo Video =

https://www.facebook.com/nextycoin/videos/278881949353506/

= Exchange API =
At the moment, we are using only the exchange API service of Coinmarketcap to exchange the order total from store currency into NTY. In the future we will add several APIs to the list.
Coinmarketcap API https://coinmarketcap.com/api/
Public API Terms Of Use https://coinmarketcap.com/api/terms/
Privacy And Cookie Policy https://coinmarketcap.com/privacy/

Coming soon

= Features =

* Quick installation and setup.
* Easily take payment for a service from your site via Nextypay.
* Zero Transfer fee and instant payment
* Fully decentralize
* Secure whole informations, even the QR code only displays hexed code
* Opensource
* Become a partner of the growing up Nexty ecosystem

The setup is very easy. Once you have installed the plugin, all you need to do is setting the backend with full instructions.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to "Plugins->Add New" from your dashboard (coming soon)
2. Search for 'Nextypay'
3. Click 'Install Now'
4. Activate the plugin

= Uploading via WordPress Dashboard =
0. Download link : https://github.com/nextyio/nextypay-woocomerce
1. Navigate to the "Add New" in the plugins dashboard
2. Navigate to the "Upload" area
3. Select `Nextypay.zip` from your computer
4. Click "Install Now"
5. Activate the plugin in the Plugin dashboard

= Using FTP =

0. Download link : https://github.com/nextyio/nextypay-woocomerce
1. Download `Nextypay.zip`
2. Extract the `Nextypay` directory on your computer
3. Upload the `Nextypay` directory to the `/wp-content/plugins/` directory
4. Activate it from the Plugins dashboard

== Frequently Asked Questions ==
1. How to convert the price of NTY in each moment?
Ans: The price will be updated from Coinmarketcap.com and converted to the equivalent amount, which will be shown in the "total amount"
2. What is the benefit of paying by NTY compared to traditional payment method?
Ans: The key features of NTY is instant transfer and Zero transfer fee. This means that we do not charge extra fees like Paypal. The matter seems to be nothing with a $10 goods, but if the fee accounts for 1% of a $1,000,000 item, it does matter.

== Screenshots ==

1. Nexty Plugin Settings
2. Nexty Plugin Payment Page
3. Nexty Plugin Orders Menu

== Upgrade Notice ==
None.

== Changelog ==

= 1.0.0 =
* First Release
