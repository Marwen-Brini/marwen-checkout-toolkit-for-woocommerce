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
        $delivery_method_settings = get_option('checkout_toolkit_delivery_method_settings', []);
        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        $delivery_method = $order->get_meta('_wct_delivery_method');
        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        // Check if we have anything to display
        $show_delivery_method = !empty($delivery_method) && !empty($delivery_method_settings['show_in_emails']);
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_admin']);
        $show_field = $custom_field !== '' && !empty($field_settings['show_in_admin']);
        $show_field_2 = $custom_field_2 !== '' && !empty($field_2_settings['show_in_admin']);

        if (!$show_delivery_method && !$show_delivery && !$show_field && !$show_field_2) {
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

        if ($show_delivery_method) {
            $label = $delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'checkout-toolkit-for-woo');
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'checkout-toolkit-for-woo'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'checkout-toolkit-for-woo'));

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . esc_html($method_label) . '</td>';
            echo '</tr>';
        }

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
            $output = $this->format_field_value($custom_field, $field_settings);

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . nl2br(esc_html($output)) . '</td>';
            echo '</tr>';
        }

        if ($show_field_2) {
            $label = $field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo');
            $output = $this->format_field_value($custom_field_2, $field_2_settings);

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . nl2br(esc_html($output)) . '</td>';
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

    /**
     * Format field value based on field type
     *
     * @param string $value    The raw value.
     * @param array  $settings The field settings.
     * @return string Formatted value.
     */
    private function format_field_value(string $value, array $settings): string
    {
        $field_type = $settings['field_type'] ?? 'text';

        switch ($field_type) {
            case 'checkbox':
                return $value === '1'
                    ? __('Yes', 'checkout-toolkit-for-woo')
                    : __('No', 'checkout-toolkit-for-woo');

            case 'select':
                $options = $settings['select_options'] ?? [];
                foreach ($options as $option) {
                    if (($option['value'] ?? '') === $value) {
                        return $option['label'] ?? $value;
                    }
                }
                return $value;

            default:
                return $value;
        }
    }
}
