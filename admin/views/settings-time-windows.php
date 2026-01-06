<?php
/**
 * Time Window Settings Tab
 *
 * @package WooCheckoutToolkit
 *
 * @var array $marwchto_time_window_settings Time window settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="marwchto-settings-section">
    <h2><?php esc_html_e('Time Window Selection', 'marwen-marwchto-for-woocommerce'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to select a preferred delivery time window (Morning, Afternoon, Evening) or custom time slots.', 'marwen-marwchto-for-woocommerce'); ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <!-- Enable/Disable -->
            <tr>
                <th scope="row">
                    <label for="time_window_enabled"><?php esc_html_e('Enable Time Window', 'marwen-marwchto-for-woocommerce'); ?></label>
                </th>
                <td>
                    <label class="marwchto-toggle">
                        <input type="checkbox"
                               id="time_window_enabled"
                               name="marwchto_time_window_settings[enabled]"
                               value="1"
                               <?php checked(!empty($marwchto_time_window_settings['enabled'])); ?>>
                        <span class="marwchto-toggle-slider"></span>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Show a time window selection dropdown on checkout.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Required -->
            <tr>
                <th scope="row">
                    <label for="time_window_required"><?php esc_html_e('Required Field', 'marwen-marwchto-for-woocommerce'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               id="time_window_required"
                               name="marwchto_time_window_settings[required]"
                               value="1"
                               <?php checked(!empty($marwchto_time_window_settings['required'])); ?>
                               <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('Make time window selection required', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="time_window_field_label"><?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="time_window_field_label"
                           name="marwchto_time_window_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_time_window_settings['field_label']); ?>"
                           class="regular-text"
                           <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                </td>
            </tr>

            <!-- Time Slots -->
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Time Slots', 'marwen-marwchto-for-woocommerce'); ?></label>
                </th>
                <td>
                    <div id="time-slots-container">
                        <?php
                        $marwchto_time_slots = $marwchto_time_window_settings['time_slots'] ?? [];
                        if (empty($marwchto_time_slots)) {
                            $marwchto_time_slots = [['value' => '', 'label' => '']];
                        }
                        foreach ($marwchto_time_slots as $marwchto_index => $marwchto_slot) :
                            ?>
                            <div class="time-slot-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                <input type="text"
                                       name="marwchto_time_window_settings[time_slots][<?php echo esc_attr($marwchto_index); ?>][value]"
                                       value="<?php echo esc_attr($marwchto_slot['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value (e.g., morning)', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text"
                                       style="width: 200px;"
                                       <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                                <input type="text"
                                       name="marwchto_time_window_settings[time_slots][<?php echo esc_attr($marwchto_index); ?>][label]"
                                       value="<?php echo esc_attr($marwchto_slot['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label (e.g., Morning 9am-12pm)', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text"
                                       style="width: 300px;"
                                       <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                                <button type="button" class="button remove-time-slot" <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                                    <?php esc_html_e('Remove', 'marwen-marwchto-for-woocommerce'); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-time-slot" class="button" <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('+ Add Time Slot', 'marwen-marwchto-for-woocommerce'); ?>
                    </button>
                    <p class="description">
                        <?php esc_html_e('Define the time slots customers can choose from. Value is stored internally, Label is shown to customers.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Show Only With Delivery -->
            <tr>
                <th scope="row">
                    <label for="time_window_show_only_with_delivery"><?php esc_html_e('Delivery Only', 'marwen-marwchto-for-woocommerce'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               id="time_window_show_only_with_delivery"
                               name="marwchto_time_window_settings[show_only_with_delivery]"
                               value="1"
                               <?php checked(!empty($marwchto_time_window_settings['show_only_with_delivery'])); ?>
                               <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('Only show when Delivery is selected (hide for Pickup)', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('When enabled, the time window field will be hidden if the customer selects Pickup.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Display Options -->
            <tr>
                <th scope="row"><?php esc_html_e('Display Options', 'marwen-marwchto-for-woocommerce'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox"
                                   name="marwchto_time_window_settings[show_in_admin]"
                                   value="1"
                                   <?php checked(!empty($marwchto_time_window_settings['show_in_admin'])); ?>
                                   <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                            <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox"
                                   name="marwchto_time_window_settings[show_in_emails]"
                                   value="1"
                                   <?php checked(!empty($marwchto_time_window_settings['show_in_emails'])); ?>
                                   <?php disabled(empty($marwchto_time_window_settings['enabled'])); ?>>
                            <?php esc_html_e('Show in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="marwchto-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'marwen-marwchto-for-woocommerce'); ?></h3>
        <div class="marwchto-preview-field">
            <label id="preview-time-window-label" style="display: block; margin-bottom: 5px; font-weight: 600;">
                <?php echo esc_html($marwchto_time_window_settings['field_label']); ?>
                <?php if (!empty($marwchto_time_window_settings['required'])) : ?>
                    <span style="color: #cc0000;">*</span>
                <?php endif; ?>
            </label>
            <select id="preview-time-window-select" style="width: 100%; max-width: 400px; padding: 10px;">
                <option value=""><?php esc_html_e('Select a time...', 'marwen-marwchto-for-woocommerce'); ?></option>
                <?php foreach ($marwchto_time_window_settings['time_slots'] as $marwchto_slot) : ?>
                    <?php if (!empty($marwchto_slot['label'])) : ?>
                        <option value="<?php echo esc_attr($marwchto_slot['value']); ?>">
                            <?php echo esc_html($marwchto_slot['label']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
