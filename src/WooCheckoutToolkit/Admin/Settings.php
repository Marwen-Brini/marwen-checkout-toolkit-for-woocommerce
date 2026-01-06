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
            __('Checkout Toolkit', 'marwen-checkout-toolkit-for-woocommerce'),
            __('Checkout Toolkit', 'marwen-checkout-toolkit-for-woocommerce'),
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
            'marwchto_settings',
            'marwchto_delivery_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_delivery_settings'],
                'default' => Main::get_instance()->get_default_delivery_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_field_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_field_settings'],
                'default' => Main::get_instance()->get_default_field_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_order_notes_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_order_notes_settings'],
                'default' => $this->get_default_order_notes_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_field_2_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_field_2_settings'],
                'default' => $this->get_default_field_2_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_delivery_method_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_delivery_method_settings'],
                'default' => $this->get_default_delivery_method_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_delivery_instructions_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_delivery_instructions_settings'],
                'default' => $this->get_default_delivery_instructions_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_time_window_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_time_window_settings'],
                'default' => $this->get_default_time_window_settings(),
            ]
        );

        register_setting(
            'marwchto_settings',
            'marwchto_store_locations_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_store_locations_settings'],
                'default' => $this->get_default_store_locations_settings(),
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
            'visibility_type' => 'always',
            'visibility_products' => [],
            'visibility_categories' => [],
            'visibility_mode' => 'show',
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
            return get_option('marwchto_field_2_settings', $this->get_default_field_2_settings());
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
            'select_options' => $this->sanitize_select_options(is_array($input['select_options'] ?? null) ? $input['select_options'] : []),
            'visibility_type' => in_array($input['visibility_type'] ?? '', ['always', 'products', 'categories'], true)
                ? $input['visibility_type']
                : $defaults['visibility_type'],
            'visibility_products' => $this->sanitize_visibility_ids(is_array($input['visibility_products'] ?? null) ? $input['visibility_products'] : []),
            'visibility_categories' => $this->sanitize_visibility_ids(is_array($input['visibility_categories'] ?? null) ? $input['visibility_categories'] : []),
            'visibility_mode' => in_array($input['visibility_mode'] ?? '', ['show', 'hide'], true)
                ? $input['visibility_mode']
                : $defaults['visibility_mode'],
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
            return get_option('marwchto_order_notes_settings', $this->get_default_order_notes_settings());
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
            return get_option('marwchto_delivery_method_settings', $this->get_default_delivery_method_settings());
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
     * Get default delivery instructions settings
     *
     * @return array Default settings.
     */
    public function get_default_delivery_instructions_settings(): array
    {
        return [
            'enabled' => false,
            'required' => false,
            'field_label' => 'Delivery Instructions',
            'preset_label' => 'Common Instructions',
            'preset_options' => [
                ['value' => 'leave_door', 'label' => 'Leave at door'],
                ['value' => 'ring_bell', 'label' => 'Ring doorbell'],
                ['value' => 'call_arrival', 'label' => 'Call on arrival'],
                ['value' => 'front_desk', 'label' => 'Leave with front desk/reception'],
            ],
            'custom_label' => 'Additional Instructions',
            'custom_placeholder' => 'Any other delivery instructions...',
            'max_length' => 500,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Sanitize delivery instructions settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_delivery_instructions_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('marwchto_delivery_instructions_settings', $this->get_default_delivery_instructions_settings());
        }

        $defaults = $this->get_default_delivery_instructions_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'preset_label' => sanitize_text_field($input['preset_label'] ?? $defaults['preset_label']),
            'preset_options' => $this->sanitize_select_options(is_array($input['preset_options'] ?? null) ? $input['preset_options'] : $defaults['preset_options']),
            'custom_label' => sanitize_text_field($input['custom_label'] ?? $defaults['custom_label']),
            'custom_placeholder' => sanitize_text_field($input['custom_placeholder'] ?? $defaults['custom_placeholder']),
            'max_length' => absint($input['max_length'] ?? $defaults['max_length']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
        ];
    }

    /**
     * Get default time window settings
     *
     * @return array Default settings.
     */
    public function get_default_time_window_settings(): array
    {
        return [
            'enabled' => false,
            'required' => false,
            'field_label' => 'Preferred Time',
            'time_slots' => [
                ['value' => 'morning', 'label' => 'Morning (9am - 12pm)'],
                ['value' => 'afternoon', 'label' => 'Afternoon (12pm - 5pm)'],
                ['value' => 'evening', 'label' => 'Evening (5pm - 8pm)'],
            ],
            'show_only_with_delivery' => true,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Sanitize time window settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_time_window_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('marwchto_time_window_settings', $this->get_default_time_window_settings());
        }

        $defaults = $this->get_default_time_window_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'time_slots' => $this->sanitize_select_options(is_array($input['time_slots'] ?? null) ? $input['time_slots'] : $defaults['time_slots']),
            'show_only_with_delivery' => !empty($input['show_only_with_delivery']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
        ];
    }

    /**
     * Get default store locations settings
     *
     * @return array Default settings.
     */
    public function get_default_store_locations_settings(): array
    {
        return [
            'enabled' => false,
            'required' => true,
            'field_label' => 'Pickup Location',
            'locations' => [],
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Sanitize store locations settings
     *
     * @param array|null $input Input array or null when saving other tab.
     * @return array Sanitized settings.
     */
    public function sanitize_store_locations_settings(?array $input): array
    {
        // Return current settings if input is null (saving from another tab).
        if ($input === null) {
            return get_option('marwchto_store_locations_settings', $this->get_default_store_locations_settings());
        }

        $defaults = $this->get_default_store_locations_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'locations' => $this->sanitize_store_locations(is_array($input['locations'] ?? null) ? $input['locations'] : []),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
        ];
    }

    /**
     * Sanitize store locations array
     *
     * @param array $locations Raw locations array.
     * @return array Sanitized locations.
     */
    private function sanitize_store_locations(array $locations): array
    {
        $sanitized = [];

        foreach ($locations as $location) {
            if (!is_array($location)) {
                continue;
            }

            $name = sanitize_text_field($location['name'] ?? '');

            // Skip locations without a name.
            if (empty($name)) {
                continue;
            }

            // Generate ID from name if not provided.
            $id = sanitize_key($location['id'] ?? '');
            if (empty($id)) {
                $id = sanitize_key($name);
            }

            $sanitized[] = [
                'id' => $id,
                'name' => $name,
                'address' => sanitize_textarea_field($location['address'] ?? ''),
                'phone' => sanitize_text_field($location['phone'] ?? ''),
                'hours' => sanitize_textarea_field($location['hours'] ?? ''),
            ];
        }

        return $sanitized;
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
            return get_option('marwchto_delivery_settings', Main::get_instance()->get_default_delivery_settings());
        }

        $defaults = Main::get_instance()->get_default_delivery_settings();

        return [
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'field_label' => sanitize_text_field($input['field_label'] ?? $defaults['field_label']),
            'field_position' => sanitize_key($input['field_position'] ?? $defaults['field_position']),
            'min_lead_days' => absint($input['min_lead_days'] ?? $defaults['min_lead_days']),
            'max_future_days' => absint($input['max_future_days'] ?? $defaults['max_future_days']),
            'disabled_weekdays' => array_map('absint', is_array($input['disabled_weekdays'] ?? null) ? $input['disabled_weekdays'] : []),
            'blocked_dates' => $this->sanitize_blocked_dates(is_array($input['blocked_dates'] ?? null) ? $input['blocked_dates'] : []),
            'date_format' => sanitize_text_field($input['date_format'] ?? $defaults['date_format']),
            'first_day_of_week' => absint($input['first_day_of_week'] ?? $defaults['first_day_of_week']),
            'show_in_emails' => !empty($input['show_in_emails']),
            'show_in_admin' => !empty($input['show_in_admin']),
            // Estimated delivery settings
            'show_estimated_delivery' => !empty($input['show_estimated_delivery']),
            'estimated_delivery_message' => sanitize_text_field($input['estimated_delivery_message'] ?? $defaults['estimated_delivery_message']),
            'cutoff_time' => $this->sanitize_time($input['cutoff_time'] ?? $defaults['cutoff_time']),
            'cutoff_message' => sanitize_text_field($input['cutoff_message'] ?? $defaults['cutoff_message']),
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
            return get_option('marwchto_field_settings', Main::get_instance()->get_default_field_settings());
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
            'select_options' => $this->sanitize_select_options(is_array($input['select_options'] ?? null) ? $input['select_options'] : []),
            'visibility_type' => in_array($input['visibility_type'] ?? '', ['always', 'products', 'categories'], true)
                ? $input['visibility_type']
                : $defaults['visibility_type'],
            'visibility_products' => $this->sanitize_visibility_ids(is_array($input['visibility_products'] ?? null) ? $input['visibility_products'] : []),
            'visibility_categories' => $this->sanitize_visibility_ids(is_array($input['visibility_categories'] ?? null) ? $input['visibility_categories'] : []),
            'visibility_mode' => in_array($input['visibility_mode'] ?? '', ['show', 'hide'], true)
                ? $input['visibility_mode']
                : $defaults['visibility_mode'],
        ];
    }

    /**
     * Sanitize visibility IDs array
     *
     * @param mixed $ids Input IDs (array or empty).
     * @return array Sanitized array of integer IDs.
     */
    private function sanitize_visibility_ids($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('absint', $ids)));
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
     * Sanitize time in HH:MM format
     *
     * @param string $time Time string to sanitize.
     * @return string Sanitized time in HH:MM format.
     */
    private function sanitize_time(string $time): string
    {
        $time = sanitize_text_field($time);

        // Validate HH:MM format
        if (preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
            // Normalize to HH:MM (with leading zero)
            $parts = explode(':', $time);
            return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        // Return default if invalid
        return '14:00';
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
            wp_die(esc_html__('You do not have permission to access this page.', 'marwen-checkout-toolkit-for-woocommerce'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation, no data processing.
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'delivery-method';

        include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Get available field positions
     */
    public function get_field_positions(): array
    {
        return [
            'woocommerce_before_checkout_billing_form' => __('Before billing fields', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_after_checkout_billing_form' => __('After billing fields', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_before_checkout_shipping_form' => __('Before shipping fields', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_after_checkout_shipping_form' => __('After shipping fields', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_before_order_notes' => __('Before order notes', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_after_order_notes' => __('After order notes', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_before_cart_contents' => __('Before order review', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_after_cart_contents' => __('After order review items', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_before_shipping' => __('Before shipping in review', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_after_shipping' => __('After shipping in review', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_before_order_total' => __('Before order total', 'marwen-checkout-toolkit-for-woocommerce'),
            'woocommerce_review_order_before_submit' => __('Before Place Order button', 'marwen-checkout-toolkit-for-woocommerce'),
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
