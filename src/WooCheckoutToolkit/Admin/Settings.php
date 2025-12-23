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
    }

    /**
     * Sanitize delivery settings
     */
    public function sanitize_delivery_settings(array $input): array
    {
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
     */
    public function sanitize_field_settings(array $input): array
    {
        $defaults = Main::get_instance()->get_default_field_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_type' => in_array($input['field_type'] ?? '', ['text', 'textarea'], true)
                ? $input['field_type']
                : $defaults['field_type'],
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'field_placeholder' => sanitize_text_field($input['field_placeholder'] ?? $defaults['field_placeholder']),
            'field_position' => sanitize_key($input['field_position'] ?? $defaults['field_position']),
            'max_length' => absint($input['max_length'] ?? $defaults['max_length']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
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
     * Render settings page
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'checkout-toolkit-for-woo'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation, no data processing.
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'delivery';

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
