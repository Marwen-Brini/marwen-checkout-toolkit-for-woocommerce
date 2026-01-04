<?php
/**
 * Store locations settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $checkout_toolkit_store_locations_settings Store locations settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="wct-settings-section">
    <h2><?php esc_html_e('Store Location Selector', 'checkout-toolkit-for-woo'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to select a pickup location when they choose "Pickup" as their fulfillment method.', 'checkout-toolkit-for-woo'); ?>
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
                               name="checkout_toolkit_store_locations_settings[enabled]"
                               id="wct-sl-enabled"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_store_locations_settings['enabled'])); ?>>
                        <?php esc_html_e('Show store location selector on checkout', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Note: This field only appears when customer selects "Pickup" (hidden for Delivery orders).', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>
        </tbody>

        <tbody id="wct-sl-options" class="<?php echo empty($checkout_toolkit_store_locations_settings['enabled']) ? 'wct-field-options-disabled' : ''; ?>">
            <!-- Required -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="checkout_toolkit_store_locations_settings[required]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_store_locations_settings['required'])); ?>>
                        <?php esc_html_e('Require customers to select a pickup location', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="wct_sl_field_label">
                        <?php esc_html_e('Field Label', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="wct_sl_field_label"
                           name="checkout_toolkit_store_locations_settings[field_label]"
                           value="<?php echo esc_attr($checkout_toolkit_store_locations_settings['field_label'] ?? 'Pickup Location'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label shown above the location dropdown.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                </td>
            </tr>

            <!-- Store Locations -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Store Locations', 'checkout-toolkit-for-woo'); ?>
                </th>
                <td>
                    <p class="description" style="margin-bottom: 10px;">
                        <?php esc_html_e('Configure the pickup locations customers can choose from.', 'checkout-toolkit-for-woo'); ?>
                    </p>
                    <div class="wct-locations-wrapper">
                        <div class="wct-locations-list">
                            <?php
                            $checkout_toolkit_locations = $checkout_toolkit_store_locations_settings['locations'] ?? [];
                            if (empty($checkout_toolkit_locations)) {
                                $checkout_toolkit_locations = [
                                    [
                                        'id' => '',
                                        'name' => '',
                                        'address' => '',
                                        'phone' => '',
                                        'hours' => '',
                                    ],
                                ];
                            }
                            foreach ($checkout_toolkit_locations as $checkout_toolkit_index => $checkout_toolkit_location) :
                            ?>
                            <div class="wct-location-row">
                                <div class="wct-location-header">
                                    <h4><?php esc_html_e('Location', 'checkout-toolkit-for-woo'); ?> <span class="wct-location-number"><?php echo esc_html($checkout_toolkit_index + 1); ?></span></h4>
                                    <a href="#" class="button-link-delete wct-remove-location" title="<?php esc_attr_e('Remove location', 'checkout-toolkit-for-woo'); ?>">
                                        <?php esc_html_e('Remove', 'checkout-toolkit-for-woo'); ?>
                                    </a>
                                </div>
                                <div class="wct-location-fields">
                                    <div class="wct-location-field">
                                        <label><?php esc_html_e('Location ID', 'checkout-toolkit-for-woo'); ?></label>
                                        <input type="text"
                                               name="checkout_toolkit_store_locations_settings[locations][<?php echo esc_attr($checkout_toolkit_index); ?>][id]"
                                               value="<?php echo esc_attr($checkout_toolkit_location['id'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('e.g., main-store (auto-generated if empty)', 'checkout-toolkit-for-woo'); ?>">
                                    </div>
                                    <div class="wct-location-field">
                                        <label><?php esc_html_e('Store Name', 'checkout-toolkit-for-woo'); ?> <span style="color: #d63638;">*</span></label>
                                        <input type="text"
                                               name="checkout_toolkit_store_locations_settings[locations][<?php echo esc_attr($checkout_toolkit_index); ?>][name]"
                                               value="<?php echo esc_attr($checkout_toolkit_location['name'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Store name (required)', 'checkout-toolkit-for-woo'); ?>"
                                               class="wct-location-name">
                                    </div>
                                    <div class="wct-location-field full-width">
                                        <label><?php esc_html_e('Address', 'checkout-toolkit-for-woo'); ?></label>
                                        <input type="text"
                                               name="checkout_toolkit_store_locations_settings[locations][<?php echo esc_attr($checkout_toolkit_index); ?>][address]"
                                               value="<?php echo esc_attr($checkout_toolkit_location['address'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Full address', 'checkout-toolkit-for-woo'); ?>">
                                    </div>
                                    <div class="wct-location-field">
                                        <label><?php esc_html_e('Phone', 'checkout-toolkit-for-woo'); ?></label>
                                        <input type="text"
                                               name="checkout_toolkit_store_locations_settings[locations][<?php echo esc_attr($checkout_toolkit_index); ?>][phone]"
                                               value="<?php echo esc_attr($checkout_toolkit_location['phone'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Phone number', 'checkout-toolkit-for-woo'); ?>">
                                    </div>
                                    <div class="wct-location-field">
                                        <label><?php esc_html_e('Hours', 'checkout-toolkit-for-woo'); ?></label>
                                        <input type="text"
                                               name="checkout_toolkit_store_locations_settings[locations][<?php echo esc_attr($checkout_toolkit_index); ?>][hours]"
                                               value="<?php echo esc_attr($checkout_toolkit_location['hours'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('e.g., Mon-Fri: 9am-6pm', 'checkout-toolkit-for-woo'); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="wct-add-location" style="margin-top: 15px;">
                            <?php esc_html_e('+ Add Location', 'checkout-toolkit-for-woo'); ?>
                        </button>
                    </div>
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
                               name="checkout_toolkit_store_locations_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_store_locations_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'checkout-toolkit-for-woo'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="checkout_toolkit_store_locations_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($checkout_toolkit_store_locations_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'checkout-toolkit-for-woo'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="wct-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'checkout-toolkit-for-woo'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the store location selector will appear on checkout (when Pickup is selected).', 'checkout-toolkit-for-woo'); ?></p>

        <div class="wct-preview-content" style="margin-top: 15px; max-width: 400px;">
            <label id="wct-preview-field-label" style="display: block; margin-bottom: 8px; font-weight: 600;">
                <?php echo esc_html($checkout_toolkit_store_locations_settings['field_label'] ?? 'Pickup Location'); ?>
            </label>
            <select id="wct-preview-location-select" style="width: 100%; padding: 8px;">
                <option value=""><?php esc_html_e('Select a location...', 'checkout-toolkit-for-woo'); ?></option>
                <?php
                $checkout_toolkit_preview_locations = $checkout_toolkit_store_locations_settings['locations'] ?? [];
                foreach ($checkout_toolkit_preview_locations as $checkout_toolkit_location) :
                    if (!empty($checkout_toolkit_location['name'])) :
                ?>
                    <option value="<?php echo esc_attr($checkout_toolkit_location['id'] ?? ''); ?>">
                        <?php echo esc_html($checkout_toolkit_location['name']); ?>
                    </option>
                <?php
                    endif;
                endforeach;
                ?>
            </select>
        </div>
    </div>
</div>
