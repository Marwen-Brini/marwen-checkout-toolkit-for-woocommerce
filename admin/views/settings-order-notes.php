<?php
/**
 * Order notes settings tab
 *
 * @package WooCheckoutToolkit
 *
 * @var array $marwchto_order_notes_settings Order notes settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Order Notes Customization', 'marwen-marwchto-for-woocommerce'); ?></h2>
    <p class="description">
        <?php esc_html_e('Customize the default WooCommerce order notes field that appears at checkout.', 'marwen-marwchto-for-woocommerce'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Enable Customization', 'marwen-marwchto-for-woocommerce'); ?></th>
            <td>
                <label>
                    <input type="checkbox"
                           name="marwchto_order_notes_settings[enabled]"
                           value="1"
                           <?php checked(!empty($marwchto_order_notes_settings['enabled'])); ?>>
                    <?php esc_html_e('Enable order notes customization', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('When enabled, you can customize the placeholder and label of the order notes field.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="wct_order_notes_label"><?php esc_html_e('Custom Label', 'marwen-marwchto-for-woocommerce'); ?></label>
            </th>
            <td>
                <input type="text"
                       id="wct_order_notes_label"
                       name="marwchto_order_notes_settings[custom_label]"
                       value="<?php echo esc_attr($marwchto_order_notes_settings['custom_label'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('Leave empty to use the default WooCommerce label: "Order notes (optional)"', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="wct_order_notes_placeholder"><?php esc_html_e('Custom Placeholder', 'marwen-marwchto-for-woocommerce'); ?></label>
            </th>
            <td>
                <textarea id="wct_order_notes_placeholder"
                          name="marwchto_order_notes_settings[custom_placeholder]"
                          rows="3"
                          class="large-text"><?php echo esc_textarea($marwchto_order_notes_settings['custom_placeholder'] ?? ''); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Leave empty to use the default WooCommerce placeholder: "Notes about your order, e.g. special notes for delivery."', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="wct-settings-section wct-preview-section">
    <h3><?php esc_html_e('Preview', 'marwen-marwchto-for-woocommerce'); ?></h3>
    <p class="description"><?php esc_html_e('This is how the order notes field will appear on checkout:', 'marwen-marwchto-for-woocommerce'); ?></p>

    <div class="wct-field-preview" style="max-width: 500px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px;">
        <label for="marwchto_preview_notes" style="display: block; margin-bottom: 8px; font-weight: 600;">
            <?php
            $marwchto_preview_label = !empty($marwchto_order_notes_settings['custom_label'])
                ? $marwchto_order_notes_settings['custom_label']
                : __('Order notes (optional)', 'marwen-marwchto-for-woocommerce');
            echo esc_html($marwchto_preview_label);
            ?>
        </label>
        <textarea id="marwchto_preview_notes"
                  readonly
                  style="width: 100%; min-height: 80px; padding: 10px; border: 1px solid #8c8f94; border-radius: 4px;"
                  placeholder="<?php
                      $marwchto_preview_placeholder = !empty($marwchto_order_notes_settings['custom_placeholder'])
                          ? $marwchto_order_notes_settings['custom_placeholder']
                          : __('Notes about your order, e.g. special notes for delivery.', 'marwen-marwchto-for-woocommerce');
                      echo esc_attr($marwchto_preview_placeholder);
                  ?>"></textarea>
    </div>
</div>
