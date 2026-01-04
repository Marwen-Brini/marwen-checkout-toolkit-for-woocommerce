<?php
/**
 * Admin handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

defined('ABSPATH') || exit;

/**
 * Class Admin
 *
 * Handles admin functionality including settings pages and assets.
 */
class Admin
{
    /**
     * Settings instance
     */
    private ?Settings $settings = null;

    /**
     * Initialize admin
     */
    public function init(): void
    {
        $this->settings = new Settings();
        $this->settings->init();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('plugin_action_links_' . CHECKOUT_TOOLKIT_PLUGIN_BASENAME, [$this, 'add_action_links']);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets(string $hook): void
    {
        // Load CSS on settings page and dashboard (for widget)
        if (in_array($hook, ['woocommerce_page_wct-settings', 'index.php'], true)) {
            wp_enqueue_style(
                'wct-admin',
                CHECKOUT_TOOLKIT_PLUGIN_URL . 'admin/css/admin.css',
                [],
                CHECKOUT_TOOLKIT_VERSION
            );
        }

        // Only load full JS on our settings page
        if ($hook !== 'woocommerce_page_wct-settings') {
            return;
        }

        wp_enqueue_script(
            'wct-admin',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            CHECKOUT_TOOLKIT_VERSION,
            true
        );

        // WooCommerce enhanced select for product search
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_style('woocommerce_admin_styles');

        // Flatpickr for blocked dates manager
        wp_enqueue_style(
            'flatpickr',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
            [],
            '4.6.13'
        );

        wp_enqueue_script(
            'flatpickr',
            CHECKOUT_TOOLKIT_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
            [],
            '4.6.13',
            true
        );

        wp_localize_script('wct-admin', 'wctAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wct_admin'),
            'nonces' => [
                'searchProducts' => wp_create_nonce('search-products'),
            ],
            'i18n' => [
                // General
                'confirmRemove' => __('Are you sure you want to remove this date?', 'checkout-toolkit-for-woo'),
                'dateAdded' => __('Date added successfully', 'checkout-toolkit-for-woo'),
                'dateRemoved' => __('Date removed', 'checkout-toolkit-for-woo'),
                'selectDate' => __('Please select a date', 'checkout-toolkit-for-woo'),
                'remove' => __('Remove', 'checkout-toolkit-for-woo'),
                'label' => __('Label', 'checkout-toolkit-for-woo'),
                'value' => __('Value', 'checkout-toolkit-for-woo'),
                // Delivery Instructions
                'labelShownToCustomer' => __('Label (shown to customer)', 'checkout-toolkit-for-woo'),
                'valueStored' => __('Value (stored)', 'checkout-toolkit-for-woo'),
                // Store Locations
                'location' => __('Location', 'checkout-toolkit-for-woo'),
                'removeLocation' => __('Remove location', 'checkout-toolkit-for-woo'),
                'locationId' => __('Location ID', 'checkout-toolkit-for-woo'),
                'locationIdPlaceholder' => __('e.g., main-store (auto-generated if empty)', 'checkout-toolkit-for-woo'),
                'storeName' => __('Store Name', 'checkout-toolkit-for-woo'),
                'storeNamePlaceholder' => __('Store name (required)', 'checkout-toolkit-for-woo'),
                'address' => __('Address', 'checkout-toolkit-for-woo'),
                'fullAddress' => __('Full address', 'checkout-toolkit-for-woo'),
                'phone' => __('Phone', 'checkout-toolkit-for-woo'),
                'phoneNumber' => __('Phone number', 'checkout-toolkit-for-woo'),
                'hours' => __('Hours', 'checkout-toolkit-for-woo'),
                'hoursPlaceholder' => __('e.g., Mon-Fri: 9am-6pm', 'checkout-toolkit-for-woo'),
                // Time Window
                'timeSlotValuePlaceholder' => __('Value (e.g., morning)', 'checkout-toolkit-for-woo'),
                'timeSlotLabelPlaceholder' => __('Label (e.g., Morning 9am-12pm)', 'checkout-toolkit-for-woo'),
            ],
        ]);
    }

    /**
     * Add plugin action links
     */
    public function add_action_links(array $links): array
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wct-settings'),
            __('Settings', 'checkout-toolkit-for-woo')
        );

        array_unshift($links, $settings_link);

        return $links;
    }
}
