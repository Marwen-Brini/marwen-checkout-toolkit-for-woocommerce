<?php
/**
 * Store locations settings template
 *
 * @package CheckoutToolkitForWoo
 *
 * @var array $marwchto_store_locations_settings Store locations settings array.
 */

defined('ABSPATH') || exit;
?>

<div class="marwchto-settings-section">
    <h2><?php esc_html_e('Store Location Selector', 'marwen-marwchto-for-woocommerce'); ?></h2>
    <p class="description">
        <?php esc_html_e('Allow customers to select a pickup location when they choose "Pickup" as their fulfillment method.', 'marwen-marwchto-for-woocommerce'); ?>
    </p>

    <table class="form-table marwchto-settings-table">
        <tbody>
            <!-- Enable Feature -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Enable Feature', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_store_locations_settings[enabled]"
                               id="marwchto-sl-enabled"
                               value="1"
                               <?php checked(!empty($marwchto_store_locations_settings['enabled'])); ?>>
                        <?php esc_html_e('Show store location selector on checkout', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Note: This field only appears when customer selects "Pickup" (hidden for Delivery orders).', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>
        </tbody>

        <tbody id="marwchto-sl-options" class="<?php echo empty($marwchto_store_locations_settings['enabled']) ? 'marwchto-field-options-disabled' : ''; ?>">
            <!-- Required -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Required', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="marwchto_store_locations_settings[required]"
                               value="1"
                               <?php checked(!empty($marwchto_store_locations_settings['required'])); ?>>
                        <?php esc_html_e('Require customers to select a pickup location', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>

            <!-- Field Label -->
            <tr>
                <th scope="row">
                    <label for="marwchto_sl_field_label">
                        <?php esc_html_e('Field Label', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="marwchto_sl_field_label"
                           name="marwchto_store_locations_settings[field_label]"
                           value="<?php echo esc_attr($marwchto_store_locations_settings['field_label'] ?? 'Pickup Location'); ?>"
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Label shown above the location dropdown.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Store Locations -->
            <tr>
                <th scope="row">
                    <?php esc_html_e('Store Locations', 'marwen-marwchto-for-woocommerce'); ?>
                </th>
                <td>
                    <p class="description" style="margin-bottom: 10px;">
                        <?php esc_html_e('Configure the pickup locations customers can choose from.', 'marwen-marwchto-for-woocommerce'); ?>
                    </p>
                    <div class="marwchto-locations-wrapper">
                        <div class="marwchto-locations-list">
                            <?php
                            $marwchto_locations = $marwchto_store_locations_settings['locations'] ?? [];
                            if (empty($marwchto_locations)) {
                                $marwchto_locations = [
                                    [
                                        'id' => '',
                                        'name' => '',
                                        'address' => '',
                                        'phone' => '',
                                        'hours' => '',
                                    ],
                                ];
                            }
                            foreach ($marwchto_locations as $marwchto_index => $marwchto_location) :
                            ?>
                            <div class="marwchto-location-row">
                                <div class="marwchto-location-header">
                                    <h4><?php esc_html_e('Location', 'marwen-marwchto-for-woocommerce'); ?> <span class="marwchto-location-number"><?php echo esc_html($marwchto_index + 1); ?></span></h4>
                                    <a href="#" class="button-link-delete marwchto-remove-location" title="<?php esc_attr_e('Remove location', 'marwen-marwchto-for-woocommerce'); ?>">
                                        <?php esc_html_e('Remove', 'marwen-marwchto-for-woocommerce'); ?>
                                    </a>
                                </div>
                                <div class="marwchto-location-fields">
                                    <div class="marwchto-location-field">
                                        <label><?php esc_html_e('Location ID', 'marwen-marwchto-for-woocommerce'); ?></label>
                                        <input type="text"
                                               name="marwchto_store_locations_settings[locations][<?php echo esc_attr($marwchto_index); ?>][id]"
                                               value="<?php echo esc_attr($marwchto_location['id'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('e.g., main-store (auto-generated if empty)', 'marwen-marwchto-for-woocommerce'); ?>">
                                    </div>
                                    <div class="marwchto-location-field">
                                        <label><?php esc_html_e('Store Name', 'marwen-marwchto-for-woocommerce'); ?> <span style="color: #d63638;">*</span></label>
                                        <input type="text"
                                               name="marwchto_store_locations_settings[locations][<?php echo esc_attr($marwchto_index); ?>][name]"
                                               value="<?php echo esc_attr($marwchto_location['name'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Store name (required)', 'marwen-marwchto-for-woocommerce'); ?>"
                                               class="marwchto-location-name">
                                    </div>
                                    <div class="marwchto-location-field full-width">
                                        <label><?php esc_html_e('Address', 'marwen-marwchto-for-woocommerce'); ?></label>
                                        <input type="text"
                                               name="marwchto_store_locations_settings[locations][<?php echo esc_attr($marwchto_index); ?>][address]"
                                               value="<?php echo esc_attr($marwchto_location['address'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Full address', 'marwen-marwchto-for-woocommerce'); ?>">
                                    </div>
                                    <div class="marwchto-location-field">
                                        <label><?php esc_html_e('Phone', 'marwen-marwchto-for-woocommerce'); ?></label>
                                        <input type="text"
                                               name="marwchto_store_locations_settings[locations][<?php echo esc_attr($marwchto_index); ?>][phone]"
                                               value="<?php echo esc_attr($marwchto_location['phone'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('Phone number', 'marwen-marwchto-for-woocommerce'); ?>">
                                    </div>
                                    <div class="marwchto-location-field">
                                        <label><?php esc_html_e('Hours', 'marwen-marwchto-for-woocommerce'); ?></label>
                                        <input type="text"
                                               name="marwchto_store_locations_settings[locations][<?php echo esc_attr($marwchto_index); ?>][hours]"
                                               value="<?php echo esc_attr($marwchto_location['hours'] ?? ''); ?>"
                                               placeholder="<?php esc_attr_e('e.g., Mon-Fri: 9am-6pm', 'marwen-marwchto-for-woocommerce'); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="marwchto-add-location" style="margin-top: 15px;">
                            <?php esc_html_e('+ Add Location', 'marwen-marwchto-for-woocommerce'); ?>
                        </button>
                    </div>
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
                               name="marwchto_store_locations_settings[show_in_admin]"
                               value="1"
                               <?php checked(!empty($marwchto_store_locations_settings['show_in_admin'])); ?>>
                        <?php esc_html_e('Show in admin order details', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox"
                               name="marwchto_store_locations_settings[show_in_emails]"
                               value="1"
                               <?php checked(!empty($marwchto_store_locations_settings['show_in_emails'])); ?>>
                        <?php esc_html_e('Include in order emails', 'marwen-marwchto-for-woocommerce'); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Preview Section -->
    <div class="marwchto-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Preview', 'marwen-marwchto-for-woocommerce'); ?></h3>
        <p class="description"><?php esc_html_e('This is how the store location selector will appear on checkout (when Pickup is selected).', 'marwen-marwchto-for-woocommerce'); ?></p>

        <div class="marwchto-preview-content" style="margin-top: 15px; max-width: 400px;">
            <label id="marwchto-preview-field-label" style="display: block; margin-bottom: 8px; font-weight: 600;">
                <?php echo esc_html($marwchto_store_locations_settings['field_label'] ?? 'Pickup Location'); ?>
            </label>
            <select id="marwchto-preview-location-select" style="width: 100%; padding: 8px;">
                <option value=""><?php esc_html_e('Select a location...', 'marwen-marwchto-for-woocommerce'); ?></option>
                <?php
                $marwchto_preview_locations = $marwchto_store_locations_settings['locations'] ?? [];
                foreach ($marwchto_preview_locations as $marwchto_location) :
                    if (!empty($marwchto_location['name'])) :
                ?>
                    <option value="<?php echo esc_attr($marwchto_location['id'] ?? ''); ?>">
                        <?php echo esc_html($marwchto_location['name']); ?>
                    </option>
                <?php
                    endif;
                endforeach;
                ?>
            </select>
        </div>
    </div>
</div>
