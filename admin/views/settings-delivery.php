<?php
/**
 * Delivery date settings template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables.
$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_positions = $checkout_toolkit_settings_obj->get_field_positions();
$checkout_toolkit_date_formats = $checkout_toolkit_settings_obj->get_date_formats();
$checkout_toolkit_weekdays = [
    0 => __('Sunday', 'checkout-toolkit-for-woo'),
    1 => __('Monday', 'checkout-toolkit-for-woo'),
    2 => __('Tuesday', 'checkout-toolkit-for-woo'),
    3 => __('Wednesday', 'checkout-toolkit-for-woo'),
    4 => __('Thursday', 'checkout-toolkit-for-woo'),
    5 => __('Friday', 'checkout-toolkit-for-woo'),
    6 => __('Saturday', 'checkout-toolkit-for-woo'),
];
// phpcs:enable
?>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Delivery Date -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Delivery Date', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[enabled]"
                           value="1"
                           <?php checked(!empty($delivery_settings['enabled'])); ?>>
                    <?php esc_html_e('Show delivery date picker on checkout', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_delivery_label">
                    <?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_delivery_label"
                       name="checkout_toolkit_delivery_settings[field_label]"
                       value="<?php echo esc_attr($delivery_settings['field_label'] ?? ''); ?>"
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
                           name="checkout_toolkit_delivery_settings[required]"
                           value="1"
                           <?php checked(!empty($delivery_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Minimum Lead Time -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_min_lead_days">
                    <?php esc_html_e('Minimum Lead Time', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="checkout_toolkit_min_lead_days"
                       name="checkout_toolkit_delivery_settings[min_lead_days]"
                       value="<?php echo esc_attr($delivery_settings['min_lead_days'] ?? 2); ?>"
                       min="0"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'checkout-toolkit-for-woo'); ?>
                <p class="description">
                    <?php esc_html_e('Customers must order at least this many days ahead.', 'checkout-toolkit-for-woo'); ?>
                </p>
            </td>
        </tr>

        <!-- Maximum Advance Booking -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_max_future_days">
                    <?php esc_html_e('Maximum Advance Booking', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="checkout_toolkit_max_future_days"
                       name="checkout_toolkit_delivery_settings[max_future_days]"
                       value="<?php echo esc_attr($delivery_settings['max_future_days'] ?? 30); ?>"
                       min="1"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'checkout-toolkit-for-woo'); ?>
                <p class="description">
                    <?php esc_html_e('How far in advance customers can book.', 'checkout-toolkit-for-woo'); ?>
                </p>
            </td>
        </tr>

        <!-- Disabled Days -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Disabled Days', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <fieldset>
                    <?php foreach ($checkout_toolkit_weekdays as $checkout_toolkit_day_num => $checkout_toolkit_day_name) : ?>
                        <label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
                            <input type="checkbox"
                                   name="checkout_toolkit_delivery_settings[disabled_weekdays][]"
                                   value="<?php echo esc_attr($checkout_toolkit_day_num); ?>"
                                   <?php checked(in_array($checkout_toolkit_day_num, $delivery_settings['disabled_weekdays'] ?? [], false)); ?>>
                            <?php echo esc_html($checkout_toolkit_day_name); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Select days when delivery is not available.', 'checkout-toolkit-for-woo'); ?>
                </p>
            </td>
        </tr>

        <!-- Blocked Dates -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Blocked Dates', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <?php include CHECKOUT_TOOLKIT_PLUGIN_DIR . 'admin/views/blocked-dates-manager.php'; ?>
            </td>
        </tr>

        <!-- Date Format -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_date_format">
                    <?php esc_html_e('Date Format', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <select id="checkout_toolkit_date_format" name="checkout_toolkit_delivery_settings[date_format]">
                    <?php foreach ($checkout_toolkit_date_formats as $checkout_toolkit_format => $checkout_toolkit_example) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_format); ?>"
                                <?php selected($delivery_settings['date_format'] ?? 'F j, Y', $checkout_toolkit_format); ?>>
                            <?php echo esc_html($checkout_toolkit_example); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- First Day of Week -->
        <tr>
            <th scope="row">
                <?php esc_html_e('First Day of Week', 'checkout-toolkit-for-woo'); ?>
            </th>
            <td>
                <label style="margin-right: 15px;">
                    <input type="radio"
                           name="checkout_toolkit_delivery_settings[first_day_of_week]"
                           value="0"
                           <?php checked(($delivery_settings['first_day_of_week'] ?? 1) == 0); ?>>
                    <?php esc_html_e('Sunday', 'checkout-toolkit-for-woo'); ?>
                </label>
                <label>
                    <input type="radio"
                           name="checkout_toolkit_delivery_settings[first_day_of_week]"
                           value="1"
                           <?php checked(($delivery_settings['first_day_of_week'] ?? 1) == 1); ?>>
                    <?php esc_html_e('Monday', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_delivery_position">
                    <?php esc_html_e('Field Position', 'checkout-toolkit-for-woo'); ?>
                </label>
            </th>
            <td>
                <select id="checkout_toolkit_delivery_position" name="checkout_toolkit_delivery_settings[field_position]">
                    <?php foreach ($checkout_toolkit_positions as $checkout_toolkit_hook => $checkout_toolkit_label) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_hook); ?>"
                                <?php selected($delivery_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
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
                           name="checkout_toolkit_delivery_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($delivery_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($delivery_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>
