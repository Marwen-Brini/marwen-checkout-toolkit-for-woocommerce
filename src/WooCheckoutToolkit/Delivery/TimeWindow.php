<?php
/**
 * Time window selection handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

use WooCheckoutToolkit\CheckoutDetector;

defined('ABSPATH') || exit;

/**
 * Class TimeWindow
 *
 * Handles time window selection on checkout.
 */
class TimeWindow
{
    /**
     * Initialize time window functionality
     */
    public function init(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Add field to checkout (after delivery date by default)
        add_action('woocommerce_after_order_notes', [$this, 'render_time_window_field'], 15);

        // Validate and save
        add_action('woocommerce_checkout_process', [$this, 'validate_time_window']);
        add_action('woocommerce_checkout_create_order', [$this, 'save_time_window'], 10, 2);
    }

    /**
     * Render time window field
     */
    public function render_time_window_field(): void
    {
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        $time_slots = $settings['time_slots'] ?? [];

        if (empty($time_slots)) {
            return;
        }

        // Check if delivery method is enabled - if so, we need conditional visibility
        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $delivery_method_enabled = !empty($delivery_method_settings['enabled']);
        $default_method = $delivery_method_settings['default_method'] ?? 'delivery';
        $show_only_delivery = $settings['show_only_with_delivery'] ?? true;
        $initial_hidden = $delivery_method_enabled && $show_only_delivery && $default_method === 'pickup';

        do_action('marwchto_before_time_window_field');

        // Wrapper for conditional visibility
        $style = $initial_hidden ? 'display: none;' : '';
        echo '<div class="marwchto-time-window-wrapper" style="' . esc_attr($style) . '">';

        // Build options array for select field
        $options = ['' => __('Select a time...', 'marwen-marwchto-for-woocommerce')];
        foreach ($time_slots as $slot) {
            if (!empty($slot['value']) && !empty($slot['label'])) {
                $options[$slot['value']] = $slot['label'];
            }
        }

        woocommerce_form_field(
            'marwchto_time_window',
            apply_filters('marwchto_time_window_field_args', [
                'type' => 'select',
                'label' => $settings['field_label'] ?? __('Preferred Time', 'marwen-marwchto-for-woocommerce'),
                'required' => $settings['required'] ?? false,
                'class' => ['form-row-wide', 'marwchto-time-window-field'],
                'options' => $options,
            ]),
            WC()->checkout->get_value('marwchto_time_window')
        );

        echo '</div>';

        do_action('marwchto_after_time_window_field');
    }

    /**
     * Validate time window
     */
    public function validate_time_window(): void
    {
        // Nonce verification is handled by WooCommerce checkout process.
        if (!isset($_POST['woocommerce-process-checkout-nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if we should validate (only if delivery method is delivery or not using delivery method)
        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $show_only_delivery = $settings['show_only_with_delivery'] ?? true;

        if (!empty($delivery_method_settings['enabled']) && $show_only_delivery) {
            $delivery_method = isset($_POST['marwchto_delivery_method'])
                ? sanitize_key(wp_unslash($_POST['marwchto_delivery_method']))
                : 'delivery';

            if ($delivery_method === 'pickup') {
                return; // Skip validation for pickup
            }
        }

        $time_window = isset($_POST['marwchto_time_window'])
            ? sanitize_text_field(wp_unslash($_POST['marwchto_time_window']))
            : '';

        // Required validation
        if (!empty($settings['required']) && empty($time_window)) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'marwen-marwchto-for-woocommerce'),
                    '<strong>' . esc_html($settings['field_label'] ?? __('Preferred Time', 'marwen-marwchto-for-woocommerce')) . '</strong>'
                ),
                'error'
            );
            return;
        }

        // Validate against available options
        if (!empty($time_window)) {
            $valid_values = array_column($settings['time_slots'] ?? [], 'value');
            if (!in_array($time_window, $valid_values, true)) {
                wc_add_notice(
                    __('Please select a valid time window.', 'marwen-marwchto-for-woocommerce'),
                    'error'
                );
            }
        }
    }

    /**
     * Save time window to order
     *
     * @param \WC_Order $order Order object.
     * @param array     $data  Posted data.
     */
    public function save_time_window(\WC_Order $order, array $data): void
    {
        // Nonce verification is handled by WooCommerce checkout process.
        if (!isset($_POST['woocommerce-process-checkout-nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if we should save (only if delivery method is delivery or not using delivery method)
        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $show_only_delivery = $settings['show_only_with_delivery'] ?? true;

        if (!empty($delivery_method_settings['enabled']) && $show_only_delivery) {
            $delivery_method = isset($_POST['marwchto_delivery_method'])
                ? sanitize_key(wp_unslash($_POST['marwchto_delivery_method']))
                : 'delivery';

            if ($delivery_method === 'pickup') {
                return; // Skip saving for pickup
            }
        }

        $time_window = isset($_POST['marwchto_time_window'])
            ? sanitize_text_field(wp_unslash($_POST['marwchto_time_window']))
            : '';

        if (!empty($time_window)) {
            $order->update_meta_data('_marwchto_time_window', $time_window);

            /**
             * Action fired after time window is saved.
             *
             * @param int    $order_id    The order ID.
             * @param string $time_window The selected time window.
             */
            do_action('marwchto_time_window_saved', $order->get_id(), $time_window);
        }
    }

    /**
     * Get settings with defaults
     *
     * @return array Settings array.
     */
    private function get_settings(): array
    {
        $defaults = [
            'enabled' => false,
            'required' => false,
            'field_label' => __('Preferred Time', 'marwen-marwchto-for-woocommerce'),
            'time_slots' => [
                ['value' => 'morning', 'label' => __('Morning (9am - 12pm)', 'marwen-marwchto-for-woocommerce')],
                ['value' => 'afternoon', 'label' => __('Afternoon (12pm - 5pm)', 'marwen-marwchto-for-woocommerce')],
                ['value' => 'evening', 'label' => __('Evening (5pm - 8pm)', 'marwen-marwchto-for-woocommerce')],
            ],
            'show_only_with_delivery' => true,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];

        $settings = get_option('marwchto_time_window_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get time slot label by value
     *
     * @param string $value Time slot value.
     * @return string Time slot label or value if not found.
     */
    public function get_time_slot_label(string $value): string
    {
        $settings = $this->get_settings();
        $time_slots = $settings['time_slots'] ?? [];

        foreach ($time_slots as $slot) {
            if (($slot['value'] ?? '') === $value) {
                return $slot['label'] ?? $value;
            }
        }

        return $value;
    }
}
