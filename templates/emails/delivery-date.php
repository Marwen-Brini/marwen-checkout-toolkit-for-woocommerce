<?php
/**
 * Delivery date in order emails
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/marwen-checkout-toolkit-for-woocommerce/emails/delivery-date.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var string $label          Field label
 * @var string $formatted_date Formatted delivery date
 * @var bool   $plain_text     Whether this is plain text email
 * @var WC_Order $order        Order object
 */

defined('ABSPATH') || exit;

if ($plain_text) :
    echo esc_html($label) . ': ' . esc_html($formatted_date) . "\n";
else :
    ?>
    <tr>
        <th style="text-align: left; padding: 12px; background-color: #f8f8f8;"><?php echo esc_html($label); ?></th>
        <td style="text-align: left; padding: 12px;"><?php echo esc_html($formatted_date); ?></td>
    </tr>
    <?php
endif;
