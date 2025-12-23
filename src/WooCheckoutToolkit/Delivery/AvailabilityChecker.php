<?php
/**
 * Date availability checker
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

defined('ABSPATH') || exit;

/**
 * Class AvailabilityChecker
 *
 * Handles date availability logic.
 */
class AvailabilityChecker
{
    /**
     * Get available dates for the configured date range
     *
     * @param int|null $days_ahead Override max future days
     * @return array Array of available date strings (Y-m-d format)
     */
    public function get_available_dates(?int $days_ahead = null): array
    {
        $settings = $this->get_settings();
        $available = [];

        $start_date = new \DateTime();
        $start_date->modify('+' . ($settings['min_lead_days'] ?? 2) . ' days');

        $end_date = new \DateTime();
        $end_date->modify('+' . ($days_ahead ?? $settings['max_future_days'] ?? 30) . ' days');

        $current = clone $start_date;

        while ($current <= $end_date) {
            $date_string = $current->format('Y-m-d');

            if ($this->is_date_available($date_string)) {
                $available[] = $date_string;
            }

            $current->modify('+1 day');
        }

        return apply_filters('checkout_toolkit_available_dates', $available);
    }

    /**
     * Check if a specific date is available
     *
     * @param string $date Date in Y-m-d format
     * @return bool
     */
    public function is_date_available(string $date): bool
    {
        $settings = $this->get_settings();

        try {
            $date_obj = new \DateTime($date);
        } catch (\Exception $e) {
            return false;
        }

        // Check if day of week is disabled
        $day_of_week = (int) $date_obj->format('w');
        $disabled_weekdays = array_map('intval', $settings['disabled_weekdays'] ?? []);

        if (in_array($day_of_week, $disabled_weekdays, true)) {
            return false;
        }

        // Check if date is in blocked dates
        $blocked_dates = $settings['blocked_dates'] ?? [];

        if (in_array($date, $blocked_dates, true)) {
            return false;
        }

        // Check minimum lead time
        if (!$this->meets_lead_time($date)) {
            return false;
        }

        // Check maximum future date
        if (!$this->within_booking_window($date)) {
            return false;
        }

        return apply_filters('checkout_toolkit_is_date_available', true, $date);
    }

    /**
     * Get blocked dates array
     *
     * @return array
     */
    public function get_blocked_dates(): array
    {
        $settings = $this->get_settings();
        return $settings['blocked_dates'] ?? [];
    }

    /**
     * Get disabled weekdays
     *
     * @return array Array of day numbers (0=Sunday, 6=Saturday)
     */
    public function get_disabled_weekdays(): array
    {
        $settings = $this->get_settings();
        return array_map('intval', $settings['disabled_weekdays'] ?? []);
    }

    /**
     * Check minimum lead time requirement
     *
     * @param string $date Date in Y-m-d format
     * @return bool
     */
    private function meets_lead_time(string $date): bool
    {
        $settings = $this->get_settings();
        $min_lead_days = (int) ($settings['min_lead_days'] ?? 2);

        $min_date = new \DateTime();
        $min_date->modify('+' . $min_lead_days . ' days');
        $min_date->setTime(0, 0, 0);

        try {
            $check_date = new \DateTime($date);
            $check_date->setTime(0, 0, 0);
        } catch (\Exception $e) {
            return false;
        }

        return $check_date >= $min_date;
    }

    /**
     * Check if date is within booking window
     *
     * @param string $date Date in Y-m-d format
     * @return bool
     */
    private function within_booking_window(string $date): bool
    {
        $settings = $this->get_settings();
        $max_future_days = (int) ($settings['max_future_days'] ?? 30);

        $max_date = new \DateTime();
        $max_date->modify('+' . $max_future_days . ' days');
        $max_date->setTime(23, 59, 59);

        try {
            $check_date = new \DateTime($date);
        } catch (\Exception $e) {
            return false;
        }

        return $check_date <= $max_date;
    }

    /**
     * Get delivery settings
     *
     * @return array
     */
    private function get_settings(): array
    {
        return get_option('checkout_toolkit_delivery_settings', []);
    }
}
