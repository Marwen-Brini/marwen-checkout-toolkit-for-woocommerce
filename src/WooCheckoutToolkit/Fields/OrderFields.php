<?php
/**
 * Order fields handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Fields;

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
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        $show = apply_filters('wct_show_custom_field', true, WC()->cart);

        if (!$show) {
            return;
        }

        do_action('wct_before_custom_field');

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
            'wct_custom_field',
            apply_filters('wct_custom_field_args', $args),
            WC()->checkout->get_value('wct_custom_field')
        );

        do_action('wct_after_custom_field');
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

        $value = isset($_POST['wct_custom_field'])
            ? sanitize_textarea_field($_POST['wct_custom_field'])
            : '';

        // Required validation
        if (!empty($settings['required']) && empty($value)) {
            wc_add_notice(
                sprintf(
                    __('%s is a required field.', 'woo-checkout-toolkit'),
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
                    __('%s is too long. Maximum %d characters allowed.', 'woo-checkout-toolkit'),
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
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        if (!empty($_POST['wct_custom_field'])) {
            $value = sanitize_textarea_field($_POST['wct_custom_field']);
            $value = apply_filters('wct_sanitize_field_value', $value);

            $order->update_meta_data('_wct_custom_field', $value);

            do_action('wct_custom_field_saved', $order->get_id(), $value);
        }
    }

    /**
     * Get field settings
     */
    private function get_settings(): array
    {
        return get_option('wct_field_settings', []);
    }
}
