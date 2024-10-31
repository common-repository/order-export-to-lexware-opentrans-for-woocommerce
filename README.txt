=== Order Export to Lexware for WooCommerce - OpenTRANS ===
Tags: woocommerce, lexware, order export, opentrans, woocommerce to lexware
Requires at least: 4.0
Donate link: https://www.paypal.me/nikelschubert/6.00EUR
Tested up to: 6.6
Requires PHP: 7.4
License: GPLv3
Stable tag: 1.4.0

Exports WooCommerce orders to Lexware. This plugin exports the orders to an openTRANS XML file to be used in Lexware or other suitable systems.

== Description ==
Exports WooCommerce orders to Lexware. This plugin exports the orders to an openTRANS XML file to be used in Lexware or other suitable systems.

= Features =
**Export filters**
Export orders by order number, all orders after a specific order number, all orders after a specific date or only not yet exported (with [premium add-on](https://beautiful-wp.com/wordpress-plugin/order-export-opentrans-lexware-for-woocommerce/))

With [premium add-on](https://beautiful-wp.com/wordpress-plugin/order-export-opentrans-lexware-for-woocommerce/) you can switch the openTRANS version to 2.1,  export shipping cost as items and map an sku to the shipping cost items and some more.


== Screenshots ==

1. Upload screen

== Installation ==
Just install this plugin and go to WooCommerce > Order Export.

== Changelog ==

= 1.4.0 =
* FIX: Plugin version now in Generator tag of file
* FIX: Sometimes order time was not exported correctly
* IMPROVEMENT: Removed in PHP8.1 deprecated strftime()
* IMPROVEMENT: Preps for name configuration of premium add-on

= 1.3.2 =
* REFACTOR: using alias now in sql queries.

= 1.3.1 =
* FIX: missing isset() was spamming the logs

= 1.3.0 =
* FIX: Critical security fix
* FIX: Removed GB from EU countries

= 1.2.4 =
* FIX: Translation had wrong domain

= 1.2.3 =
* IMPROVMENT: openTrans 2.1 in GA now.

= 1.2.2 =
* FIX: scheduling interval was broken
* FEATURE: support for one file per mail

= 1.2.1 =
* FEATURE: Support for premium plugin automated exports
* Minor Refactorings

= 1.2.0 =
* FEATURE: Support for premium plugin automated exports

= 1.1.1 =
* FIX: If no tax for an item, order export crashed

= 1.1.0 =
* IMPROVMENT: Added error messages for the wp admin
* FIX: Fixing PHP warnings

= 1.0.1 =

* FIX: minor JS error in admin.

= 1.0 =

* First version of this plugin. More to come!
