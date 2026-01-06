<?php
/**
 * Blocked dates manager template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$marwchto_blocked_dates = $marwchto_delivery_settings['blocked_dates'] ?? [];
?>

<div class="marwchto-blocked-dates-manager">
    <div class="marwchto-add-date-row">
        <input type="text"
               id="marwchto_add_blocked_date"
               class="marwchto-datepicker-admin"
               placeholder="<?php esc_attr_e('Select date to block', 'marwen-checkout-toolkit-for-woocommerce'); ?>"
               readonly>
        <button type="button" id="marwchto_add_date_btn" class="button">
            <?php esc_html_e('Add Date', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </button>
    </div>

    <div id="marwchto_blocked_dates_list" class="marwchto-blocked-dates-list">
        <?php if (empty($marwchto_blocked_dates)) : ?>
            <p class="marwchto-no-dates"><?php esc_html_e('No dates blocked.', 'marwen-checkout-toolkit-for-woocommerce'); ?></p>
        <?php else : ?>
            <?php foreach ($marwchto_blocked_dates as $marwchto_date) : ?>
                <div class="marwchto-blocked-date-item" data-date="<?php echo esc_attr($marwchto_date); ?>">
                    <span class="marwchto-date-display">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($marwchto_date))); ?>
                    </span>
                    <input type="hidden"
                           name="marwchto_delivery_settings[blocked_dates][]"
                           value="<?php echo esc_attr($marwchto_date); ?>">
                    <button type="button" class="marwchto-remove-date button-link button-link-delete">
                        <?php esc_html_e('Remove', 'marwen-checkout-toolkit-for-woocommerce'); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p class="description">
        <?php esc_html_e('Block specific dates when delivery is not available (e.g., holidays).', 'marwen-checkout-toolkit-for-woocommerce'); ?>
    </p>
</div>
