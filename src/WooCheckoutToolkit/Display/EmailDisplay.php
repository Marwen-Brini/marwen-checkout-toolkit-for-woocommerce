<?php
/**
 * Email display handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Display;

defined('ABSPATH') || exit;

/**
 * Class EmailDisplay
 *
 * Handles displaying fields in order emails.
 */
class EmailDisplay
{
    /**
     * Initialize email display
     */
    public function init(): void
    {
        add_action('woocommerce_email_after_order_table', [$this, 'add_to_email'], 10, 4);
    }

    /**
     * Add fields to order emails
     */
    public function add_to_email(\WC_Order $order, bool $sent_to_admin, bool $plain_text, $email): void
    {
        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');

        // Check if we should display in emails
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_emails']);
        $show_field = !empty($custom_field) && !empty($field_settings['show_in_emails']);

        if (!$show_delivery && !$show_field) {
            return;
        }

        if ($plain_text) {
            $this->render_plain_text($order, $delivery_date, $custom_field, $delivery_settings, $field_settings, $show_delivery, $show_field);
        } else {
            $this->render_html($order, $delivery_date, $custom_field, $delivery_settings, $field_settings, $show_delivery, $show_field, $email);
        }
    }

    /**
     * Render HTML email content
     */
    private function render_html(
        \WC_Order $order,
        ?string $delivery_date,
        ?string $custom_field,
        array $delivery_settings,
        array $field_settings,
        bool $show_delivery,
        bool $show_field,
        $email
    ): void {
        echo '<h2>' . esc_html__('Additional Order Information', 'checkout-toolkit-for-woo') . '</h2>';
        echo '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #e5e5e5; margin-bottom: 20px;" border="1">';

        if ($show_delivery) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            $label = $delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo');

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . esc_html($formatted_date) . '</td>';
            echo '</tr>';
        }

        if ($show_field) {
            $label = $field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo');
            $output = apply_filters('checkout_toolkit_email_custom_field', $custom_field, $order, $email);

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . nl2br(esc_html($output)) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    /**
     * Render plain text email content
     */
    private function render_plain_text(
        \WC_Order $order,
        ?string $delivery_date,
        ?string $custom_field,
        array $delivery_settings,
        array $field_settings,
        bool $show_delivery,
        bool $show_field
    ): void {
        // Plain text emails - escaping for safety.
        echo "\n" . esc_html(str_repeat('=', 50)) . "\n";
        echo esc_html(strtoupper(__('Additional Order Information', 'checkout-toolkit-for-woo'))) . "\n";
        echo esc_html(str_repeat('=', 50)) . "\n\n";

        if ($show_delivery) {
            $formatted_date = $this->format_date($delivery_date, $delivery_settings['date_format'] ?? 'F j, Y');
            $label = $delivery_settings['field_label'] ?? __('Delivery Date', 'checkout-toolkit-for-woo');

            echo esc_html($label) . ': ' . esc_html($formatted_date) . "\n";
        }

        if ($show_field) {
            $label = $field_settings['field_label'] ?? __('Special Instructions', 'checkout-toolkit-for-woo');

            echo esc_html($label) . ': ' . esc_html($custom_field) . "\n";
        }

        echo "\n";
    }

    /**
     * Format date for display
     */
    private function format_date(string $date, string $format): string
    {
        try {
            $date_obj = new \DateTime($date);
            return date_i18n($format, $date_obj->getTimestamp());
        } catch (\Exception $e) {
            return $date;
        }
    }
}
