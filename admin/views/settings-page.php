<?php
/**
 * Settings page template
 *
 * @package WooCheckoutToolkit
 */

defined('ABSPATH') || exit;

$delivery_settings = get_option('wct_delivery_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_delivery_settings());
$field_settings = get_option('wct_field_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_field_settings());
?>

<div class="wrap wct-settings-wrap">
    <h1><?php esc_html_e('WooCommerce Checkout Toolkit', 'woo-checkout-toolkit'); ?></h1>

    <nav class="nav-tab-wrapper wct-nav-tabs">
        <a href="?page=wct-settings&tab=delivery"
           class="nav-tab <?php echo $active_tab === 'delivery' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Delivery Date', 'woo-checkout-toolkit'); ?>
        </a>
        <a href="?page=wct-settings&tab=fields"
           class="nav-tab <?php echo $active_tab === 'fields' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Custom Field', 'woo-checkout-toolkit'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="wct-settings-form">
        <?php settings_fields('wct_settings'); ?>

        <?php if ($active_tab === 'delivery') : ?>
            <?php include WCT_PLUGIN_DIR . 'admin/views/settings-delivery.php'; ?>
        <?php elseif ($active_tab === 'fields') : ?>
            <?php include WCT_PLUGIN_DIR . 'admin/views/settings-fields.php'; ?>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
