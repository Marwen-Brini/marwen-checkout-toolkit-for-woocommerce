<?php
/**
 * Time Window Settings Tab
 *
 * @package WooCheckoutToolkit
 *
 * @var array $checkout_toolkit_time_window_settings Time window settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Time Window Selection', 'checkout-toolkit-for-woo'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to select a preferred delivery time window (Morning, Afternoon, Evening) or custom time slots.', 'checkout-toolkit-for-woo'); ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <!-- Enable/Disable -->
            <tr>
                <th scope="row">
                    <label for="time_window_enabled"><?php esc_html_e('Enable Time Window', 'checkout-toolkit-for-woo'); ?></label>
                </th>
                <td>
                    <label class="wct-toggle">
                        <input type="checkbox"
                               id="time_window_enabled"
                               name="checkout_toolkit_time_window_settings[enabled]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                        <span class="wct-toggle-slider"></span>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Show a time window selection dropdown on checkout.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Required -->
            <tr>
                <th scope="row">
                    <label for="time_window_required"><?php esc_html_e('Required Field', 'checkout-toolkit-for-woo'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               id="time_window_required"
                               name="checkout_toolkit_time_window_settings[required]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_time_window_settings['required'])); ?>
                               <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('Make time window selection required', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="time_window_field_label"><?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="time_window_field_label"
                           name="checkout_toolkit_time_window_settings[field_label]"
                           value="<?php echo esc_attr($checkout_toolkit_time_window_settings['field_label']); ?>"
                           class="regular-text"
                           <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                </td>
            </tr>

            <!-- Time Slots -->
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Time Slots', 'checkout-toolkit-for-woo'); ?></label>
                </th>
                <td>
                    <div id="time-slots-container">
                        <?php
                        $checkout_toolkit_time_slots = $checkout_toolkit_time_window_settings['time_slots'] ?? [];
                        if (empty($checkout_toolkit_time_slots)) {
                            $checkout_toolkit_time_slots = [['value' => '', 'label' => '']];
                        }
                        foreach ($checkout_toolkit_time_slots as $checkout_toolkit_index => $checkout_toolkit_slot) :
                            ?>
                            <div class="time-slot-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                <input type="text"
                                       name="checkout_toolkit_time_window_settings[time_slots][<?php echo esc_attr($checkout_toolkit_index); ?>][value]"
                                       value="<?php echo esc_attr($checkout_toolkit_slot['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value (e.g., morning)', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text"
                                       style="width: 200px;"
                                       <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                                <input type="text"
                                       name="checkout_toolkit_time_window_settings[time_slots][<?php echo esc_attr($checkout_toolkit_index); ?>][label]"
                                       value="<?php echo esc_attr($checkout_toolkit_slot['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label (e.g., Morning 9am-12pm)', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text"
                                       style="width: 300px;"
                                       <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                                <button type="button" class="button remove-time-slot" <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                                    <?php esc_html_e('Remove', 'checkout-toolkit-for-woo'); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-time-slot" class="button" <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('+ Add Time Slot', 'checkout-toolkit-for-woo'); ?>
                    </button>
                    <p class="description">
                        <?php esc_html_e('Define the time slots customers can choose from. Value is stored internally, Label is shown to customers.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Show Only With Delivery -->
            <tr>
                <th scope="row">
                    <label for="time_window_show_only_with_delivery"><?php esc_html_e('Delivery Only', 'checkout-toolkit-for-woo'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               id="time_window_show_only_with_delivery"
                               name="checkout_toolkit_time_window_settings[show_only_with_delivery]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_time_window_settings['show_only_with_delivery'])); ?>
                               <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                        <?php esc_html_e('Only show when Delivery is selected (hide for Pickup)', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('When enabled, the time window field will be hidden if the customer selects Pickup.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Display Options -->
            <tr>
                <th scope="row"><?php esc_html_e('Display Options', 'checkout-toolkit-for-woo'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox"
                                   name="checkout_toolkit_time_window_settings[show_in_admin]"
                                   value="1"
                                   <?php checked(!empty($checkout_toolkit_time_window_settings['show_in_admin'])); ?>
                                   <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                            <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox"
                                   name="checkout_toolkit_time_window_settings[show_in_emails]"
                                   value="1"
                                   <?php checked(!empty($checkout_toolkit_time_window_settings['show_in_emails'])); ?>
                                   <?php disabled(empty($checkout_toolkit_time_window_settings['enabled'])); ?>>
                            <?php esc_html_e('Show in order emails', 'checkout-toolkit-for-woo'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="wct-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'checkout-toolkit-for-woo'); ?></h3>
        <div class="wct-preview-field">
            <label id="preview-time-window-label" style="display: block; margin-bottom: 5px; font-weight: 600;">
                <?php echo esc_html($checkout_toolkit_time_window_settings['field_label']); ?>
                <?php if (!empty($checkout_toolkit_time_window_settings['required'])) : ?>
                    <span style="color: #cc0000;">*</span>
                <?php endif; ?>
            </label>
            <select id="preview-time-window-select" style="width: 100%; max-width: 400px; padding: 10px;">
                <option value=""><?php esc_html_e('Select a time...', 'checkout-toolkit-for-woo'); ?></option>
                <?php foreach ($checkout_toolkit_time_window_settings['time_slots'] as $checkout_toolkit_slot) : ?>
                    <?php if (!empty($checkout_toolkit_slot['label'])) : ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_slot['value']); ?>">
                            <?php echo esc_html($checkout_toolkit_slot['label']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<script>
jQuery(function($) {
    var slotIndex = <?php echo count($checkout_toolkit_time_slots); ?>;
    var isEnabled = <?php echo !empty($checkout_toolkit_time_window_settings['enabled']) ? 'true' : 'false'; ?>;

    // Toggle form fields based on enabled state
    $('#time_window_enabled').on('change', function() {
        var enabled = $(this).is(':checked');
        var $fields = $(this).closest('.wct-settings-section').find('input:not(#time_window_enabled), select, button:not([type="submit"])');

        if (enabled) {
            $fields.prop('disabled', false);
        } else {
            $fields.prop('disabled', true);
        }
    });

    // Add new time slot
    $('#add-time-slot').on('click', function() {
        var html = '<div class="time-slot-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">' +
            '<input type="text" name="checkout_toolkit_time_window_settings[time_slots][' + slotIndex + '][value]" ' +
            'placeholder="<?php esc_attr_e('Value (e.g., morning)', 'checkout-toolkit-for-woo'); ?>" class="regular-text" style="width: 200px;">' +
            '<input type="text" name="checkout_toolkit_time_window_settings[time_slots][' + slotIndex + '][label]" ' +
            'placeholder="<?php esc_attr_e('Label (e.g., Morning 9am-12pm)', 'checkout-toolkit-for-woo'); ?>" class="regular-text" style="width: 300px;">' +
            '<button type="button" class="button remove-time-slot"><?php esc_html_e('Remove', 'checkout-toolkit-for-woo'); ?></button>' +
            '</div>';
        $('#time-slots-container').append(html);
        slotIndex++;
        updatePreview();
    });

    // Remove time slot
    $(document).on('click', '.remove-time-slot', function() {
        if ($('.time-slot-row').length > 1) {
            $(this).closest('.time-slot-row').remove();
            updatePreview();
        }
    });

    // Update preview on input changes
    $('#time_window_field_label').on('input', function() {
        $('#preview-time-window-label').contents().first().replaceWith($(this).val() + ' ');
    });

    $('#time_window_required').on('change', function() {
        var $label = $('#preview-time-window-label');
        var $asterisk = $label.find('span');
        if ($(this).is(':checked')) {
            if ($asterisk.length === 0) {
                $label.append('<span style="color: #cc0000;">*</span>');
            }
        } else {
            $asterisk.remove();
        }
    });

    // Update preview select options
    function updatePreview() {
        var $select = $('#preview-time-window-select');
        $select.find('option:not(:first)').remove();

        $('.time-slot-row').each(function() {
            var value = $(this).find('input:first').val();
            var label = $(this).find('input:eq(1)').val();
            if (label) {
                $select.append('<option value="' + value + '">' + label + '</option>');
            }
        });
    }

    $(document).on('input', '.time-slot-row input', updatePreview);
});
</script>
