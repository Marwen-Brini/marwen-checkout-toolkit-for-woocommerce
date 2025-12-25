<?php
/**
 * Blocked dates manager template
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

$checkout_toolkit_blocked_dates = $checkout_toolkit_delivery_settings['blocked_dates'] ?? [];
?>

<div class="wct-blocked-dates-manager">
    <div class="wct-add-date-row">
        <input type="text"
               id="checkout_toolkit_add_blocked_date"
               class="wct-datepicker-admin"
               placeholder="<?php esc_attr_e('Select date to block', 'checkout-toolkit-for-woo'); ?>"
               readonly>
        <button type="button" id="checkout_toolkit_add_date_btn" class="button">
            <?php esc_html_e('Add Date', 'checkout-toolkit-for-woo'); ?>
        </button>
    </div>

    <div id="checkout_toolkit_blocked_dates_list" class="wct-blocked-dates-list">
        <?php if (empty($checkout_toolkit_blocked_dates)) : ?>
            <p class="wct-no-dates"><?php esc_html_e('No dates blocked.', 'checkout-toolkit-for-woo'); ?></p>
        <?php else : ?>
            <?php foreach ($checkout_toolkit_blocked_dates as $checkout_toolkit_date) : ?>
                <div class="wct-blocked-date-item" data-date="<?php echo esc_attr($checkout_toolkit_date); ?>">
                    <span class="wct-date-display">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($checkout_toolkit_date))); ?>
                    </span>
                    <input type="hidden"
                           name="checkout_toolkit_delivery_settings[blocked_dates][]"
                           value="<?php echo esc_attr($checkout_toolkit_date); ?>">
                    <button type="button" class="wct-remove-date button-link button-link-delete">
                        <?php esc_html_e('Remove', 'checkout-toolkit-for-woo'); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p class="description">
        <?php esc_html_e('Block specific dates when delivery is not available (e.g., holidays).', 'checkout-toolkit-for-woo'); ?>
    </p>
</div>
