<?php
/**
 * Delivery date settings template
 *
 * @package WooCheckoutToolkit
 */

defined('ABSPATH') || exit;

$settings = new \WooCheckoutToolkit\Admin\Settings();
$positions = $settings->get_field_positions();
$date_formats = $settings->get_date_formats();
$weekdays = [
    0 => __('Sunday', 'woo-checkout-toolkit'),
    1 => __('Monday', 'woo-checkout-toolkit'),
    2 => __('Tuesday', 'woo-checkout-toolkit'),
    3 => __('Wednesday', 'woo-checkout-toolkit'),
    4 => __('Thursday', 'woo-checkout-toolkit'),
    5 => __('Friday', 'woo-checkout-toolkit'),
    6 => __('Saturday', 'woo-checkout-toolkit'),
];
?>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Delivery Date -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Delivery Date', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="wct_delivery_settings[enabled]"
                           value="1"
                           <?php checked(!empty($delivery_settings['enabled'])); ?>>
                    <?php esc_html_e('Show delivery date picker on checkout', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="wct_delivery_label">
                    <?php esc_html_e('Field Label', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="wct_delivery_label"
                       name="wct_delivery_settings[field_label]"
                       value="<?php echo esc_attr($delivery_settings['field_label'] ?? ''); ?>"
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
                           name="wct_delivery_settings[required]"
                           value="1"
                           <?php checked(!empty($delivery_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Minimum Lead Time -->
        <tr>
            <th scope="row">
                <label for="wct_min_lead_days">
                    <?php esc_html_e('Minimum Lead Time', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="wct_min_lead_days"
                       name="wct_delivery_settings[min_lead_days]"
                       value="<?php echo esc_attr($delivery_settings['min_lead_days'] ?? 2); ?>"
                       min="0"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'woo-checkout-toolkit'); ?>
                <p class="description">
                    <?php esc_html_e('Customers must order at least this many days ahead.', 'woo-checkout-toolkit'); ?>
                </p>
            </td>
        </tr>

        <!-- Maximum Advance Booking -->
        <tr>
            <th scope="row">
                <label for="wct_max_future_days">
                    <?php esc_html_e('Maximum Advance Booking', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="wct_max_future_days"
                       name="wct_delivery_settings[max_future_days]"
                       value="<?php echo esc_attr($delivery_settings['max_future_days'] ?? 30); ?>"
                       min="1"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'woo-checkout-toolkit'); ?>
                <p class="description">
                    <?php esc_html_e('How far in advance customers can book.', 'woo-checkout-toolkit'); ?>
                </p>
            </td>
        </tr>

        <!-- Disabled Days -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Disabled Days', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <fieldset>
                    <?php foreach ($weekdays as $day_num => $day_name) : ?>
                        <label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
                            <input type="checkbox"
                                   name="wct_delivery_settings[disabled_weekdays][]"
                                   value="<?php echo esc_attr($day_num); ?>"
                                   <?php checked(in_array($day_num, $delivery_settings['disabled_weekdays'] ?? [], false)); ?>>
                            <?php echo esc_html($day_name); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Select days when delivery is not available.', 'woo-checkout-toolkit'); ?>
                </p>
            </td>
        </tr>

        <!-- Blocked Dates -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Blocked Dates', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <?php include WCT_PLUGIN_DIR . 'admin/views/blocked-dates-manager.php'; ?>
            </td>
        </tr>

        <!-- Date Format -->
        <tr>
            <th scope="row">
                <label for="wct_date_format">
                    <?php esc_html_e('Date Format', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <select id="wct_date_format" name="wct_delivery_settings[date_format]">
                    <?php foreach ($date_formats as $format => $example) : ?>
                        <option value="<?php echo esc_attr($format); ?>"
                                <?php selected($delivery_settings['date_format'] ?? 'F j, Y', $format); ?>>
                            <?php echo esc_html($example); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- First Day of Week -->
        <tr>
            <th scope="row">
                <?php esc_html_e('First Day of Week', 'woo-checkout-toolkit'); ?>
            </th>
            <td>
                <label style="margin-right: 15px;">
                    <input type="radio"
                           name="wct_delivery_settings[first_day_of_week]"
                           value="0"
                           <?php checked(($delivery_settings['first_day_of_week'] ?? 1) == 0); ?>>
                    <?php esc_html_e('Sunday', 'woo-checkout-toolkit'); ?>
                </label>
                <label>
                    <input type="radio"
                           name="wct_delivery_settings[first_day_of_week]"
                           value="1"
                           <?php checked(($delivery_settings['first_day_of_week'] ?? 1) == 1); ?>>
                    <?php esc_html_e('Monday', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="wct_delivery_position">
                    <?php esc_html_e('Field Position', 'woo-checkout-toolkit'); ?>
                </label>
            </th>
            <td>
                <select id="wct_delivery_position" name="wct_delivery_settings[field_position]">
                    <?php foreach ($positions as $hook => $label) : ?>
                        <option value="<?php echo esc_attr($hook); ?>"
                                <?php selected($delivery_settings['field_position'] ?? 'woocommerce_after_order_notes', $hook); ?>>
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
                           name="wct_delivery_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($delivery_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'woo-checkout-toolkit'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="wct_delivery_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($delivery_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'woo-checkout-toolkit'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>
