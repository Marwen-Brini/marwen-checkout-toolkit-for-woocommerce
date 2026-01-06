<?php
/**
 * Delivery calendar data provider
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

defined('ABSPATH') || exit;

/**
 * Class DeliveryCalendar
 *
 * Provides calendar data for delivery management.
 */
class DeliveryCalendar
{
    /**
     * Get calendar data for a month
     *
     * @param int $year Year.
     * @param int $month Month (1-12).
     * @return array Calendar data.
     */
    public function get_month_data(int $year, int $month): array
    {
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = gmdate('Y-m-t', strtotime($start_date));

        // Get all orders with delivery dates in this month
        $orders = wc_get_orders([
            'meta_query' => [
                [
                    'key' => '_marwchto_delivery_date',
                    'value' => [$start_date, $end_date],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ],
            'limit' => -1,
            'status' => ['processing', 'on-hold', 'pending', 'completed'],
        ]);

        // Group by date and status
        $days = [];

        foreach ($orders as $order) {
            $date = $order->get_meta('_marwchto_delivery_date');
            $status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;

            if (!isset($days[$date])) {
                $days[$date] = [
                    'total' => 0,
                    'statuses' => [],
                    'orders' => [],
                ];
            }

            $days[$date]['total']++;
            $days[$date]['statuses'][$status] = ($days[$date]['statuses'][$status] ?? 0) + 1;
            $days[$date]['orders'][] = [
                'id' => $order->get_id(),
                'number' => $order->get_order_number(),
                'status' => $status,
                'customer' => $order->get_formatted_billing_full_name(),
                'total' => $order->get_total(),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => date_i18n('F Y', strtotime($start_date)),
            'first_day' => (int) gmdate('w', strtotime($start_date)),
            'days_in_month' => (int) gmdate('t', strtotime($start_date)),
            'today' => gmdate('Y-m-d'),
            'days' => $days,
            'status_colors' => DeliveryStatus::get_colors(),
            'status_labels' => DeliveryStatus::get_statuses(),
        ];
    }

    /**
     * Get summary statistics
     *
     * @return array Statistics.
     */
    public function get_statistics(): array
    {
        $today = gmdate('Y-m-d');
        $week_end = gmdate('Y-m-d', strtotime('+7 days'));
        $month_end = gmdate('Y-m-t');

        // Get all orders with delivery dates
        $all_orders = wc_get_orders([
            'meta_key' => '_marwchto_delivery_date',
            'meta_compare' => 'EXISTS',
            'limit' => -1,
            'status' => ['processing', 'on-hold', 'pending', 'completed'],
        ]);

        $stats = [
            'today' => [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'out_for_delivery' => 0,
                'delivered' => 0,
                'failed' => 0,
            ],
            'week' => [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'out_for_delivery' => 0,
                'delivered' => 0,
                'failed' => 0,
            ],
            'month' => [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'out_for_delivery' => 0,
                'delivered' => 0,
                'failed' => 0,
            ],
        ];

        foreach ($all_orders as $order) {
            $date = $order->get_meta('_marwchto_delivery_date');
            $status = $order->get_meta(DeliveryManager::META_STATUS) ?: DeliveryStatus::PENDING;

            if ($date === $today) {
                $stats['today']['total']++;
                if (isset($stats['today'][$status])) {
                    $stats['today'][$status]++;
                }
            }

            if ($date >= $today && $date <= $week_end) {
                $stats['week']['total']++;
                if (isset($stats['week'][$status])) {
                    $stats['week'][$status]++;
                }
            }

            if ($date >= $today && $date <= $month_end) {
                $stats['month']['total']++;
                if (isset($stats['month'][$status])) {
                    $stats['month'][$status]++;
                }
            }
        }

        return $stats;
    }

    /**
     * Render calendar HTML
     *
     * @param int $year Year.
     * @param int $month Month.
     */
    public function render(int $year, int $month): void
    {
        $data = $this->get_month_data($year, $month);

        // Calculate previous and next month
        $prev_month = $month - 1;
        $prev_year = $year;
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year--;
        }

        $next_month = $month + 1;
        $next_year = $year;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }

        $base_url = admin_url('admin.php?page=marwchto-deliveries&tab=calendar');
        ?>
        <div class="marwchto-calendar-wrapper">
            <div class="marwchto-calendar-header">
                <a href="<?php echo esc_url(add_query_arg(['year' => $prev_year, 'month' => $prev_month], $base_url)); ?>" class="marwchto-calendar-nav prev">
                    &laquo; <?php esc_html_e('Previous', 'marwen-marwchto-for-woocommerce'); ?>
                </a>
                <h2 class="marwchto-calendar-title"><?php echo esc_html($data['month_name']); ?></h2>
                <a href="<?php echo esc_url(add_query_arg(['year' => $next_year, 'month' => $next_month], $base_url)); ?>" class="marwchto-calendar-nav next">
                    <?php esc_html_e('Next', 'marwen-marwchto-for-woocommerce'); ?> &raquo;
                </a>
            </div>

            <table class="marwchto-calendar">
                <thead>
                    <tr>
                        <?php
                        $days_of_week = [
                            __('Sun', 'marwen-marwchto-for-woocommerce'),
                            __('Mon', 'marwen-marwchto-for-woocommerce'),
                            __('Tue', 'marwen-marwchto-for-woocommerce'),
                            __('Wed', 'marwen-marwchto-for-woocommerce'),
                            __('Thu', 'marwen-marwchto-for-woocommerce'),
                            __('Fri', 'marwen-marwchto-for-woocommerce'),
                            __('Sat', 'marwen-marwchto-for-woocommerce'),
                        ];
                        foreach ($days_of_week as $day_name) {
                            echo '<th>' . esc_html($day_name) . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day = 1;
                    $started = false;

                    for ($row = 0; $row < 6; $row++) {
                        if ($day > $data['days_in_month']) {
                            break;
                        }

                        echo '<tr>';

                        for ($col = 0; $col < 7; $col++) {
                            if (!$started && $col === $data['first_day']) {
                                $started = true;
                            }

                            if (!$started || $day > $data['days_in_month']) {
                                echo '<td class="marwchto-calendar-empty"></td>';
                            } else {
                                $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $day_data = $data['days'][$date_str] ?? null;
                                $is_today = $date_str === $data['today'];
                                $is_past = $date_str < $data['today'];

                                $classes = ['marwchto-calendar-day'];
                                if ($is_today) {
                                    $classes[] = 'today';
                                }
                                if ($is_past) {
                                    $classes[] = 'past';
                                }
                                if ($day_data) {
                                    $classes[] = 'has-deliveries';
                                }

                                echo '<td class="' . esc_attr(implode(' ', $classes)) . '" data-date="' . esc_attr($date_str) . '">';
                                echo '<span class="day-number">' . esc_html($day) . '</span>';

                                if ($day_data) {
                                    $list_url = admin_url('admin.php?page=marwchto-deliveries&tab=list&filter_date=' . $date_str);
                                    echo '<a href="' . esc_url($list_url) . '" class="marwchto-calendar-count">';
                                    echo '<span class="count">' . esc_html($day_data['total']) . '</span>';
                                    echo '<span class="label">' . esc_html(_n('delivery', 'deliveries', $day_data['total'], 'marwen-marwchto-for-woocommerce')) . '</span>';
                                    echo '</a>';

                                    // Status breakdown dots
                                    echo '<div class="marwchto-calendar-statuses">';
                                    foreach ($day_data['statuses'] as $status => $count) {
                                        $color = $data['status_colors'][$status] ?? ['bg' => '#ccc'];
                                        echo '<span class="status-dot" style="background-color: ' . esc_attr($color['bg']) . ';" title="' . esc_attr($count . ' ' . ($data['status_labels'][$status] ?? $status)) . '"></span>';
                                    }
                                    echo '</div>';
                                }

                                echo '</td>';
                                $day++;
                            }
                        }

                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="marwchto-calendar-legend">
                <span class="legend-title"><?php esc_html_e('Status:', 'marwen-marwchto-for-woocommerce'); ?></span>
                <?php foreach ($data['status_colors'] as $status => $colors) : ?>
                    <span class="legend-item">
                        <span class="legend-dot" style="background-color: <?php echo esc_attr($colors['bg']); ?>;"></span>
                        <?php echo esc_html($data['status_labels'][$status] ?? $status); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
