<?php
/**
 * Main plugin class
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

use WooCheckoutToolkit\Admin\Admin;
use WooCheckoutToolkit\Admin\DeliveryManager;
use WooCheckoutToolkit\Delivery\DeliveryDate;
use WooCheckoutToolkit\Delivery\DeliveryMethod;
use WooCheckoutToolkit\Delivery\DeliveryInstructions;
use WooCheckoutToolkit\Delivery\TimeWindow;
use WooCheckoutToolkit\Pickup\StoreLocationSelector;
use WooCheckoutToolkit\Fields\OrderFields;
use WooCheckoutToolkit\Fields\OrderFields2;
use WooCheckoutToolkit\Display\OrderDisplay;
use WooCheckoutToolkit\Display\EmailDisplay;
use WooCheckoutToolkit\Display\AccountDisplay;
use WooCheckoutToolkit\Communication\OrderNotesCustomizer;
use WooCheckoutToolkit\Logger;
use WooCheckoutToolkit\CheckoutDetector;
use WooCheckoutToolkit\Blocks\BlocksIntegration;

defined('ABSPATH') || exit;

/**
 * Class Main
 *
 * Main plugin class that initializes all components.
 */
final class Main
{
    /**
     * Singleton instance
     */
    private static ?Main $instance = null;

    /**
     * Admin instance
     */
    private ?Admin $admin = null;

    /**
     * Delivery date instance
     */
    private ?DeliveryDate $delivery_date = null;

    /**
     * Delivery method instance
     */
    private ?DeliveryMethod $delivery_method = null;

    /**
     * Delivery instructions instance
     */
    private ?DeliveryInstructions $delivery_instructions = null;

    /**
     * Time window instance
     */
    private ?TimeWindow $time_window = null;

    /**
     * Store location selector instance
     */
    private ?StoreLocationSelector $store_location_selector = null;

    /**
     * Order fields instance
     */
    private ?OrderFields $order_fields = null;

    /**
     * Order fields 2 instance
     */
    private ?OrderFields2 $order_fields_2 = null;

    /**
     * Order display instance
     */
    private ?OrderDisplay $order_display = null;

    /**
     * Email display instance
     */
    private ?EmailDisplay $email_display = null;

    /**
     * Account display instance
     */
    private ?AccountDisplay $account_display = null;

    /**
     * Delivery manager instance
     */
    private ?DeliveryManager $delivery_manager = null;

    /**
     * Order notes customizer instance
     */
    private ?OrderNotesCustomizer $order_notes_customizer = null;

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Get singleton instance
     */
    public static function get_instance(): Main
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        Logger::info('Initializing Checkout Toolkit for WooCommerce', [
            'version' => MARWCHTO_VERSION,
            'php_version' => PHP_VERSION,
            'checkout_type' => CheckoutDetector::get_checkout_type(),
        ]);

        $this->init_admin();
        $this->init_frontend();
        $this->init_display();
        $this->init_blocks_integration();

