<?php
/**
 * Delivery date settings template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$marwchto_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$marwchto_positions = $marwchto_settings_obj->get_field_positions();
$marwchto_date_formats = $marwchto_settings_obj->get_date_formats();
$marwchto_weekdays = [
    0 => __('Sunday', 'marwen-marwchto-for-woocommerce'),
    1 => __('Monday', 'marwen-marwchto-for-woocommerce'),
    2 => __('Tuesday', 'marwen-marwchto-for-woocommerce'),
    3 => __('Wednesday', 'marwen-marwchto-for-woocommerce'),
    4 => __('Thursday', 'marwen-marwchto-for-woocommerce'),
    5 => __('Friday', 'marwen-marwchto-for-woocommerce'),
    6 => __('Saturday', 'marwen-marwchto-for-woocommerce'),
];
?>

<table class="form-table marwchto-settings-table">
    <tbody>
        <!-- Enable Delivery Date -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Delivery Date', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="marwchto_delivery_settings[enabled]"
                           value="1"
                           <?php checked(!empty($marwchto_delivery_settings['enabled'])); ?>>
                    <?php esc_html_e('Show delivery date picker on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="marwchto_delivery_label">
                    <?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="marwchto_delivery_label"
                       name="marwchto_delivery_settings[field_label]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['field_label'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Required -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Required', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="marwchto_delivery_settings[required]"
                           value="1"
                           <?php checked(!empty($marwchto_delivery_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Minimum Lead Time -->
        <tr>
            <th scope="row">
                <label for="marwchto_min_lead_days">
                    <?php esc_html_e('Minimum Lead Time', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="marwchto_min_lead_days"
                       name="marwchto_delivery_settings[min_lead_days]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['min_lead_days'] ?? 2); ?>"
                       min="0"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'marwen-marwchto-for-woocommerce'); ?>
                <p class="description">
                    <?php esc_html_e('Customers must order at least this many days ahead.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Maximum Advance Booking -->
        <tr>
            <th scope="row">
                <label for="marwchto_max_future_days">
                    <?php esc_html_e('Maximum Advance Booking', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="marwchto_max_future_days"
                       name="marwchto_delivery_settings[max_future_days]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['max_future_days'] ?? 30); ?>"
                       min="1"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'marwen-marwchto-for-woocommerce'); ?>
                <p class="description">
                    <?php esc_html_e('How far in advance customers can book.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Disabled Days -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Disabled Days', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <fieldset>
                    <?php foreach ($marwchto_weekdays as $marwchto_day_num => $marwchto_day_name) : ?>
                        <label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
                            <input type="checkbox"
                                   name="marwchto_delivery_settings[disabled_weekdays][]"
                                   value="<?php echo esc_attr($marwchto_day_num); ?>"
                                   <?php checked(in_array($marwchto_day_num, $marwchto_delivery_settings['disabled_weekdays'] ?? [], false)); ?>>
                            <?php echo esc_html($marwchto_day_name); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Select days when delivery is not available.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Blocked Dates -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Blocked Dates', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/blocked-dates-manager.php'; ?>
            </td>
        </tr>

        <!-- Date Format -->
        <tr>
            <th scope="row">
                <label for="marwchto_date_format">
                    <?php esc_html_e('Date Format', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <select id="marwchto_date_format" name="marwchto_delivery_settings[date_format]">
                    <?php foreach ($marwchto_date_formats as $marwchto_format => $marwchto_example) : ?>
                        <option value="<?php echo esc_attr($marwchto_format); ?>"
                                <?php selected($marwchto_delivery_settings['date_format'] ?? 'F j, Y', $marwchto_format); ?>>
                            <?php echo esc_html($marwchto_example); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- First Day of Week -->
        <tr>
            <th scope="row">
                <?php esc_html_e('First Day of Week', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <label style="margin-right: 15px;">
                    <input type="radio"
                           name="marwchto_delivery_settings[first_day_of_week]"
                           value="0"
                           <?php checked(($marwchto_delivery_settings['first_day_of_week'] ?? 1) == 0); ?>>
                    <?php esc_html_e('Sunday', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
                <label>
                    <input type="radio"
                           name="marwchto_delivery_settings[first_day_of_week]"
                           value="1"
                           <?php checked(($marwchto_delivery_settings['first_day_of_week'] ?? 1) == 1); ?>>
                    <?php esc_html_e('Monday', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="marwchto_delivery_position">
                    <?php esc_html_e('Field Position', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <select id="marwchto_delivery_position" name="marwchto_delivery_settings[field_position]">
                    <?php foreach ($marwchto_positions as $marwchto_hook => $marwchto_label) : ?>
                        <option value="<?php echo esc_attr($marwchto_hook); ?>"
                                <?php selected($marwchto_delivery_settings['field_position'] ?? 'woocommerce_after_order_notes', $marwchto_hook); ?>>
                            <?php echo esc_html($marwchto_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                           name="marwchto_delivery_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($marwchto_delivery_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="marwchto_delivery_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($marwchto_delivery_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>

<h3 style="margin-top: 30px;"><?php esc_html_e('Estimated Delivery Message', 'marwen-marwchto-for-woocommerce'); ?></h3>
<p class="description" style="margin-bottom: 15px;">
    <?php esc_html_e('Display a message showing the earliest available delivery date based on your lead time settings.', 'marwen-marwchto-for-woocommerce'); ?>
</p>

<table class="form-table marwchto-settings-table">
    <tbody>
        <!-- Enable Estimated Delivery -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Show Estimated Delivery', 'marwen-marwchto-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="marwchto_delivery_settings[show_estimated_delivery]"
                           value="1"
                           <?php checked(!empty($marwchto_delivery_settings['show_estimated_delivery'])); ?>>
                    <?php esc_html_e('Display estimated delivery date message on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Cutoff Time -->
        <tr>
            <th scope="row">
                <label for="marwchto_cutoff_time">
                    <?php esc_html_e('Order Cutoff Time', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="time"
                       id="marwchto_cutoff_time"
                       name="marwchto_delivery_settings[cutoff_time]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['cutoff_time'] ?? '14:00'); ?>"
                       class="regular-text"
                       style="width: 120px;">
                <p class="description">
                    <?php esc_html_e('Orders placed after this time will have +1 day added to their estimated delivery.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Before Cutoff Message -->
        <tr>
            <th scope="row">
                <label for="marwchto_cutoff_message">
                    <?php esc_html_e('Before Cutoff Message', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="marwchto_cutoff_message"
                       name="marwchto_delivery_settings[cutoff_message]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['cutoff_message'] ?? 'Order by {time} for delivery as early as {date}'); ?>"
                       class="large-text">
                <p class="description">
                    <?php esc_html_e('Message shown before cutoff time. Use {time} for cutoff time and {date} for earliest delivery date.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- After Cutoff Message -->
        <tr>
            <th scope="row">
                <label for="marwchto_estimated_message">
                    <?php esc_html_e('After Cutoff Message', 'marwen-marwchto-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="marwchto_estimated_message"
                       name="marwchto_delivery_settings[estimated_delivery_message]"
                       value="<?php echo esc_attr($marwchto_delivery_settings['estimated_delivery_message'] ?? 'Order now for delivery as early as {date}'); ?>"
                       class="large-text">
                <p class="description">
                    <?php esc_html_e('Message shown after cutoff time. Use {date} for earliest delivery date.', 'marwen-marwchto-for-woocommerce'); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
