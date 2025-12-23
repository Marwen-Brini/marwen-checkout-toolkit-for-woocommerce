<?php
/**
 * Custom field display in admin order
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/checkout-toolkit-for-woo/order/custom-field-display.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var string $label   Field label
 * @var string $value   Field value
 * @var WC_Order $order Order object
 */

defined('ABSPATH') || exit;
?>

<p class="form-field form-field-wide wct-custom-field-display">
    <strong><?php echo esc_html($label); ?>:</strong><br>
    <?php echo nl2br(esc_html($value)); ?>
</p>
