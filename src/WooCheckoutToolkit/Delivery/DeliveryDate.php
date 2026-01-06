<?php
/**
 * Delivery date handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

use WooCheckoutToolkit\Logger;
use WooCheckoutToolkit\CheckoutDetector;

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
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if delivery method is enabled - if so, we need conditional visibility
        $delivery_method_settings = get_option('marwchto_delivery_method_settings', []);
        $delivery_method_enabled = !empty($delivery_method_settings['enabled']);
        $default_method = $delivery_method_settings['default_method'] ?? 'delivery';
        $initial_hidden = $delivery_method_enabled && $default_method === 'pickup';

        do_action('marwchto_before_delivery_date_field');

        // Wrapper for conditional visibility
        $style = $initial_hidden ? 'display: none;' : '';
        echo '<div class="marwchto-delivery-date-wrapper" style="' . esc_attr($style) . '">';

        // Render estimated delivery message above the date picker
        $this->render_estimated_delivery_message();

        woocommerce_form_field(
            'marwchto_delivery_date',
            apply_filters('marwchto_delivery_date_field_args', [
                'type' => 'text',
                'label' => $settings['field_label'],
                'required' => $settings['required'],
                'class' => ['form-row-wide', 'marwchto-delivery-date-field'],
                'input_class' => ['marwchto-datepicker'],
                'custom_attributes' => [
                    'readonly' => 'readonly',
                    'data-wct-datepicker' => 'true',
                ],
            ]),
            WC()->checkout->get_value('marwchto_delivery_date')
        );

        // Hidden field for actual date value (Y-m-d format)
        echo '<input type="hidden" name="marwchto_delivery_date_value" id="marwchto_delivery_date_value" value="" />';

        echo '</div>';

        do_action('marwchto_after_delivery_date_field');
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

        $date = isset($_POST['marwchto_delivery_date_value'])
            ? sanitize_text_field(wp_unslash($_POST['marwchto_delivery_date_value']))
            : '';

        Logger::debug('Validating delivery date', ['date' => $date, 'required' => $settings['required']]);

        // Required validation
        if ($settings['required'] && empty($date)) {
            Logger::warning('Delivery date required but empty');
            wc_add_notice(
                sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'marwen-marwchto-for-woocommerce'),
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
                __('Invalid date format. Please select a valid date.', 'marwen-marwchto-for-woocommerce'),
                'error'
            );
            return;
        }

        // Availability validation
        $is_valid = apply_filters('marwchto_validate_delivery_date', true, $date);

        if ($is_valid && !$this->availability_checker->is_date_available($date)) {
            Logger::warning('Selected date not available', ['date' => $date]);
            wc_add_notice(
                __('The selected delivery date is not available. Please choose another date.', 'marwen-marwchto-for-woocommerce'),
                'error'
            );
        }
    }

    /**
     * Save delivery date to order
     */
    public function save_delivery_date(\WC_Order $order, array $data): void
    {
        // Skip for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        // Nonce already verified in validate_delivery_date via WooCommerce checkout process.
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WooCommerce checkout.
        $date = isset($_POST['marwchto_delivery_date_value'])
            ? sanitize_text_field(wp_unslash($_POST['marwchto_delivery_date_value'])) // phpcs:ignore WordPress.Security.NonceVerification.Missing
            : '';

        if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $order->update_meta_data('_wct_delivery_date', $date);

            Logger::info('Delivery date saved to order', [
                'order_id' => $order->get_id(),
                'delivery_date' => $date,
            ]);

            do_action('marwchto_delivery_date_saved', $order->get_id(), $date);
        }
    }

    /**
     * Render estimated delivery message
     *
     * Displays a message showing the earliest available delivery date
     * based on cutoff time and lead time settings.
     */
    public function render_estimated_delivery_message(): void
    {
        $settings = $this->get_settings();

        if (empty($settings['show_estimated_delivery'])) {
            return;
        }

        $cutoff_time = $settings['cutoff_time'] ?? '14:00';
        $now = current_time('H:i');
        $is_past_cutoff = ($now >= $cutoff_time);

        $earliest_date = $this->availability_checker->get_earliest_available_date($is_past_cutoff);

        if (empty($earliest_date)) {
            return;
        }

        // Format the date for display
        $formatted_date = date_i18n('l, F j', strtotime($earliest_date));

        if ($is_past_cutoff) {
            $message = $settings['estimated_delivery_message'] ?? 'Order now for delivery as early as {date}';
        } else {
            $message = $settings['cutoff_message'] ?? 'Order by {time} for delivery as early as {date}';
            // Format cutoff time for display
            $formatted_time = date_i18n('g:ia', strtotime("today $cutoff_time"));
            $message = str_replace('{time}', $formatted_time, $message);
        }

        $message = str_replace('{date}', $formatted_date, $message);

        echo '<p class="wct-estimated-delivery-message">' . esc_html($message) . '</p>';
    }

    /**
     * Get delivery settings
     */
    private function get_settings(): array
    {
        $defaults = \WooCheckoutToolkit\Main::get_instance()->get_default_delivery_settings();
        $settings = get_option('marwchto_delivery_settings', []);
        return wp_parse_args($settings, $defaults);
    }
}
