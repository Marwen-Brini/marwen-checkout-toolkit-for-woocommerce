<?php
/**
 * Delivery list table
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

defined('ABSPATH') || exit;

// Load WP_List_Table if not already loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class DeliveryList
 *
 * WP_List_Table for displaying deliveries.
 */
class DeliveryList extends \WP_List_Table
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'delivery',
            'plural' => 'deliveries',
            'ajax' => false,
        ]);
    }

    /**
     * Get columns
     *
     * @return array Column definitions.
     */
    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox" />',
            'order' => __('Order', 'marwen-marwchto-for-woocommerce'),
            'customer' => __('Customer', 'marwen-marwchto-for-woocommerce'),
            'delivery_date' => __('Delivery Date', 'marwen-marwchto-for-woocommerce'),
            'delivery_status' => __('Delivery Status', 'marwen-marwchto-for-woocommerce'),
            'order_status' => __('Order Status', 'marwen-marwchto-for-woocommerce'),
            'order_total' => __('Total', 'marwen-marwchto-for-woocommerce'),
            'actions' => __('Actions', 'marwen-marwchto-for-woocommerce'),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array Sortable column definitions.
     */
    public function get_sortable_columns(): array
    {
        return [
            'order' => ['order', false],
            'delivery_date' => ['delivery_date', true],
            'order_status' => ['order_status', false],
        ];
    }

    /**
     * Get bulk actions
     *
     * @return array Bulk action definitions.
     */
    public function get_bulk_actions(): array
    {
        $actions = [];

        foreach (DeliveryStatus::get_statuses() as $key => $label) {
            $actions['set_status_' . $key] = sprintf(
                /* translators: %s: Status label */
                __('Set to %s', 'marwen-marwchto-for-woocommerce'),
                $label
            );
        }

        return $actions;
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action(): void
    {
        $action = $this->current_action();

        if (!$action || strpos($action, 'set_status_') !== 0) {
            return;
        }

        // Verify nonce
        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'bulk-deliveries')) {
            return;
        }

        $status = str_replace('set_status_', '', $action);

        if (!DeliveryStatus::is_valid($status)) {
            return;
        }

        $order_ids = isset($_REQUEST['order_ids']) ? array_map('absint', (array) $_REQUEST['order_ids']) : [];

        if (empty($order_ids)) {
            return;
        }

        $manager = new DeliveryManager();
        $updated = 0;

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($order && $manager->update_status($order, $status)) {
                $updated++;
            }
        }

        // Add admin notice
        add_action('admin_notices', function () use ($updated) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(
                    /* translators: %d: Number of orders updated */
                    esc_html__('%d delivery status(es) updated.', 'marwen-marwchto-for-woocommerce'),
                    (int) $updated
                )
            );
        });
    }

    /**
     * Verify filter nonce
     *
     * @return bool Whether nonce is valid.
     */
    private function verify_filter_nonce(): bool
    {
        // No filter parameters means no nonce needed
        if (!isset($_GET['filter_date']) && !isset($_GET['filter_status']) && !isset($_GET['filter_order_status']) && !isset($_GET['orderby']) && !isset($_GET['order'])) {
            return true;
        }

        $nonce = isset($_GET['wct_filter_nonce']) ? sanitize_text_field(wp_unslash($_GET['wct_filter_nonce'])) : '';
        return wp_verify_nonce($nonce, 'wct_delivery_list_filter');
    }

    /**
     * Prepare items for display
     */
    public function prepare_items(): void
    {
        // Verify user capability
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Get filter values only if nonce is valid
        $filter_date = '';
        $filter_status = '';
        $filter_order_status = '';

        $has_filters = isset($_GET['filter_date']) || isset($_GET['filter_status']) || isset($_GET['filter_order_status']);
        $nonce_value = isset($_GET['wct_filter_nonce']) ? sanitize_text_field(wp_unslash($_GET['wct_filter_nonce'])) : '';
        $nonce_valid = !$has_filters || wp_verify_nonce($nonce_value, 'wct_delivery_list_filter');

        if ($nonce_valid && isset($_GET['filter_date'])) {
            $filter_date = sanitize_text_field(wp_unslash($_GET['filter_date']));
        }
        if ($nonce_valid && isset($_GET['filter_status'])) {
            $filter_status = sanitize_key(wp_unslash($_GET['filter_status']));
        }
        if ($nonce_valid && isset($_GET['filter_order_status'])) {
            $filter_order_status = sanitize_key(wp_unslash($_GET['filter_order_status']));
        }

        // Build query args
        $args = [
            'limit' => -1,
            'meta_key' => '_wct_delivery_date',
            'meta_compare' => 'EXISTS',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        ];

        // Apply date filter
        if ($filter_date) {
            $date_range = $this->get_date_range($filter_date);
            if ($date_range) {
                $args['meta_query'] = [
                    [
                        'key' => '_wct_delivery_date',
                        'value' => $date_range,
                        'compare' => 'BETWEEN',
                        'type' => 'DATE',
                    ],
                ];
            }
        }

        // Apply order status filter
        if ($filter_order_status) {
            $args['status'] = $filter_order_status;
        } else {
            $args['status'] = ['processing', 'on-hold', 'pending', 'completed'];
        }

        // Get orders
        $orders = wc_get_orders($args);

        // Filter by delivery status (post-query since it's meta)
        if ($filter_status && DeliveryStatus::is_valid($filter_status)) {
            $orders = array_filter($orders, function ($order) use ($filter_status) {
                $status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;
                return $status === $filter_status;
            });
        }

        // Handle sorting (uses same nonce validation as filters)
        $orderby = 'delivery_date';
        $order = 'ASC';

        $has_sort = isset($_GET['orderby']) || isset($_GET['order']);
        $sort_nonce_valid = !$has_sort || wp_verify_nonce($nonce_value, 'wct_delivery_list_filter');

        if ($sort_nonce_valid && isset($_GET['orderby'])) {
            $orderby = sanitize_key(wp_unslash($_GET['orderby']));
        }
        if ($sort_nonce_valid && isset($_GET['order'])) {
            $order_param = sanitize_key(wp_unslash($_GET['order']));
            $order = strtoupper($order_param) === 'DESC' ? 'DESC' : 'ASC';
        }

        usort($orders, function ($a, $b) use ($orderby, $order) {
            $result = 0;

            switch ($orderby) {
                case 'order':
                    $result = $a->get_id() - $b->get_id();
                    break;
                case 'delivery_date':
                    $date_a = $a->get_meta('_wct_delivery_date') ?: '';
                    $date_b = $b->get_meta('_wct_delivery_date') ?: '';
                    $result = strcmp($date_a, $date_b);
                    break;
                case 'order_status':
                    $result = strcmp($a->get_status(), $b->get_status());
                    break;
            }

            return $order === 'DESC' ? -$result : $result;
        });

        // Pagination
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($orders);

        $this->items = array_slice($orders, ($current_page - 1) * $per_page, $per_page);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Get date range from filter value
     *
     * @param string $filter Filter value.
     * @return array|null [start_date, end_date] or null.
     */
    private function get_date_range(string $filter): ?array
    {
        $today = gmdate('Y-m-d');

        switch ($filter) {
            case 'today':
                return [$today, $today];

            case 'tomorrow':
                $tomorrow = gmdate('Y-m-d', strtotime('+1 day'));
                return [$tomorrow, $tomorrow];

            case 'this_week':
                $week_end = gmdate('Y-m-d', strtotime('+7 days'));
                return [$today, $week_end];

            case 'this_month':
                $month_end = gmdate('Y-m-t');
                return [$today, $month_end];

            default:
                // Check for custom date range (YYYY-MM-DD_YYYY-MM-DD)
                if (strpos($filter, '_') !== false) {
                    $parts = explode('_', $filter);
                    if (count($parts) === 2) {
                        return $parts;
                    }
                }
                // Single date
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter)) {
                    return [$filter, $filter];
                }
                return null;
        }
    }

    /**
     * Render checkbox column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_cb($order): string
    {
        return sprintf(
            '<input type="checkbox" name="order_ids[]" value="%d" />',
            $order->get_id()
        );
    }

    /**
     * Render order column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_order(\WC_Order $order): string
    {
        $edit_url = $order->get_edit_order_url();
        return sprintf(
            '<a href="%s"><strong>#%s</strong></a>',
            esc_url($edit_url),
            esc_html($order->get_order_number())
        );
    }

    /**
     * Render customer column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_customer(\WC_Order $order): string
    {
        $name = $order->get_formatted_billing_full_name();
        $email = $order->get_billing_email();

        return sprintf(
            '%s<br><small>%s</small>',
            esc_html($name),
            esc_html($email)
        );
    }

    /**
     * Render delivery date column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_delivery_date(\WC_Order $order): string
    {
        $delivery_date = $order->get_meta('_wct_delivery_date');

        if (!$delivery_date) {
            return '&mdash;';
        }

        try {
            $date = new \DateTime($delivery_date);
            $settings = get_option('marwchto_delivery_settings', []);
            $format = $settings['date_format'] ?? 'F j, Y';
            $formatted = date_i18n($format, $date->getTimestamp());

            // Check if it's today, tomorrow, or in the past
            $today = new \DateTime('today');
            $tomorrow = new \DateTime('tomorrow');

            $class = '';
            $badge = '';

            if ($date < $today) {
                $class = 'marwchto-date-past';
                $badge = '<span class="wct-date-badge past">' . esc_html__('Past', 'marwen-marwchto-for-woocommerce') . '</span>';
            } elseif ($date->format('Y-m-d') === $today->format('Y-m-d')) {
                $class = 'marwchto-date-today';
                $badge = '<span class="wct-date-badge today">' . esc_html__('Today', 'marwen-marwchto-for-woocommerce') . '</span>';
            } elseif ($date->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                $class = 'marwchto-date-tomorrow';
                $badge = '<span class="wct-date-badge tomorrow">' . esc_html__('Tomorrow', 'marwen-marwchto-for-woocommerce') . '</span>';
            }

            return sprintf(
                '<span class="%s">%s</span> %s',
                esc_attr($class),
                esc_html($formatted),
                $badge
            );
        } catch (\Exception $e) {
            return esc_html($delivery_date);
        }
    }

    /**
     * Render delivery status column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_delivery_status(\WC_Order $order): string
    {
        $status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;

        return sprintf(
            '<div class="wct-status-wrapper" data-order-id="%d">%s %s</div>',
            $order->get_id(),
            DeliveryStatus::get_badge_html($status),
            DeliveryStatus::get_dropdown_html($status, 'delivery_status_' . $order->get_id(), 'marwchto-quick-status-' . $order->get_id())
        );
    }

    /**
     * Render order status column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_order_status(\WC_Order $order): string
    {
        $status = $order->get_status();
        $status_name = wc_get_order_status_name($status);

        return sprintf(
            '<mark class="order-status status-%s"><span>%s</span></mark>',
            esc_attr($status),
            esc_html($status_name)
        );
    }

    /**
     * Render order total column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_order_total(\WC_Order $order): string
    {
        return $order->get_formatted_order_total();
    }

    /**
     * Render actions column
     *
     * @param \WC_Order $order Order object.
     * @return string Column content.
     */
    public function column_actions(\WC_Order $order): string
    {
        $actions = [];

        $actions['view'] = sprintf(
            '<a href="%s" class="button button-small" title="%s">%s</a>',
            esc_url($order->get_edit_order_url()),
            esc_attr__('View Order', 'marwen-marwchto-for-woocommerce'),
            esc_html__('View', 'marwen-marwchto-for-woocommerce')
        );

        return implode(' ', $actions);
    }

    /**
     * Display extra table navigation (filters)
     *
     * @param string $which Top or bottom.
     */
    public function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        // Get current filter values for display
        $filter_date = '';
        $filter_status = '';
        $filter_order_status = '';

        $has_filters = isset($_GET['filter_date']) || isset($_GET['filter_status']) || isset($_GET['filter_order_status']);
        $nonce_value = isset($_GET['wct_filter_nonce']) ? sanitize_text_field(wp_unslash($_GET['wct_filter_nonce'])) : '';
        $nonce_valid = !$has_filters || wp_verify_nonce($nonce_value, 'wct_delivery_list_filter');

        if ($nonce_valid && isset($_GET['filter_date'])) {
            $filter_date = sanitize_text_field(wp_unslash($_GET['filter_date']));
        }
        if ($nonce_valid && isset($_GET['filter_status'])) {
            $filter_status = sanitize_key(wp_unslash($_GET['filter_status']));
        }
        if ($nonce_valid && isset($_GET['filter_order_status'])) {
            $filter_order_status = sanitize_key(wp_unslash($_GET['filter_order_status']));
        }

        echo '<div class="alignleft actions">';

        // Add nonce field for filter form
        wp_nonce_field('wct_delivery_list_filter', 'wct_filter_nonce', false);

        // Date filter
        echo '<select name="filter_date" id="filter_date">';
        echo '<option value="">' . esc_html__('All dates', 'marwen-marwchto-for-woocommerce') . '</option>';
        echo '<option value="today"' . selected($filter_date, 'today', false) . '>' . esc_html__('Today', 'marwen-marwchto-for-woocommerce') . '</option>';
        echo '<option value="tomorrow"' . selected($filter_date, 'tomorrow', false) . '>' . esc_html__('Tomorrow', 'marwen-marwchto-for-woocommerce') . '</option>';
        echo '<option value="this_week"' . selected($filter_date, 'this_week', false) . '>' . esc_html__('This week', 'marwen-marwchto-for-woocommerce') . '</option>';
        echo '<option value="this_month"' . selected($filter_date, 'this_month', false) . '>' . esc_html__('This month', 'marwen-marwchto-for-woocommerce') . '</option>';
        echo '</select>';

        // Delivery status filter
        echo '<select name="filter_status" id="filter_status">';
        echo '<option value="">' . esc_html__('All statuses', 'marwen-marwchto-for-woocommerce') . '</option>';
        foreach (DeliveryStatus::get_statuses() as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($filter_status, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';

        // Order status filter
        echo '<select name="filter_order_status" id="filter_order_status">';
        echo '<option value="">' . esc_html__('All order statuses', 'marwen-marwchto-for-woocommerce') . '</option>';
        foreach (wc_get_order_statuses() as $key => $label) {
            $key = str_replace('wc-', '', $key);
            echo '<option value="' . esc_attr($key) . '"' . selected($filter_order_status, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';

        submit_button(__('Filter', 'marwen-marwchto-for-woocommerce'), '', 'filter_action', false);

        echo '</div>';
    }

    /**
     * Message for no items
     */
    public function no_items(): void
    {
        esc_html_e('No deliveries found.', 'marwen-marwchto-for-woocommerce');
    }
}
