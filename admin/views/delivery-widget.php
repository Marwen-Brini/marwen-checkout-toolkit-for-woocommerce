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
            <span class="stat-label"><?php esc_html_e("Today's Deliveries", 'marwen-checkout-toolkit-for-woocommerce'); ?></span>
        </div>
        <div class="wct-widget-stat">
            <span class="stat-number"><?php echo esc_html($week_count); ?></span>
            <span class="stat-label"><?php esc_html_e('This Week', 'marwen-checkout-toolkit-for-woocommerce'); ?></span>
        </div>
    </div>

    <?php if ($today_count > 0) : ?>
        <div class="wct-widget-breakdown">
            <h4><?php esc_html_e("Today's Status Breakdown", 'marwen-checkout-toolkit-for-woocommerce'); ?></h4>
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
            <?php esc_html_e("View Today's Deliveries", 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wct-deliveries&tab=calendar')); ?>" class="button">
            <?php esc_html_e('View Calendar', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
    </div>
</div>
