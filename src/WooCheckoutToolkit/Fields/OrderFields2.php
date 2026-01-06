<?php
/**
 * Second order field handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Fields;

use WooCheckoutToolkit\CheckoutDetector;
use WooCheckoutToolkit\Admin\Settings;

defined('ABSPATH') || exit;

/**
 * Class OrderFields2
 *
 * Handles the second custom order field on checkout.
 */
class OrderFields2
{
    /**
     * Initialize second order field functionality
     */
    public function init(): void
    {
        // Add field to checkout
        $this->register_field_position();

        // Validate and save
        add_action('woocommerce_checkout_process', [$this, 'validate_field']);
        add_action('woocommerce_checkout_create_order', [$this, 'save_field'], 10, 2);
    }

    /**
     * Register field at configured position
     */
    private function register_field_position(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        $position = $settings['field_position'] ?? 'woocommerce_after_order_notes';
        add_action($position, [$this, 'render_custom_field'], 15);
    }

    /**
     * Render second custom field
     */
    public function render_custom_field(): void
    {
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Check visibility based on cart contents
        if (!$this->should_show_field()) {
            return;
        }

        $show = apply_filters('marwchto_show_custom_field_2', true, WC()->cart);

        if (!$show) {
            return;
        }

        do_action('marwchto_before_custom_field_2');

        $field_type = $settings['field_type'] ?? 'text';
        $args = [
            'label' => $settings['field_label'],
            'required' => $settings['required'] ?? false,
            'class' => ['form-row-wide', 'wct-custom-field', 'wct-custom-field-2'],
        ];

        switch ($field_type) {
            case 'checkbox':
                $args['type'] = 'checkbox';
                $args['label'] = $settings['checkbox_label'] ?: $settings['field_label'];
                break;

            case 'select':
                $args['type'] = 'select';
                $args['options'] = $this->get_select_options($settings['select_options'] ?? []);
                $args['placeholder'] = $settings['field_placeholder'] ?? '';
                break;

            case 'textarea':
                $args['type'] = 'textarea';
                $args['placeholder'] = $settings['field_placeholder'] ?? '';
                if (!empty($settings['max_length']) && $settings['max_length'] > 0) {
                    $args['custom_attributes'] = [
                        'maxlength' => $settings['max_length'],
                        'data-wct-maxlength' => $settings['max_length'],
                    ];
                }
                break;

            default: // text
                $args['type'] = 'text';
                $args['placeholder'] = $settings['field_placeholder'] ?? '';
                if (!empty($settings['max_length']) && $settings['max_length'] > 0) {
                    $args['custom_attributes'] = [
                        'maxlength' => $settings['max_length'],
                        'data-wct-maxlength' => $settings['max_length'],
                    ];
                }
                break;
        }

        woocommerce_form_field(
            'marwchto_custom_field_2',
            apply_filters('marwchto_custom_field_2_args', $args),
            WC()->checkout->get_value('marwchto_custom_field_2')
        );

        do_action('marwchto_after_custom_field_2');
    }

    /**
     * Get select options formatted for WooCommerce
     *
     * @param array $options Raw options array.
     * @return array Formatted options.
     */
    private function get_select_options(array $options): array
    {
        $formatted = ['' => __('Select an option...', 'marwen-checkout-toolkit-for-woocommerce')];

        foreach ($options as $option) {
            if (!empty($option['value'])) {
                $formatted[$option['value']] = $option['label'] ?? $option['value'];
            }
        }

        return $formatted;
    }

