<?php
/**
 * Delivery status change email template
 *
 * @package WooCheckoutToolkit
 *
 * @var WC_Order $checkout_toolkit_order Order object.
 * @var string $checkout_toolkit_status Status key.
 * @var string $checkout_toolkit_status_label Status label.
 * @var string $checkout_toolkit_message Status message.
 */

defined('ABSPATH') || exit;

$checkout_toolkit_delivery_date = $checkout_toolkit_order->get_meta('_wct_delivery_date');
$checkout_toolkit_formatted_date = '';

if ($checkout_toolkit_delivery_date) {
    try {
        $checkout_toolkit_date_obj = new DateTime($checkout_toolkit_delivery_date);
        $checkout_toolkit_settings = get_option('checkout_toolkit_delivery_settings', []);
        $checkout_toolkit_format = $checkout_toolkit_settings['date_format'] ?? 'F j, Y';
        $checkout_toolkit_formatted_date = date_i18n($checkout_toolkit_format, $checkout_toolkit_date_obj->getTimestamp());
    } catch (Exception $e) {
        $checkout_toolkit_formatted_date = $checkout_toolkit_delivery_date;
    }
}
?>

<div style="margin-bottom: 20px;">
    <p style="margin: 0 0 16px;">
        <?php
        printf(
            /* translators: %s: Customer first name */
            esc_html__('Hi %s,', 'checkout-toolkit-for-woo'),
            esc_html($checkout_toolkit_order->get_billing_first_name())
        );
        ?>
    </p>

    <p style="margin: 0 0 16px;">
        <?php echo esc_html($checkout_toolkit_message); ?>
    </p>
</div>

<div style="background: #f7f7f7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <h3 style="margin: 0 0 10px; font-size: 16px;">
        <?php esc_html_e('Delivery Details', 'checkout-toolkit-for-woo'); ?>
    </h3>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Order Number:', 'checkout-toolkit-for-woo'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                #<?php echo esc_html($checkout_toolkit_order->get_order_number()); ?>
            </td>
        </tr>
        <?php if ($checkout_toolkit_formatted_date) : ?>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Date:', 'checkout-toolkit-for-woo'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                <?php echo esc_html($checkout_toolkit_formatted_date); ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Status:', 'checkout-toolkit-for-woo'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                <?php echo esc_html($checkout_toolkit_status_label); ?>
            </td>
        </tr>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Address:', 'checkout-toolkit-for-woo'); ?>
            </td>
            <td style="padding: 5px 0;">
                <?php echo wp_kses_post($checkout_toolkit_order->get_formatted_shipping_address() ?: $checkout_toolkit_order->get_formatted_billing_address()); ?>
            </td>
        </tr>
    </table>
</div>

<p style="margin: 0 0 16px; color: #666; font-size: 13px;">
    <?php esc_html_e('If you have any questions about your delivery, please contact us.', 'checkout-toolkit-for-woo'); ?>
</p>

<p style="margin: 0;">
    <?php esc_html_e('Thank you for your order!', 'checkout-toolkit-for-woo'); ?>
</p>
