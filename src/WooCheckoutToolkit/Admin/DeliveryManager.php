<?php
/**
 * Delivery manager
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

use WooCheckoutToolkit\Logger;

defined('ABSPATH') || exit;

/**
 * Class DeliveryManager
 *
 * Main class for managing deliveries in admin.
 */
class DeliveryManager
{
    /**
     * Order meta key for delivery status
     */
    public const META_STATUS = '_wct_delivery_status';

    /**
     * Order meta key for status history
     */
    public const META_HISTORY = '_wct_delivery_status_history';

    /**
     * Initialize delivery manager
     */
    public function init(): void
    {
        // Admin menu
        add_action('admin_menu', [$this, 'add_menu_page'], 20);

        // AJAX handlers
        add_action('wp_ajax_marwchto_update_delivery_status', [$this, 'ajax_update_status']);
        add_action('wp_ajax_marwchto_bulk_update_status', [$this, 'ajax_bulk_update_status']);
        add_action('wp_ajax_marwchto_get_calendar_data', [$this, 'ajax_get_calendar_data']);

        // Set default status when delivery date is saved
        add_action('marwchto_delivery_date_saved', [$this, 'set_default_status'], 10, 2);

        // Add column to orders list
        add_filter('manage_edit-shop_order_columns', [$this, 'add_orders_column']);
        add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'add_orders_column']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'render_orders_column'], 10, 2);
        add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'render_orders_column_hpos'], 10, 2);

        // Dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add deliveries page to admin menu
     */
    public function add_menu_page(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Deliveries', 'marwen-marwchto-for-woocommerce'),
            __('Deliveries', 'marwen-marwchto-for-woocommerce'),
            'manage_woocommerce',
            'marwchto-deliveries',
            [$this, 'render_dashboard']
        );
    }

    /**
     * Render delivery dashboard
     */
    public function render_dashboard(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'marwen-marwchto-for-woocommerce'));
        }

        include MARWCHTO_PLUGIN_DIR . 'admin/views/delivery-dashboard.php';
    }

    /**
     * Enqueue admin assets for delivery management
     */
    public function enqueue_assets(string $hook): void
    {
        // Only load on our pages and order pages
        $allowed_hooks = [
            'woocommerce_page_wct-deliveries',
            'post.php',
            'woocommerce_page_wc-orders',
        ];

        // Check if we're on an allowed page
        $is_allowed = in_array($hook, $allowed_hooks, true);

        // Also check for order edit page
        if ($hook === 'post.php') {
            global $post;
            if (!$post || $post->post_type !== 'shop_order') {
                $is_allowed = false;
            }
        }

        if (!$is_allowed) {
            return;
        }

        wp_enqueue_style(
            'marwchto-delivery-admin',
            MARWCHTO_PLUGIN_URL . 'admin/css/delivery-admin.css',
            [],
            MARWCHTO_VERSION
        );

        wp_enqueue_script(
            'marwchto-delivery-admin',
            MARWCHTO_PLUGIN_URL . 'admin/js/delivery-admin.js',
            ['jquery'],
            MARWCHTO_VERSION,
            true
        );

        wp_localize_script('marwchto-delivery-admin', 'marwchtoDelivery', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('marwchto_delivery_nonce'),
            'statuses' => DeliveryStatus::get_statuses(),
            'colors' => DeliveryStatus::get_colors(),
            'i18n' => [
                'confirmStatusChange' => __('Change delivery status?', 'marwen-marwchto-for-woocommerce'),
                'statusUpdated' => __('Delivery status updated', 'marwen-marwchto-for-woocommerce'),
                'error' => __('An error occurred', 'marwen-marwchto-for-woocommerce'),
                'loading' => __('Updating...', 'marwen-marwchto-for-woocommerce'),
            ],
        ]);
    }

    /**
     * Set default delivery status when delivery date is saved
     *
     * @param int $order_id Order ID.
     * @param string $delivery_date Delivery date.
     */
    public function set_default_status(int $order_id, string $delivery_date): void
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Only set if no status exists yet
        $current_status = $order->get_meta(self::META_STATUS);

        if (empty($current_status)) {
            $this->update_status($order, DeliveryStatus::get_default(), false);
        }
    }

    /**
     * Update delivery status for an order
     *
     * @param \WC_Order $order Order object.
     * @param string $new_status New status key.
     * @param bool $send_email Whether to send customer email.
     * @return bool Success.
     */
    public function update_status(\WC_Order $order, string $new_status, bool $send_email = true): bool
    {
        if (!DeliveryStatus::is_valid($new_status)) {
            Logger::error('Invalid delivery status', ['status' => $new_status]);
            return false;
        }

        $old_status = $order->get_meta(self::META_STATUS) ?: '';

        // Update status
        $order->update_meta_data(self::META_STATUS, $new_status);

        // Add to history
        $history = $order->get_meta(self::META_HISTORY) ?: [];
        $history[] = [
            'status' => $new_status,
            'timestamp' => current_time('timestamp'),
            'user_id' => get_current_user_id(),
        ];
        $order->update_meta_data(self::META_HISTORY, $history);

        $order->save();

        Logger::info('Delivery status updated', [
            'order_id' => $order->get_id(),
            'old_status' => $old_status,
            'new_status' => $new_status,
        ]);

        /**
         * Action fired when delivery status changes.
         *
         * @param int $order_id Order ID.
         * @param string $new_status New status key.
         * @param string $old_status Previous status key.
         */
        do_action('marwchto_delivery_status_changed', $order->get_id(), $new_status, $old_status);

        // Send customer email
        if ($send_email && $old_status !== $new_status) {
            $this->send_status_email($order, $new_status);
        }

        return true;
    }

    /**
     * Send status change email to customer
     *
     * @param \WC_Order $order Order object.
     * @param string $status New status key.
     */
    private function send_status_email(\WC_Order $order, string $status): void
    {
        /**
         * Filter whether to send delivery status email.
         *
         * @param bool $send Whether to send email.
         * @param \WC_Order $order Order object.
         * @param string $status Status key.
         */
        $send = apply_filters('marwchto_send_delivery_status_email', true, $order, $status);

        if (!$send) {
            return;
        }

        /**
         * Action fired before sending delivery status email.
         *
         * @param \WC_Order $order Order object.
         * @param string $status Status key.
         */
        do_action('marwchto_before_delivery_status_email', $order, $status);

        $to = $order->get_billing_email();
        $subject = DeliveryStatus::get_email_subject($status, $order);
        $message = $this->get_email_content($order, $status);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        $mailer = WC()->mailer();
        $wrapped_message = $mailer->wrap_message($subject, $message);

        $mailer->send($to, $subject, $wrapped_message, $headers);

        Logger::info('Delivery status email sent', [
            'order_id' => $order->get_id(),
            'status' => $status,
            'to' => $to,
        ]);
    }

    /**
     * Get email content for status
     *
     * @param \WC_Order $order Order object.
     * @param string $status Status key.
     * @return string Email HTML content.
     */
    private function get_email_content(\WC_Order $order, string $status): string
    {
        $template_path = MARWCHTO_PLUGIN_DIR . 'templates/emails/delivery-status-change.php';

        if (!file_exists($template_path)) {
            // Fallback content
            return sprintf(
                '<p>%s</p><p>%s</p>',
                DeliveryStatus::get_email_message($status, $order),
                sprintf(
                    /* translators: %s: Order number */
                    __('Order number: %s', 'marwen-marwchto-for-woocommerce'),
                    $order->get_order_number()
                )
            );
        }

        ob_start();
        $marwchto_order = $order;
        $marwchto_status = $status;
        $marwchto_status_label = DeliveryStatus::get_label($status);
        $marwchto_message = DeliveryStatus::get_email_message($status, $order);
        include $template_path;
        return ob_get_clean();
    }

    /**
     * AJAX handler for updating delivery status
     */
    public function ajax_update_status(): void
    {
        check_ajax_referer('marwchto_delivery_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied', 'marwen-marwchto-for-woocommerce')]);
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        if (!$order_id || !$status) {
            wp_send_json_error(['message' => __('Invalid request', 'marwen-marwchto-for-woocommerce')]);
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(['message' => __('Order not found', 'marwen-marwchto-for-woocommerce')]);
        }

        $success = $this->update_status($order, $status);

        if ($success) {
            wp_send_json_success([
                'message' => __('Delivery status updated', 'marwen-marwchto-for-woocommerce'),
                'status' => $status,
                'badge' => DeliveryStatus::get_badge_html($status),
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to update status', 'marwen-marwchto-for-woocommerce')]);
        }
    }

    /**
     * AJAX handler for bulk status update
     */
    public function ajax_bulk_update_status(): void
    {
        check_ajax_referer('marwchto_delivery_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied', 'marwen-marwchto-for-woocommerce')]);
        }

        $order_ids = isset($_POST['order_ids']) ? array_map('absint', (array) $_POST['order_ids']) : [];
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        if (empty($order_ids) || !$status) {
            wp_send_json_error(['message' => __('Invalid request', 'marwen-marwchto-for-woocommerce')]);
        }

        $updated = 0;
        $failed = 0;

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($order && $this->update_status($order, $status)) {
                $updated++;
            } else {
                $failed++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %d: Number of orders updated */
                __('%d orders updated', 'marwen-marwchto-for-woocommerce'),
                $updated
            ),
            'updated' => $updated,
            'failed' => $failed,
        ]);
    }

    /**
     * AJAX handler for calendar data
     */
    public function ajax_get_calendar_data(): void
    {
        check_ajax_referer('marwchto_delivery_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied', 'marwen-marwchto-for-woocommerce')]);
        }

        $month = isset($_GET['month']) ? absint($_GET['month']) : (int) gmdate('n');
        $year = isset($_GET['year']) ? absint($_GET['year']) : (int) gmdate('Y');

        $calendar = new DeliveryCalendar();
        $data = $calendar->get_month_data($year, $month);

        wp_send_json_success($data);
    }

    /**
     * Add delivery column to orders list
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function add_orders_column(array $columns): array
    {
        $new_columns = [];

        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;

            // Add after order status column
            if ($key === 'order_status') {
                $new_columns['marwchto_delivery'] = __('Delivery', 'marwen-marwchto-for-woocommerce');
            }
        }

        return $new_columns;
    }

    /**
     * Render delivery column content (legacy)
     *
     * @param string $column Column key.
     * @param int $order_id Order ID.
     */
    public function render_orders_column(string $column, int $order_id): void
    {
        if ($column !== 'marwchto_delivery') {
            return;
        }

        $order = wc_get_order($order_id);
        $this->render_column_content($order);
    }

    /**
     * Render delivery column content (HPOS)
     *
     * @param string $column Column key.
     * @param \WC_Order $order Order object.
     */
    public function render_orders_column_hpos(string $column, \WC_Order $order): void
    {
        if ($column !== 'marwchto_delivery') {
            return;
        }

        $this->render_column_content($order);
    }

    /**
     * Render column content
     *
     * @param \WC_Order|false $order Order object.
     */
    private function render_column_content($order): void
    {
        if (!$order) {
            echo '&mdash;';
            return;
        }

        $delivery_date = $order->get_meta('_wct_delivery_date');

        if (!$delivery_date) {
            echo '&mdash;';
            return;
        }

        $status = $order->get_meta(self::META_STATUS) ?: DeliveryStatus::PENDING;

        // Format date
        try {
            $date = new \DateTime($delivery_date);
            $settings = get_option('marwchto_delivery_settings', []);
            $format = $settings['date_format'] ?? 'M j';
            $formatted_date = date_i18n($format, $date->getTimestamp());
        } catch (\Exception $e) {
            $formatted_date = $delivery_date;
        }

        echo '<div class="wct-delivery-column">';
        echo '<span class="wct-delivery-date">' . esc_html($formatted_date) . '</span><br>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge HTML is escaped in get_badge_html
        echo DeliveryStatus::get_badge_html($status);
        echo '</div>';
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        wp_add_dashboard_widget(
            'marwchto_deliveries_widget',
            __('Upcoming Deliveries', 'marwen-marwchto-for-woocommerce'),
            [$this, 'render_dashboard_widget']
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget(): void
    {
        $today = gmdate('Y-m-d');
        $week_end = gmdate('Y-m-d', strtotime('+7 days'));

        // Get today's deliveries
        $today_orders = $this->get_orders_by_date($today);
        $today_count = count($today_orders);

        // Get this week's deliveries
        $week_orders = $this->get_orders_by_date_range($today, $week_end);
        $week_count = count($week_orders);

        // Count by status for today
        $status_counts = [];
        foreach ($today_orders as $order) {
            $status = $order->get_meta(self::META_STATUS) ?: DeliveryStatus::PENDING;
            $status_counts[$status] = ($status_counts[$status] ?? 0) + 1;
        }

        include MARWCHTO_PLUGIN_DIR . 'admin/views/delivery-widget.php';
    }

    /**
     * Get orders by delivery date
     *
     * @param string $date Date in Y-m-d format.
     * @return \WC_Order[] Orders.
     */
    public function get_orders_by_date(string $date): array
    {
        return wc_get_orders([
            'meta_key' => '_wct_delivery_date',
            'meta_value' => $date,
            'limit' => -1,
            'status' => ['processing', 'on-hold', 'pending', 'completed'],
        ]);
    }

    /**
     * Get orders by date range
     *
     * @param string $start_date Start date in Y-m-d format.
     * @param string $end_date End date in Y-m-d format.
     * @return \WC_Order[] Orders.
     */
    public function get_orders_by_date_range(string $start_date, string $end_date): array
    {
        return wc_get_orders([
            'meta_query' => [
                [
                    'key' => '_wct_delivery_date',
                    'value' => [$start_date, $end_date],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ],
            'limit' => -1,
            'status' => ['processing', 'on-hold', 'pending', 'completed'],
        ]);
    }
}
