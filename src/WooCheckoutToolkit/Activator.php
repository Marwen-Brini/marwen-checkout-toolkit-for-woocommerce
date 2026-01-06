<?php
/**
 * Plugin activation handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

defined('ABSPATH') || exit;

/**
 * Class Activator
 *
 * Handles plugin activation tasks.
 */
class Activator
{
    /**
     * Run activation tasks
     */
    public static function activate(): void
    {
        self::check_requirements();
        self::create_default_options();
        self::set_activation_timestamp();

        // Clear any cached data
        wp_cache_flush();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Check system requirements
     */
    private static function check_requirements(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            deactivate_plugins(plugin_basename(MARWCHTO_PLUGIN_FILE));
            wp_die(
                esc_html__(
                    'Checkout Toolkit for WooCommerce requires PHP 8.1 or higher.',
                    'marwen-checkout-toolkit-for-woocommerce'
                ),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.8', '<')) {
            deactivate_plugins(plugin_basename(MARWCHTO_PLUGIN_FILE));
            wp_die(
                esc_html__(
                    'Checkout Toolkit for WooCommerce requires WordPress 5.8 or higher.',
                    'marwen-checkout-toolkit-for-woocommerce'
                ),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }
    }

    /**
     * Create default plugin options
     */
    private static function create_default_options(): void
    {
        $main = Main::get_instance();

        // Only set defaults if options don't exist
        if (get_option('checkout_toolkit_delivery_settings') === false) {
            add_option('checkout_toolkit_delivery_settings', $main->get_default_delivery_settings());
        }

        if (get_option('checkout_toolkit_field_settings') === false) {
            add_option('checkout_toolkit_field_settings', $main->get_default_field_settings());
        }
    }

    /**
     * Set activation timestamp for tracking
     */
    private static function set_activation_timestamp(): void
    {
        if (get_option('checkout_toolkit_activated_at') === false) {
            add_option('checkout_toolkit_activated_at', time());
        }

        update_option('checkout_toolkit_version', MARWCHTO_VERSION);
    }
}
