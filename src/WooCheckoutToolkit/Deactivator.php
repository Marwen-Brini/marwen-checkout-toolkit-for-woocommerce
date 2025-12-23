<?php
/**
 * Plugin deactivation handler
 *
 * @package WooCheckoutToolkit
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
        $timestamp = wp_next_scheduled('wct_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wct_daily_cleanup');
        }
    }

    /**
     * Clear plugin transients
     */
    private static function clear_transients(): void
    {
        global $wpdb;

        // Delete all plugin transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_wct_%'
            OR option_name LIKE '_transient_timeout_wct_%'"
        );
    }
}
