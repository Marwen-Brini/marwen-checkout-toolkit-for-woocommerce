<?php
/**
 * Custom fields settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $checkout_toolkit_field_settings   Field 1 settings array.
 * @var array $checkout_toolkit_field_2_settings Field 2 settings array.
 */

defined('ABSPATH') || exit;

$checkout_toolkit_settings_obj = new \WooCheckoutToolkit\Admin\Settings();
$checkout_toolkit_positions = $checkout_toolkit_settings_obj->get_field_positions();

// Get product categories for visibility settings
$checkout_toolkit_product_categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC',
]);
if (is_wp_error($checkout_toolkit_product_categories)) {
    $checkout_toolkit_product_categories = [];
}
?>

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
                               <?php checked(!empty($checkout_toolkit_field_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-1-options" class="<?php echo empty($checkout_toolkit_field_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
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
                               <?php checked(($checkout_toolkit_field_settings['field_type'] ?? 'textarea') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($checkout_toolkit_field_settings['field_type'] ?? 'textarea') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($checkout_toolkit_field_settings['field_type'] ?? 'textarea') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_field_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="1"
                               <?php checked(($checkout_toolkit_field_settings['field_type'] ?? 'textarea') === 'select'); ?>>
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
                           value="<?php echo esc_attr($checkout_toolkit_field_settings['field_label'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Checkbox Label (conditional) -->
            <tr class="wct-conditional-field wct-field-1-checkbox <?php echo ($checkout_toolkit_field_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_checkbox_label"
                           name="checkout_toolkit_field_settings[checkbox_label]"
                           value="<?php echo esc_attr($checkout_toolkit_field_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options (conditional) -->
            <tr class="wct-conditional-field wct-field-1-select <?php echo ($checkout_toolkit_field_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-1-options">
                        <div class="wct-select-options-list">
                            <?php
                            $checkout_toolkit_options_1 = $checkout_toolkit_field_settings['select_options'] ?? [];
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
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea wct-field-1-select <?php echo in_array($checkout_toolkit_field_settings['field_type'] ?? 'textarea', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_placeholder">
                        <?php esc_html_e('Placeholder Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_placeholder"
                           name="checkout_toolkit_field_settings[field_placeholder]"
                           value="<?php echo esc_attr($checkout_toolkit_field_settings['field_placeholder'] ?? ''); ?>"
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
                               <?php checked(!empty($checkout_toolkit_field_settings['required'])); ?>>
                        <?php esc_html_e('Make field required', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Maximum Characters (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-1-text wct-field-1-textarea <?php echo in_array($checkout_toolkit_field_settings['field_type'] ?? 'textarea', ['text', 'textarea'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_max_length">
                        <?php esc_html_e('Maximum Characters', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="checkout_toolkit_max_length"
                           name="checkout_toolkit_field_settings[max_length]"
                           value="<?php echo esc_attr($checkout_toolkit_field_settings['max_length'] ?? 500); ?>"
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
                                    <?php selected($checkout_toolkit_field_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
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
                               <?php checked(!empty($checkout_toolkit_field_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_field_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_field_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Visibility Settings -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Visibility', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-visibility-options">
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="checkout_toolkit_field_settings[visibility_type]"
                                   value="always"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($checkout_toolkit_field_settings['visibility_type'] ?? 'always') === 'always'); ?>>
                            <?php esc_html_e('Always show', 'checkout-toolkit-for-woo'); ?>
                        </label>
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="checkout_toolkit_field_settings[visibility_type]"
                                   value="products"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($checkout_toolkit_field_settings['visibility_type'] ?? 'always') === 'products'); ?>>
                            <?php esc_html_e('Show for specific products', 'checkout-toolkit-for-woo'); ?>
                        </label>
                        <label style="display: block;">
                            <input type="radio"
                                   name="checkout_toolkit_field_settings[visibility_type]"
                                   value="categories"
                                   class="wct-visibility-type-radio"
                                   data-field="1"
                                   <?php checked(($checkout_toolkit_field_settings['visibility_type'] ?? 'always') === 'categories'); ?>>
                            <?php esc_html_e('Show for specific categories', 'checkout-toolkit-for-woo'); ?>
                        </label>

                        <!-- Product Selection -->
                        <div class="wct-visibility-products wct-visibility-1-products <?php echo ($checkout_toolkit_field_settings['visibility_type'] ?? '') === 'products' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Products:', 'checkout-toolkit-for-woo'); ?></label>
                            <select class="wc-product-search"
                                    multiple="multiple"
                                    style="width: 100%;"
                                    data-placeholder="<?php esc_attr_e('Search for products...', 'checkout-toolkit-for-woo'); ?>"
                                    data-action="woocommerce_json_search_products"
                                    name="checkout_toolkit_field_settings[visibility_products][]">
                                <?php
                                $checkout_toolkit_selected_products_1 = $checkout_toolkit_field_settings['visibility_products'] ?? [];
                                foreach ($checkout_toolkit_selected_products_1 as $checkout_toolkit_product_id) :
                                    $checkout_toolkit_product = wc_get_product($checkout_toolkit_product_id);
                                    if ($checkout_toolkit_product) :
                                ?>
                                    <option value="<?php echo esc_attr($checkout_toolkit_product_id); ?>" selected>
                                        <?php echo esc_html($checkout_toolkit_product->get_formatted_name()); ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>

                        <!-- Category Selection -->
                        <div class="wct-visibility-categories wct-visibility-1-categories <?php echo ($checkout_toolkit_field_settings['visibility_type'] ?? '') === 'categories' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Categories:', 'checkout-toolkit-for-woo'); ?></label>
                            <div class="wct-category-checkboxes">
                                <?php
                                $checkout_toolkit_selected_cats_1 = $checkout_toolkit_field_settings['visibility_categories'] ?? [];
                                foreach ($checkout_toolkit_product_categories as $checkout_toolkit_cat) :
                                ?>
                                <label>
                                    <input type="checkbox"
                                           name="checkout_toolkit_field_settings[visibility_categories][]"
                                           value="<?php echo esc_attr($checkout_toolkit_cat->term_id); ?>"
                                           <?php checked(in_array($checkout_toolkit_cat->term_id, $checkout_toolkit_selected_cats_1)); ?>>
                                    <?php echo esc_html($checkout_toolkit_cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Visibility Mode -->
                        <div class="wct-visibility-mode wct-visibility-1-mode" style="<?php echo ($checkout_toolkit_field_settings['visibility_type'] ?? 'always') === 'always' ? 'display:none;' : ''; ?>">
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="radio"
                                       name="checkout_toolkit_field_settings[visibility_mode]"
                                       value="show"
                                       <?php checked(($checkout_toolkit_field_settings['visibility_mode'] ?? 'show') === 'show'); ?>>
                                <?php esc_html_e('Show field when product/category is in cart', 'checkout-toolkit-for-woo'); ?>
                            </label>
                            <label style="display: block;">
                                <input type="radio"
                                       name="checkout_toolkit_field_settings[visibility_mode]"
                                       value="hide"
                                       <?php checked(($checkout_toolkit_field_settings['visibility_mode'] ?? 'show') === 'hide'); ?>>
                                <?php esc_html_e('Hide field when product/category is in cart', 'checkout-toolkit-for-woo'); ?>
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
                               <?php checked(!empty($checkout_toolkit_field_2_settings['enabled'])); ?>>
                        <?php esc_html_e('Show this field on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
        <tbody id="wct-field-2-options" class="<?php echo empty($checkout_toolkit_field_2_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
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
                               <?php checked(($checkout_toolkit_field_2_settings['field_type'] ?? 'text') === 'text'); ?>>
                        <?php esc_html_e('Single line text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="textarea"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($checkout_toolkit_field_2_settings['field_type'] ?? 'text') === 'textarea'); ?>>
                        <?php esc_html_e('Multi-line textarea', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="checkbox"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($checkout_toolkit_field_2_settings['field_type'] ?? 'text') === 'checkbox'); ?>>
                        <?php esc_html_e('Checkbox', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="radio"
                               name="checkout_toolkit_field_2_settings[field_type]"
                               value="select"
                               class="wct-field-type-radio"
                               data-field="2"
                               <?php checked(($checkout_toolkit_field_2_settings['field_type'] ?? 'text') === 'select'); ?>>
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
                           value="<?php echo esc_attr($checkout_toolkit_field_2_settings['field_label'] ?? ''); ?>"
                           class="regular-text">
                </td>
            </tr>

            <!-- Checkbox Label 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-checkbox <?php echo ($checkout_toolkit_field_2_settings['field_type'] ?? '') === 'checkbox' ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_checkbox_label">
                        <?php esc_html_e('Checkbox Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_2_checkbox_label"
                           name="checkout_toolkit_field_2_settings[checkbox_label]"
                           value="<?php echo esc_attr($checkout_toolkit_field_2_settings['checkbox_label'] ?? ''); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Text displayed next to the checkbox.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Select Options 2 (conditional) -->
            <tr class="wct-conditional-field wct-field-2-select <?php echo ($checkout_toolkit_field_2_settings['field_type'] ?? '') === 'select' ? 'active' : ''; ?>">
                <th scope="row">
                    <?php esc_html_e('Dropdown Options', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-select-options-wrapper" id="wct-field-2-options">
                        <div class="wct-select-options-list">
                            <?php
                            $checkout_toolkit_options_2 = $checkout_toolkit_field_2_settings['select_options'] ?? [];
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
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea wct-field-2-select <?php echo in_array($checkout_toolkit_field_2_settings['field_type'] ?? 'text', ['text', 'textarea', 'select'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_placeholder">
                        <?php esc_html_e('Placeholder Text', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="checkout_toolkit_field_2_placeholder"
                           name="checkout_toolkit_field_2_settings[field_placeholder]"
                           value="<?php echo esc_attr($checkout_toolkit_field_2_settings['field_placeholder'] ?? ''); ?>"
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
                               <?php checked(!empty($checkout_toolkit_field_2_settings['required'])); ?>>
                        <?php esc_html_e('Make field required', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Maximum Characters 2 (conditional - for text/textarea only) -->
            <tr class="wct-conditional-field wct-field-2-text wct-field-2-textarea <?php echo in_array($checkout_toolkit_field_2_settings['field_type'] ?? 'text', ['text', 'textarea'], true) ? 'active' : ''; ?>">
                <th scope="row">
                    <label for="checkout_toolkit_field_2_max_length">
                        <?php esc_html_e('Maximum Characters', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="checkout_toolkit_field_2_max_length"
                           name="checkout_toolkit_field_2_settings[max_length]"
                           value="<?php echo esc_attr($checkout_toolkit_field_2_settings['max_length'] ?? 200); ?>"
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
                                    <?php selected($checkout_toolkit_field_2_settings['field_position'] ?? 'woocommerce_after_order_notes', $checkout_toolkit_hook); ?>>
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
                               <?php checked(!empty($checkout_toolkit_field_2_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_field_2_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_field_2_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Visibility Settings 2 -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Field Visibility', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <div class="wct-visibility-options">
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="checkout_toolkit_field_2_settings[visibility_type]"
                                   value="always"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($checkout_toolkit_field_2_settings['visibility_type'] ?? 'always') === 'always'); ?>>
                            <?php esc_html_e('Always show', 'checkout-toolkit-for-woo'); ?>
                        </label>
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="radio"
                                   name="checkout_toolkit_field_2_settings[visibility_type]"
                                   value="products"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($checkout_toolkit_field_2_settings['visibility_type'] ?? 'always') === 'products'); ?>>
                            <?php esc_html_e('Show for specific products', 'checkout-toolkit-for-woo'); ?>
                        </label>
                        <label style="display: block;">
                            <input type="radio"
                                   name="checkout_toolkit_field_2_settings[visibility_type]"
                                   value="categories"
                                   class="wct-visibility-type-radio"
                                   data-field="2"
                                   <?php checked(($checkout_toolkit_field_2_settings['visibility_type'] ?? 'always') === 'categories'); ?>>
                            <?php esc_html_e('Show for specific categories', 'checkout-toolkit-for-woo'); ?>
                        </label>

                        <!-- Product Selection 2 -->
                        <div class="wct-visibility-products wct-visibility-2-products <?php echo ($checkout_toolkit_field_2_settings['visibility_type'] ?? '') === 'products' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Products:', 'checkout-toolkit-for-woo'); ?></label>
                            <select class="wc-product-search"
                                    multiple="multiple"
                                    style="width: 100%;"
                                    data-placeholder="<?php esc_attr_e('Search for products...', 'checkout-toolkit-for-woo'); ?>"
                                    data-action="woocommerce_json_search_products"
                                    name="checkout_toolkit_field_2_settings[visibility_products][]">
                                <?php
                                $checkout_toolkit_selected_products_2 = $checkout_toolkit_field_2_settings['visibility_products'] ?? [];
                                foreach ($checkout_toolkit_selected_products_2 as $checkout_toolkit_product_id) :
                                    $checkout_toolkit_product = wc_get_product($checkout_toolkit_product_id);
                                    if ($checkout_toolkit_product) :
                                ?>
                                    <option value="<?php echo esc_attr($checkout_toolkit_product_id); ?>" selected>
                                        <?php echo esc_html($checkout_toolkit_product->get_formatted_name()); ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>

                        <!-- Category Selection 2 -->
                        <div class="wct-visibility-categories wct-visibility-2-categories <?php echo ($checkout_toolkit_field_2_settings['visibility_type'] ?? '') === 'categories' ? 'active' : ''; ?>">
                            <label><?php esc_html_e('Select Categories:', 'checkout-toolkit-for-woo'); ?></label>
                            <div class="wct-category-checkboxes">
                                <?php
                                $checkout_toolkit_selected_cats_2 = $checkout_toolkit_field_2_settings['visibility_categories'] ?? [];
                                foreach ($checkout_toolkit_product_categories as $checkout_toolkit_cat) :
                                ?>
                                <label>
                                    <input type="checkbox"
                                           name="checkout_toolkit_field_2_settings[visibility_categories][]"
                                           value="<?php echo esc_attr($checkout_toolkit_cat->term_id); ?>"
                                           <?php checked(in_array($checkout_toolkit_cat->term_id, $checkout_toolkit_selected_cats_2)); ?>>
                                    <?php echo esc_html($checkout_toolkit_cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Visibility Mode 2 -->
                        <div class="wct-visibility-mode wct-visibility-2-mode" style="<?php echo ($checkout_toolkit_field_2_settings['visibility_type'] ?? 'always') === 'always' ? 'display:none;' : ''; ?>">
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="radio"
                                       name="checkout_toolkit_field_2_settings[visibility_mode]"
                                       value="show"
                                       <?php checked(($checkout_toolkit_field_2_settings['visibility_mode'] ?? 'show') === 'show'); ?>>
                                <?php esc_html_e('Show field when product/category is in cart', 'checkout-toolkit-for-woo'); ?>
                            </label>
                            <label style="display: block;">
                                <input type="radio"
                                       name="checkout_toolkit_field_2_settings[visibility_mode]"
                                       value="hide"
                                       <?php checked(($checkout_toolkit_field_2_settings['visibility_mode'] ?? 'show') === 'hide'); ?>>
                                <?php esc_html_e('Hide field when product/category is in cart', 'checkout-toolkit-for-woo'); ?>
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
