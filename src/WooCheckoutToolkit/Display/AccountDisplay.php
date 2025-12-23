<?php
/**
 * My Account display handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Display;

defined('ABSPATH') || exit;

/**
 * Class AccountDisplay
 *
 * Handles displaying fields in My Account order view.
 */
class AccountDisplay
{
    /**
     * Initialize account display
     */
    public function init(): void
    {
        add_action('woocommerce_order_details_after_order_table', [$this, 'display_in_order_details']);
    }

    /**
     * Display fields in order details
     */
    public function display_in_order_details(\WC_Order $order): void
    {
        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        // Check if we have anything to display
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_admin']);
        $show_field = !empty($custom_field) && !empty($field_settings['show_in_admin']);
        $show_field_2 = !empty($custom_field_2) && !empty($field_2_settings['show_in_admin']);

        if (!$show_delivery && !$show_field && !$show_field_2) {
            return;
        }

        // Check order status
        $display_statuses = apply_filters('checkout_toolkit_display_order_statuses', ['completed', 'processing', 'on-hold', 'pending']);

        if (!in_array($order->get_status(), $display_statuses, true)) {
            return;
        }

        echo '<section class="wct-order-details">';
        echo '<h2>' . esc_html__('Additional Order Information', 'checkout-toolkit-for-woo') . '</h2>';
        echo '<table class="woocommerce-table woocommerce-table--wct-details shop_table wct-details">';
        echo '<tbody>';

        if ($show_delivery) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            $label = $delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo');

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . esc_html($formatted_date) . '</td>';
            echo '</tr>';
        }

        if ($show_field) {
            $label = $field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo');

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . nl2br(esc_html($custom_field)) . '</td>';
            echo '</tr>';
        }

        if ($show_field_2) {
            $label = $field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo');

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . nl2br(esc_html($custom_field_2)) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</section>';
    }

    /**
     * Format date for display
     */
    private function format_date(string $date, string $format): string
    {
        try {
            $date_obj = new \DateTime($date);
            return date_i18n($format, $date_obj->getTimestamp());
        } catch (\Exception $e) {
            return $date;
        }
    }
}
