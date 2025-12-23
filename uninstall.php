<?php
/**
 * Uninstall script
 *
 * Runs when the plugin is deleted from WordPress admin.
 * Removes all plugin data from the database.
 *
 * @package WooCheckoutToolkit
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove all plugin options
 */
function wct_delete_options(): void
{
    $options = [
        'wct_delivery_settings',
        'wct_field_settings',
        'wct_activated_at',
        'wct_version',
    ];

    foreach ($options as $option) {
        delete_option($option);
    }
}

/**
 * Remove all plugin transients
 */
function wct_delete_transients(): void
{
    global $wpdb;

    $wpdb->query(
        "DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_wct_%'
        OR option_name LIKE '_transient_timeout_wct_%'"
    );
}

/**
 * Remove order meta (optional - uncomment if desired)
 * Note: This removes customer data, so it's commented out by default
 */
function wct_delete_order_meta(): void
{
    global $wpdb;

    // For traditional post meta storage
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta}
        WHERE meta_key IN ('_wct_delivery_date', '_wct_custom_field')"
    );

    // For HPOS storage (wc_orders_meta table)
    $orders_meta_table = $wpdb->prefix . 'wc_orders_meta';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$orders_meta_table}'") === $orders_meta_table) {
        $wpdb->query(
            "DELETE FROM {$orders_meta_table}
            WHERE meta_key IN ('_wct_delivery_date', '_wct_custom_field')"
        );
    }
}

// Run cleanup
wct_delete_options();
wct_delete_transients();

// Uncomment the following line to also remove order meta data on uninstall
// wct_delete_order_meta();

// Clear object cache
wp_cache_flush();
