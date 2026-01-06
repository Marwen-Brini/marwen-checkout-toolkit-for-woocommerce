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
        add_filter('plugin_action_links_' . MARWCHTO_PLUGIN_BASENAME, [$this, 'add_action_links']);
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
                MARWCHTO_PLUGIN_URL . 'admin/css/admin.css',
                [],
                MARWCHTO_VERSION
            );
        }

        // Only load full JS on our settings page
        if ($hook !== 'woocommerce_page_wct-settings') {
            return;
        }

        wp_enqueue_script(
            'wct-admin',
            MARWCHTO_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            MARWCHTO_VERSION,
            true
        );

        // WooCommerce enhanced select for product search
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_style('woocommerce_admin_styles');

        // Flatpickr for blocked dates manager
        wp_enqueue_style(
            'flatpickr',
            MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
            [],
            '4.6.13'
        );

        wp_enqueue_script(
            'flatpickr',
            MARWCHTO_PLUGIN_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
            [],
            '4.6.13',
            true
        );

        wp_localize_script('wct-admin', 'marwchtoAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wct_admin'),
            'nonces' => [
                'searchProducts' => wp_create_nonce('search-products'),
            ],
            'i18n' => [
                // General
                'confirmRemove' => __('Are you sure you want to remove this date?', 'marwen-checkout-toolkit-for-woocommerce'),
                'dateAdded' => __('Date added successfully', 'marwen-checkout-toolkit-for-woocommerce'),
                'dateRemoved' => __('Date removed', 'marwen-checkout-toolkit-for-woocommerce'),
                'selectDate' => __('Please select a date', 'marwen-checkout-toolkit-for-woocommerce'),
                'remove' => __('Remove', 'marwen-checkout-toolkit-for-woocommerce'),
                'label' => __('Label', 'marwen-checkout-toolkit-for-woocommerce'),
                'value' => __('Value', 'marwen-checkout-toolkit-for-woocommerce'),
                // Delivery Instructions
                'labelShownToCustomer' => __('Label (shown to customer)', 'marwen-checkout-toolkit-for-woocommerce'),
                'valueStored' => __('Value (stored)', 'marwen-checkout-toolkit-for-woocommerce'),
                // Store Locations
                'location' => __('Location', 'marwen-checkout-toolkit-for-woocommerce'),
                'removeLocation' => __('Remove location', 'marwen-checkout-toolkit-for-woocommerce'),
                'locationId' => __('Location ID', 'marwen-checkout-toolkit-for-woocommerce'),
                'locationIdPlaceholder' => __('e.g., main-store (auto-generated if empty)', 'marwen-checkout-toolkit-for-woocommerce'),
                'storeName' => __('Store Name', 'marwen-checkout-toolkit-for-woocommerce'),
                'storeNamePlaceholder' => __('Store name (required)', 'marwen-checkout-toolkit-for-woocommerce'),
                'address' => __('Address', 'marwen-checkout-toolkit-for-woocommerce'),
                'fullAddress' => __('Full address', 'marwen-checkout-toolkit-for-woocommerce'),
                'phone' => __('Phone', 'marwen-checkout-toolkit-for-woocommerce'),
                'phoneNumber' => __('Phone number', 'marwen-checkout-toolkit-for-woocommerce'),
                'hours' => __('Hours', 'marwen-checkout-toolkit-for-woocommerce'),
                'hoursPlaceholder' => __('e.g., Mon-Fri: 9am-6pm', 'marwen-checkout-toolkit-for-woocommerce'),
                // Time Window
                'timeSlotValuePlaceholder' => __('Value (e.g., morning)', 'marwen-checkout-toolkit-for-woocommerce'),
                'timeSlotLabelPlaceholder' => __('Label (e.g., Morning 9am-12pm)', 'marwen-checkout-toolkit-for-woocommerce'),
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
            __('Settings', 'marwen-checkout-toolkit-for-woocommerce')
        );

        array_unshift($links, $settings_link);

        return $links;
    }
}
