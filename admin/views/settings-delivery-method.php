<?php
/**
 * Delivery method settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $checkout_toolkit_delivery_method_settings Delivery method settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Pickup vs Delivery Toggle', 'checkout-toolkit-for-woo'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to choose between pickup and delivery at checkout.', 'checkout-toolkit-for-woo'); ?>
    </p>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Feature -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Feature', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_method_settings[enabled]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_method_settings['enabled'])); ?>>
                        <?php esc_html_e('Show pickup/delivery toggle on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Default Method -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Default Method', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method_settings[default_method]"
                               value="delivery"
                               <?php checked(($checkout_toolkit_delivery_method_settings['default_method'] ?? 'delivery') === 'delivery'); ?>>
                        <?php esc_html_e('Delivery', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method_settings[default_method]"
                               value="pickup"
                               <?php checked(($checkout_toolkit_delivery_method_settings['default_method'] ?? 'delivery') === 'pickup'); ?>>
                        <?php esc_html_e('Pickup', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Which option should be selected by default.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="checkout_toolkit_dm_field_label">
                        <?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_dm_field_label"
                           name="checkout_toolkit_delivery_method_settings[field_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_method_settings['field_label'] ?? 'Fulfillment Method'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label shown above the toggle/radio options.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Delivery Label -->
            <tr>
                <th scope="row">
                    <label for="checkout_toolkit_dm_delivery_label">
                        <?php esc_html_e('Delivery Option Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_dm_delivery_label"
                           name="checkout_toolkit_delivery_method_settings[delivery_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Pickup Label -->
            <tr>
                <th scope="row">
                    <label for="checkout_toolkit_dm_pickup_label">
                        <?php esc_html_e('Pickup Option Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_dm_pickup_label"
                           name="checkout_toolkit_delivery_method_settings[pickup_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Display Style -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Style', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method_settings[show_as]"
                               value="toggle"
                               <?php checked(($checkout_toolkit_delivery_method_settings['show_as'] ?? 'toggle') === 'toggle'); ?>>
                        <?php esc_html_e('Toggle buttons (side by side)', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method_settings[show_as]"
                               value="radio"
                               <?php checked(($checkout_toolkit_delivery_method_settings['show_as'] ?? 'toggle') === 'radio'); ?>>
                        <?php esc_html_e('Radio buttons (stacked)', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Display Options -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_method_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_method_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_method_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_method_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="wct-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'checkout-toolkit-for-woo'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the toggle will appear on checkout.', 'checkout-toolkit-for-woo'); ?></p>

        <div class="wct-preview-content" style="margin-top: 15px;">
            <h4 id="wct-preview-label"><?php echo esc_html($checkout_toolkit_delivery_method_settings['field_label'] ?? 'Fulfillment Method'); ?></h4>

            <div id="wct-preview-toggle" style="<?php echo ($checkout_toolkit_delivery_method_settings['show_as'] ?? 'toggle') !== 'toggle' ? 'display:none;' : ''; ?>">
                <div style="display: flex; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; max-width: 300px;">
                    <span style="flex: 1; text-align: center; padding: 12px 20px; background: #2271b1; color: #fff; font-weight: 500;" id="wct-preview-delivery-toggle">
                        <?php echo esc_html($checkout_toolkit_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?>
                    </span>
                    <span style="flex: 1; text-align: center; padding: 12px 20px; background: #f9f9f9; border-left: 1px solid #ddd; font-weight: 500;" id="wct-preview-pickup-toggle">
                        <?php echo esc_html($checkout_toolkit_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?>
                    </span>
                </div>
            </div>

            <div id="wct-preview-radio" style="<?php echo ($checkout_toolkit_delivery_method_settings['show_as'] ?? 'toggle') === 'toggle' ? 'display:none;' : ''; ?>">
                <p style="margin: 5px 0;">
                    <label><input type="radio" name="wct-preview-method" checked> <span id="wct-preview-delivery-radio"><?php echo esc_html($checkout_toolkit_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?></span></label>
                </p>
                <p style="margin: 5px 0;">
                    <label><input type="radio" name="wct-preview-method"> <span id="wct-preview-pickup-radio"><?php echo esc_html($checkout_toolkit_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?></span></label>
                </p>
            </div>
        </div>
    </div>
</div>
