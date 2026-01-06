<?php
/**
 * Order display handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Display;

use WooCheckoutToolkit\Admin\DeliveryManager;
use WooCheckoutToolkit\Admin\DeliveryStatus;

defined('ABSPATH') || exit;

/**
 * Class OrderDisplay
 *
 * Handles displaying fields in admin order view.
 */
class OrderDisplay
{
    /**
     * Initialize order display
     */
    public function init(): void
    {
        // Display in admin order meta box
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'display_in_admin']);

        // Add to order meta box (alternative display)
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
    }

    /**
     * Display fields after billing address in admin
     */
    public function display_in_admin(\WC_Order $order): void
    {
        if (!current_user_can('edit_shop_orders')) {
            return;
        }

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

        // Display delivery method
        if (!empty($delivery_method) && !empty($delivery_method_settings['show_in_admin'])) {
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'marwen-checkout-toolkit-for-woocommerce'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'marwen-checkout-toolkit-for-woocommerce'));
            echo '<p><strong>' . esc_html($delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
            echo esc_html($method_label) . '</p>';
        }

        // Display store location (only when pickup)
        if (!empty($store_location) && !empty($store_locations_settings['show_in_admin'])) {
            $location = $this->get_store_location_by_id($store_location, $store_locations_settings);
            if ($location) {
                echo '<p><strong>' . esc_html($store_locations_settings['field_label'] ?? __('Pickup Location', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
                echo '<strong>' . esc_html($location['name']) . '</strong><br>';
                if (!empty($location['address'])) {
                    echo esc_html($location['address']) . '<br>';
                }
                if (!empty($location['phone'])) {
                    echo esc_html__('Phone:', 'marwen-checkout-toolkit-for-woocommerce') . ' ' . esc_html($location['phone']) . '<br>';
                }
                if (!empty($location['hours'])) {
                    echo esc_html__('Hours:', 'marwen-checkout-toolkit-for-woocommerce') . ' ' . esc_html($location['hours']);
                }
                echo '</p>';
            }
        }

        // Display delivery instructions
        if ((!empty($delivery_instructions_preset) || !empty($delivery_instructions_custom)) && !empty($delivery_instructions_settings['show_in_admin'])) {
            echo '<p><strong>' . esc_html($delivery_instructions_settings['field_label'] ?? __('Delivery Instructions', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';

            if (!empty($delivery_instructions_preset)) {
                $preset_label = $this->get_preset_label($delivery_instructions_preset, $delivery_instructions_settings);
                echo esc_html($preset_label) . '<br>';
            }

            if (!empty($delivery_instructions_custom)) {
                echo nl2br(esc_html($delivery_instructions_custom));
            }

            echo '</p>';
        }

        // Display time window
        if (!empty($time_window) && !empty($time_window_settings['show_in_admin'])) {
            $time_label = $this->get_time_slot_label($time_window, $time_window_settings);
            echo '<p><strong>' . esc_html($time_window_settings['field_label'] ?? __('Preferred Time', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
            echo esc_html($time_label) . '</p>';
        }

        // Display delivery date
        if (!empty($delivery_date) && !empty($delivery_settings['show_in_admin'])) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            echo '<p><strong>' . esc_html($delivery_settings['field_label'] ?? __('Delivery Date', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
            echo esc_html($formatted_date) . '</p>';
        }

        // Display custom field
        if ($custom_field !== '' && !empty($field_settings['show_in_admin'])) {
            $output = $this->format_field_value($custom_field, $field_settings);
            $output = apply_filters('marwchto_display_custom_field', $output, $order);
            echo '<p><strong>' . esc_html($field_settings['field_label'] ?? __('Special Instructions', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
            echo nl2br(esc_html($output)) . '</p>';
        }

        // Display custom field 2
        if ($custom_field_2 !== '' && !empty($field_2_settings['show_in_admin'])) {
            $output = $this->format_field_value($custom_field_2, $field_2_settings);
            $output = apply_filters('marwchto_display_custom_field_2', $output, $order);
            echo '<p><strong>' . esc_html($field_2_settings['field_label'] ?? __('Additional Information', 'marwen-checkout-toolkit-for-woocommerce')) . ':</strong><br>';
            echo nl2br(esc_html($output)) . '</p>';
        }

        do_action('marwchto_after_admin_order_display', $order);
    }

    /**
     * Add meta box to order page
     */
    public function add_meta_box(): void
    {
        $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')
            && wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        add_meta_box(
            'marwchto_order_details',
            __('Checkout Toolkit Details', 'marwen-checkout-toolkit-for-woocommerce'),
            [$this, 'render_meta_box'],
            $screen,
            'side',
            'default'
        );
    }

    /**
     * Render meta box content
     */
    public function render_meta_box(\WP_Post|\WC_Order $post_or_order): void
    {
        $order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);

        if (!$order) {
            return;
        }

        $delivery_method = $order->get_meta('_marwchto_delivery_method');
        $delivery_instructions_preset = $order->get_meta('_marwchto_delivery_instructions_preset');
        $delivery_instructions_custom = $order->get_meta('_marwchto_delivery_instructions_custom');
        $time_window = $order->get_meta('_marwchto_time_window');
        $store_location = $order->get_meta('_marwchto_store_location');
        $delivery_date = $order->get_meta('_marwchto_delivery_date');
        $custom_field = $order->get_meta('_marwchto_custom_field');
        $custom_field_2 = $order->get_meta('_marwchto_custom_field_2');

        if (empty($delivery_method) && empty($delivery_instructions_preset) && empty($delivery_instructions_custom) && empty($time_window) && empty($store_location) && empty($delivery_date) && empty($custom_field) && empty($custom_field_2)) {
            echo '<p>' . esc_html__('No additional checkout data.', 'marwen-checkout-toolkit-for-woocommerce') . '</p>';
            return;
        }

        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $delivery_instructions_settings = get_option('marwchto_delivery_instructions_settings', []);
        $time_window_settings = get_option('marwchto_time_window_settings', []);
        $store_locations_settings = get_option('marwchto_store_locations_settings', []);
        $delivery_settings = get_option('marwchto_delivery_settings', []);
        $field_settings = get_option('marwchto_field_settings', []);
        $field_2_settings = get_option('marwchto_field_2_settings', []);

        echo '<div class="marwchto-order-delivery-meta">';

        // Display delivery method
        if (!empty($delivery_method)) {
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'marwen-checkout-toolkit-for-woocommerce'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'marwen-checkout-toolkit-for-woocommerce'));
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html($delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value">' . esc_html($method_label) . '</span>';
            echo '</div>';
        }

        // Display store location (only when pickup)
        if (!empty($store_location)) {
            $location = $this->get_store_location_by_id($store_location, $store_locations_settings);
            if ($location) {
                echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
                echo '<span class="delivery-label">' . esc_html($store_locations_settings['field_label'] ?? __('Pickup Location', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
                echo '<span class="delivery-value" style="margin-top: 5px;">';
                echo '<strong>' . esc_html($location['name']) . '</strong><br>';
                if (!empty($location['address'])) {
                    echo esc_html($location['address']) . '<br>';
                }
                if (!empty($location['phone'])) {
                    echo esc_html__('Phone:', 'marwen-checkout-toolkit-for-woocommerce') . ' ' . esc_html($location['phone']) . '<br>';
                }
                if (!empty($location['hours'])) {
                    echo esc_html__('Hours:', 'marwen-checkout-toolkit-for-woocommerce') . ' ' . esc_html($location['hours']);
                }
                echo '</span>';
                echo '</div>';
            }
        }

        // Display delivery instructions
        if (!empty($delivery_instructions_preset) || !empty($delivery_instructions_custom)) {
            echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
            echo '<span class="delivery-label">' . esc_html($delivery_instructions_settings['field_label'] ?? __('Delivery Instructions', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value" style="margin-top: 5px;">';

            if (!empty($delivery_instructions_preset)) {
                $preset_label = $this->get_preset_label($delivery_instructions_preset, $delivery_instructions_settings);
                echo '<strong>' . esc_html($preset_label) . '</strong><br>';
            }

            if (!empty($delivery_instructions_custom)) {
                echo nl2br(esc_html($delivery_instructions_custom));
            }

            echo '</span>';
            echo '</div>';
        }

        // Display time window
        if (!empty($time_window)) {
            $time_label = $this->get_time_slot_label($time_window, $time_window_settings);
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html($time_window_settings['field_label'] ?? __('Preferred Time', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value">' . esc_html($time_label) . '</span>';
            echo '</div>';
        }

        // Display delivery date
        if (!empty($delivery_date)) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html($delivery_settings['field_label'] ?? __('Delivery Date', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value">' . esc_html($formatted_date) . '</span>';
            echo '</div>';

            // Display delivery status with dropdown
            $delivery_status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html__('Status', 'marwen-checkout-toolkit-for-woocommerce') . '</span>';
            echo '<span class="delivery-value">';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge HTML is escaped in get_badge_html
            echo DeliveryStatus::get_badge_html($delivery_status);
            echo '<select id="marwchto_delivery_status" class="marwchto-delivery-status-select" data-order-id="' . esc_attr($order->get_id()) . '">';
            foreach (DeliveryStatus::get_statuses() as $key => $label) {
                echo '<option value="' . esc_attr($key) . '"' . selected($key, $delivery_status, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            echo '</span>';
            echo '</div>';

            // Display status history
            $this->render_status_history($order);
        }

        // Display custom field
        if ($custom_field !== '') {
            $formatted_value = $this->format_field_value($custom_field, $field_settings);
            echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
            echo '<span class="delivery-label">' . esc_html($field_settings['field_label'] ?? __('Special Instructions', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value" style="margin-top: 5px;">' . nl2br(esc_html($formatted_value)) . '</span>';
            echo '</div>';
        }

        // Display custom field 2
        if ($custom_field_2 !== '') {
            $formatted_value = $this->format_field_value($custom_field_2, $field_2_settings);
            echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
            echo '<span class="delivery-label">' . esc_html($field_2_settings['field_label'] ?? __('Additional Information', 'marwen-checkout-toolkit-for-woocommerce')) . '</span>';
            echo '<span class="delivery-value" style="margin-top: 5px;">' . nl2br(esc_html($formatted_value)) . '</span>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render status history
     */
    private function render_status_history(\WC_Order $order): void
    {
        $history = $order->get_meta(DeliveryManager::META_HISTORY);

        if (empty($history) || !is_array($history)) {
            return;
        }

        // Reverse to show newest first
        $history = array_reverse($history);

        echo '<div class="marwchto-delivery-history">';
        echo '<h4>' . esc_html__('Status History', 'marwen-checkout-toolkit-for-woocommerce') . '</h4>';
        echo '<ul>';

        foreach (array_slice($history, 0, 5) as $entry) {
            $status_label = DeliveryStatus::get_label($entry['status'] ?? '');
            $timestamp = $entry['timestamp'] ?? 0;
            $user_id = $entry['user_id'] ?? 0;

            $user_name = '';
            if ($user_id) {
                $user = get_user_by('id', $user_id);
                $user_name = $user ? $user->display_name : '';
            }

            echo '<li>';
            echo '<span class="history-status">' . esc_html($status_label) . '</span>';
            if ($timestamp) {
                echo '<br><span class="history-date">' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp));
                if ($user_name) {
                    /* translators: %s: User name */
                    echo ' ' . sprintf(esc_html__('by %s', 'marwen-checkout-toolkit-for-woocommerce'), esc_html($user_name));
                }
                echo '</span>';
            }
            echo '</li>';
        }

        echo '</ul>';
        echo '</div>';
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
                    ? __('Yes', 'marwen-checkout-toolkit-for-woocommerce')
                    : __('No', 'marwen-checkout-toolkit-for-woocommerce');

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
