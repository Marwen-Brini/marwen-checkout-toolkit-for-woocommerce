<?php
/**
 * Settings page template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables.
$delivery_settings = get_option('checkout_toolkit_delivery_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_delivery_settings());
$field_settings = get_option('checkout_toolkit_field_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_field_settings());
// phpcs:enable
?>

<div class="wrap wct-settings-wrap">
    <h1><?php esc_html_e('Checkout Toolkit for WooCommerce', 'checkout-toolkit-for-woo'); ?></h1>

    <nav class="nav-tab-wrapper wct-nav-tabs">
        <a href="?page=wct-settings&tab=delivery"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Delivery Date', 'checkout-toolkit-for-woo'); ?>
        </a>
        <a href="?page=wct-settings&tab=fields"
           class="nav-tab <?php echo esc_attr($active_tab === 'fields' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Custom Field', 'checkout-toolkit-for-woo'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="wct-settings-form">
        <?php settings_fields('checkout_toolkit_settings'); ?>

        <?php if ($active_tab === 'delivery') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-delivery.php'; ?>
        <?php elseif ($active_tab === 'fields') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-fields.php'; ?>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
