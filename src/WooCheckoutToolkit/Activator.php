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
            deactivate_plugins(plugin_basename(WCT_PLUGIN_FILE));
            wp_die(
                esc_html__(
                    'WooCommerce Checkout Toolkit requires PHP 8.1 or higher.',
                    'woo-checkout-toolkit'
                ),
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.8', '<')) {
            deactivate_plugins(plugin_basename(WCT_PLUGIN_FILE));
            wp_die(
                esc_html__(
                    'WooCommerce Checkout Toolkit requires WordPress 5.8 or higher.',
                    'woo-checkout-toolkit'
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
        if (get_option('wct_delivery_settings') === false) {
            add_option('wct_delivery_settings', $main->get_default_delivery_settings());
        }

        if (get_option('wct_field_settings') === false) {
            add_option('wct_field_settings', $main->get_default_field_settings());
        }
    }

    /**
     * Set activation timestamp for tracking
     */
    private static function set_activation_timestamp(): void
    {
        if (get_option('wct_activated_at') === false) {
            add_option('wct_activated_at', time());
        }

        update_option('wct_version', WCT_VERSION);
    }
}
