<?php
/**
 * Order display handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Display;

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

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');

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

        if (empty($delivery_date) && empty($custom_field)) {
            echo '<p>' . esc_html__('No additional checkout data.', 'checkout-toolkit-for-woo') . '</p>';
            return;
        }

        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);

        if (!empty($delivery_date)) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            echo '<p><strong>' . esc_html($delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo')) . ':</strong><br>';
            echo esc_html($formatted_date) . '</p>';
        }

        if (!empty($custom_field)) {
            echo '<p><strong>' . esc_html($field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo')) . ':</strong><br>';
            echo nl2br(esc_html($custom_field)) . '</p>';
        }
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
