=== WooCommerce Checkout Toolkit ===
Contributors: marwenbrini
Tags: woocommerce, checkout, delivery date, custom fields, order notes
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive checkout enhancement plugin combining delivery scheduling and custom order fields into one powerful solution.

== Description ==

WooCommerce Checkout Toolkit brings together the essential checkout customization features that store owners need most:

1. **Delivery Date Picker** - Let customers choose their preferred delivery date
2. **Custom Order Fields** - Add special instructions, notes, and custom fields to checkout

**One plugin. Two powerful features. Zero bloat.**

= Key Features =

**Delivery Date Picker**

* Clean, modern date picker using Flatpickr
* Set minimum lead time (e.g., 2-day advance notice)
* Set maximum booking window (e.g., 30 days ahead)
* Block specific days of the week (e.g., no Sunday deliveries)
* Block specific dates (holidays, closures)
* Required or optional field
* Multiple date format options
* Mobile-friendly touch interface
* Displays in admin orders, emails, and My Account

**Custom Order Fields**

* Add text input or textarea
* Customizable label and placeholder
* Character limit with counter
* Required or optional
* Multiple position options on checkout
* Displays in admin orders, emails, and My Account

= Perfect For =

* **Florists** - Delivery scheduling + card messages
* **Bakeries** - Order lead time + cake inscriptions
* **Restaurants** - Delivery windows + dietary requirements
* **Gift Shops** - Scheduling + gift messages
* **Local Delivery** - Timing + access instructions
* **B2B** - PO numbers + company details

= Developer Friendly =

* Extensive hooks and filters
* Template overrides supported
* HPOS compatible
* Clean, well-documented code

= Pro Version =

Need more? Upgrade to WooCommerce Checkout Toolkit Pro for:

* Time slot selection with capacity limits
* Multiple custom fields with various types
* Conditional field logic
* Gift wrapping options with fees
* Delivery fees for rush/weekend orders
* Per-product/category field rules
* Priority support

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/woo-checkout-toolkit/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to WooCommerce > Checkout Toolkit to configure settings
4. Enable the features you want and customize to your needs

== Frequently Asked Questions ==

= Does this work with WooCommerce blocks checkout? =

Currently, the plugin works with the classic WooCommerce checkout. Block checkout support is planned for a future release.

= Can I change where the fields appear on checkout? =

Yes! Both the delivery date picker and custom field have position settings with 12+ location options throughout the checkout page.

= Is this compatible with HPOS (High-Performance Order Storage)? =

Yes, the plugin is fully compatible with WooCommerce's High-Performance Order Storage feature.

= Can I translate the plugin? =

Yes, the plugin is fully translation-ready with a .pot file included.

= How do I override the templates? =

Copy templates from `woo-checkout-toolkit/templates/` to `your-theme/woocommerce/woo-checkout-toolkit/` and modify as needed.

== Screenshots ==

1. Delivery date picker on checkout
2. Custom field for special instructions
3. Admin settings - Delivery Date tab
4. Admin settings - Custom Field tab
5. Order details in admin
6. Order information in customer email

== Changelog ==

= 1.0.0 =
* Initial release
* Delivery date picker with availability rules
* Custom order notes/instructions field
* Admin order display integration
* Email integration
* My Account integration
* Full HPOS compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of WooCommerce Checkout Toolkit.
