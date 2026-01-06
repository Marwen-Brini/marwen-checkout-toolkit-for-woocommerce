<?php
/**
 * Custom field in order emails
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/marwen-marwchto-for-woocommerce/emails/custom-field.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var string $label      Field label
 * @var string $value      Field value
 * @var bool   $plain_text Whether this is plain text email
 * @var WC_Order $order    Order object
 */

defined('ABSPATH') || exit;

if ($plain_text) :
    echo esc_html($label) . ': ' . esc_html($value) . "\n";
else :
    ?>
    <tr>
        <th style="text-align: left; padding: 12px; background-color: #f8f8f8;"><?php echo esc_html($label); ?></th>
        <td style="text-align: left; padding: 12px;"><?php echo nl2br(esc_html($value)); ?></td>
    </tr>
    <?php
endif;
