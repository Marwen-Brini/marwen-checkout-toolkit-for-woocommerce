<?php
/**
 * Plugin Name:       Checkout Toolkit for WooCommerce
 * Plugin URI:        https://github.com/Marwen-Brini/checkout-toolkit-for-woo
 * Description:       A comprehensive checkout enhancement plugin combining delivery scheduling and custom order fields into one powerful solution.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      8.1
 * Author:            Marwen Brini
 * Author URI:        https://github.com/Marwen-Brini
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       checkout-toolkit-for-woo
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.4
 *
 * @package CheckoutToolkitForWoo
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

use WooCheckoutToolkit\Admin\Admin;
use WooCheckoutToolkit\Admin\Settings;

defined('ABSPATH') || exit;

// Plugin constants
define('CHECKOUT_TOOLKIT_VERSION', '1.0.0');
define('CHECKOUT_TOOLKIT_PLUGIN_FILE', __FILE__);
define('CHECKOUT_TOOLKIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CHECKOUT_TOOLKIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CHECKOUT_TOOLKIT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Composer autoloader
if (file_exists(CHECKOUT_TOOLKIT_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CHECKOUT_TOOLKIT_PLUGIN_DIR . 'vendor/autoload.php';
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
                /* translators: 1: Plugin name, 2: WooCommerce */
                esc_html__(
                    '%1$s requires %2$s to be installed and active.',
                    'checkout-toolkit-for-woo'
                ),
                '<strong>Checkout Toolkit for WooCommerce</strong>',
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

    // Initialize main plugin on init to ensure translations are auto-loaded first
    // WordPress 4.6+ auto-loads translations for plugins hosted on WordPress.org
    add_action('init', function () {
        $plugin = Main::get_instance();
        $plugin->init();
    }, 5);
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
