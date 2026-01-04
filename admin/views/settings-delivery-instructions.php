<?php
/**
 * Delivery instructions settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $checkout_toolkit_delivery_instructions_settings Delivery instructions settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Delivery Instructions', 'checkout-toolkit-for-woo'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add a delivery instructions field with preset options and custom text. This field is only shown when "Delivery" is selected (hidden for Pickup).', 'checkout-toolkit-for-woo'); ?>
    </p>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Feature -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Feature', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_instructions_settings[enabled]"
                               id="wct-di-enabled"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_instructions_settings['enabled'])); ?>>
                        <?php esc_html_e('Show delivery instructions field on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Note: This field only appears when customer selects "Delivery" (hidden for Pickup orders).', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>
        </tbody>

        <tbody id="wct-di-options" class="<?php echo empty($checkout_toolkit_delivery_instructions_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
            <!-- Required -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_instructions_settings[required]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_instructions_settings['required'])); ?>>
                        <?php esc_html_e('Require customers to select or enter delivery instructions', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_field_label">
                        <?php esc_html_e('Section Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_field_label"
                           name="checkout_toolkit_delivery_instructions_settings[field_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['field_label'] ?? 'Delivery Instructions'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Main heading for the delivery instructions section.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Preset Options Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_preset_label">
                        <?php esc_html_e('Preset Dropdown Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_preset_label"
                           name="checkout_toolkit_delivery_instructions_settings[preset_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['preset_label'] ?? 'Common Instructions'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Preset Options -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Preset Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <p class="description" style="margin-bottom: 10px;">
                        <?php esc_html_e('Common delivery instructions customers can select from a dropdown.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                    <div class="wct-preset-options-wrapper">
                        <div class="wct-preset-options-list">
                            <?php
                            $checkout_toolkit_preset_options = $checkout_toolkit_delivery_instructions_settings['preset_options'] ?? [];
                            if (empty($checkout_toolkit_preset_options)) {
                                $checkout_toolkit_preset_options = [['value' => '', 'label' => '']];
                            }
                            foreach ($checkout_toolkit_preset_options as $checkout_toolkit_index => $checkout_toolkit_option) :
                            ?>
                            <div class="wct-preset-option-row">
                                <input type="text"
                                       name="checkout_toolkit_delivery_instructions_settings[preset_options][<?php echo esc_attr($checkout_toolkit_index); ?>][label]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label (shown to customer)', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="checkout_toolkit_delivery_instructions_settings[preset_options][<?php echo esc_attr($checkout_toolkit_index); ?>][value]"
                                       value="<?php echo esc_attr($checkout_toolkit_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value (stored)', 'checkout-toolkit-for-woo'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-preset-option" title="<?php esc_attr_e('Remove', 'checkout-toolkit-for-woo'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="wct-add-preset-option">
                            <?php esc_html_e('Add Option', 'checkout-toolkit-for-woo'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Custom Text Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_custom_label">
                        <?php esc_html_e('Custom Text Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_custom_label"
                           name="checkout_toolkit_delivery_instructions_settings[custom_label]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['custom_label'] ?? 'Additional Instructions'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label for the optional custom text area.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Custom Placeholder -->
            <tr>
                <th scope="row">
                    <label for="wct_di_custom_placeholder">
                        <?php esc_html_e('Custom Text Placeholder', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_custom_placeholder"
                           name="checkout_toolkit_delivery_instructions_settings[custom_placeholder]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['custom_placeholder'] ?? 'Any other delivery instructions...'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Maximum Characters -->
            <tr>
                <th scope="row">
                    <label for="wct_di_max_length">
                        <?php esc_html_e('Maximum Characters', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="wct_di_max_length"
                           name="checkout_toolkit_delivery_instructions_settings[max_length]"
                           value="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['max_length'] ?? 500); ?>"
                           min="0"
                           max="10000"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Maximum characters for custom text. Set to 0 for unlimited.', 'checkout-toolkit-for-woo'); ?>
                    </p>
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
                               name="checkout_toolkit_delivery_instructions_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_instructions_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_delivery_instructions_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_delivery_instructions_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="wct-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'checkout-toolkit-for-woo'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the delivery instructions will appear on checkout (when Delivery is selected).', 'checkout-toolkit-for-woo'); ?></p>

        <div class="wct-preview-content" style="margin-top: 15px; max-width: 400px;">
            <h4 id="wct-preview-field-label" style="margin-bottom: 15px;"><?php echo esc_html($checkout_toolkit_delivery_instructions_settings['field_label'] ?? 'Delivery Instructions'); ?></h4>

            <div style="margin-bottom: 15px;">
                <label id="wct-preview-preset-label" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <?php echo esc_html($checkout_toolkit_delivery_instructions_settings['preset_label'] ?? 'Common Instructions'); ?>
                </label>
                <select style="width: 100%; padding: 8px;" id="wct-preview-preset-select">
                    <option value=""><?php esc_html_e('Select an option...', 'checkout-toolkit-for-woo'); ?></option>
                    <?php
                    $checkout_toolkit_preview_options = $checkout_toolkit_delivery_instructions_settings['preset_options'] ?? [];
                    foreach ($checkout_toolkit_preview_options as $checkout_toolkit_option) :
                        if (!empty($checkout_toolkit_option['label'])) :
                    ?>
                        <option value="<?php echo esc_attr($checkout_toolkit_option['value'] ?? ''); ?>">
                            <?php echo esc_html($checkout_toolkit_option['label']); ?>
                        </option>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </select>
            </div>

            <div>
                <label id="wct-preview-custom-label" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <?php echo esc_html($checkout_toolkit_delivery_instructions_settings['custom_label'] ?? 'Additional Instructions'); ?>
                </label>
                <textarea id="wct-preview-custom-textarea"
                          style="width: 100%; padding: 8px; min-height: 80px;"
                          placeholder="<?php echo esc_attr($checkout_toolkit_delivery_instructions_settings['custom_placeholder'] ?? 'Any other delivery instructions...'); ?>"></textarea>
            </div>
        </div>
    </div>
</div>
