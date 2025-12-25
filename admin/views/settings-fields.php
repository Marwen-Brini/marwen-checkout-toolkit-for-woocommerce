<?php
/**
 * Custom fields settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $field_settings   Field 1 settings array.
 * @var array $field_2_settings Field 2 settings array.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables.
$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_positions = $checkout_toolkit_settings_obj->get_field_positions();
// phpcs:enable
?>

<style>
.wct-select-options-wrapper {
    border: 1px solid #c3c4c7;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    margin-top: 10px;
}
.wct-select-option-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}
.wct-select-option-row input {
    flex: 1;
}
.wct-select-option-row .button-link-delete {
    color: #b32d2e;
    text-decoration: none;
}
.wct-select-option-row .button-link-delete:hover {
    color: #a00;
}
.wct-conditional-field {
    display: none;
}
.wct-conditional-field.active {
    display: table-row;
}
/* Disabled state for field options */
.wct-field-options-disabled {
    opacity: 0.5;
    pointer-events: none;
}
.wct-field-options-disabled input,
.wct-field-options-disabled select,
.wct-field-options-disabled textarea,
.wct-field-options-disabled button,
.wct-field-options-disabled a {
    pointer-events: none;
}
</style>

<!-- Custom Field 1 -->
<div class="wct-settings-section">
    <h2><?php esc_html_e('Custom Field 1', 'checkout-toolkit-for-woo'); ?></h2>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Custom Field -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Field', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_field_settings[enabled]"
                               id="wct-field-1-enabled"
                               value="1"
                               <?php checked(!empty($field_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-1-options" class="<?php echo empty($field_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
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
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($field_settings['field_type'] ?? 'textarea') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($field_settings['field_type'] ?? 'textarea') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($field_settings['field_type'] ?? 'textarea') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($field_settings['field_type'] ?? 'textarea') === 'select'); ?>>
                        <?php esc_html_e('Dropdown select', 'checkout-toolkit-for-woo'); ?>
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

            <!-- Checkbox Label (conditional) -->
            <tr class="wct-conditional-field wct-field-1-checkbox <?php echo ($field_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_checkbox_label"
                           name="checkout_toolkit_field_settings[checkbox_label]"
                           value="<?php echo esc_attr($field_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options (conditional) -->
            <tr class="wct-conditional-field wct-field-1-select <?php echo ($field_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-1-options">
                        <div class="wct-select-options-list">
                            <?php
                            $checkout_toolkit_options_1 = $field_settings['select_options'] ?? [];
                            if (empty($checkout_toolkit_options_1)) {
                                $checkout_toolkit_options_1 = [['value' => '', 'label' => '']];
                            }
                            foreach ($checkout_toolkit_options_1 as $checkout_toolkit_index => $checkout_toolkit_option) :
                            ?>
                            <div class="wct-select-option-row">
                                <input type="text"
                                       name="checkout_toolkit_field_settings[select_options][<?php echo esc_attr($checkout_toolkit_index); ?>][label]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="checkout_toolkit_field_settings[select_options][<?php echo esc_attr($checkout_toolkit_index); ?>][value]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-option" title="<?php esc_attr_e('Remove', 'checkout-toolkit-for-woo'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button wct-add-option" data-field="1">
                            <?php esc_html_e('Add Option', 'checkout-toolkit-for-woo'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Placeholder Text (conditional - not for checkbox) -->
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea wct-field-1-select <?php echo in_array($field_settings['field_type'] ?? 'textarea', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
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

            <!-- Maximum Characters (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea <?php echo in_array($field_settings['field_type'] ?? 'textarea', ['text', 'textarea'], true) ? 'active' : ''; ?>">
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
</div>

<!-- Custom Field 2 -->
<div class="wct-settings-section" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #c3c4c7;">
    <h2><?php esc_html_e('Custom Field 2', 'checkout-toolkit-for-woo'); ?></h2>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Custom Field 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Field', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_field_2_settings[enabled]"
                               id="wct-field-2-enabled"
                               value="1"
                               <?php checked(!empty($field_2_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-2-options" class="<?php echo empty($field_2_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
            <!-- Field Type 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Type', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="text"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($field_2_settings['field_type'] ?? 'text') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($field_2_settings['field_type'] ?? 'text') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($field_2_settings['field_type'] ?? 'text') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($field_2_settings['field_type'] ?? 'text') === 'select'); ?>>
                        <?php esc_html_e('Dropdown select', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label 2 -->
            <tr>
                <th scope="row">
                    <label for="checkout_toolkit_field_2_label">
                        <?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_2_label"
                           name="checkout_toolkit_field_2_settings[field_label]"
                           value="<?php echo esc_attr($field_2_settings['field_label'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Checkbox Label 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-checkbox <?php echo ($field_2_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_2_checkbox_label"
                           name="checkout_toolkit_field_2_settings[checkbox_label]"
                           value="<?php echo esc_attr($field_2_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-select <?php echo ($field_2_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-2-options">
                        <div class="wct-select-options-list">
                            <?php
                            $checkout_toolkit_options_2 = $field_2_settings['select_options'] ?? [];
                            if (empty($checkout_toolkit_options_2)) {
                                $checkout_toolkit_options_2 = [['value' => '', 'label' => '']];
                            }
                            foreach ($checkout_toolkit_options_2 as $checkout_toolkit_index => $checkout_toolkit_option) :
                            ?>
                            <div class="wct-select-option-row">
                                <input type="text"
                                       name="checkout_toolkit_field_2_settings[select_options][<?php echo esc_attr($checkout_toolkit_index); ?>][label]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="checkout_toolkit_field_2_settings[select_options][<?php echo esc_attr($checkout_toolkit_index); ?>][value]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-option" title="<?php esc_attr_e('Remove', 'checkout-toolkit-for-woo'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button wct-add-option" data-field="2">
                            <?php esc_html_e('Add Option', 'checkout-toolkit-for-woo'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Placeholder Text 2 (conditional - not for checkbox) -->
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea wct-field-2-select <?php echo in_array($field_2_settings['field_type'] ?? 'text', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_placeholder">
                        <?php esc_html_e('Placeholder Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_2_placeholder"
                           name="checkout_toolkit_field_2_settings[field_placeholder]"
                           value="<?php echo esc_attr($field_2_settings['field_placeholder'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Required 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_field_2_settings[required]"
                               value="1"
                               <?php checked(!empty($field_2_settings['required'])); ?>>
                        <?php esc_html_e('Make field required', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Maximum Characters 2 (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea <?php echo in_array($field_2_settings['field_type'] ?? 'text', ['text', 'textarea'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_max_length">
                        <?php esc_html_e('Maximum Characters', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="checkout_toolkit_field_2_max_length"
                           name="checkout_toolkit_field_2_settings[max_length]"
                           value="<?php echo esc_attr($field_2_settings['max_length'] ?? 200); ?>"
                           min="0"
                           max="10000"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Set to 0 for unlimited.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Field Position 2 -->
            <tr>
                <th scope="row">
                    <label for="checkout_toolkit_field_2_position">
                        <?php esc_html_e('Field Position', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <select id="checkout_toolkit_field_2_position" name="checkout_toolkit_field_2_settings[field_position]">
                        <?php foreach ($checkout_toolkit_positions as $checkout_toolkit_hook => $checkout_toolkit_label) : ?>
                            <option value="<?php echo esc_attr($checkout_toolkit_hook); ?>"
                                    <?php selected($field_2_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
                                <?php echo esc_html($checkout_toolkit_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Display Options 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="checkout_toolkit_field_2_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($field_2_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_field_2_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($field_2_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // Enable/disable field options based on enabled checkbox
    function toggleFieldOptions(fieldNum) {
        var isEnabled = $('#wct-field-' + fieldNum + '-enabled').is(':checked');
        var optionsWrapper = $('#wct-field-' + fieldNum + '-options');

        if (isEnabled) {
            optionsWrapper.removeClass('wct-field-options-disabled');
        } else {
            optionsWrapper.addClass('wct-field-options-disabled');
        }
    }

    // Bind enable checkbox handlers
    $('#wct-field-1-enabled').on('change', function() {
        toggleFieldOptions(1);
    });

    $('#wct-field-2-enabled').on('change', function() {
        toggleFieldOptions(2);
    });

    // Field type change handler
    $('.wct-field-type-radio').on('change', function() {
        var fieldNum = $(this).data('field');
        var fieldType = $(this).val();

        // Hide all conditional fields for this field number
        $('.wct-field-' + fieldNum + '-text, .wct-field-' + fieldNum + '-textarea, .wct-field-' + fieldNum + '-checkbox, .wct-field-' + fieldNum + '-select').removeClass('active');

        // Show relevant conditional fields
        $('.wct-field-' + fieldNum + '-' + fieldType).addClass('active');
    });

    // Add option button handler
    $('.wct-add-option').on('click', function(e) {
        e.preventDefault();
        var fieldNum = $(this).data('field');
        var wrapper = $('#wct-field-' + fieldNum + '-select-options .wct-select-options-list');
        var optionName = fieldNum === 1 ? 'checkout_toolkit_field_settings' : 'checkout_toolkit_field_2_settings';
        var index = wrapper.find('.wct-select-option-row').length;

        var newRow = '<div class="wct-select-option-row">' +
            '<input type="text" name="' + optionName + '[select_options][' + index + '][label]" value="" placeholder="<?php echo esc_js(__('Label', 'checkout-toolkit-for-woo')); ?>" class="regular-text">' +
            '<input type="text" name="' + optionName + '[select_options][' + index + '][value]" value="" placeholder="<?php echo esc_js(__('Value', 'checkout-toolkit-for-woo')); ?>" class="regular-text">' +
            '<a href="#" class="button-link-delete wct-remove-option" title="<?php echo esc_js(__('Remove', 'checkout-toolkit-for-woo')); ?>">&times;</a>' +
            '</div>';

        wrapper.append(newRow);
    });

    // Remove option button handler
    $(document).on('click', '.wct-remove-option', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.wct-select-options-list');

        // Keep at least one option row
        if (wrapper.find('.wct-select-option-row').length > 1) {
            $(this).closest('.wct-select-option-row').remove();
        }
    });
});
</script>
