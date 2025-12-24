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
     *
     * @param \WC_Order $order         Order object.
     * @param bool      $sent_to_admin Whether sent to admin.
     * @param bool      $plain_text    Whether plain text email.
     * @param mixed     $email         Email object.
     */
    public function add_to_email(\WC_Order $order, bool $sent_to_admin, bool $plain_text, $email): void
    {
        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        // Check if we should display in emails
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_emails']);
        $show_field = !empty($custom_field) && !empty($field_settings['show_in_emails']);
        $show_field_2 = !empty($custom_field_2) && !empty($field_2_settings['show_in_emails']);

        if (!$show_delivery && !$show_field && !$show_field_2) {
            return;
        }

        if ($plain_text) {
            $this->render_plain_text(
                $order,
                $delivery_date,
                $custom_field,
                $custom_field_2,
                $delivery_settings,
                $field_settings,
                $field_2_settings,
                $show_delivery,
                $show_field,
                $show_field_2
            );
        } else {
            $this->render_html(
                $order,
                $delivery_date,
                $custom_field,
                $custom_field_2,
                $delivery_settings,
                $field_settings,
                $field_2_settings,
                $show_delivery,
                $show_field,
                $show_field_2,
                $email
            );
        }
    }

    /**
     * Render HTML email content
     *
     * @param \WC_Order   $order             Order object.
     * @param string|null $delivery_date     Delivery date.
     * @param string|null $custom_field      Custom field value.
     * @param string|null $custom_field_2    Custom field 2 value.
     * @param array       $delivery_settings Delivery settings.
     * @param array       $field_settings    Field settings.
     * @param array       $field_2_settings  Field 2 settings.
     * @param bool        $show_delivery     Show delivery date.
     * @param bool        $show_field        Show custom field.
     * @param bool        $show_field_2      Show custom field 2.
     * @param mixed       $email             Email object.
     */
    private function render_html(
        \WC_Order $order,
        ?string $delivery_date,
        ?string $custom_field,
        ?string $custom_field_2,
        array $delivery_settings,
        array $field_settings,
        array $field_2_settings,
        bool $show_delivery,
        bool $show_field,
        bool $show_field_2,
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
            $output = $this->format_field_value($custom_field, $field_settings);
            $output = apply_filters('checkout_toolkit_email_custom_field', $output, $order, $email);

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . nl2br(esc_html($output)) . '</td>';
            echo '</tr>';
        }

        if ($show_field_2) {
            $label = $field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo');
            $output = $this->format_field_value($custom_field_2, $field_2_settings);
            $output = apply_filters('checkout_toolkit_email_custom_field_2', $output, $order, $email);

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . nl2br(esc_html($output)) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    /**
     * Render plain text email content
     *
     * @param \WC_Order   $order             Order object.
     * @param string|null $delivery_date     Delivery date.
     * @param string|null $custom_field      Custom field value.
     * @param string|null $custom_field_2    Custom field 2 value.
     * @param array       $delivery_settings Delivery settings.
     * @param array       $field_settings    Field settings.
     * @param array       $field_2_settings  Field 2 settings.
     * @param bool        $show_delivery     Show delivery date.
     * @param bool        $show_field        Show custom field.
     * @param bool        $show_field_2      Show custom field 2.
     */
    private function render_plain_text(
        \WC_Order $order,
        ?string $delivery_date,
        ?string $custom_field,
        ?string $custom_field_2,
        array $delivery_settings,
        array $field_settings,
        array $field_2_settings,
        bool $show_delivery,
        bool $show_field,
        bool $show_field_2
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
            $output = $this->format_field_value($custom_field, $field_settings);

            echo esc_html($label) . ': ' . esc_html($output) . "\n";
        }

        if ($show_field_2) {
            $label = $field_2_settings['field_label'] ?? __('Additional Information', 'checkout-toolkit-for-woo');
            $output = $this->format_field_value($custom_field_2, $field_2_settings);

            echo esc_html($label) . ': ' . esc_html($output) . "\n";
        }

        echo "\n";
    }

    /**
     * Format date for display
     *
     * @param string $date   Date string.
     * @param string $format Date format.
     * @return string Formatted date.
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

    /**
     * Format field value based on field type
     *
     * @param string $value    The raw value.
     * @param array  $settings The field settings.
     * @return string Formatted value.
     */
    private function format_field_value(string $value, array $settings): string
    {
        $field_type = $settings['field_type'] ?? 'text';

        switch ($field_type) {
            case 'checkbox':
                return $value === '1'
                    ? __('Yes', 'checkout-toolkit-for-woo')
                    : __('No', 'checkout-toolkit-for-woo');

            case 'select':
                $options = $settings['select_options'] ?? [];
                foreach ($options as $option) {
                    if (($option['value'] ?? '') === $value) {
                        return $option['label'] ?? $value;
                    }
                }
                return $value;

            default:
                return $value;
        }
    }
}
