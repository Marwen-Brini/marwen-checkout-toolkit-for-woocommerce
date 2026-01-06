<?php
/**
 * Delivery instructions settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $marwchto_delivery_instructions_settings Delivery instructions settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Delivery Instructions', 'marwen-checkout-toolkit-for-woocommerce'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add a delivery instructions field with preset options and custom text. This field is only shown when "Delivery" is selected (hidden for Pickup).', 'marwen-checkout-toolkit-for-woocommerce'); ?>
    </p>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Feature -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Feature', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_delivery_instructions_settings[enabled]"
                               id="wct-di-enabled"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_instructions_settings['enabled'])); ?>>
                        <?php esc_html_e('Show delivery instructions field on checkout', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Note: This field only appears when customer selects "Delivery" (hidden for Pickup orders).', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>
        </tbody>

        <tbody id="wct-di-options" class="<?php echo empty($marwchto_delivery_instructions_settings['enabled']) ? 'marwchto-field-options-disabled' : ''; ?>">
            <!-- Required -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_delivery_instructions_settings[required]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_instructions_settings['required'])); ?>>
                        <?php esc_html_e('Require customers to select or enter delivery instructions', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_field_label">
                        <?php esc_html_e('Section Label', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_field_label"
                           name="marwchto_delivery_instructions_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_delivery_instructions_settings['field_label'] ?? 'Delivery Instructions'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Main heading for the delivery instructions section.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Preset Options Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_preset_label">
                        <?php esc_html_e('Preset Dropdown Label', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_preset_label"
                           name="marwchto_delivery_instructions_settings[preset_label]"
                           value="<?php echo esc_attr($marwchto_delivery_instructions_settings['preset_label'] ?? 'Common Instructions'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Preset Options -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Preset Options', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                </th>
                <td>
                    <p class="description" style="margin-bottom: 10px;">
                        <?php esc_html_e('Common delivery instructions customers can select from a dropdown.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </p>
                    <div class="wct-preset-options-wrapper">
                        <div class="wct-preset-options-list">
                            <?php
                            $marwchto_preset_options = $marwchto_delivery_instructions_settings['preset_options'] ?? [];
                            if (empty($marwchto_preset_options)) {
                                $marwchto_preset_options = [['value' => '', 'label' => '']];
                            }
                            foreach ($marwchto_preset_options as $marwchto_index => $marwchto_option) :
                            ?>
                            <div class="wct-preset-option-row">
                                <input type="text"
                                       name="marwchto_delivery_instructions_settings[preset_options][<?php echo esc_attr($marwchto_index); ?>][label]"
                                       value="<?php echo esc_attr($marwchto_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label (shown to customer)', 'marwen-checkout-toolkit-for-woocommerce'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="marwchto_delivery_instructions_settings[preset_options][<?php echo esc_attr($marwchto_index); ?>][value]"
                                       value="<?php echo esc_attr($marwchto_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value (stored)', 'marwen-checkout-toolkit-for-woocommerce'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-preset-option" title="<?php esc_attr_e('Remove', 'marwen-checkout-toolkit-for-woocommerce'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="wct-add-preset-option">
                            <?php esc_html_e('Add Option', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Custom Text Label -->
            <tr>
                <th scope="row">
                    <label for="wct_di_custom_label">
                        <?php esc_html_e('Custom Text Label', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_custom_label"
                           name="marwchto_delivery_instructions_settings[custom_label]"
                           value="<?php echo esc_attr($marwchto_delivery_instructions_settings['custom_label'] ?? 'Additional Instructions'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label for the optional custom text area.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Custom Placeholder -->
            <tr>
                <th scope="row">
                    <label for="wct_di_custom_placeholder">
                        <?php esc_html_e('Custom Text Placeholder', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_di_custom_placeholder"
                           name="marwchto_delivery_instructions_settings[custom_placeholder]"
                           value="<?php echo esc_attr($marwchto_delivery_instructions_settings['custom_placeholder'] ?? 'Any other delivery instructions...'); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Maximum Characters -->
            <tr>
                <th scope="row">
                    <label for="wct_di_max_length">
                        <?php esc_html_e('Maximum Characters', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="wct_di_max_length"
                           name="marwchto_delivery_instructions_settings[max_length]"
                           value="<?php echo esc_attr($marwchto_delivery_instructions_settings['max_length'] ?? 500); ?>"
                           min="0"
                           max="10000"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Maximum characters for custom text. Set to 0 for unlimited.', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </p>
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
                               name="marwchto_delivery_instructions_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_instructions_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="marwchto_delivery_instructions_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($marwchto_delivery_instructions_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="wct-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'marwen-checkout-toolkit-for-woocommerce'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the delivery instructions will appear on checkout (when Delivery is selected).', 'marwen-checkout-toolkit-for-woocommerce'); ?></p>

        <div class="wct-preview-content" style="margin-top: 15px; max-width: 400px;">
            <h4 id="wct-preview-field-label" style="margin-bottom: 15px;"><?php echo esc_html($marwchto_delivery_instructions_settings['field_label'] ?? 'Delivery Instructions'); ?></h4>

            <div style="margin-bottom: 15px;">
                <label id="wct-preview-preset-label" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <?php echo esc_html($marwchto_delivery_instructions_settings['preset_label'] ?? 'Common Instructions'); ?>
                </label>
                <select style="width: 100%; padding: 8px;" id="wct-preview-preset-select">
                    <option value=""><?php esc_html_e('Select an option...', 'marwen-checkout-toolkit-for-woocommerce'); ?></option>
                    <?php
                    $marwchto_preview_options = $marwchto_delivery_instructions_settings['preset_options'] ?? [];
                    foreach ($marwchto_preview_options as $marwchto_option) :
                        if (!empty($marwchto_option['label'])) :
                    ?>
                        <option value="<?php echo esc_attr($marwchto_option['value'] ?? ''); ?>">
                            <?php echo esc_html($marwchto_option['label']); ?>
                        </option>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </select>
            </div>

            <div>
                <label id="wct-preview-custom-label" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    <?php echo esc_html($marwchto_delivery_instructions_settings['custom_label'] ?? 'Additional Instructions'); ?>
                </label>
                <textarea id="wct-preview-custom-textarea"
                          style="width: 100%; padding: 8px; min-height: 80px;"
                          placeholder="<?php echo esc_attr($marwchto_delivery_instructions_settings['custom_placeholder'] ?? 'Any other delivery instructions...'); ?>"></textarea>
            </div>
        </div>
    </div>
</div>
