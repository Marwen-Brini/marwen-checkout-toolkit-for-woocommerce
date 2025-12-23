<?php
/**
 * Custom field settings template
 *
 * @package WooCheckoutToolkit
 */

defined('ABSPATH') || exit;

$settings = new \WooCheckoutToolkit\Admin\Settings();
$positions = $settings->get_field_positions();
?>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Custom Field -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Custom Field', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="wct_field_settings[enabled]"
                           value="1"
                           <?php checked(!empty($field_settings['enabled'])); ?>>
                    <?php esc_html_e('Show custom field on checkout', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Type -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Field Type', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="radio"
                           name="wct_field_settings[field_type]"
                           value="text"
                           <?php checked(($field_settings['field_type'] ?? 'textarea') === 'text'); ?>>
                    <?php esc_html_e('Single line text', 'woo-checkout-toolkit'); ?>
                </label>
                <label style="display: block;">
                    <input type="radio"
                           name="wct_field_settings[field_type]"
                           value="textarea"
                           <?php checked(($field_settings['field_type'] ?? 'textarea') === 'textarea'); ?>>
                    <?php esc_html_e('Multi-line textarea', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="wct_field_label">
                    <?php esc_html_e('Field Label', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="wct_field_label"
                       name="wct_field_settings[field_label]"
                       value="<?php echo esc_attr($field_settings['field_label'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Placeholder Text -->
        <tr>
            <th scope="row">
                <label for="wct_field_placeholder">
                    <?php esc_html_e('Placeholder Text', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="wct_field_placeholder"
                       name="wct_field_settings[field_placeholder]"
                       value="<?php echo esc_attr($field_settings['field_placeholder'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Required -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Required', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="wct_field_settings[required]"
                           value="1"
                           <?php checked(!empty($field_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Maximum Characters -->
        <tr>
            <th scope="row">
                <label for="wct_max_length">
                    <?php esc_html_e('Maximum Characters', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="wct_max_length"
                       name="wct_field_settings[max_length]"
                       value="<?php echo esc_attr($field_settings['max_length'] ?? 500); ?>"
                       min="0"
                       max="10000"
                       class="small-text">
                <p class="description">
                    <?php esc_html_e('Set to 0 for unlimited.', 'woo-checkout-toolkit'); ?>
                </p>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="wct_field_position">
                    <?php esc_html_e('Field Position', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <select id="wct_field_position" name="wct_field_settings[field_position]">
                    <?php foreach ($positions as $hook => $label) : ?>
                        <option value="<?php echo esc_attr($hook); ?>"
                                <?php selected($field_settings['field_position'] ?? 'woocommerce_after_order_notes', $hook); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- Display Options -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Display Options', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox"
                           name="wct_field_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($field_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'woo-checkout-toolkit'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="wct_field_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($field_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>
