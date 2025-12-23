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

        $args = [
            'type' => $settings['field_type'] === 'textarea' ? 'textarea' : 'text',
            'label' => $settings['field_label'],
            'placeholder' => $settings['field_placeholder'] ?? '',
            'required' => $settings['required'],
            'class' => ['form-row-wide', 'wct-custom-field'],
        ];

        if (!empty($settings['max_length']) && $settings['max_length'] > 0) {
            $args['custom_attributes'] = [
                'maxlength' => $settings['max_length'],
                'data-wct-maxlength' => $settings['max_length'],
            ];
        }

        woocommerce_form_field(
            'checkout_toolkit_custom_field',
            apply_filters('checkout_toolkit_custom_field_args', $args),
            WC()->checkout->get_value('checkout_toolkit_custom_field')
        );

        do_action('checkout_toolkit_after_custom_field');
    }

    /**
     * Validate custom field
     */
    public function validate_field(): void
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

        $value = isset($_POST['checkout_toolkit_custom_field'])
            ? sanitize_textarea_field(wp_unslash($_POST['checkout_toolkit_custom_field']))
            : '';

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

        // Length validation
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

    /**
     * Save custom field to order
     */
    public function save_field(\WC_Order $order, array $data): void
    {
        // Nonce already verified in validate_field via WooCommerce checkout process.
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WooCommerce checkout.
        if (!empty($_POST['checkout_toolkit_custom_field'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WooCommerce checkout.
            $value = sanitize_textarea_field(wp_unslash($_POST['checkout_toolkit_custom_field']));
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
        return get_option('checkout_toolkit_field_settings', []);
    }
}
