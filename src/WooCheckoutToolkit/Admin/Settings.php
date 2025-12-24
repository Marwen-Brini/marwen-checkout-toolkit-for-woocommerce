<?php
/**
 * Settings handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

use WooCheckoutToolkit\Main;

defined('ABSPATH') || exit;

/**
 * Class Settings
 *
 * Handles plugin settings page and options.
 */
class Settings
{
    /**
     * Initialize settings
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page to WooCommerce menu
     */
    public function add_menu_page(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Checkout Toolkit', 'checkout-toolkit-for-woo'),
            __('Checkout Toolkit', 'checkout-toolkit-for-woo'),
            'manage_woocommerce',
            'wct-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings(): void
    {
        register_setting(
            'checkout_toolkit_settings',
            'checkout_toolkit_delivery_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_delivery_settings'],
                'default' => Main::get_instance()->get_default_delivery_settings(),
            ]
        );

        register_setting(
            'checkout_toolkit_settings',
            'checkout_toolkit_field_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_field_settings'],
                'default' => Main::get_instance()->get_default_field_settings(),
            ]
        );

        register_setting(
            'checkout_toolkit_settings',
            'checkout_toolkit_order_notes_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_order_notes_settings'],
                'default' => $this->get_default_order_notes_settings(),
            ]
        );

        register_setting(
            'checkout_toolkit_settings',
            'checkout_toolkit_field_2_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_field_2_settings'],
                'default' => $this->get_default_field_2_settings(),
            ]
        );

        register_setting(
            'checkout_toolkit_settings',
            'checkout_toolkit_delivery_method_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_delivery_method_settings'],
                'default' => $this->get_default_delivery_method_settings(),
            ]
        );
    }

    /**
     * Get default field 2 settings
     *
     * @return array Default settings.
     */
    public function get_default_field_2_settings(): array
    {
        return [
            'enabled' => false,
            'required' => false,
            'field_type' => 'text',
            'field_label' => 'Additional Information',
            'field_placeholder' => '',
            'field_position' => 'woocommerce_after_order_notes',
            'max_length' => 200,
            'show_in_emails' => true,
            'show_in_admin' => true,
            'checkbox_label' => '',
            'select_options' => [],
        ];
    }

    /**
     * Sanitize field 2 settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_field_2_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('checkout_toolkit_field_2_settings', $this->get_default_field_2_settings());
        }

        $defaults = $this->get_default_field_2_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_type' => in_array($input['field_type'] ?? '', ['text', 'textarea', 'checkbox', 'select'], true)
                ? $input['field_type']
                : $defaults['field_type'],
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'field_placeholder' => sanitize_text_field($input['field_placeholder'] ?? $defaults['field_placeholder']),
            'field_position' => sanitize_key($input['field_position'] ?? $defaults['field_position']),
            'max_length' => absint($input['max_length'] ?? $defaults['max_length']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
            'checkbox_label' => sanitize_text_field($input['checkbox_label'] ?? $defaults['checkbox_label']),
            'select_options' => $this->sanitize_select_options($input['select_options'] ?? []),
        ];
    }

    /**
     * Get default order notes settings
     *
     * @return array Default settings.
     */
    public function get_default_order_notes_settings(): array
    {
        return [
            'enabled' => false,
            'custom_placeholder' => '',
            'custom_label' => '',
        ];
    }

    /**
     * Sanitize order notes settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_order_notes_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('checkout_toolkit_order_notes_settings', $this->get_default_order_notes_settings());
        }

        return [
            'enabled' => !empty($input['enabled']),
            'custom_placeholder' => sanitize_textarea_field($input['custom_placeholder'] ?? ''),
            'custom_label' => sanitize_text_field($input['custom_label'] ?? ''),
        ];
    }

    /**
     * Get default delivery method settings
     *
     * @return array Default settings.
     */
    public function get_default_delivery_method_settings(): array
    {
        return [
            'enabled' => false,
            'default_method' => 'delivery',
            'field_label' => 'Fulfillment Method',
            'delivery_label' => 'Delivery',
            'pickup_label' => 'Pickup',
            'show_as' => 'toggle',
            'show_in_admin' => true,
            'show_in_emails' => true,
        ];
    }

    /**
     * Sanitize delivery method settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_delivery_method_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('checkout_toolkit_delivery_method_settings', $this->get_default_delivery_method_settings());
        }

        $defaults = $this->get_default_delivery_method_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'default_method' => in_array($input['default_method'] ?? '', ['delivery', 'pickup'], true)
                ? $input['default_method']
                : $defaults['default_method'],
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'delivery_label' => sanitize_text_field($input['delivery_label'] ?? $defaults['delivery_label']),
            'pickup_label' => sanitize_text_field($input['pickup_label'] ?? $defaults['pickup_label']),
            'show_as' => in_array($input['show_as'] ?? '', ['toggle', 'radio'], true)
                ? $input['show_as']
                : $defaults['show_as'],
            'show_in_admin' => !empty($input['show_in_admin']),
            'show_in_emails' => !empty($input['show_in_emails']),
        ];
    }

    /**
     * Sanitize delivery settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_delivery_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('checkout_toolkit_delivery_settings', Main::get_instance()->get_default_delivery_settings());
        }

        $defaults = Main::get_instance()->get_default_delivery_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'field_position' => sanitize_key($input['field_position'] ?? $defaults['field_position']),
            'min_lead_days' => absint($input['min_lead_days'] ?? $defaults['min_lead_days']),
            'max_future_days' => absint($input['max_future_days'] ?? $defaults['max_future_days']),
            'disabled_weekdays' => array_map('absint', $input['disabled_weekdays'] ?? []),
            'blocked_dates' => $this->sanitize_blocked_dates($input['blocked_dates'] ?? []),
            'date_format' => sanitize_text_field($input['date_format'] ?? $defaults['date_format']),
            'first_day_of_week' => absint($input['first_day_of_week'] ?? $defaults['first_day_of_week']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
        ];
    }

    /**
     * Sanitize field settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_field_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('checkout_toolkit_field_settings', Main::get_instance()->get_default_field_settings());
        }

        $defaults = Main::get_instance()->get_default_field_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_type' => in_array($input['field_type'] ?? '', ['text', 'textarea', 'checkbox', 'select'], true)
                ? $input['field_type']
                : $defaults['field_type'],
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'field_placeholder' => sanitize_text_field($input['field_placeholder'] ?? $defaults['field_placeholder']),
            'field_position' => sanitize_key($input['field_position'] ?? $defaults['field_position']),
            'max_length' => absint($input['max_length'] ?? $defaults['max_length']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
            'checkbox_label' => sanitize_text_field($input['checkbox_label'] ?? ($defaults['checkbox_label'] ?? '')),
            'select_options' => $this->sanitize_select_options($input['select_options'] ?? []),
        ];
    }

    /**
     * Sanitize blocked dates array
     */
    private function sanitize_blocked_dates(array $dates): array
    {
        $sanitized = [];

        foreach ($dates as $date) {
            $date = sanitize_text_field($date);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $sanitized[] = $date;
            }
        }

        return array_unique($sanitized);
    }

    /**
     * Sanitize select options array
     *
     * @param array $options Raw options array.
     * @return array Sanitized options.
     */
    private function sanitize_select_options(array $options): array
    {
        $sanitized = [];

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $value = sanitize_key($option['value'] ?? '');
            $label = sanitize_text_field($option['label'] ?? '');

            // Skip empty options.
            if (empty($value) && empty($label)) {
                continue;
            }

            // Use label as value if value is empty.
            if (empty($value) && !empty($label)) {
                $value = sanitize_key($label);
            }

            $sanitized[] = [
                'value' => $value,
                'label' => $label ?: $value,
            ];
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'checkout-toolkit-for-woo'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation, no data processing.
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'delivery-method';

        include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Get available field positions
     */
    public function get_field_positions(): array
    {
        return [
            'woocommerce_before_checkout_billing_form' => __('Before billing fields', 'checkout-toolkit-for-woo'),
            'woocommerce_after_checkout_billing_form' => __('After billing fields', 'checkout-toolkit-for-woo'),
            'woocommerce_before_checkout_shipping_form' => __('Before shipping fields', 'checkout-toolkit-for-woo'),
            'woocommerce_after_checkout_shipping_form' => __('After shipping fields', 'checkout-toolkit-for-woo'),
            'woocommerce_before_order_notes' => __('Before order notes', 'checkout-toolkit-for-woo'),
            'woocommerce_after_order_notes' => __('After order notes', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_before_cart_contents' => __('Before order review', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_after_cart_contents' => __('After order review items', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_before_shipping' => __('Before shipping in review', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_after_shipping' => __('After shipping in review', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_before_order_total' => __('Before order total', 'checkout-toolkit-for-woo'),
            'woocommerce_review_order_before_submit' => __('Before Place Order button', 'checkout-toolkit-for-woo'),
        ];
    }

    /**
     * Get available date formats
     */
    public function get_date_formats(): array
    {
        return [
            'F j, Y' => date_i18n('F j, Y'),
            'm/d/Y' => date_i18n('m/d/Y'),
            'd/m/Y' => date_i18n('d/m/Y'),
            'Y-m-d' => date_i18n('Y-m-d'),
            'l, F j, Y' => date_i18n('l, F j, Y'),
            'M j, Y' => date_i18n('M j, Y'),
            'D, M j, Y' => date_i18n('D, M j, Y'),
        ];
    }
}
