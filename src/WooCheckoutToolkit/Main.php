<?php
/**
 * Main plugin class
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

use WooCheckoutToolkit\Admin\Admin;
use WooCheckoutToolkit\Delivery\DeliveryDate;
use WooCheckoutToolkit\Fields\OrderFields;
use WooCheckoutToolkit\Display\OrderDisplay;
use WooCheckoutToolkit\Display\EmailDisplay;
use WooCheckoutToolkit\Display\AccountDisplay;
use WooCheckoutToolkit\Logger;

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
        Logger::info('Initializing WooCommerce Checkout Toolkit', [
            'version' => WCT_VERSION,
            'php_version' => PHP_VERSION,
        ]);

        $this->init_admin();
        $this->init_frontend();
        $this->init_display();

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
        }
    }

    /**
     * Initialize frontend components
     */
    private function init_frontend(): void
    {
        $this->delivery_date = new DeliveryDate();
        $this->delivery_date->init();

        $this->order_fields = new OrderFields();
        $this->order_fields->init();

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
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
     * Enqueue frontend assets on checkout page
     */
    public function enqueue_frontend_assets(): void
    {
        if (!is_checkout()) {
            return;
        }

        $delivery_settings = get_option('wct_delivery_settings', []);
        $field_settings = get_option('wct_field_settings', []);

        // Only load if at least one feature is enabled
        if (empty($delivery_settings['enabled']) && empty($field_settings['enabled'])) {
            return;
        }

        // Flatpickr CSS
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_style(
                'flatpickr',
                WCT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                [],
                '4.6.13'
            );

            wp_enqueue_style(
                'wct-flatpickr-theme',
                WCT_PLUGIN_URL . 'public/css/flatpickr-theme.css',
                ['flatpickr'],
                WCT_VERSION
            );
        }

        // Main checkout CSS
        wp_enqueue_style(
            'wct-checkout',
            WCT_PLUGIN_URL . 'public/css/checkout.css',
            [],
            WCT_VERSION
        );

        // Flatpickr JS
        if (!empty($delivery_settings['enabled'])) {
            wp_enqueue_script(
                'flatpickr',
                WCT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                [],
                '4.6.13',
                true
            );
        }

        // Main checkout JS
        wp_enqueue_script(
            'wct-checkout',
            WCT_PLUGIN_URL . 'public/js/checkout.js',
            ['jquery', 'flatpickr'],
            WCT_VERSION,
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
        $delivery_settings = get_option('wct_delivery_settings', $this->get_default_delivery_settings());
        $field_settings = get_option('wct_field_settings', $this->get_default_field_settings());

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
            'nonce' => wp_create_nonce('wct_checkout'),
            'i18n' => [
                'selectDate' => __('Select a date', 'woo-checkout-toolkit'),
                'charactersRemaining' => __('characters remaining', 'woo-checkout-toolkit'),
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

        return apply_filters('wct_frontend_config', $config);
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
            'field_label' => __('Preferred Delivery Date', 'woo-checkout-toolkit'),
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
            'field_label' => __('Special Instructions', 'woo-checkout-toolkit'),
            'field_placeholder' => __('Any special requests for your order?', 'woo-checkout-toolkit'),
            'field_position' => 'woocommerce_after_order_notes',
            'max_length' => 500,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }
}
