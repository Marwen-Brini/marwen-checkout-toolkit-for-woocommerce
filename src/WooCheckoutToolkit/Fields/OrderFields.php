<?php
/**
 * Order fields handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Fields;

use WooCheckoutToolkit\CheckoutDetector;

defined('ABSPATH') || exit;

/**
 * Class OrderFields
 *
 * Handles custom order fields on checkout.
 */
class OrderFields
{
    /**
     * Initialize order fields functionality
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
        add_action($position, [$this, 'render_custom_field']);
    }

    /**
     * Render custom field
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

        $show = apply_filters('checkout_toolkit_show_custom_field', true, WC()->cart);

        if (!$show) {
            return;
        }

        do_action('checkout_toolkit_before_custom_field');

        $field_type = $settings['field_type'] ?? 'textarea';
        $args = [
            'label' => $settings['field_label'],
            'required' => $settings['required'] ?? false,
            'class' => ['form-row-wide', 'wct-custom-field'],
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
            'checkout_toolkit_custom_field',
            apply_filters('checkout_toolkit_custom_field_args', $args),
            WC()->checkout->get_value('checkout_toolkit_custom_field')
        );

        do_action('checkout_toolkit_after_custom_field');
    }

    /**
     * Get select options formatted for WooCommerce
     *
     * @param array $options Raw options array.
     * @return array Formatted options.
     */
    private function get_select_options(array $options): array
    {
        $formatted = ['' => __('Select an option...', 'checkout-toolkit-for-woo')];

        foreach ($options as $option) {
            if (!empty($option['value'])) {
                $formatted[$option['value']] = $option['label'] ?? $option['value'];
            }
        }

        return $formatted;
    }

    /**
     * Validate custom field
     */
    public function validate_field(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        $field_type = $settings['field_type'] ?? 'textarea';
        $value = $this->get_posted_value($field_type);

        // Required validation
        if (!empty($settings['required']) && empty($value)) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'checkout-toolkit-for-woo'),
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
                        __('%s has an invalid selection.', 'checkout-toolkit-for-woo'),
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
                        __('%1$s is too long. Maximum %2$d characters allowed.', 'checkout-toolkit-for-woo'),
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
            return isset($_POST['checkout_toolkit_custom_field']) ? '1' : '';
        }

        return isset($_POST['checkout_toolkit_custom_field'])
            ? sanitize_textarea_field(wp_unslash($_POST['checkout_toolkit_custom_field']))
            : '';
    }

    /**
     * Save custom field to order
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

        $field_type = $settings['field_type'] ?? 'textarea';
        $value = $this->get_posted_value($field_type);

        // Handle checkbox type - always save (even unchecked as '0')
        if ($field_type === 'checkbox') {
            $value = !empty($value) ? '1' : '0';
            $order->update_meta_data('_wct_custom_field', $value);
            do_action('checkout_toolkit_custom_field_saved', $order->get_id(), $value);
            return;
        }

        if (!empty($value)) {
            $value = apply_filters('checkout_toolkit_sanitize_field_value', $value);
            $order->update_meta_data('_wct_custom_field', $value);
            do_action('checkout_toolkit_custom_field_saved', $order->get_id(), $value);
        }
    }

    /**
     * Get field settings
     */
    private function get_settings(): array
    {
        $defaults = \WooCheckoutToolkit\Main::get_instance()->get_default_field_settings();
        $settings = get_option('checkout_toolkit_field_settings', []);
        return wp_parse_args($settings, $defaults);
    }
}
