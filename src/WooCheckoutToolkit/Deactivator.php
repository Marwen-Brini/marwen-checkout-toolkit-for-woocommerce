<?php
/**
 * Plugin deactivation handler
 *
 * @package CheckoutToolkitForWoo
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

defined('ABSPATH') || exit;

/**
 * Class Deactivator
 *
 * Handles plugin deactivation tasks.
 */
class Deactivator
{
    /**
     * Run deactivation tasks
     */
    public static function deactivate(): void
    {
        // Clear any scheduled events
        self::clear_scheduled_events();

        // Clear transients
        self::clear_transients();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear any scheduled cron events
     */
    private static function clear_scheduled_events(): void
    {
        // Clear any plugin-specific cron jobs if they exist
        $timestamp = wp_next_scheduled('checkout_toolkit_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'checkout_toolkit_daily_cleanup');
        }
    }

    /**
     * Clear plugin transients
     */
    private static function clear_transients(): void
    {
        global $wpdb;

        // Delete all plugin transients
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deactivation cleanup.
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_checkout_toolkit_%',
                '_transient_timeout_checkout_toolkit_%'
            )
        );
    }
}