    /**
     * Validate second custom field
     */
    public function validate_field(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Skip validation if field is hidden by visibility rules
        if (!$this->should_show_field()) {
            return;
        }

        $field_type = $settings['field_type'] ?? 'text';
        $value = $this->get_posted_value($field_type);

        // Required validation
        if (!empty($settings['required']) && empty($value)) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'marwen-checkout-toolkit-for-woocommerce'),
                    '<strong>' . esc_html($settings['field_label']) . '</strong>'
                ),
                'error'
            );
            return;
        }

        // Select validation - ensure value is a valid option
        if ($field_type === 'select' && !empty($value)) {
            $valid_values = array_column($settings['select_options'] ?? [], 'value');
            if (!in_array($value, $valid_values, true)) {
                wc_add_notice(
                    sprintf(
                        /* translators: %s: Field label */
                        __('%s has an invalid selection.', 'marwen-checkout-toolkit-for-woocommerce'),
                        '<strong>' . esc_html($settings['field_label']) . '</strong>'
                    ),
                    'error'
                );
                return;
            }
        }

        // Length validation (only for text/textarea)
        if (in_array($field_type, ['text', 'textarea'], true)) {
            $max_length = (int) ($settings['max_length'] ?? 0);

            if ($max_length > 0 && strlen($value) > $max_length) {
                wc_add_notice(
                    sprintf(
                        /* translators: 1: Field label, 2: Maximum character count */
                        __('%1$s is too long. Maximum %2$d characters allowed.', 'marwen-checkout-toolkit-for-woocommerce'),
                        '<strong>' . esc_html($settings['field_label']) . '</strong>',
                        $max_length
                    ),
                    'error'
                );
            }
        }
    }

    /**
     * Get posted field value
     *
     * @param string $field_type Field type.
     * @return string Field value.
     */
    private function get_posted_value(string $field_type): string
    {
        // Verify WooCommerce checkout nonce
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return '';
        }

        if ($field_type === 'checkbox') {
            return isset($_POST['marwchto_custom_field_2']) ? '1' : '';
        }

        return isset($_POST['marwchto_custom_field_2'])
            ? sanitize_textarea_field(wp_unslash($_POST['marwchto_custom_field_2']))
            : '';
    }

    /**
     * Save second custom field to order
     *
     * @param \WC_Order $order Order object.
     * @param array     $data  Checkout data.
     */
    public function save_field(\WC_Order $order, array $data): void
    {
        // Skip for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Skip saving if field was hidden by visibility rules
        if (!$this->should_show_field()) {
            return;
        }

        $field_type = $settings['field_type'] ?? 'text';
        $value = $this->get_posted_value($field_type);

        // Handle checkbox type - always save (even unchecked as '0')
        if ($field_type === 'checkbox') {
            $value = !empty($value) ? '1' : '0';
            $order->update_meta_data('_wct_custom_field_2', $value);
            do_action('marwchto_custom_field_2_saved', $order->get_id(), $value);
            return;
        }

        if (!empty($value)) {
            $value = apply_filters('marwchto_sanitize_field_2_value', $value);
            $order->update_meta_data('_wct_custom_field_2', $value);
            do_action('marwchto_custom_field_2_saved', $order->get_id(), $value);
        }
    }

    /**
     * Get field 2 settings
     *
     * @return array Settings array.
     */
    private function get_settings(): array
    {
        $defaults = (new Settings())->get_default_field_2_settings();
        $settings = get_option('marwchto_field_2_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Check if field should be shown based on visibility settings
     *
     * @return bool Whether field should be displayed.
     */
    private function should_show_field(): bool
    {
        $settings = $this->get_settings();

        // Always show if visibility_type is 'always' or not set
        $visibility_type = $settings['visibility_type'] ?? 'always';
        if ($visibility_type === 'always') {
            return true;
        }

        // Get cart contents
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            // Empty cart: show if mode is 'hide', hide if mode is 'show'
            return ($settings['visibility_mode'] ?? 'show') !== 'show';
        }

        $cart_product_ids = [];
        $cart_category_ids = [];

        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $cart_product_ids[] = $product_id;

            // Get product categories
            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $cart_category_ids[] = $term->term_id;
                }
            }
        }

        $cart_category_ids = array_unique($cart_category_ids);

        $has_match = false;

        if ($visibility_type === 'products') {
            $target_ids = array_map('intval', (array) ($settings['visibility_products'] ?? []));
            $has_match = !empty(array_intersect($cart_product_ids, $target_ids));
        } elseif ($visibility_type === 'categories') {
            $target_ids = array_map('intval', (array) ($settings['visibility_categories'] ?? []));
            $has_match = !empty(array_intersect($cart_category_ids, $target_ids));
        }

        // Apply visibility mode
        $visibility_mode = $settings['visibility_mode'] ?? 'show';
        if ($visibility_mode === 'hide') {
            return !$has_match; // Hide when match = show when no match
        }

        return $has_match; // Show when match
    }
}
