<?php
/**
 * Delivery date handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

use WooCheckoutToolkit\Logger;

defined('ABSPATH') || exit;

/**
 * Class DeliveryDate
 *
 * Handles delivery date picker on checkout.
 */
class DeliveryDate
{
    /**
     * Availability checker instance
     */
    private ?AvailabilityChecker $availability_checker = null;

    /**
     * Initialize delivery date functionality
     */
    public function init(): void
    {
        $this->availability_checker = new AvailabilityChecker();

        // Add field to checkout
        $this->register_field_position();

        // Validate and save
        add_action('woocommerce_checkout_process', [$this, 'validate_delivery_date']);
        add_action('woocommerce_checkout_create_order', [$this, 'save_delivery_date'], 10, 2);
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
        add_action($position, [$this, 'render_delivery_date_field']);
    }

    /**
     * Render delivery date field
     */
    public function render_delivery_date_field(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        do_action('checkout_toolkit_before_delivery_date_field');

        woocommerce_form_field(
            'checkout_toolkit_delivery_date',
            apply_filters('checkout_toolkit_delivery_date_field_args', [
                'type' => 'text',
                'label' => $settings['field_label'],
                'required' => $settings['required'],
                'class' => ['form-row-wide', 'wct-delivery-date-field'],
                'input_class' => ['wct-datepicker'],
                'custom_attributes' => [
                    'readonly' => 'readonly',
                    'data-wct-datepicker' => 'true',
                ],
            ]),
            WC()->checkout->get_value('checkout_toolkit_delivery_date')
        );

        // Hidden field for actual date value (Y-m-d format)
        echo '<input type="hidden" name="wct_delivery_date_value" id="wct_delivery_date_value" value="" />';

        do_action('checkout_toolkit_after_delivery_date_field');
    }

    /**
     * Validate delivery date
     */
    public function validate_delivery_date(): void
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

        $date = isset($_POST['checkout_toolkit_delivery_date_value'])
            ? sanitize_text_field(wp_unslash($_POST['checkout_toolkit_delivery_date_value']))
            : '';

        Logger::debug('Validating delivery date', ['date' => $date, 'required' => $settings['required']]);

        // Required validation
        if ($settings['required'] && empty($date)) {
            Logger::warning('Delivery date required but empty');
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

        // Skip further validation if empty and not required
        if (empty($date)) {
            return;
        }

        // Format validation
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Logger::error('Invalid date format', ['date' => $date]);
            wc_add_notice(
                __('Invalid date format. Please select a valid date.', 'checkout-toolkit-for-woo'),
                'error'
            );
            return;
        }

        // Availability validation
        $is_valid = apply_filters('checkout_toolkit_validate_delivery_date', true, $date);

        if ($is_valid && !$this->availability_checker->is_date_available($date)) {
            Logger::warning('Selected date not available', ['date' => $date]);
            wc_add_notice(
                __('The selected delivery date is not available. Please choose another date.', 'checkout-toolkit-for-woo'),
                'error'
            );
        }
    }

    /**
     * Save delivery date to order
     */
    public function save_delivery_date(\WC_Order $order, array $data): void
    {
        // Nonce already verified in validate_delivery_date via WooCommerce checkout process.
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WooCommerce checkout.
        $date = isset($_POST['checkout_toolkit_delivery_date_value'])
            ? sanitize_text_field(wp_unslash($_POST['checkout_toolkit_delivery_date_value'])) // phpcs:ignore WordPress.Security.NonceVerification.Missing
            : '';

        if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $order->update_meta_data('_wct_delivery_date', $date);

            Logger::info('Delivery date saved to order', [
                'order_id' => $order->get_id(),
                'delivery_date' => $date,
            ]);

            do_action('checkout_toolkit_delivery_date_saved', $order->get_id(), $date);
        }
    }

    /**
     * Get delivery settings
     */
    private function get_settings(): array
    {
        return get_option('checkout_toolkit_delivery_settings', []);
    }
}
