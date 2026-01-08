<?php
/**
 * Settings page template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$marwchto_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$marwchto_delivery_settings = get_option('marwchto_delivery_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_delivery_settings());
$marwchto_field_settings = get_option('marwchto_field_settings', \WooCheckoutToolkit\Main::get_instance()->get_default_field_settings());
$marwchto_field_2_settings = get_option('marwchto_field_2_settings', $marwchto_settings_obj->get_default_field_2_settings());
$marwchto_order_notes_settings = get_option('marwchto_order_notes_settings', [
    'enabled' => false,
    'custom_placeholder' => '',
    'custom_label' => '',
]);
$marwchto_delivery_method_settings = get_option('marwchto_delivery_method_settings', $marwchto_settings_obj->get_default_delivery_method_settings());
$marwchto_delivery_instructions_settings = get_option('marwchto_delivery_instructions_settings', $marwchto_settings_obj->get_default_delivery_instructions_settings());
$marwchto_time_window_settings = get_option('marwchto_time_window_settings', $marwchto_settings_obj->get_default_time_window_settings());
$marwchto_store_locations_settings = get_option('marwchto_store_locations_settings', $marwchto_settings_obj->get_default_store_locations_settings());
?>

<div class="wrap marwchto-settings-wrap">
    <h1><?php esc_html_e('Marwen Checkout Toolkit for WooCommerce', 'marwen-checkout-toolkit-for-woocommerce'); ?></h1>

    <nav class="nav-tab-wrapper marwchto-nav-tabs">
        <a href="?page=marwchto-settings&tab=delivery-method"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery-method' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Pickup/Delivery', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=store-locations"
           class="nav-tab <?php echo esc_attr($active_tab === 'store-locations' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Store Locations', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=delivery-instructions"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery-instructions' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Delivery Instructions', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=time-windows"
           class="nav-tab <?php echo esc_attr($active_tab === 'time-windows' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Time Windows', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=delivery"
           class="nav-tab <?php echo esc_attr($active_tab === 'delivery' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Delivery Date', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=fields"
           class="nav-tab <?php echo esc_attr($active_tab === 'fields' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Custom Fields', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="?page=marwchto-settings&tab=order-notes"
           class="nav-tab <?php echo esc_attr($active_tab === 'order-notes' ? 'nav-tab-active' : ''); ?>">
            <?php esc_html_e('Order Notes', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="marwchto-settings-form">
        <?php settings_fields('marwchto_settings'); ?>

        <?php if ($active_tab === 'delivery-method') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-delivery-method.php'; ?>
        <?php elseif ($active_tab === 'store-locations') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-store-locations.php'; ?>
        <?php elseif ($active_tab === 'delivery-instructions') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-delivery-instructions.php'; ?>
        <?php elseif ($active_tab === 'time-windows') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-time-windows.php'; ?>
        <?php elseif ($active_tab === 'delivery') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-delivery.php'; ?>
        <?php elseif ($active_tab === 'fields') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-fields.php'; ?>
        <?php elseif ($active_tab === 'order-notes') : ?>
            <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/settings-order-notes.php'; ?>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
