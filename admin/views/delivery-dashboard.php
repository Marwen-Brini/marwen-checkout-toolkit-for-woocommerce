<?php
/**
 * Delivery dashboard view
 *
 * @package WooCheckoutToolkit
 */

defined('ABSPATH') || exit;

use WooCheckoutToolkit\Admin\DeliveryList;
use WooCheckoutToolkit\Admin\DeliveryCalendar;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation only
$checkout_toolkit_active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'list';

// Calendar month/year
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameters
$checkout_toolkit_calendar_month = isset($_GET['month']) ? absint($_GET['month']) : (int) gmdate('n');
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameters
$checkout_toolkit_calendar_year = isset($_GET['year']) ? absint($_GET['year']) : (int) gmdate('Y');

// Validate month
if ($checkout_toolkit_calendar_month < 1 || $checkout_toolkit_calendar_month > 12) {
    $checkout_toolkit_calendar_month = (int) gmdate('n');
}
?>

<div class="wrap wct-delivery-dashboard">
    <h1 class="wp-heading-inline"><?php esc_html_e('Delivery Management', 'marwen-checkout-toolkit-for-woocommerce'); ?></h1>
    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper wct-delivery-tabs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wct-deliveries&tab=list')); ?>"
           class="nav-tab <?php echo $checkout_toolkit_active_tab === 'list' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Deliveries', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wct-deliveries&tab=calendar')); ?>"
           class="nav-tab <?php echo $checkout_toolkit_active_tab === 'calendar' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Calendar', 'marwen-checkout-toolkit-for-woocommerce'); ?>
        </a>
    </nav>

    <div class="wct-delivery-content">
        <?php if ($checkout_toolkit_active_tab === 'list') : ?>
            <?php
            $checkout_toolkit_list_table = new DeliveryList();
            $checkout_toolkit_list_table->prepare_items();
            ?>

            <form method="get">
                <input type="hidden" name="page" value="wct-deliveries" />
                <input type="hidden" name="tab" value="list" />
                <?php $checkout_toolkit_list_table->display(); ?>
            </form>

        <?php elseif ($checkout_toolkit_active_tab === 'calendar') : ?>
            <?php
            $checkout_toolkit_calendar = new DeliveryCalendar();
            $checkout_toolkit_calendar->render($checkout_toolkit_calendar_year, $checkout_toolkit_calendar_month);
            ?>

        <?php endif; ?>
    </div>
</div>
