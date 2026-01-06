<?php
/**
 * Delivery method settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $marwchto_delivery_method_settings Delivery method settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="marwchto-settings-section">
    <h2><?php esc_html_e('Pickup vs Delivery Toggle', 'marwen-marwchto-for-woocommerce'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to choose between pickup and delivery at checkout.', 'marwen-marwchto-for-woocommerce'); ?>
    </p>

    <table class="form-table marwchto-settings-table">
        <tbody>
            <!-- Enable Feature -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Feature', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_delivery_method_settings[enabled]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_method_settings['enabled'])); ?>>
                        <?php esc_html_e('Show pickup/delivery toggle on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Default Method -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Default Method', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_delivery_method_settings[default_method]"
                               value="delivery"
                               <?php checked(($marwchto_delivery_method_settings['default_method'] ?? 'delivery') === 'delivery'); ?>>
                        <?php esc_html_e('Delivery', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="marwchto_delivery_method_settings[default_method]"
                               value="pickup"
                               <?php checked(($marwchto_delivery_method_settings['default_method'] ?? 'delivery') === 'pickup'); ?>>
                        <?php esc_html_e('Pickup', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Which option should be selected by default.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="marwchto_dm_field_label">
                        <?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_dm_field_label"
                           name="marwchto_delivery_method_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_delivery_method_settings['field_label'] ?? 'Fulfillment Method'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label shown above the toggle/radio options.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Delivery Label -->
            <tr>
                <th scope="row">
                    <label for="marwchto_dm_delivery_label">
                        <?php esc_html_e('Delivery Option Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_dm_delivery_label"
                           name="marwchto_delivery_method_settings[delivery_label]"
                           value="<?php echo esc_attr($marwchto_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Pickup Label -->
            <tr>
                <th scope="row">
                    <label for="marwchto_dm_pickup_label">
                        <?php esc_html_e('Pickup Option Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_dm_pickup_label"
                           name="marwchto_delivery_method_settings[pickup_label]"
                           value="<?php echo esc_attr($marwchto_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Display Style -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Style', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_delivery_method_settings[show_as]"
                               value="toggle"
                               <?php checked(($marwchto_delivery_method_settings['show_as'] ?? 'toggle') === 'toggle'); ?>>
                        <?php esc_html_e('Toggle buttons (side by side)', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="marwchto_delivery_method_settings[show_as]"
                               value="radio"
                               <?php checked(($marwchto_delivery_method_settings['show_as'] ?? 'toggle') === 'radio'); ?>>
                        <?php esc_html_e('Radio buttons (stacked)', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Display Options -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Options', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="marwchto_delivery_method_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_method_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="marwchto_delivery_method_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_method_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="marwchto-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'marwen-marwchto-for-woocommerce'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the toggle will appear on checkout.', 'marwen-marwchto-for-woocommerce'); ?></p>

        <div class="marwchto-preview-content" style="margin-top: 15px;">
            <h4 id="marwchto-preview-label"><?php echo esc_html($marwchto_delivery_method_settings['field_label'] ?? 'Fulfillment Method'); ?></h4>

            <div id="marwchto-preview-toggle" style="<?php echo ($marwchto_delivery_method_settings['show_as'] ?? 'toggle') !== 'toggle' ? 'display:none;' : ''; ?>">
                <div style="display: flex; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; max-width: 300px;">
                    <span style="flex: 1; text-align: center; padding: 12px 20px; background: #2271b1; color: #fff; font-weight: 500;" id="marwchto-preview-delivery-toggle">
                        <?php echo esc_html($marwchto_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?>
                    </span>
                    <span style="flex: 1; text-align: center; padding: 12px 20px; background: #f9f9f9; border-left: 1px solid #ddd; font-weight: 500;" id="marwchto-preview-pickup-toggle">
                        <?php echo esc_html($marwchto_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?>
                    </span>
                </div>
            </div>

            <div id="marwchto-preview-radio" style="<?php echo ($marwchto_delivery_method_settings['show_as'] ?? 'toggle') === 'toggle' ? 'display:none;' : ''; ?>">
                <p style="margin: 5px 0;">
                    <label><input type="radio" name="marwchto-preview-method" checked> <span id="marwchto-preview-delivery-radio"><?php echo esc_html($marwchto_delivery_method_settings['delivery_label'] ?? 'Delivery'); ?></span></label>
                </p>
                <p style="margin: 5px 0;">
                    <label><input type="radio" name="marwchto-preview-method"> <span id="marwchto-preview-pickup-radio"><?php echo esc_html($marwchto_delivery_method_settings['pickup_label'] ?? 'Pickup'); ?></span></label>
                </p>
            </div>
        </div>
    </div>
</div>