        Logger::debug('Plugin initialization complete');
    }

    /**
     * Initialize admin components
     */
    private function init_admin(): void
    {
        if (is_admin()) {
            $this->admin = new Admin();
            $this->admin->init();

            // Initialize delivery manager
            $this->delivery_manager = new DeliveryManager();
            $this->delivery_manager->init();
        }
    }

    /**
     * Initialize frontend components
     *
     * Classic checkout hooks are always registered.
     * They check internally whether to render based on checkout type.
     * Blocks checkout uses BlocksIntegration for rendering but classic
     * components still handle validation/saving as fallback.
     */
    private function init_frontend(): void
    {
        // Always initialize classic components - they handle validation and saving
        // for both checkout types. The rendering is conditional.
        $this->delivery_method = new DeliveryMethod();
        $this->delivery_method->init();

        $this->delivery_instructions = new DeliveryInstructions();
        $this->delivery_instructions->init();

        $this->delivery_date = new DeliveryDate();
        $this->delivery_date->init();

        $this->time_window = new TimeWindow();
        $this->time_window->init();

        $this->store_location_selector = new StoreLocationSelector();
        $this->store_location_selector->init();

        $this->order_fields = new OrderFields();
        $this->order_fields->init();

        $this->order_fields_2 = new OrderFields2();
        $this->order_fields_2->init();

        // Initialize order notes customizer
        $this->order_notes_customizer = new OrderNotesCustomizer();
        $this->order_notes_customizer->init();

        // Enqueue frontend assets for classic checkout only
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    /**
     * Initialize WooCommerce Blocks integration
     */
    private function init_blocks_integration(): void
    {
        // Only initialize if WooCommerce Blocks is available
        if (!CheckoutDetector::has_wc_blocks()) {
            Logger::debug('WooCommerce Blocks not available');
            return;
        }

        // Register the integration with WooCommerce Blocks IntegrationRegistry
        add_action('woocommerce_blocks_checkout_block_registration', function ($integration_registry) {
            $integration = new BlocksIntegration();
            $integration_registry->register($integration);
            Logger::debug('Blocks integration registered with IntegrationRegistry');
        });

        // Register Store API endpoint for saving data
        add_action('woocommerce_blocks_loaded', function () {
            $integration = new BlocksIntegration();
            $integration->initialize();
            Logger::debug('Blocks Store API initialized');
        });

        // Enqueue blocks assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_blocks_assets']);
    }

    /**
     * Enqueue blocks checkout assets
     */
    public function enqueue_blocks_assets(): void
    {
        if (!is_checkout() || !CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $order_notes_settings = $this->get_order_notes_settings();
        $delivery_method_settings = $this->get_delivery_method_settings();
        $delivery_instructions_settings = $this->get_delivery_instructions_settings();
        $time_window_settings = $this->get_time_window_settings();
        $store_locations_settings = $this->get_store_locations_settings();
        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();
        $field_2_settings = $this->get_field_2_settings();

        // Only load if at least one feature is enabled
        $has_enabled_feature = !empty($order_notes_settings['enabled'])
            || !empty($delivery_method_settings['enabled'])
            || !empty($delivery_instructions_settings['enabled'])
            || !empty($time_window_settings['enabled'])
            || !empty($store_locations_settings['enabled'])
            || !empty($delivery_settings['enabled'])
            || !empty($field_settings['enabled'])
            || !empty($field_2_settings['enabled']);

        if (!$has_enabled_feature) {
            return;
        }

        // Flatpickr for date picker
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_script(
                'checkout-toolkit-flatpickr',
                MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                [],
                '4.6.13',
                true
            );

            wp_enqueue_style(
                'checkout-toolkit-flatpickr',
                MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                [],
                '4.6.13'
            );
        }

        // Blocks checkout script
        wp_enqueue_script(
            'checkout-toolkit-blocks',
            MARWCHTO_PLUGIN_URL . 'public/js/blocks-checkout.js',
            [
                'wp-plugins',
                'wp-element',
                'wp-data',
                'wc-blocks-checkout',
            ],
            MARWCHTO_VERSION,
            true
        );

        // Pass settings to JavaScript
        $script_data = $this->get_blocks_script_data();
        wp_localize_script('checkout-toolkit-blocks', 'checkoutToolkitData', $script_data);

        // Blocks checkout styles
        wp_enqueue_style(
            'checkout-toolkit-blocks-style',
            MARWCHTO_PLUGIN_URL . 'public/css/blocks-checkout.css',
            [],
            MARWCHTO_VERSION
        );
    }

    /**
     * Get script data for blocks checkout
     */
    private function get_blocks_script_data(): array
    {
        $delivery_method_settings = $this->get_delivery_method_settings();
        $delivery_instructions_settings = $this->get_delivery_instructions_settings();
        $time_window_settings = $this->get_time_window_settings();
        $store_locations_settings = $this->get_store_locations_settings();
        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();
        $field_2_settings = $this->get_field_2_settings();
        $order_notes_settings = $this->get_order_notes_settings();

        return [
            'orderNotes' => [
                'enabled' => $order_notes_settings['enabled'],
                'customLabel' => $order_notes_settings['custom_label'],
                'customPlaceholder' => $order_notes_settings['custom_placeholder'],
            ],
            'deliveryMethod' => [
                'enabled' => $delivery_method_settings['enabled'],
                'defaultMethod' => $delivery_method_settings['default_method'],
                'fieldLabel' => $delivery_method_settings['field_label'],
                'deliveryLabel' => $delivery_method_settings['delivery_label'],
                'pickupLabel' => $delivery_method_settings['pickup_label'],
                'showAs' => $delivery_method_settings['show_as'],
            ],
            'deliveryInstructions' => [
                'enabled' => $delivery_instructions_settings['enabled'],
                'required' => $delivery_instructions_settings['required'],
                'fieldLabel' => $delivery_instructions_settings['field_label'],
                'presetLabel' => $delivery_instructions_settings['preset_label'],
                'presetOptions' => $delivery_instructions_settings['preset_options'],
                'customLabel' => $delivery_instructions_settings['custom_label'],
                'customPlaceholder' => $delivery_instructions_settings['custom_placeholder'],
                'maxLength' => $delivery_instructions_settings['max_length'],
            ],
            'timeWindow' => [
                'enabled' => $time_window_settings['enabled'],
                'required' => $time_window_settings['required'],
                'fieldLabel' => $time_window_settings['field_label'],
                'timeSlots' => $time_window_settings['time_slots'],
                'showOnlyWithDelivery' => $time_window_settings['show_only_with_delivery'],
            ],
            'storeLocations' => [
                'enabled' => $store_locations_settings['enabled'],
                'required' => $store_locations_settings['required'],
                'fieldLabel' => $store_locations_settings['field_label'],
                'locations' => $store_locations_settings['locations'],
            ],
            'delivery' => [
                'enabled' => $delivery_settings['enabled'],
                'required' => $delivery_settings['required'],
                'label' => $delivery_settings['field_label'],
                'position' => $delivery_settings['field_position'] ?? 'woocommerce_after_order_notes',
                'minLeadDays' => $delivery_settings['min_lead_days'],
                'maxFutureDays' => $delivery_settings['max_future_days'],
                'disabledWeekdays' => array_map('intval', $delivery_settings['disabled_weekdays']),
                'blockedDates' => $delivery_settings['blocked_dates'],
                'dateFormat' => $this->php_to_flatpickr_format($delivery_settings['date_format']),
                'firstDayOfWeek' => $delivery_settings['first_day_of_week'],
            ],
            'estimatedDelivery' => $this->get_estimated_delivery_data($delivery_settings),
            'customField' => [
                'enabled' => $field_settings['enabled'],
                'required' => $field_settings['required'],
                'type' => $field_settings['field_type'],
                'label' => $field_settings['field_label'],
                'placeholder' => $field_settings['field_placeholder'],
                'position' => $field_settings['field_position'] ?? 'woocommerce_after_order_notes',
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
                'position' => $field_2_settings['field_position'] ?? 'woocommerce_after_order_notes',
                'maxLength' => $field_2_settings['max_length'],
                'checkboxLabel' => $field_2_settings['checkbox_label'] ?? '',
                'selectOptions' => $field_2_settings['select_options'] ?? [],
            ],
            'i18n' => [
                'selectDate' => __('Select a date', 'marwen-checkout-toolkit-for-woocommerce'),
                'charactersRemaining' => __('characters remaining', 'marwen-checkout-toolkit-for-woocommerce'),
            ],
        ];
    }

    /**
     * Get default delivery method settings
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
     * Get delivery method settings (with defaults)
     */
    public function get_delivery_method_settings(): array
    {
        $defaults = $this->get_default_delivery_method_settings();
        $settings = get_option('marwchto_delivery_method_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default delivery instructions settings
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
     * Get delivery instructions settings (with defaults)
     */
    public function get_delivery_instructions_settings(): array
    {
        $defaults = $this->get_default_delivery_instructions_settings();
        $settings = get_option('marwchto_delivery_instructions_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default time window settings
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
     * Get time window settings (with defaults)
     */
    public function get_time_window_settings(): array
    {
        $defaults = $this->get_default_time_window_settings();
        $settings = get_option('marwchto_time_window_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default store locations settings
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
     * Get store locations settings (with defaults)
     */
    public function get_store_locations_settings(): array
    {
        $defaults = $this->get_default_store_locations_settings();
        $settings = get_option('marwchto_store_locations_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default field 2 settings
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
     * Get field 2 settings (with defaults)
     */
    public function get_field_2_settings(): array
    {
        $defaults = $this->get_default_field_2_settings();
        $settings = get_option('marwchto_field_2_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Initialize display components (admin order, emails, my account)
     */
    private function init_display(): void
    {
        $this->order_display = new OrderDisplay();
        $this->order_display->init();

        $this->email_display = new EmailDisplay();
        $this->email_display->init();

        $this->account_display = new AccountDisplay();
        $this->account_display->init();
    }

    /**
     * Enqueue frontend assets on checkout page (classic checkout only)
     */
    public function enqueue_frontend_assets(): void
    {
        if (!is_checkout()) {
            return;
        }

        // Skip for blocks checkout - handled by enqueue_blocks_assets
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $delivery_method_settings = $this->get_delivery_method_settings();
        $delivery_instructions_settings = $this->get_delivery_instructions_settings();
        $time_window_settings = $this->get_time_window_settings();
        $store_locations_settings = $this->get_store_locations_settings();
        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();
        $field_2_settings = $this->get_field_2_settings();

        // Only load if at least one feature is enabled (order notes uses PHP filter, no JS needed for classic)
        $has_enabled_feature = !empty($delivery_method_settings['enabled'])
            || !empty($delivery_instructions_settings['enabled'])
            || !empty($time_window_settings['enabled'])
            || !empty($store_locations_settings['enabled'])
            || !empty($delivery_settings['enabled'])
            || !empty($field_settings['enabled'])
            || !empty($field_2_settings['enabled']);

        if (!$has_enabled_feature) {
            return;
        }

        // Flatpickr CSS
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_style(
                'flatpickr',
                MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                [],
                '4.6.13'
            );

            wp_enqueue_style(
                'wct-flatpickr-theme',
                MARWCHTO_PLUGIN_URL . 'public/css/flatpickr-theme.css',
                ['flatpickr'],
                MARWCHTO_VERSION
            );
        }

        // Main checkout CSS
        wp_enqueue_style(
            'wct-checkout',
            MARWCHTO_PLUGIN_URL . 'public/css/checkout.css',
            [],
            MARWCHTO_VERSION
        );

        // Build dependencies array - flatpickr only needed if delivery date enabled
        $checkout_js_deps = ['jquery'];

        // Flatpickr JS (only if delivery date enabled)
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_script(
                'flatpickr',
                MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                [],
                '4.6.13',
                true
            );
            $checkout_js_deps[] = 'flatpickr';
        }

        // Main checkout JS
        wp_enqueue_script(
            'wct-checkout',
            MARWCHTO_PLUGIN_URL . 'public/js/checkout.js',
            $checkout_js_deps,
            MARWCHTO_VERSION,
            true
        );

        // Localize script with configuration
        wp_localize_script('wct-checkout', 'wctConfig', $this->get_frontend_config());
    }

    /**
     * Get frontend configuration for JavaScript
     */
    private function get_frontend_config(): array
    {
        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();

        $config = [
            'delivery' => [
                'enabled' => !empty($delivery_settings['enabled']),
            ],
            'field' => [
                'enabled' => !empty($field_settings['enabled']),
                'maxLength' => (int) ($field_settings['max_length'] ?? 500),
                'showCounter' => true,
            ],
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('marwchto_checkout'),
            'i18n' => [
                'selectDate' => __('Select a date', 'marwen-checkout-toolkit-for-woocommerce'),
                'charactersRemaining' => __('characters remaining', 'marwen-checkout-toolkit-for-woocommerce'),
            ],
        ];

        // Add delivery date specific config
        if (!empty($delivery_settings['enabled'])) {
            $min_date = new \DateTime();
            $min_date->modify('+' . ($delivery_settings['min_lead_days'] ?? 2) . ' days');

            $max_date = new \DateTime();
            $max_date->modify('+' . ($delivery_settings['max_future_days'] ?? 30) . ' days');

            $config['delivery'] = array_merge($config['delivery'], [
                'minDate' => $min_date->format('Y-m-d'),
                'maxDate' => $max_date->format('Y-m-d'),
                'disabledDates' => $delivery_settings['blocked_dates'] ?? [],
                'disabledDays' => array_map('intval', $delivery_settings['disabled_weekdays'] ?? [0]),
                'dateFormat' => $this->php_to_flatpickr_format($delivery_settings['date_format'] ?? 'F j, Y'),
                'firstDayOfWeek' => (int) ($delivery_settings['first_day_of_week'] ?? 1),
            ]);
        }

        return apply_filters('marwchto_frontend_config', $config);
    }

    /**
     * Convert PHP date format to Flatpickr format
     */
    private function php_to_flatpickr_format(string $php_format): string
    {
        $replacements = [
            'F' => 'F',      // Full month name
            'M' => 'M',      // Short month name
            'm' => 'm',      // Month with leading zero
            'n' => 'n',      // Month without leading zero
            'd' => 'd',      // Day with leading zero
            'j' => 'j',      // Day without leading zero
            'Y' => 'Y',      // 4-digit year
            'y' => 'y',      // 2-digit year
            'l' => 'l',      // Full day name
            'D' => 'D',      // Short day name
        ];

        return strtr($php_format, $replacements);
    }

    /**
     * Get estimated delivery data for blocks checkout
     *
     * @param array $delivery_settings The delivery settings.
     * @return array Estimated delivery data.
     */
    private function get_estimated_delivery_data(array $delivery_settings): array
    {
        $enabled = !empty($delivery_settings['show_estimated_delivery']);

        if (!$enabled) {
            return ['enabled' => false];
        }

        $checker = new Delivery\AvailabilityChecker();

        return [
            'enabled' => true,
            'message' => $delivery_settings['estimated_delivery_message'] ?? 'Order now for delivery as early as {date}',
            'cutoffTime' => $delivery_settings['cutoff_time'] ?? '14:00',
            'cutoffMessage' => $delivery_settings['cutoff_message'] ?? 'Order by {time} for delivery as early as {date}',
            'earliestDate' => $checker->get_earliest_available_date(false),
            'earliestDateAfterCutoff' => $checker->get_earliest_available_date(true),
        ];
    }

    /**
     * Get default delivery settings
     */
    public function get_default_delivery_settings(): array
    {
        return [
            'enabled' => true,
            'required' => false,
            'field_label' => 'Preferred Delivery Date',
            'field_position' => 'woocommerce_after_order_notes',
            'min_lead_days' => 2,
            'max_future_days' => 30,
            'disabled_weekdays' => [0],
            'blocked_dates' => [],
            'date_format' => 'F j, Y',
            'first_day_of_week' => 1,
            'show_in_emails' => true,
            'show_in_admin' => true,
            // Estimated delivery settings
            'show_estimated_delivery' => false,
            'estimated_delivery_message' => 'Order now for delivery as early as {date}',
            'cutoff_time' => '14:00',
            'cutoff_message' => 'Order by {time} for delivery as early as {date}',
        ];
    }

    /**
     * Get default field settings
     */
    public function get_default_field_settings(): array
    {
        return [
            'enabled' => true,
            'required' => false,
            'field_type' => 'textarea',
            'field_label' => 'Special Instructions',
            'field_placeholder' => 'Any special requests for your order?',
            'field_position' => 'woocommerce_after_order_notes',
            'max_length' => 500,
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
     * Get delivery settings (with defaults)
     */
    public function get_delivery_settings(): array
    {
        $defaults = $this->get_default_delivery_settings();
        $settings = get_option('marwchto_delivery_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get field settings (with defaults)
     */
    public function get_field_settings(): array
    {
        $defaults = $this->get_default_field_settings();
        $settings = get_option('marwchto_field_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default order notes settings
     */
    public function get_default_order_notes_settings(): array
    {
        return [
            'enabled' => false,
            'custom_label' => '',
            'custom_placeholder' => '',
        ];
    }

    /**
     * Get order notes settings (with defaults)
     */
    public function get_order_notes_settings(): array
    {
        $defaults = $this->get_default_order_notes_settings();
        $settings = get_option('marwchto_order_notes_settings', []);
        return wp_parse_args($settings, $defaults);
    }
}
