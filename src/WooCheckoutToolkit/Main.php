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
use WooCheckoutToolkit\Fields\OrderFields;
use WooCheckoutToolkit\Display\OrderDisplay;
use WooCheckoutToolkit\Display\EmailDisplay;
use WooCheckoutToolkit\Display\AccountDisplay;
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
     * Order fields instance
     */
    private ?OrderFields $order_fields = null;

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
            'version' => CHECKOUT_TOOLKIT_VERSION,
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
        $this->delivery_date = new DeliveryDate();
        $this->delivery_date->init();

        $this->order_fields = new OrderFields();
        $this->order_fields->init();

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

        // Register Store API endpoint for saving data
        add_action('woocommerce_blocks_loaded', function () {
            $integration = new BlocksIntegration();
            $integration->initialize();
            Logger::debug('Blocks integration initialized');
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

        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();

        // Only load if at least one feature is enabled
        if (!$delivery_settings['enabled'] && !$field_settings['enabled']) {
            return;
        }

        // Flatpickr for date picker
        if ($delivery_settings['enabled']) {
            wp_enqueue_script(
                'checkout-toolkit-flatpickr',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                [],
                '4.6.13',
                true
            );

            wp_enqueue_style(
                'checkout-toolkit-flatpickr',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                [],
                '4.6.13'
            );
        }

        // Blocks checkout script
        wp_enqueue_script(
            'checkout-toolkit-blocks',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/js/blocks-checkout.js',
            [
                'wp-plugins',
                'wp-element',
                'wp-data',
                'wc-blocks-checkout',
            ],
            CHECKOUT_TOOLKIT_VERSION,
            true
        );

        // Pass settings to JavaScript
        $script_data = $this->get_blocks_script_data();
        wp_localize_script('checkout-toolkit-blocks', 'checkoutToolkitData', $script_data);

        // Blocks checkout styles
        wp_enqueue_style(
            'checkout-toolkit-blocks-style',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/css/blocks-checkout.css',
            [],
            CHECKOUT_TOOLKIT_VERSION
        );
    }

    /**
     * Get script data for blocks checkout
     */
    private function get_blocks_script_data(): array
    {
        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();

        return [
            'delivery' => [
                'enabled' => $delivery_settings['enabled'],
                'required' => $delivery_settings['required'],
                'label' => $delivery_settings['field_label'],
                'minLeadDays' => $delivery_settings['min_lead_days'],
                'maxFutureDays' => $delivery_settings['max_future_days'],
                'disabledWeekdays' => array_map('intval', $delivery_settings['disabled_weekdays']),
                'blockedDates' => $delivery_settings['blocked_dates'],
                'dateFormat' => $this->php_to_flatpickr_format($delivery_settings['date_format']),
                'firstDayOfWeek' => $delivery_settings['first_day_of_week'],
            ],
            'customField' => [
                'enabled' => $field_settings['enabled'],
                'required' => $field_settings['required'],
                'type' => $field_settings['field_type'],
                'label' => $field_settings['field_label'],
                'placeholder' => $field_settings['field_placeholder'],
                'maxLength' => $field_settings['max_length'],
            ],
            'i18n' => [
                'selectDate' => __('Select a date', 'checkout-toolkit-for-woo'),
                'charactersRemaining' => __('characters remaining', 'checkout-toolkit-for-woo'),
            ],
        ];
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

        $delivery_settings = $this->get_delivery_settings();
        $field_settings = $this->get_field_settings();

        // Only load if at least one feature is enabled
        if (empty($delivery_settings['enabled']) && empty($field_settings['enabled'])) {
            return;
        }

        // Flatpickr CSS
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_style(
                'flatpickr',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                [],
                '4.6.13'
            );

            wp_enqueue_style(
                'wct-flatpickr-theme',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/css/flatpickr-theme.css',
                ['flatpickr'],
                CHECKOUT_TOOLKIT_VERSION
            );
        }

        // Main checkout CSS
        wp_enqueue_style(
            'wct-checkout',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/css/checkout.css',
            [],
            CHECKOUT_TOOLKIT_VERSION
        );

        // Flatpickr JS
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_script(
                'flatpickr',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                [],
                '4.6.13',
                true
            );
        }

        // Main checkout JS
        wp_enqueue_script(
            'wct-checkout',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'public/js/checkout.js',
            ['jquery', 'flatpickr'],
            CHECKOUT_TOOLKIT_VERSION,
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
            'nonce' => wp_create_nonce('checkout_toolkit_checkout'),
            'i18n' => [
                'selectDate' => __('Select a date', 'checkout-toolkit-for-woo'),
                'charactersRemaining' => __('characters remaining', 'checkout-toolkit-for-woo'),
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

        return apply_filters('checkout_toolkit_frontend_config', $config);
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
     * Get default delivery settings
     */
    public function get_default_delivery_settings(): array
    {
        return [
            'enabled' => true,
            'required' => false,
            'field_label' => __('Preferred Delivery Date', 'checkout-toolkit-for-woo'),
            'field_position' => 'woocommerce_after_order_notes',
            'min_lead_days' => 2,
            'max_future_days' => 30,
            'disabled_weekdays' => [0],
            'blocked_dates' => [],
            'date_format' => 'F j, Y',
            'first_day_of_week' => 1,
            'show_in_emails' => true,
            'show_in_admin' => true,
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
            'field_label' => __('Special Instructions', 'checkout-toolkit-for-woo'),
            'field_placeholder' => __('Any special requests for your order?', 'checkout-toolkit-for-woo'),
            'field_position' => 'woocommerce_after_order_notes',
            'max_length' => 500,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Get delivery settings (with defaults)
     */
    public function get_delivery_settings(): array
    {
        $defaults = $this->get_default_delivery_settings();
        $settings = get_option('checkout_toolkit_delivery_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get field settings (with defaults)
     */
    public function get_field_settings(): array
    {
        $defaults = $this->get_default_field_settings();
        $settings = get_option('checkout_toolkit_field_settings', []);
        return wp_parse_args($settings, $defaults);
    }
}
