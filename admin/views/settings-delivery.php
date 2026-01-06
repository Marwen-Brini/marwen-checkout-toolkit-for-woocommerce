<?php
/**
 * Delivery date settings template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_positions = $checkout_toolkit_settings_obj->get_field_positions();
$checkout_toolkit_date_formats = $checkout_toolkit_settings_obj->get_date_formats();
$checkout_toolkit_weekdays = [
    0 => __('Sunday', 'marwen-checkout-toolkit-for-woocommerce'),
    1 => __('Monday', 'marwen-checkout-toolkit-for-woocommerce'),
    2 => __('Tuesday', 'marwen-checkout-toolkit-for-woocommerce'),
    3 => __('Wednesday', 'marwen-checkout-toolkit-for-woocommerce'),
    4 => __('Thursday', 'marwen-checkout-toolkit-for-woocommerce'),
    5 => __('Friday', 'marwen-checkout-toolkit-for-woocommerce'),
    6 => __('Saturday', 'marwen-checkout-toolkit-for-woocommerce'),
];
?>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Delivery Date -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Enable Delivery Date', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[enabled]"
                           value="1"
                           <?php checked(!empty($checkout_toolkit_delivery_settings['enabled'])); ?>>
                    <?php esc_html_e('Show delivery date picker on checkout', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Label -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_delivery_label">
                    <?php esc_html_e('Field Label', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_delivery_label"
                       name="checkout_toolkit_delivery_settings[field_label]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['field_label'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>

        <!-- Required -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Required', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[required]"
                           value="1"
                           <?php checked(!empty($checkout_toolkit_delivery_settings['required'])); ?>>
                    <?php esc_html_e('Make field required', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Minimum Lead Time -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_min_lead_days">
                    <?php esc_html_e('Minimum Lead Time', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="checkout_toolkit_min_lead_days"
                       name="checkout_toolkit_delivery_settings[min_lead_days]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['min_lead_days'] ?? 2); ?>"
                       min="0"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                <p class="description">
                    <?php esc_html_e('Customers must order at least this many days ahead.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Maximum Advance Booking -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_max_future_days">
                    <?php esc_html_e('Maximum Advance Booking', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="number"
                       id="checkout_toolkit_max_future_days"
                       name="checkout_toolkit_delivery_settings[max_future_days]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['max_future_days'] ?? 30); ?>"
                       min="1"
                       max="365"
                       class="small-text">
                <?php esc_html_e('days', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                <p class="description">
                    <?php esc_html_e('How far in advance customers can book.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Disabled Days -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Disabled Days', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <fieldset>
                    <?php foreach ($checkout_toolkit_weekdays as $checkout_toolkit_day_num => $checkout_toolkit_day_name) : ?>
                        <label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
                            <input type="checkbox"
                                   name="checkout_toolkit_delivery_settings[disabled_weekdays][]"
                                   value="<?php echo esc_attr($checkout_toolkit_day_num); ?>"
                                   <?php checked(in_array($checkout_toolkit_day_num, $checkout_toolkit_delivery_settings['disabled_weekdays'] ?? [], false)); ?>>
                            <?php echo esc_html($checkout_toolkit_day_name); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Select days when delivery is not available.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Blocked Dates -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Blocked Dates', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <?php include MARWCHTO_PLUGIN_DIR . 'admin/views/blocked-dates-manager.php'; ?>
            </td>
        </tr>

        <!-- Date Format -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_date_format">
                    <?php esc_html_e('Date Format', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <select id="checkout_toolkit_date_format" name="checkout_toolkit_delivery_settings[date_format]">
                    <?php foreach ($checkout_toolkit_date_formats as $checkout_toolkit_format => $checkout_toolkit_example) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_format); ?>"
                                <?php selected($checkout_toolkit_delivery_settings['date_format'] ?? 'F j, Y', $checkout_toolkit_format); ?>>
                            <?php echo esc_html($checkout_toolkit_example); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- First Day of Week -->
        <tr>
            <th scope="row">
                <?php esc_html_e('First Day of Week', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <label style="margin-right: 15px;">
                    <input type="radio"
                           name="checkout_toolkit_delivery_settings[first_day_of_week]"
                           value="0"
                           <?php checked(($checkout_toolkit_delivery_settings['first_day_of_week'] ?? 1) == 0); ?>>
                    <?php esc_html_e('Sunday', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
                <label>
                    <input type="radio"
                           name="checkout_toolkit_delivery_settings[first_day_of_week]"
                           value="1"
                           <?php checked(($checkout_toolkit_delivery_settings['first_day_of_week'] ?? 1) == 1); ?>>
                    <?php esc_html_e('Monday', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Field Position -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_delivery_position">
                    <?php esc_html_e('Field Position', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <select id="checkout_toolkit_delivery_position" name="checkout_toolkit_delivery_settings[field_position]">
                    <?php foreach ($checkout_toolkit_positions as $checkout_toolkit_hook => $checkout_toolkit_label) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_hook); ?>"
                                <?php selected($checkout_toolkit_delivery_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
                            <?php echo esc_html($checkout_toolkit_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <!-- Display Options -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Display Options', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[show_in_admin]"
                           value="1"
                           <?php checked(!empty($checkout_toolkit_delivery_settings['show_in_admin'])); ?>>
                    <?php esc_html_e('Show in admin order details', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[show_in_emails]"
                           value="1"
                           <?php checked(!empty($checkout_toolkit_delivery_settings['show_in_emails'])); ?>>
                    <?php esc_html_e('Include in order emails', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </td>
        </tr>
    </tbody>
</table>

<h3 style="margin-top: 30px;"><?php esc_html_e('Estimated Delivery Message', 'marwen-checkout-toolkit-for-woocommerce'); ?></h3>
<p class="description" style="margin-bottom: 15px;">
    <?php esc_html_e('Display a message showing the earliest available delivery date based on your lead time settings.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
</p>

<table class="form-table wct-settings-table">
    <tbody>
        <!-- Enable Estimated Delivery -->
        <tr>
            <th scope="row">
                <?php esc_html_e('Show Estimated Delivery', 'marwen-checkout-toolkit-for-woocommerce'); ?>
            </th>
            <td>
                <label>
                    <input type="checkbox"
                           name="checkout_toolkit_delivery_settings[show_estimated_delivery]"
                           value="1"
                           <?php checked(!empty($checkout_toolkit_delivery_settings['show_estimated_delivery'])); ?>>
                    <?php esc_html_e('Display estimated delivery date message on checkout', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </td>
        </tr>

        <!-- Cutoff Time -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_cutoff_time">
                    <?php esc_html_e('Order Cutoff Time', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="time"
                       id="checkout_toolkit_cutoff_time"
                       name="checkout_toolkit_delivery_settings[cutoff_time]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['cutoff_time'] ?? '14:00'); ?>"
                       class="regular-text"
                       style="width: 120px;">
                <p class="description">
                    <?php esc_html_e('Orders placed after this time will have +1 day added to their estimated delivery.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- Before Cutoff Message -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_cutoff_message">
                    <?php esc_html_e('Before Cutoff Message', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_cutoff_message"
                       name="checkout_toolkit_delivery_settings[cutoff_message]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['cutoff_message'] ?? 'Order by {time} for delivery as early as {date}'); ?>"
                       class="large-text">
                <p class="description">
                    <?php esc_html_e('Message shown before cutoff time. Use {time} for cutoff time and {date} for earliest delivery date.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>

        <!-- After Cutoff Message -->
        <tr>
            <th scope="row">
                <label for="checkout_toolkit_estimated_message">
                    <?php esc_html_e('After Cutoff Message', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       id="checkout_toolkit_estimated_message"
                       name="checkout_toolkit_delivery_settings[estimated_delivery_message]"
                       value="<?php echo esc_attr($checkout_toolkit_delivery_settings['estimated_delivery_message'] ?? 'Order now for delivery as early as {date}'); ?>"
                       class="large-text">
                <p class="description">
                    <?php esc_html_e('Message shown after cutoff time. Use {date} for earliest delivery date.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
