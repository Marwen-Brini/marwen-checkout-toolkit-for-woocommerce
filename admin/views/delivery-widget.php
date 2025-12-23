<?php
/**
 * Delivery dashboard widget view
 *
 * @package WooCheckoutToolkit
 *
 * @var int $today_count Today's delivery count.
 * @var int $week_count This week's delivery count.
 * @var array $status_counts Status counts for today.
 */

defined('ABSPATH') || exit;

use WooCheckoutToolkit\Admin\DeliveryStatus;
?>

<div class="wct-widget-content">
    <div class="wct-widget-stats">
        <div class="wct-widget-stat">
            <span class="stat-number"><?php echo esc_html($today_count); ?></span>
            <span class="stat-label"><?php esc_html_e("Today's Deliveries", 'checkout-toolkit-for-woo'); ?></span>
        </div>
        <div class="wct-widget-stat">
            <span class="stat-number"><?php echo esc_html($week_count); ?></span>
            <span class="stat-label"><?php esc_html_e('This Week', 'checkout-toolkit-for-woo'); ?></span>
        </div>
    </div>

    <?php if ($today_count > 0) : ?>
        <div class="wct-widget-breakdown">
            <h4><?php esc_html_e("Today's Status Breakdown", 'checkout-toolkit-for-woo'); ?></h4>
            <ul class="wct-status-list">
                <?php foreach ($status_counts as $checkout_toolkit_status => $checkout_toolkit_count) : ?>
                    <li>
                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge HTML is escaped in get_badge_html
                        echo DeliveryStatus::get_badge_html($checkout_toolkit_status);
                        ?>
                        <span class="status-count"><?php echo esc_html($checkout_toolkit_count); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="wct-widget-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wct-deliveries&tab=list&filter_date=today')); ?>" class="button">
            <?php esc_html_e("View Today's Deliveries", 'checkout-toolkit-for-woo'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wct-deliveries&tab=calendar')); ?>" class="button">
            <?php esc_html_e('View Calendar', 'checkout-toolkit-for-woo'); ?>
        </a>
    </div>
</div>

<style>
.wct-widget-content {
    padding: 10px 0;
}
.wct-widget-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}
.wct-widget-stat {
    flex: 1;
    text-align: center;
    padding: 10px;
    background: #f6f7f7;
    border-radius: 4px;
}
.wct-widget-stat .stat-number {
    display: block;
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
}
.wct-widget-stat .stat-label {
    display: block;
    font-size: 12px;
    color: #646970;
}
.wct-widget-breakdown h4 {
    margin: 0 0 10px;
    font-size: 13px;
}
.wct-status-list {
    margin: 0;
    padding: 0;
    list-style: none;
}
.wct-status-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f1;
}
.wct-status-list li:last-child {
    border-bottom: none;
}
.wct-widget-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}
.wct-widget-actions .button {
    flex: 1;
    text-align: center;
}
</style>
