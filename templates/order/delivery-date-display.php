<?php
/**
 * Delivery date display in admin order
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/marwen-marwchto-for-woocommerce/order/delivery-date-display.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var string $label          Field label
 * @var string $formatted_date Formatted delivery date
 * @var string $raw_date       Raw date (Y-m-d format)
 * @var WC_Order $order        Order object
 */

defined('ABSPATH') || exit;
?>

<p class="form-field form-field-wide marwchto-delivery-date-display">
    <strong><?php echo esc_html($label); ?>:</strong><br>
    <?php echo esc_html($formatted_date); ?>
</p>
