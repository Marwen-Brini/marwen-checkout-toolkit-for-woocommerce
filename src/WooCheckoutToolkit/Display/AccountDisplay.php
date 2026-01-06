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
        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $delivery_instructions_settings = get_option('marwchto_delivery_instructions_settings', []);
        $time_window_settings = get_option('marwchto_time_window_settings', []);
        $store_locations_settings = get_option('marwchto_store_locations_settings', []);
        $delivery_settings = get_option('marwchto_delivery_settings', []);
        $field_settings = get_option('marwchto_field_settings', []);
        $field_2_settings = get_option('marwchto_field_2_settings', []);

        $delivery_method = $order->get_meta('_marwchto_delivery_method');
        $delivery_instructions_preset = $order->get_meta('_marwchto_delivery_instructions_preset');
        $delivery_instructions_custom = $order->get_meta('_marwchto_delivery_instructions_custom');
        $time_window = $order->get_meta('_marwchto_time_window');
        $store_location = $order->get_meta('_marwchto_store_location');
        $delivery_date = $order->get_meta('_marwchto_delivery_date');
        $custom_field = $order->get_meta('_marwchto_custom_field');
        $custom_field_2 = $order->get_meta('_marwchto_custom_field_2');

        // Check if we have anything to display
        $show_delivery_method = !empty($delivery_method) && !empty($delivery_method_settings['show_in_emails']);
        $show_store_location = !empty($store_location) && !empty($store_locations_settings['show_in_emails']);
        $show_delivery_instructions = (!empty($delivery_instructions_preset) || !empty($delivery_instructions_custom)) && !empty($delivery_instructions_settings['show_in_emails']);
        $show_time_window = !empty($time_window) && !empty($time_window_settings['show_in_emails']);
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_admin']);
        $show_field = $custom_field !== '' && !empty($field_settings['show_in_admin']);
        $show_field_2 = $custom_field_2 !== '' && !empty($field_2_settings['show_in_admin']);

        if (!$show_delivery_method && !$show_store_location && !$show_delivery_instructions && !$show_time_window && !$show_delivery && !$show_field && !$show_field_2) {
            return;
        }

        // Check order status
        $display_statuses = apply_filters('marwchto_display_order_statuses', ['completed', 'processing', 'on-hold', 'pending']);

        if (!in_array($order->get_status(), $display_statuses, true)) {
            return;
        }

        echo '<section class="marwchto-order-details">';
        echo '<h2>' . esc_html__('Additional Order Information', 'marwen-marwchto-for-woocommerce') . '</h2>';
        echo '<table class="woocommerce-table woocommerce-table--marwchto-details shop_table marwchto-details">';
        echo '<tbody>';

        if ($show_delivery_method) {
            $label = $delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'marwen-marwchto-for-woocommerce');
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'marwen-marwchto-for-woocommerce'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'marwen-marwchto-for-woocommerce'));

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . esc_html($method_label) . '</td>';
            echo '</tr>';
        }

        if ($show_store_location) {
            $location = $this->get_store_location_by_id($store_location, $store_locations_settings);
            if ($location) {
                $label = $store_locations_settings['field_label'] ?? __('Pickup Location', 'marwen-marwchto-for-woocommerce');
                $output = '<strong>' . esc_html($location['name']) . '</strong>';
                if (!empty($location['address'])) {
                    $output .= '<br>' . esc_html($location['address']);
                }
                if (!empty($location['phone'])) {
                    $output .= '<br>' . esc_html__('Phone:', 'marwen-marwchto-for-woocommerce') . ' ' . esc_html($location['phone']);
                }
                if (!empty($location['hours'])) {
                    $output .= '<br>' . esc_html__('Hours:', 'marwen-marwchto-for-woocommerce') . ' ' . esc_html($location['hours']);
                }

                echo '<tr>';
                echo '<th>' . esc_html($label) . '</th>';
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $output is already escaped above.
                echo '<td>' . $output . '</td>';
                echo '</tr>';
            }
        }

        if ($show_delivery_instructions) {
            $label = $delivery_instructions_settings['field_label'] ?? __('Delivery Instructions', 'marwen-marwchto-for-woocommerce');
            $output = '';

            if (!empty($delivery_instructions_preset)) {
                $preset_label = $this->get_preset_label($delivery_instructions_preset, $delivery_instructions_settings);
                $output .= '<strong>' . esc_html($preset_label) . '</strong>';
            }

            if (!empty($delivery_instructions_custom)) {
                if (!empty($output)) {
                    $output .= '<br>';
                }
                $output .= nl2br(esc_html($delivery_instructions_custom));
            }

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $output is already escaped above.
            echo '<td>' . $output . '</td>';
            echo '</tr>';
        }

        if ($show_time_window) {
            $label = $time_window_settings['field_label'] ?? __('Preferred Time', 'marwen-marwchto-for-woocommerce');
            $time_label = $this->get_time_slot_label($time_window, $time_window_settings);

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . esc_html($time_label) . '</td>';
            echo '</tr>';
        }

        if ($show_delivery) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            $label = $delivery_settings['field_label'] ?? __('Delivery Date', 'marwen-marwchto-for-woocommerce');

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . esc_html($formatted_date) . '</td>';
            echo '</tr>';
        }

        if ($show_field) {
            $label = $field_settings['field_label'] ?? __('Special Instructions', 'marwen-marwchto-for-woocommerce');
            $output = $this->format_field_value($custom_field, $field_settings);

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>' . nl2br(esc_html($output)) . '</td>';
            echo '</tr>';
        }

        if ($show_field_2) {
            $label = $field_2_settings['field_label'] ?? __('Additional Information', 'marwen-marwchto-for-woocommerce');
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
                    ? __('Yes', 'marwen-marwchto-for-woocommerce')
                    : __('No', 'marwen-marwchto-for-woocommerce');

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

    /**
     * Get preset label by value
     *
     * @param string $value    Preset value.
     * @param array  $settings Delivery instructions settings.
     * @return string Preset label or value if not found.
     */
    private function get_preset_label(string $value, array $settings): string
    {
        $preset_options = $settings['preset_options'] ?? [];

        foreach ($preset_options as $option) {
            if (($option['value'] ?? '') === $value) {
                return $option['label'] ?? $value;
            }
        }

        return $value;
    }

    /**
     * Get time slot label by value
     *
     * @param string $value    Time slot value.
     * @param array  $settings Time window settings.
     * @return string Time slot label or value if not found.
     */
    private function get_time_slot_label(string $value, array $settings): string
    {
        $time_slots = $settings['time_slots'] ?? [];

        foreach ($time_slots as $slot) {
            if (($slot['value'] ?? '') === $value) {
                return $slot['label'] ?? $value;
            }
        }

        return $value;
    }

    /**
     * Get store location by ID
     *
     * @param string $location_id Location ID.
     * @param array  $settings    Store locations settings.
     * @return array|null Location data or null if not found.
     */
    private function get_store_location_by_id(string $location_id, array $settings): ?array
    {
        $locations = $settings['locations'] ?? [];

        foreach ($locations as $location) {
            if (($location['id'] ?? '') === $location_id) {
                return $location;
            }
        }

        return null;
    }
}
