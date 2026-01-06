<?php
/**
 * Custom fields settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $marwchto_field_settings   Field 1 settings array.
 * @var array $marwchto_field_2_settings Field 2 settings array.
 */

defined('ABSPATH') || exit;

$marwchto_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$marwchto_positions = $marwchto_settings_obj->get_field_positions();

// Get product categories for visibility settings
$marwchto_product_categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC',
]);
if (is_wp_error($marwchto_product_categories)) {
    $marwchto_product_categories = [];
}
?>

<!-- Custom Field 1 -->
<div class="wct-settings-section">
    <h2><?php esc_html_e('Custom Field 1', 'marwen-marwchto-for-woocommerce'); ?></h2>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Custom Field -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Field', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_field_settings[enabled]"
                               id="wct-field-1-enabled"
                               value="1"
                               <?php checked(!empty($marwchto_field_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-1-options" class="<?php echo empty($marwchto_field_settings['enabled']) ? 'marwchto-field-options-disabled' : ''; ?>">
            <!-- Field Type -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Type', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_settings[field_type]"
                               value="text"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($marwchto_field_settings['field_type'] ?? 'textarea') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($marwchto_field_settings['field_type'] ?? 'textarea') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($marwchto_field_settings['field_type'] ?? 'textarea') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="marwchto_field_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($marwchto_field_settings['field_type'] ?? 'textarea') === 'select'); ?>>
                        <?php esc_html_e('Dropdown select', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="marwchto_field_label">
                        <?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_field_label"
                           name="marwchto_field_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_field_settings['field_label'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Checkbox Label (conditional) -->
            <tr class="wct-conditional-field wct-field-1-checkbox <?php echo ($marwchto_field_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_checkbox_label"
                           name="marwchto_field_settings[checkbox_label]"
                           value="<?php echo esc_attr($marwchto_field_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options (conditional) -->
            <tr class="wct-conditional-field wct-field-1-select <?php echo ($marwchto_field_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-1-options">
                        <div class="wct-select-options-list">
                            <?php
                            $marwchto_options_1 = $marwchto_field_settings['select_options'] ?? [];
                            if (empty($marwchto_options_1)) {
                                $marwchto_options_1 = [['value' => '', 'label' => '']];
                            }
                            foreach ($marwchto_options_1 as $marwchto_index => $marwchto_option) :
                            ?>
                            <div class="wct-select-option-row">
                                <input type="text"
                                       name="marwchto_field_settings[select_options][<?php echo esc_attr($marwchto_index); ?>][label]"
                                       value="<?php echo esc_attr($marwchto_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="marwchto_field_settings[select_options][<?php echo esc_attr($marwchto_index); ?>][value]"
                                       value="<?php echo esc_attr($marwchto_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-option" title="<?php esc_attr_e('Remove', 'marwen-marwchto-for-woocommerce'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button wct-add-option" data-field="1">
                            <?php esc_html_e('Add Option', 'marwen-marwchto-for-woocommerce'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Placeholder Text (conditional - not for checkbox) -->
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea wct-field-1-select <?php echo in_array($marwchto_field_settings['field_type'] ?? 'textarea', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_field_placeholder">
                        <?php esc_html_e('Placeholder Text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_field_placeholder"
                           name="marwchto_field_settings[field_placeholder]"
                           value="<?php echo esc_attr($marwchto_field_settings['field_placeholder'] ?? ''); ?>"
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
                               name="marwchto_field_settings[required]"
                               value="1"
                               <?php checked(!empty($marwchto_field_settings['required'])); ?>>
                        <?php esc_html_e('Make field required', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Maximum Characters (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea <?php echo in_array($marwchto_field_settings['field_type'] ?? 'textarea', ['text', 'textarea'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_max_length">
                        <?php esc_html_e('Maximum Characters', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="marwchto_max_length"
                           name="marwchto_field_settings[max_length]"
                           value="<?php echo esc_attr($marwchto_field_settings['max_length'] ?? 500); ?>"
                           min="0"
                           max="10000"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Set to 0 for unlimited.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Field Position -->
            <tr>
                <th scope="row">
                    <label for="marwchto_field_position">
                        <?php esc_html_e('Field Position', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <select id="marwchto_field_position" name="marwchto_field_settings[field_position]">
                        <?php foreach ($marwchto_positions as $marwchto_hook => $marwchto_label) : ?>
                            <option value="<?php echo esc_attr($marwchto_hook); ?>"
                                    <?php selected($marwchto_field_settings['field_position'] ?? 'woocommerce_after_order_notes', $marwchto_hook); ?>>
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
                               name="marwchto_field_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($marwchto_field_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="marwchto_field_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($marwchto_field_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Visibility Settings -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Visibility', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <div class="wct-visibility-options">
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="marwchto_field_settings[visibility_type]"
                                   value="always"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($marwchto_field_settings['visibility_type'] ?? 'always') === 'always'); ?>>
                            <?php esc_html_e('Always show', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="marwchto_field_settings[visibility_type]"
                                   value="products"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($marwchto_field_settings['visibility_type'] ?? 'always') === 'products'); ?>>
                            <?php esc_html_e('Show for specific products', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                        <label style="display: block;">
                            <input type="radio"
                                   name="marwchto_field_settings[visibility_type]"
                                   value="categories"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($marwchto_field_settings['visibility_type'] ?? 'always') === 'categories'); ?>>
                            <?php esc_html_e('Show for specific categories', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>

                        <!-- Product Selection -->
                        <div class="wct-visibility-products wct-visibility-1-products <?php echo ($marwchto_field_settings['visibility_type'] ?? '') === 'products' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Products:', 'marwen-marwchto-for-woocommerce'); ?></label>
                            <select class="wc-product-search"
                                    multiple="multiple"
                                    style="width: 100%;"
                                    data-placeholder="<?php esc_attr_e('Search for products...', 'marwen-marwchto-for-woocommerce'); ?>"
                                    data-action="woocommerce_json_search_products"
                                    name="marwchto_field_settings[visibility_products][]">
                                <?php
                                $marwchto_selected_products_1 = $marwchto_field_settings['visibility_products'] ?? [];
                                foreach ($marwchto_selected_products_1 as $marwchto_product_id) :
                                    $marwchto_product = wc_get_product($marwchto_product_id);
                                    if ($marwchto_product) :
                                ?>
                                    <option value="<?php echo esc_attr($marwchto_product_id); ?>" selected>
                                        <?php echo esc_html($marwchto_product->get_formatted_name()); ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>

                        <!-- Category Selection -->
                        <div class="wct-visibility-categories wct-visibility-1-categories <?php echo ($marwchto_field_settings['visibility_type'] ?? '') === 'categories' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Categories:', 'marwen-marwchto-for-woocommerce'); ?></label>
                            <div class="wct-category-checkboxes">
                                <?php
                                $marwchto_selected_cats_1 = $marwchto_field_settings['visibility_categories'] ?? [];
                                foreach ($marwchto_product_categories as $marwchto_cat) :
                                ?>
                                <label>
                                    <input type="checkbox"
                                           name="marwchto_field_settings[visibility_categories][]"
                                           value="<?php echo esc_attr($marwchto_cat->term_id); ?>"
                                           <?php checked(in_array($marwchto_cat->term_id, $marwchto_selected_cats_1)); ?>>
                                    <?php echo esc_html($marwchto_cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Visibility Mode -->
                        <div class="wct-visibility-mode wct-visibility-1-mode" style="<?php echo ($marwchto_field_settings['visibility_type'] ?? 'always') === 'always' ? 'display:none;' : ''; ?>">
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="radio"
                                       name="marwchto_field_settings[visibility_mode]"
                                       value="show"
                                       <?php checked(($marwchto_field_settings['visibility_mode'] ?? 'show') === 'show'); ?>>
                                <?php esc_html_e('Show field when product/category is in cart', 'marwen-marwchto-for-woocommerce'); ?>
                            </label>
                            <label style="display: block;">
                                <input type="radio"
                                       name="marwchto_field_settings[visibility_mode]"
                                       value="hide"
                                       <?php checked(($marwchto_field_settings['visibility_mode'] ?? 'show') === 'hide'); ?>>
                                <?php esc_html_e('Hide field when product/category is in cart', 'marwen-marwchto-for-woocommerce'); ?>
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Custom Field 2 -->
<div class="wct-settings-section" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #c3c4c7;">
    <h2><?php esc_html_e('Custom Field 2', 'marwen-marwchto-for-woocommerce'); ?></h2>

    <table class="form-table wct-settings-table">
        <tbody>
            <!-- Enable Custom Field 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Field', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_field_2_settings[enabled]"
                               id="wct-field-2-enabled"
                               value="1"
                               <?php checked(!empty($marwchto_field_2_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-2-options" class="<?php echo empty($marwchto_field_2_settings['enabled']) ? 'marwchto-field-options-disabled' : ''; ?>">
            <!-- Field Type 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Type', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_2_settings[field_type]"
                               value="text"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($marwchto_field_2_settings['field_type'] ?? 'text') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_2_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($marwchto_field_2_settings['field_type'] ?? 'text') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="marwchto_field_2_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($marwchto_field_2_settings['field_type'] ?? 'text') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="marwchto_field_2_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($marwchto_field_2_settings['field_type'] ?? 'text') === 'select'); ?>>
                        <?php esc_html_e('Dropdown select', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label 2 -->
            <tr>
                <th scope="row">
                    <label for="marwchto_field_2_label">
                        <?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_field_2_label"
                           name="marwchto_field_2_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_field_2_settings['field_label'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Checkbox Label 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-checkbox <?php echo ($marwchto_field_2_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_field_2_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_field_2_checkbox_label"
                           name="marwchto_field_2_settings[checkbox_label]"
                           value="<?php echo esc_attr($marwchto_field_2_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-select <?php echo ($marwchto_field_2_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-2-options">
                        <div class="wct-select-options-list">
                            <?php
                            $marwchto_options_2 = $marwchto_field_2_settings['select_options'] ?? [];
                            if (empty($marwchto_options_2)) {
                                $marwchto_options_2 = [['value' => '', 'label' => '']];
                            }
                            foreach ($marwchto_options_2 as $marwchto_index => $marwchto_option) :
                            ?>
                            <div class="wct-select-option-row">
                                <input type="text"
                                       name="marwchto_field_2_settings[select_options][<?php echo esc_attr($marwchto_index); ?>][label]"
                                       value="<?php echo esc_attr($marwchto_option['label'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Label', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text">
                                <input type="text"
                                       name="marwchto_field_2_settings[select_options][<?php echo esc_attr($marwchto_index); ?>][value]"
                                       value="<?php echo esc_attr($marwchto_option['value'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Value', 'marwen-marwchto-for-woocommerce'); ?>"
                                       class="regular-text">
                                <a href="#" class="button-link-delete wct-remove-option" title="<?php esc_attr_e('Remove', 'marwen-marwchto-for-woocommerce'); ?>">&times;</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button wct-add-option" data-field="2">
                            <?php esc_html_e('Add Option', 'marwen-marwchto-for-woocommerce'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Placeholder Text 2 (conditional - not for checkbox) -->
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea wct-field-2-select <?php echo in_array($marwchto_field_2_settings['field_type'] ?? 'text', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_field_2_placeholder">
                        <?php esc_html_e('Placeholder Text', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_field_2_placeholder"
                           name="marwchto_field_2_settings[field_placeholder]"
                           value="<?php echo esc_attr($marwchto_field_2_settings['field_placeholder'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Required 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_field_2_settings[required]"
                               value="1"
                               <?php checked(!empty($marwchto_field_2_settings['required'])); ?>>
                        <?php esc_html_e('Make field required', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Maximum Characters 2 (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea <?php echo in_array($marwchto_field_2_settings['field_type'] ?? 'text', ['text', 'textarea'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="marwchto_field_2_max_length">
                        <?php esc_html_e('Maximum Characters', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="marwchto_field_2_max_length"
                           name="marwchto_field_2_settings[max_length]"
                           value="<?php echo esc_attr($marwchto_field_2_settings['max_length'] ?? 200); ?>"
                           min="0"
                           max="10000"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Set to 0 for unlimited.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Field Position 2 -->
            <tr>
                <th scope="row">
                    <label for="marwchto_field_2_position">
                        <?php esc_html_e('Field Position', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <select id="marwchto_field_2_position" name="marwchto_field_2_settings[field_position]">
                        <?php foreach ($marwchto_positions as $marwchto_hook => $marwchto_label) : ?>
                            <option value="<?php echo esc_attr($marwchto_hook); ?>"
                                    <?php selected($marwchto_field_2_settings['field_position'] ?? 'woocommerce_after_order_notes', $marwchto_hook); ?>>
                                <?php echo esc_html($marwchto_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Display Options 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Display Options', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="marwchto_field_2_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($marwchto_field_2_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="marwchto_field_2_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($marwchto_field_2_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Visibility Settings 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Visibility', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <div class="wct-visibility-options">
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="marwchto_field_2_settings[visibility_type]"
                                   value="always"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($marwchto_field_2_settings['visibility_type'] ?? 'always') === 'always'); ?>>
                            <?php esc_html_e('Always show', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="marwchto_field_2_settings[visibility_type]"
                                   value="products"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($marwchto_field_2_settings['visibility_type'] ?? 'always') === 'products'); ?>>
                            <?php esc_html_e('Show for specific products', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>
                        <label style="display: block;">
                            <input type="radio"
                                   name="marwchto_field_2_settings[visibility_type]"
                                   value="categories"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($marwchto_field_2_settings['visibility_type'] ?? 'always') === 'categories'); ?>>
                            <?php esc_html_e('Show for specific categories', 'marwen-marwchto-for-woocommerce'); ?>
                        </label>

                        <!-- Product Selection 2 -->
                        <div class="wct-visibility-products wct-visibility-2-products <?php echo ($marwchto_field_2_settings['visibility_type'] ?? '') === 'products' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Products:', 'marwen-marwchto-for-woocommerce'); ?></label>
                            <select class="wc-product-search"
                                    multiple="multiple"
                                    style="width: 100%;"
                                    data-placeholder="<?php esc_attr_e('Search for products...', 'marwen-marwchto-for-woocommerce'); ?>"
                                    data-action="woocommerce_json_search_products"
                                    name="marwchto_field_2_settings[visibility_products][]">
                                <?php
                                $marwchto_selected_products_2 = $marwchto_field_2_settings['visibility_products'] ?? [];
                                foreach ($marwchto_selected_products_2 as $marwchto_product_id) :
                                    $marwchto_product = wc_get_product($marwchto_product_id);
                                    if ($marwchto_product) :
                                ?>
                                    <option value="<?php echo esc_attr($marwchto_product_id); ?>" selected>
                                        <?php echo esc_html($marwchto_product->get_formatted_name()); ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>

                        <!-- Category Selection 2 -->
                        <div class="wct-visibility-categories wct-visibility-2-categories <?php echo ($marwchto_field_2_settings['visibility_type'] ?? '') === 'categories' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Categories:', 'marwen-marwchto-for-woocommerce'); ?></label>
                            <div class="wct-category-checkboxes">
                                <?php
                                $marwchto_selected_cats_2 = $marwchto_field_2_settings['visibility_categories'] ?? [];
                                foreach ($marwchto_product_categories as $marwchto_cat) :
                                ?>
                                <label>
                                    <input type="checkbox"
                                           name="marwchto_field_2_settings[visibility_categories][]"
                                           value="<?php echo esc_attr($marwchto_cat->term_id); ?>"
                                           <?php checked(in_array($marwchto_cat->term_id, $marwchto_selected_cats_2)); ?>>
                                    <?php echo esc_html($marwchto_cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Visibility Mode 2 -->
                        <div class="wct-visibility-mode wct-visibility-2-mode" style="<?php echo ($marwchto_field_2_settings['visibility_type'] ?? 'always') === 'always' ? 'display:none;' : ''; ?>">
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="radio"
                                       name="marwchto_field_2_settings[visibility_mode]"
                                       value="show"
                                       <?php checked(($marwchto_field_2_settings['visibility_mode'] ?? 'show') === 'show'); ?>>
                                <?php esc_html_e('Show field when product/category is in cart', 'marwen-marwchto-for-woocommerce'); ?>
                            </label>
                            <label style="display: block;">
                                <input type="radio"
                                       name="marwchto_field_2_settings[visibility_mode]"
                                       value="hide"
                                       <?php checked(($marwchto_field_2_settings['visibility_mode'] ?? 'show') === 'hide'); ?>>
                                <?php esc_html_e('Hide field when product/category is in cart', 'marwen-marwchto-for-woocommerce'); ?>
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
