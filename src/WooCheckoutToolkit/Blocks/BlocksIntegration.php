<?php
/**
 * WooCommerce Blocks integration
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use WooCheckoutToolkit\Main;
use WooCheckoutToolkit\Admin\Settings;

defined('ABSPATH') || exit;

/**
 * Class BlocksIntegration
 *
 * Integrates checkout fields with WooCommerce Blocks checkout.
 */
class BlocksIntegration implements IntegrationInterface
{
    /**
     * The name of the integration
     */
    public function get_name(): string
    {
        return 'checkout-toolkit';
    }

    /**
     * Initialize the integration
     */
    public function initialize(): void
    {
        // Only register Store API data - scripts are enqueued via Main::enqueue_blocks_assets()
        $this->register_store_api_data();
    }

    /**
     * Get script handles to register
     *
     * @return string[]
     */
    public function get_script_handles(): array
    {
        return ['checkout-toolkit-blocks'];
    }

    /**
     * Get editor script handles
     *
     * @return string[]
     */
    public function get_editor_script_handles(): array
    {
        return [];
    }

    /**
     * Get script data for frontend
     */
    public function get_script_data(): array
    {
        $main = Main::get_instance();
        $delivery_settings = $main->get_delivery_settings();
        $field_settings = $main->get_field_settings();
        $field_2_settings = $this->get_field_2_settings();
        $delivery_method_settings = $this->get_delivery_method_settings();

        return [
            'deliveryMethod' => [
                'enabled' => $delivery_method_settings['enabled'],
                'defaultMethod' => $delivery_method_settings['default_method'],
                'fieldLabel' => $delivery_method_settings['field_label'],
                'deliveryLabel' => $delivery_method_settings['delivery_label'],
                'pickupLabel' => $delivery_method_settings['pickup_label'],
                'showAs' => $delivery_method_settings['show_as'],
            ],
            'delivery' => [
                'enabled' => $delivery_settings['enabled'],
                'required' => $delivery_settings['required'],
                'label' => $delivery_settings['field_label'],
                'minLeadDays' => $delivery_settings['min_lead_days'],
                'maxFutureDays' => $delivery_settings['max_future_days'],
                'disabledWeekdays' => $delivery_settings['disabled_weekdays'],
                'blockedDates' => $delivery_settings['blocked_dates'],
                'dateFormat' => $this->convert_php_to_flatpickr_format($delivery_settings['date_format']),
                'firstDayOfWeek' => $delivery_settings['first_day_of_week'],
            ],
            'customField' => [
                'enabled' => $field_settings['enabled'],
                'required' => $field_settings['required'],
                'type' => $field_settings['field_type'],
                'label' => $field_settings['field_label'],
                'placeholder' => $field_settings['field_placeholder'],
                'maxLength' => $field_settings['max_length'],
                'checkboxLabel' => $field_settings['checkbox_label'] ?? '',
                'selectOptions' => $field_settings['select_options'] ?? [],
            ],
            'customField2' => [
                'enabled' => $field_2_settings['enabled'],
                'required' => $field_2_settings['required'],
                'type' => $field_2_settings['field_type'],
                'label' => $field_2_settings['field_label'],
                'placeholder' => $field_2_settings['field_placeholder'],
                'maxLength' => $field_2_settings['max_length'],
                'checkboxLabel' => $field_2_settings['checkbox_label'] ?? '',
                'selectOptions' => $field_2_settings['select_options'] ?? [],
            ],
            'i18n' => [
                'selectDate' => __('Select a date', 'checkout-toolkit-for-woo'),
                'selectOption' => __('Select an option...', 'checkout-toolkit-for-woo'),
                'charactersRemaining' => __('characters remaining', 'checkout-toolkit-for-woo'),
                'deliveryDateRequired' => sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'checkout-toolkit-for-woo'),
                    $delivery_settings['field_label']
                ),
                'customFieldRequired' => sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'checkout-toolkit-for-woo'),
                    $field_settings['field_label']
                ),
                'customField2Required' => sprintf(
                    /* translators: %s: Field label */
                    __('%s is a required field.', 'checkout-toolkit-for-woo'),
                    $field_2_settings['field_label']
                ),
            ],
        ];
    }

    /**
     * Get field 2 settings
     *
     * @return array Settings array.
     */
    private function get_field_2_settings(): array
    {
        $defaults = (new Settings())->get_default_field_2_settings();
        $settings = get_option('checkout_toolkit_field_2_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get delivery method settings
     *
     * @return array Settings array.
     */
    private function get_delivery_method_settings(): array
    {
        $defaults = (new Settings())->get_default_delivery_method_settings();
        $settings = get_option('checkout_toolkit_delivery_method_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Register block scripts
     */
    private function register_block_scripts(): void
    {
        $script_path = CHECKOUT_TOOLKIT_PLUGIN_DIR . 'public/js/blocks-checkout.js';
        $script_url = CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/js/blocks-checkout.js';

        if (!file_exists($script_path)) {
            return;
        }

        wp_register_script(
            'checkout-toolkit-blocks',
            $script_url,
            [
                'wp-plugins',
                'wp-element',
                'wp-data',
                'wc-blocks-checkout',
                'wc-settings',
            ],
            CHECKOUT_TOOLKIT_VERSION,
            true
        );

        // Flatpickr for date picker
        wp_register_script(
            'checkout-toolkit-flatpickr',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
            [],
            '4.6.13',
            true
        );

        wp_register_style(
            'checkout-toolkit-flatpickr',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
            [],
            '4.6.13'
        );

        wp_register_style(
            'checkout-toolkit-blocks-style',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/css/blocks-checkout.css',
            ['checkout-toolkit-flatpickr'],
            CHECKOUT_TOOLKIT_VERSION
        );
    }

    /**
     * Register Store API data
     */
    private function register_store_api_data(): void
    {
        // Extend cart/checkout schema with our data
        if (function_exists('woocommerce_store_api_register_endpoint_data')) {
            woocommerce_store_api_register_endpoint_data([
                'endpoint' => 'checkout',
                'namespace' => 'checkout-toolkit',
                'schema_callback' => [$this, 'get_store_api_schema'],
                'schema_type' => ARRAY_A,
            ]);
        }

        // Handle order data from checkout
        add_action(
            'woocommerce_store_api_checkout_update_order_from_request',
            [$this, 'save_order_data'],
            10,
            2
        );
    }

    /**
     * Get Store API schema
     */
    public function get_store_api_schema(): array
    {
        return [
            'delivery_method' => [
                'description' => __('Delivery method (delivery or pickup)', 'checkout-toolkit-for-woo'),
                'type' => ['string', 'null'],
                'context' => ['view', 'edit'],
                'default' => '',
            ],
            'delivery_date' => [
                'description' => __('Preferred delivery date', 'checkout-toolkit-for-woo'),
                'type' => ['string', 'null'],
                'context' => ['view', 'edit'],
                'default' => '',
            ],
            'custom_field' => [
                'description' => __('Custom order field', 'checkout-toolkit-for-woo'),
                'type' => ['string', 'null'],
                'context' => ['view', 'edit'],
                'default' => '',
            ],
            'custom_field_2' => [
                'description' => __('Second custom order field', 'checkout-toolkit-for-woo'),
                'type' => ['string', 'null'],
                'context' => ['view', 'edit'],
                'default' => '',
            ],
        ];
    }

    /**
     * Save order data from Store API request
     *
     * @param \WC_Order $order The order object.
     * @param \WP_REST_Request $request The request object.
     */
    public function save_order_data($order, $request): void
    {
        $extensions = $request->get_param('extensions');

        if (!is_array($extensions) || !isset($extensions['checkout-toolkit'])) {
            return;
        }

        $data = $extensions['checkout-toolkit'];

        // Save delivery method
        if (!empty($data['delivery_method'])) {
            $delivery_method = sanitize_key($data['delivery_method']);
            if (in_array($delivery_method, ['delivery', 'pickup'], true)) {
                $order->update_meta_data('_wct_delivery_method', $delivery_method);

                /**
                 * Action fired after delivery method is saved from blocks checkout.
                 *
                 * @param int $order_id The order ID.
                 * @param string $delivery_method The delivery method.
                 */
                do_action('checkout_toolkit_delivery_method_saved', $order->get_id(), $delivery_method);
            }
        }

        // Save delivery date
        if (!empty($data['delivery_date'])) {
            $delivery_date = sanitize_text_field($data['delivery_date']);
            // Validate date format (Y-m-d)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $delivery_date)) {
                $order->update_meta_data('_wct_delivery_date', $delivery_date);

                /**
                 * Action fired after delivery date is saved from blocks checkout.
                 *
                 * @param int $order_id The order ID.
                 * @param string $delivery_date The delivery date.
                 */
                do_action('checkout_toolkit_delivery_date_saved', $order->get_id(), $delivery_date);
            }
        }

        // Save custom field
        if (isset($data['custom_field'])) {
            $main = Main::get_instance();
            $settings = $main->get_field_settings();
            $field_type = $settings['field_type'] ?? 'textarea';

            // Handle checkbox type
            if ($field_type === 'checkbox') {
                $value = !empty($data['custom_field']) ? '1' : '0';
                $order->update_meta_data('_wct_custom_field', $value);
                do_action('checkout_toolkit_custom_field_saved', $order->get_id(), $value);
            } else {
                $value = sanitize_textarea_field($data['custom_field']);

                // Enforce max length for text fields
                if (in_array($field_type, ['text', 'textarea'], true) &&
                    $settings['max_length'] > 0 &&
                    mb_strlen($value) > $settings['max_length']) {
                    $value = mb_substr($value, 0, $settings['max_length']);
                }

                $value = apply_filters('checkout_toolkit_sanitize_field_value', $value, $order);

                if (!empty($value)) {
                    $order->update_meta_data('_wct_custom_field', $value);
                    do_action('checkout_toolkit_custom_field_saved', $order->get_id(), $value);
                }
            }
        }

        // Save custom field 2
        if (isset($data['custom_field_2'])) {
            $field_2_settings = $this->get_field_2_settings();
            $field_type = $field_2_settings['field_type'] ?? 'text';

            // Handle checkbox type
            if ($field_type === 'checkbox') {
                $value = !empty($data['custom_field_2']) ? '1' : '0';
                $order->update_meta_data('_wct_custom_field_2', $value);
                do_action('checkout_toolkit_custom_field_2_saved', $order->get_id(), $value);
            } else {
                $value = sanitize_textarea_field($data['custom_field_2']);

                // Enforce max length for text fields
                if (in_array($field_type, ['text', 'textarea'], true) &&
                    $field_2_settings['max_length'] > 0 &&
                    mb_strlen($value) > $field_2_settings['max_length']) {
                    $value = mb_substr($value, 0, $field_2_settings['max_length']);
                }

                $value = apply_filters('checkout_toolkit_sanitize_field_2_value', $value, $order);

                if (!empty($value)) {
                    $order->update_meta_data('_wct_custom_field_2', $value);
                    do_action('checkout_toolkit_custom_field_2_saved', $order->get_id(), $value);
                }
            }
        }
    }

    /**
     * Convert PHP date format to Flatpickr format
     */
    private function convert_php_to_flatpickr_format(string $php_format): string
    {
        $replacements = [
            'd' => 'd',    // Day with leading zero
            'j' => 'j',    // Day without leading zero
            'D' => 'D',    // Short day name
            'l' => 'l',    // Full day name
            'm' => 'm',    // Month with leading zero
            'n' => 'n',    // Month without leading zero
            'M' => 'M',    // Short month name
            'F' => 'F',    // Full month name
            'Y' => 'Y',    // 4-digit year
            'y' => 'y',    // 2-digit year
        ];

        return strtr($php_format, $replacements);
    }
}
