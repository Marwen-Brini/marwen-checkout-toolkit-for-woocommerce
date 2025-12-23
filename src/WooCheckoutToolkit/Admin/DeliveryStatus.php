<?php
/**
 * Delivery status constants and helpers
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Admin;

defined('ABSPATH') || exit;

/**
 * Class DeliveryStatus
 *
 * Handles delivery status constants, colors, and label helpers.
 */
class DeliveryStatus
{
    /**
     * Status constants
     */
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const FAILED = 'failed';

    /**
     * Get all available statuses
     *
     * @return array<string, string> Status key => label pairs
     */
    public static function get_statuses(): array
    {
        $statuses = [
            self::PENDING => __('Pending', 'checkout-toolkit-for-woo'),
            self::CONFIRMED => __('Confirmed', 'checkout-toolkit-for-woo'),
            self::OUT_FOR_DELIVERY => __('Out for Delivery', 'checkout-toolkit-for-woo'),
            self::DELIVERED => __('Delivered', 'checkout-toolkit-for-woo'),
            self::FAILED => __('Failed', 'checkout-toolkit-for-woo'),
        ];

        /**
         * Filter available delivery statuses.
         *
         * @param array $statuses Status key => label pairs.
         */
        return apply_filters('checkout_toolkit_delivery_statuses', $statuses);
    }

    /**
     * Get status label
     *
     * @param string $status Status key.
     * @return string Status label.
     */
    public static function get_label(string $status): string
    {
        $statuses = self::get_statuses();
        return $statuses[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get status colors for badges
     *
     * @return array<string, array{bg: string, text: string}> Status key => color config.
     */
    public static function get_colors(): array
    {
        $colors = [
            self::PENDING => [
                'bg' => '#f0f0f0',
                'text' => '#646970',
            ],
            self::CONFIRMED => [
                'bg' => '#e7f5ff',
                'text' => '#0073aa',
            ],
            self::OUT_FOR_DELIVERY => [
                'bg' => '#fff3cd',
                'text' => '#856404',
            ],
            self::DELIVERED => [
                'bg' => '#d4edda',
                'text' => '#155724',
            ],
            self::FAILED => [
                'bg' => '#f8d7da',
                'text' => '#721c24',
            ],
        ];

        /**
         * Filter delivery status colors.
         *
         * @param array $colors Status key => color config pairs.
         */
        return apply_filters('checkout_toolkit_delivery_status_colors', $colors);
    }

    /**
     * Get color config for a specific status
     *
     * @param string $status Status key.
     * @return array{bg: string, text: string} Color config.
     */
    public static function get_color(string $status): array
    {
        $colors = self::get_colors();
        return $colors[$status] ?? ['bg' => '#f0f0f0', 'text' => '#646970'];
    }

    /**
     * Check if status is valid
     *
     * @param string $status Status key to check.
     * @return bool True if valid.
     */
    public static function is_valid(string $status): bool
    {
        return array_key_exists($status, self::get_statuses());
    }

    /**
     * Get default status for new orders
     *
     * @return string Default status key.
     */
    public static function get_default(): string
    {
        return self::PENDING;
    }

    /**
     * Get status badge HTML
     *
     * @param string $status Status key.
     * @return string HTML for status badge.
     */
    public static function get_badge_html(string $status): string
    {
        $label = self::get_label($status);
        $color = self::get_color($status);

        return sprintf(
            '<span class="wct-delivery-status-badge wct-status-%s" style="background-color: %s; color: %s; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 500;">%s</span>',
            esc_attr($status),
            esc_attr($color['bg']),
            esc_attr($color['text']),
            esc_html($label)
        );
    }

    /**
     * Get status dropdown HTML
     *
     * @param string $current_status Current status key.
     * @param string $name Input name attribute.
     * @param string $id Input ID attribute.
     * @return string HTML for status dropdown.
     */
    public static function get_dropdown_html(string $current_status, string $name = 'delivery_status', string $id = ''): string
    {
        $statuses = self::get_statuses();
        $id = $id ?: $name;

        $html = sprintf(
            '<select name="%s" id="%s" class="wct-delivery-status-select">',
            esc_attr($name),
            esc_attr($id)
        );

        foreach ($statuses as $key => $label) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($key),
                selected($key, $current_status, false),
                esc_html($label)
            );
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Get email subject for status
     *
     * @param string $status Status key.
     * @param \WC_Order $order Order object.
     * @return string Email subject.
     */
    public static function get_email_subject(string $status, \WC_Order $order): string
    {
        $order_number = $order->get_order_number();

        $subjects = [
            self::PENDING => sprintf(
                /* translators: %s: Order number */
                __('Your order #%s is awaiting delivery confirmation', 'checkout-toolkit-for-woo'),
                $order_number
            ),
            self::CONFIRMED => sprintf(
                /* translators: %s: Order number */
                __('Delivery confirmed for order #%s', 'checkout-toolkit-for-woo'),
                $order_number
            ),
            self::OUT_FOR_DELIVERY => sprintf(
                /* translators: %s: Order number */
                __('Your order #%s is out for delivery', 'checkout-toolkit-for-woo'),
                $order_number
            ),
            self::DELIVERED => sprintf(
                /* translators: %s: Order number */
                __('Your order #%s has been delivered', 'checkout-toolkit-for-woo'),
                $order_number
            ),
            self::FAILED => sprintf(
                /* translators: %s: Order number */
                __('Delivery attempt failed for order #%s', 'checkout-toolkit-for-woo'),
                $order_number
            ),
        ];

        return $subjects[$status] ?? sprintf(
            /* translators: %s: Order number */
            __('Delivery update for order #%s', 'checkout-toolkit-for-woo'),
            $order_number
        );
    }

    /**
     * Get email message for status
     *
     * @param string $status Status key.
     * @param \WC_Order $order Order object.
     * @return string Email message.
     */
    public static function get_email_message(string $status, \WC_Order $order): string
    {
        $delivery_date = $order->get_meta('_wct_delivery_date');
        $formatted_date = '';

        if ($delivery_date) {
            try {
                $date = new \DateTime($delivery_date);
                $settings = get_option('checkout_toolkit_delivery_settings', []);
                $format = $settings['date_format'] ?? 'F j, Y';
                $formatted_date = date_i18n($format, $date->getTimestamp());
            } catch (\Exception $e) {
                $formatted_date = $delivery_date;
            }
        }

        $messages = [
            self::PENDING => __('We have received your order and are preparing it for delivery.', 'checkout-toolkit-for-woo'),
            self::CONFIRMED => sprintf(
                /* translators: %s: Delivery date */
                __('Great news! Your delivery has been confirmed for %s.', 'checkout-toolkit-for-woo'),
                $formatted_date
            ),
            self::OUT_FOR_DELIVERY => __('Your order is now out for delivery and will arrive soon.', 'checkout-toolkit-for-woo'),
            self::DELIVERED => __('Your order has been successfully delivered. Thank you for your purchase!', 'checkout-toolkit-for-woo'),
            self::FAILED => __('Unfortunately, the delivery attempt was unsuccessful. We will contact you to arrange a new delivery time.', 'checkout-toolkit-for-woo'),
        ];

        return $messages[$status] ?? __('There has been an update to your delivery status.', 'checkout-toolkit-for-woo');
    }
}
