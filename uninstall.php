<?php
/**
 * Uninstall script
 *
 * Runs when the plugin is deleted from WordPress admin.
 * Removes all plugin data from the database.
 *
 * @package CheckoutToolkitForWoo
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove all plugin options
 */
function checkout_toolkit_delete_options(): void
{
    $options = [
        'checkout_toolkit_delivery_settings',
        'checkout_toolkit_field_settings',
        'checkout_toolkit_activated_at',
        'checkout_toolkit_version',
    ];

    foreach ($options as $option) {
        delete_option($option);
    }
}

/**
 * Remove all plugin transients
 */
function checkout_toolkit_delete_transients(): void
{
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_checkout_toolkit_%',
            '_transient_timeout_checkout_toolkit_%'
        )
    );
}

/**
 * Remove order meta (optional - uncomment if desired)
 * Note: This removes customer data, so it's commented out by default
 */
function checkout_toolkit_delete_order_meta(): void
{
    global $wpdb;

    // For traditional post meta storage
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s)",
            '_wct_delivery_date',
            '_wct_custom_field'
        )
    );

    // For HPOS storage (wc_orders_meta table)
    $orders_meta_table = $wpdb->prefix . 'wc_orders_meta';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checking table existence.
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $orders_meta_table)) === $orders_meta_table) {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query(
            $wpdb->prepare(
                // Safely constructed table name using $wpdb->prefix.
                "DELETE FROM {$orders_meta_table} WHERE meta_key IN (%s, %s)",
                '_wct_delivery_date',
                '_wct_custom_field'
            )
        );
        // phpcs:enable
    }
}

// Run cleanup
checkout_toolkit_delete_options();
checkout_toolkit_delete_transients();

// Uncomment the following line to also remove order meta data on uninstall
// checkout_toolkit_delete_order_meta();

// Clear object cache
wp_cache_flush();
