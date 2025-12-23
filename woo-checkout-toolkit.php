<?php
/**
 * Plugin Name:       WooCommerce Checkout Toolkit
 * Plugin URI:        https://example.com/woo-checkout-toolkit
 * Description:       A comprehensive checkout enhancement plugin combining delivery scheduling and custom order fields into one powerful solution.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      8.1
 * Author:            Marwen Brini
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woo-checkout-toolkit
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   8.4
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

use WooCheckoutToolkit\Admin\Admin;
use WooCheckoutToolkit\Admin\Settings;

defined('ABSPATH') || exit;

// Plugin constants
define('WCT_VERSION', '1.0.0');
define('WCT_PLUGIN_FILE', __FILE__);
define('WCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Composer autoloader
if (file_exists(WCT_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once WCT_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Check if WooCommerce is active
 */
function wct_is_woocommerce_active(): bool
{
    return class_exists('WooCommerce');
}

/**
 * Admin notice for missing WooCommerce
 */
function wct_woocommerce_missing_notice(): void
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                esc_html__(
                    '%1$s requires %2$s to be installed and active.',
                    'woo-checkout-toolkit'
                ),
                '<strong>WooCommerce Checkout Toolkit</strong>',
                '<strong>WooCommerce</strong>'
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function wct_init(): void
{
    // Check WooCommerce dependency
    if (!wct_is_woocommerce_active()) {
        add_action('admin_notices', __NAMESPACE__ . '\\wct_woocommerce_missing_notice');
        return;
    }

    // Load text domain
    load_plugin_textdomain(
        'woo-checkout-toolkit',
        false,
        dirname(WCT_PLUGIN_BASENAME) . '/languages'
    );

    // Initialize main plugin
    $plugin = Main::get_instance();
    $plugin->init();
}
add_action('plugins_loaded', __NAMESPACE__ . '\\wct_init');

/**
 * Activation hook
 */
function wct_activate(): void
{
    Activator::activate();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\wct_activate');

/**
 * Deactivation hook
 */
function wct_deactivate(): void
{
    Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\wct_deactivate');

/**
 * Declare HPOS compatibility
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
});
