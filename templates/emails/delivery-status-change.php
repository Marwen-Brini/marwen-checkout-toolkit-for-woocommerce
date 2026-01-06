<?php
/**
 * Delivery status change email template
 *
 * @package WooCheckoutToolkit
 *
 * @var WC_Order $marwchto_order Order object.
 * @var string $marwchto_status Status key.
 * @var string $marwchto_status_label Status label.
 * @var string $marwchto_message Status message.
 */

defined('ABSPATH') || exit;

$marwchto_delivery_date = $marwchto_order->get_meta('_marwchto_delivery_date');
$marwchto_formatted_date = '';

if ($marwchto_delivery_date) {
    try {
        $marwchto_date_obj = new DateTime($marwchto_delivery_date);
        $marwchto_settings = get_option('marwchto_delivery_settings', []);
        $marwchto_format = $marwchto_settings['date_format'] ?? 'F j, Y';
        $marwchto_formatted_date = date_i18n($marwchto_format, $marwchto_date_obj->getTimestamp());
    } catch (Exception $e) {
        $marwchto_formatted_date = $marwchto_delivery_date;
    }
}
?>

<div style="margin-bottom: 20px;">
    <p style="margin: 0 0 16px;">
        <?php
        printf(
            /* translators: %s: Customer first name */
            esc_html__('Hi %s,', 'marwen-marwchto-for-woocommerce'),
            esc_html($marwchto_order->get_billing_first_name())
        );
        ?>
    </p>

    <p style="margin: 0 0 16px;">
        <?php echo esc_html($marwchto_message); ?>
    </p>
</div>

<div style="background: #f7f7f7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <h3 style="margin: 0 0 10px; font-size: 16px;">
        <?php esc_html_e('Delivery Details', 'marwen-marwchto-for-woocommerce'); ?>
    </h3>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Order Number:', 'marwen-marwchto-for-woocommerce'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                #<?php echo esc_html($marwchto_order->get_order_number()); ?>
            </td>
        </tr>
        <?php if ($marwchto_formatted_date) : ?>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Date:', 'marwen-marwchto-for-woocommerce'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                <?php echo esc_html($marwchto_formatted_date); ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Status:', 'marwen-marwchto-for-woocommerce'); ?>
            </td>
            <td style="padding: 5px 0; font-weight: 600;">
                <?php echo esc_html($marwchto_status_label); ?>
            </td>
        </tr>
        <tr>
            <td style="padding: 5px 0; color: #666;">
                <?php esc_html_e('Delivery Address:', 'marwen-marwchto-for-woocommerce'); ?>
            </td>
            <td style="padding: 5px 0;">
                <?php echo wp_kses_post($marwchto_order->get_formatted_shipping_address() ?: $marwchto_order->get_formatted_billing_address()); ?>
            </td>
        </tr>
    </table>
</div>

<p style="margin: 0 0 16px; color: #666; font-size: 13px;">
    <?php esc_html_e('If you have any questions about your delivery, please contact us.', 'marwen-marwchto-for-woocommerce'); ?>
</p>

<p style="margin: 0;">
    <?php esc_html_e('Thank you for your order!', 'marwen-marwchto-for-woocommerce'); ?>
</p>
