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

        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        // Display delivery date
        if (!empty($delivery_date) && !empty($delivery_settings['show_in_admin'])) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            echo '<p><strong>' . esc_html($delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo')) . ':</strong><br>';
            echo esc_html($formatted_date) . '</p>';
        }

        // Display custom field
        if (!empty($custom_field) && !empty($field_settings['show_in_admin'])) {
            $output = apply_filters('checkout_toolkit_display_custom_field', $custom_field, $order);
            echo '<p><strong>' . esc_html($field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo')) . ':</strong><br>';
            echo nl2br(esc_html($output)) . '</p>';
        }

        // Display custom field 2
        if (!empty($custom_field_2) && !empty($field_2_settings['show_in_admin'])) {
            $output = apply_filters('checkout_toolkit_display_custom_field_2', $custom_field_2, $order);
            echo '<p><strong>' . esc_html($field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo')) . ':</strong><br>';
            echo nl2br(esc_html($output)) . '</p>';
        }

        do_action('checkout_toolkit_after_admin_order_display', $order);
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
            'checkout_toolkit_order_details',
            __('Checkout Toolkit Details', 'checkout-toolkit-for-woo'),
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

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        if (empty($delivery_date) && empty($custom_field) && empty($custom_field_2)) {
            echo '<p>' . esc_html__('No additional checkout data.', 'checkout-toolkit-for-woo') . '</p>';
            return;
        }

        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        echo '<div class="wct-order-delivery-meta">';

        // Display delivery date
        if (!empty($delivery_date)) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html($delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo')) . '</span>';
            echo '<span class="delivery-value">' . esc_html($formatted_date) . '</span>';
            echo '</div>';

            // Display delivery status with dropdown
            $delivery_status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;
            echo '<div class="delivery-row">';
            echo '<span class="delivery-label">' . esc_html__('Status', 'checkout-toolkit-for-woo') . '</span>';
            echo '<span class="delivery-value">';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge HTML is escaped in get_badge_html
            echo DeliveryStatus::get_badge_html($delivery_status);
            echo '<select id="wct_delivery_status" class="wct-delivery-status-select" data-order-id="' . esc_attr($order->get_id()) . '">';
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
        if (!empty($custom_field)) {
            echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
            echo '<span class="delivery-label">' . esc_html($field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo')) . '</span>';
            echo '<span class="delivery-value" style="margin-top: 5px;">' . nl2br(esc_html($custom_field)) . '</span>';
            echo '</div>';
        }

        // Display custom field 2
        if (!empty($custom_field_2)) {
            echo '<div class="delivery-row" style="flex-direction: column; align-items: flex-start;">';
            echo '<span class="delivery-label">' . esc_html($field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo')) . '</span>';
            echo '<span class="delivery-value" style="margin-top: 5px;">' . nl2br(esc_html($custom_field_2)) . '</span>';
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

        echo '<div class="wct-delivery-history">';
        echo '<h4>' . esc_html__('Status History', 'checkout-toolkit-for-woo') . '</h4>';
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
                    echo ' ' . sprintf(esc_html__('by %s', 'checkout-toolkit-for-woo'), esc_html($user_name));
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
}
