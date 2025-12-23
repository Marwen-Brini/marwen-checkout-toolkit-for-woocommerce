<?php
/**
 * Custom field settings template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables.
$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_positions = $checkout_toolkit_settings_obj->get_field_positions();
// phpcs:enable
?>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Custom Field -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Custom Field', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_field_settings[enabled]"
                           value="1"
                           <?php checked(!empty($field_settings['enabled'])); ?>>
                    <?php esc_html_e('Show custom field on checkout', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Type -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Field Type', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="radio"
                           name="checkout_toolkit_field_settings[field_type]"
                           value="text"
                           <?php checked(($field_settings['field_type'] ?? 'textarea') === 'text'); ?>>
                    <?php esc_html_e('Single line text', 'checkout-toolkit-for-woo'); ?>
                </label>
                <label style="display: block;">
                    <input type="radio"
                           name="checkout_toolkit_field_settings[field_type]"
                           value="textarea"
                           <?php checked(($field_settings['field_type'] ?? 'textarea') === 'textarea'); ?>>
                    <?php esc_html_e('Multi-line textarea', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_field_label">
                    <?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_field_label"
                       name="checkout_toolkit_field_settings[field_label]"
                       value="<?php echo esc_attr($field_settings['field_label'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Placeholder Text -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_field_placeholder">
                    <?php esc_html_e('Placeholder Text', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_field_placeholder"
                       name="checkout_toolkit_field_settings[field_placeholder]"
                       value="<?php echo esc_attr($field_settings['field_placeholder'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Required -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Required', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_field_settings[required]"
                           value="1"
                           <?php checked(!empty($field_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Maximum Characters -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_max_length">
                    <?php esc_html_e('Maximum Characters', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="checkout_toolkit_max_length"
                       name="checkout_toolkit_field_settings[max_length]"
                       value="<?php echo esc_attr($field_settings['max_length'] ?? 500); ?>"
                       min="0"
                       max="10000"
                       class="small-text">
                <p class="description">
                    <?php esc_html_e('Set to 0 for unlimited.', 'checkout-toolkit-for-woo'); ?>
                </p>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_field_position">
                    <?php esc_html_e('Field Position', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <select id="checkout_toolkit_field_position" name="checkout_toolkit_field_settings[field_position]">
                    <?php foreach ($checkout_toolkit_positions as $checkout_toolkit_hook => $checkout_toolkit_label) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_hook); ?>"
                                <?php selected($field_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
                            <?php echo esc_html($checkout_toolkit_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                           name="checkout_toolkit_field_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($field_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="checkout_toolkit_field_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($field_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>
