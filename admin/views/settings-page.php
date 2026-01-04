<?php
/**
 * Settings page template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_delivery_settings = get_option('checkout_toolkit_delivery_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_delivery_settings());
$checkout_toolkit_field_settings = get_option('checkout_toolkit_field_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_field_settings());
$checkout_toolkit_field_2_settings = get_option('checkout_toolkit_field_2_settings', $checkout_toolkit_settings_obj->get_default_field_2_settings());
$checkout_toolkit_order_notes_settings = get_option('checkout_toolkit_order_notes_settings', [
    'enabled' => false,
    'custom_placeholder' => '',
    'custom_label' => '',
]);
$checkout_toolkit_delivery_method_settings = get_option('checkout_toolkit_delivery_method_settings', $checkout_toolkit_settings_obj->get_default_delivery_method_settings());
$checkout_toolkit_delivery_instructions_settings = get_option('checkout_toolkit_delivery_instructions_settings', $checkout_toolkit_settings_obj->get_default_delivery_instructions_settings());
$checkout_toolkit_time_window_settings = get_option('checkout_toolkit_time_window_settings', $checkout_toolkit_settings_obj->get_default_time_window_settings());
$checkout_toolkit_store_locations_settings = get_option('checkout_toolkit_store_locations_settings', $checkout_toolkit_settings_obj->get_default_store_locations_settings());
?>

<div class="wrap wct-settings-wrap">
    <h1><?php esc_html_e('Checkout Toolkit for WooCommerce', 'marwen-checkout-toolkit-for-woocommerce'); ?></h1>

    <nav class="nav-tab-wrapper wct-nav-tabs">
        <a href="?page=wct-settings&tab=delivery-method"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery-method' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Pickup/Delivery', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=store-locations"
           class="nav-tab <?php echo esc_attr($active_tab === 'store-locations' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Store Locations', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=delivery-instructions"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery-instructions' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Delivery Instructions', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=time-windows"
           class="nav-tab <?php echo esc_attr($active_tab === 'time-windows' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Time Windows', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=delivery"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Delivery Date', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=fields"
           class="nav-tab <?php echo esc_attr($active_tab === 'fields' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Custom Fields', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=wct-settings&tab=order-notes"
           class="nav-tab <?php echo esc_attr($active_tab === 'order-notes' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Order Notes', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="wct-settings-form">
        <?php settings_fields('checkout_toolkit_settings'); ?>

        <?php if ($active_tab === 'delivery-method') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-delivery-method.php'; ?>
        <?php elseif ($active_tab === 'store-locations') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-store-locations.php'; ?>
        <?php elseif ($active_tab === 'delivery-instructions') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-delivery-instructions.php'; ?>
        <?php elseif ($active_tab === 'time-windows') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-time-windows.php'; ?>
        <?php elseif ($active_tab === 'delivery') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-delivery.php'; ?>
        <?php elseif ($active_tab === 'fields') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-fields.php'; ?>
        <?php elseif ($active_tab === 'order-notes') : ?>
            <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/settings-order-notes.php'; ?>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
