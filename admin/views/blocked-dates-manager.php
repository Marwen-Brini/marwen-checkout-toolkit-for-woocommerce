<?php
/**
 * Blocked dates manager template
 *
 * @package WooCheckoutToolkit
 */

defined('ABSPATH') || exit;

$blocked_dates = $delivery_settings['blocked_dates'] ?? [];
?>

<div class="wct-blocked-dates-manager">
    <div class="wct-add-date-row">
        <input type="text"
               id="wct_add_blocked_date"
               class="wct-datepicker-admin"
               placeholder="<?php esc_attr_e('Select date to block', 'woo-checkout-toolkit'); ?>"
               readonly>
        <button type="button" id="wct_add_date_btn" class="button">
            <?php esc_html_e('Add Date', 'woo-checkout-toolkit'); ?>
        </button>
    </div>

    <div id="wct_blocked_dates_list" class="wct-blocked-dates-list">
        <?php if (empty($blocked_dates)) : ?>
            <p class="wct-no-dates"><?php esc_html_e('No dates blocked.', 'woo-checkout-toolkit'); ?></p>
        <?php else : ?>
            <?php foreach ($blocked_dates as $date) : ?>
                <div class="wct-blocked-date-item" data-date="<?php echo esc_attr($date); ?>">
                    <span class="wct-date-display">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($date))); ?>
                    </span>
                    <input type="hidden"
                           name="wct_delivery_settings[blocked_dates][]"
                           value="<?php echo esc_attr($date); ?>">
                    <button type="button" class="wct-remove-date button-link button-link-delete">
                        <?php esc_html_e('Remove', 'woo-checkout-toolkit'); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p class="description">
        <?php esc_html_e('Block specific dates when delivery is not available (e.g., holidays).', 'woo-checkout-toolkit'); ?>
    </p>
</div>
