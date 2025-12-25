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
        $delivery_method_settings = get_option('checkout_toolkit_delivery_method_settings', []);
        $delivery_instructions_settings = get_option('checkout_toolkit_delivery_instructions_settings', []);
        $time_window_settings = get_option('checkout_toolkit_time_window_settings', []);
        $store_locations_settings = get_option('checkout_toolkit_store_locations_settings', []);
        $delivery_settings = get_option('checkout_toolkit_delivery_settings', []);
        $field_settings = get_option('checkout_toolkit_field_settings', []);
        $field_2_settings = get_option('checkout_toolkit_field_2_settings', []);

        $delivery_method = $order->get_meta('_wct_delivery_method');
        $delivery_instructions_preset = $order->get_meta('_wct_delivery_instructions_preset');
        $delivery_instructions_custom = $order->get_meta('_wct_delivery_instructions_custom');
        $time_window = $order->get_meta('_wct_time_window');
        $store_location = $order->get_meta('_wct_store_location');
        $delivery_date = $order->get_meta('_wct_delivery_date');
        $custom_field = $order->get_meta('_wct_custom_field');
        $custom_field_2 = $order->get_meta('_wct_custom_field_2');

        // Check if we should display in emails
        $show_delivery_method = !empty($delivery_method) && !empty($delivery_method_settings['show_in_emails']);
        $show_store_location = !empty($store_location) && !empty($store_locations_settings['show_in_emails']);
        $show_delivery_instructions = (!empty($delivery_instructions_preset) || !empty($delivery_instructions_custom)) && !empty($delivery_instructions_settings['show_in_emails']);
        $show_time_window = !empty($time_window) && !empty($time_window_settings['show_in_emails']);
        $show_delivery = !empty($delivery_date) && !empty($delivery_settings['show_in_emails']);
        $show_field = !empty($custom_field) && !empty($field_settings['show_in_emails']);
        $show_field_2 = !empty($custom_field_2) && !empty($field_2_settings['show_in_emails']);

        if (!$show_delivery_method && !$show_store_location && !$show_delivery_instructions && !$show_time_window && !$show_delivery && !$show_field && !$show_field_2) {
            return;
        }

        // Get store location details
        $store_location_data = null;
        if ($show_store_location) {
            $store_location_data = $this->get_store_location_by_id($store_location, $store_locations_settings);
        }

        if ($plain_text) {
            $this->render_plain_text(
                $order,
                $delivery_method,
                $delivery_instructions_preset,
                $delivery_instructions_custom,
                $time_window,
                $store_location_data,
                $delivery_date,
                $custom_field,
                $custom_field_2,
                $delivery_method_settings,
                $delivery_instructions_settings,
                $time_window_settings,
                $store_locations_settings,
                $delivery_settings,
                $field_settings,
                $field_2_settings,
                $show_delivery_method,
                $show_store_location,
                $show_delivery_instructions,
                $show_time_window,
                $show_delivery,
                $show_field,
                $show_field_2
            );
        } else {
            $this->render_html(
                $order,
                $delivery_method,
                $delivery_instructions_preset,
                $delivery_instructions_custom,
                $time_window,
                $store_location_data,
                $delivery_date,
                $custom_field,
                $custom_field_2,
                $delivery_method_settings,
                $delivery_instructions_settings,
                $time_window_settings,
                $store_locations_settings,
                $delivery_settings,
                $field_settings,
                $field_2_settings,
                $show_delivery_method,
                $show_store_location,
                $show_delivery_instructions,
                $show_time_window,
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
     * @param \WC_Order   $order                         Order object.
     * @param string|null $delivery_method               Delivery method value.
     * @param string|null $delivery_instructions_preset  Delivery instructions preset.
     * @param string|null $delivery_instructions_custom  Delivery instructions custom.
     * @param string|null $time_window                   Time window value.
     * @param array|null  $store_location_data           Store location data.
     * @param string|null $delivery_date                 Delivery date.
     * @param string|null $custom_field                  Custom field value.
     * @param string|null $custom_field_2                Custom field 2 value.
     * @param array       $delivery_method_settings      Delivery method settings.
     * @param array       $delivery_instructions_settings Delivery instructions settings.
     * @param array       $time_window_settings          Time window settings.
     * @param array       $store_locations_settings      Store locations settings.
     * @param array       $delivery_settings             Delivery settings.
     * @param array       $field_settings                Field settings.
     * @param array       $field_2_settings              Field 2 settings.
     * @param bool        $show_delivery_method          Show delivery method.
     * @param bool        $show_store_location           Show store location.
     * @param bool        $show_delivery_instructions    Show delivery instructions.
     * @param bool        $show_time_window              Show time window.
     * @param bool        $show_delivery                 Show delivery date.
     * @param bool        $show_field                    Show custom field.
     * @param bool        $show_field_2                  Show custom field 2.
     * @param mixed       $email                         Email object.
     */
    private function render_html(
        \WC_Order $order,
        ?string $delivery_method,
        ?string $delivery_instructions_preset,
        ?string $delivery_instructions_custom,
        ?string $time_window,
        ?array $store_location_data,
        ?string $delivery_date,
        ?string $custom_field,
        ?string $custom_field_2,
        array $delivery_method_settings,
        array $delivery_instructions_settings,
        array $time_window_settings,
        array $store_locations_settings,
        array $delivery_settings,
        array $field_settings,
        array $field_2_settings,
        bool $show_delivery_method,
        bool $show_store_location,
        bool $show_delivery_instructions,
        bool $show_time_window,
        bool $show_delivery,
        bool $show_field,
        bool $show_field_2,
        $email
    ): void {
        echo '<h2>' . esc_html__('Additional Order Information', 'checkout-toolkit-for-woo') . '</h2>';
        echo '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #e5e5e5; margin-bottom: 20px;" border="1">';

        if ($show_delivery_method) {
            $label = $delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'checkout-toolkit-for-woo');
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'checkout-toolkit-for-woo'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'checkout-toolkit-for-woo'));

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . esc_html($method_label) . '</td>';
            echo '</tr>';
        }

        if ($show_store_location && $store_location_data) {
            $label = $store_locations_settings['field_label'] ?? __('Pickup Location', 'checkout-toolkit-for-woo');
            $output = '<strong>' . esc_html($store_location_data['name']) . '</strong>';
            if (!empty($store_location_data['address'])) {
                $output .= '<br>' . esc_html($store_location_data['address']);
            }
            if (!empty($store_location_data['phone'])) {
                $output .= '<br>' . esc_html__('Phone:', 'checkout-toolkit-for-woo') . ' ' . esc_html($store_location_data['phone']);
            }
            if (!empty($store_location_data['hours'])) {
                $output .= '<br>' . esc_html__('Hours:', 'checkout-toolkit-for-woo') . ' ' . esc_html($store_location_data['hours']);
            }

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8; vertical-align: top;">' . esc_html($label) . '</th>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $output is already escaped above.
            echo '<td style="text-align: left; padding: 12px;">' . $output . '</td>';
            echo '</tr>';
        }

        if ($show_delivery_instructions) {
            $label = $delivery_instructions_settings['field_label'] ?? __('Delivery Instructions', 'checkout-toolkit-for-woo');
            $output = '';

            if (!empty($delivery_instructions_preset)) {
                $preset_label = $this->get_preset_label($delivery_instructions_preset, $delivery_instructions_settings);
                $output .= '<strong>' . esc_html($preset_label) . '</strong>';
            }

            if (!empty($delivery_instructions_custom)) {
                if (!empty($output)) {
                    $output .= '<br>';
                }
                $output .= nl2br(esc_html($delivery_instructions_custom));
            }

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $output is already escaped above.
            echo '<td style="text-align: left; padding: 12px;">' . $output . '</td>';
            echo '</tr>';
        }

        if ($show_time_window) {
            $label = $time_window_settings['field_label'] ?? __('Preferred Time', 'checkout-toolkit-for-woo');
            $time_label = $this->get_time_slot_label($time_window, $time_window_settings);

            echo '<tr>';
            echo '<th style="text-align: left; padding: 12px; background-color: #f8f8f8;">' . esc_html($label) . '</th>';
            echo '<td style="text-align: left; padding: 12px;">' . esc_html($time_label) . '</td>';
            echo '</tr>';
        }

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
     * @param \WC_Order   $order                         Order object.
     * @param string|null $delivery_method               Delivery method value.
     * @param string|null $delivery_instructions_preset  Delivery instructions preset.
     * @param string|null $delivery_instructions_custom  Delivery instructions custom.
     * @param string|null $time_window                   Time window value.
     * @param array|null  $store_location_data           Store location data.
     * @param string|null $delivery_date                 Delivery date.
     * @param string|null $custom_field                  Custom field value.
     * @param string|null $custom_field_2                Custom field 2 value.
     * @param array       $delivery_method_settings      Delivery method settings.
     * @param array       $delivery_instructions_settings Delivery instructions settings.
     * @param array       $time_window_settings          Time window settings.
     * @param array       $store_locations_settings      Store locations settings.
     * @param array       $delivery_settings             Delivery settings.
     * @param array       $field_settings                Field settings.
     * @param array       $field_2_settings              Field 2 settings.
     * @param bool        $show_delivery_method          Show delivery method.
     * @param bool        $show_store_location           Show store location.
     * @param bool        $show_delivery_instructions    Show delivery instructions.
     * @param bool        $show_time_window              Show time window.
     * @param bool        $show_delivery                 Show delivery date.
     * @param bool        $show_field                    Show custom field.
     * @param bool        $show_field_2                  Show custom field 2.
     */
    private function render_plain_text(
        \WC_Order $order,
        ?string $delivery_method,
        ?string $delivery_instructions_preset,
        ?string $delivery_instructions_custom,
        ?string $time_window,
        ?array $store_location_data,
        ?string $delivery_date,
        ?string $custom_field,
        ?string $custom_field_2,
        array $delivery_method_settings,
        array $delivery_instructions_settings,
        array $time_window_settings,
        array $store_locations_settings,
        array $delivery_settings,
        array $field_settings,
        array $field_2_settings,
        bool $show_delivery_method,
        bool $show_store_location,
        bool $show_delivery_instructions,
        bool $show_time_window,
        bool $show_delivery,
        bool $show_field,
        bool $show_field_2
    ): void {
        // Plain text emails - escaping for safety.
        echo "\n" . esc_html(str_repeat('=', 50)) . "\n";
        echo esc_html(strtoupper(__('Additional Order Information', 'checkout-toolkit-for-woo'))) . "\n";
        echo esc_html(str_repeat('=', 50)) . "\n\n";

        if ($show_delivery_method) {
            $label = $delivery_method_settings['field_label'] ?? __('Fulfillment Method', 'checkout-toolkit-for-woo');
            $method_label = $delivery_method === 'pickup'
                ? ($delivery_method_settings['pickup_label'] ?? __('Pickup', 'checkout-toolkit-for-woo'))
                : ($delivery_method_settings['delivery_label'] ?? __('Delivery', 'checkout-toolkit-for-woo'));

            echo esc_html($label) . ': ' . esc_html($method_label) . "\n";
        }

        if ($show_store_location && $store_location_data) {
            $label = $store_locations_settings['field_label'] ?? __('Pickup Location', 'checkout-toolkit-for-woo');
            echo esc_html($label) . ': ' . esc_html($store_location_data['name']) . "\n";
            if (!empty($store_location_data['address'])) {
                echo '  ' . esc_html__('Address:', 'checkout-toolkit-for-woo') . ' ' . esc_html($store_location_data['address']) . "\n";
            }
            if (!empty($store_location_data['phone'])) {
                echo '  ' . esc_html__('Phone:', 'checkout-toolkit-for-woo') . ' ' . esc_html($store_location_data['phone']) . "\n";
            }
            if (!empty($store_location_data['hours'])) {
                echo '  ' . esc_html__('Hours:', 'checkout-toolkit-for-woo') . ' ' . esc_html($store_location_data['hours']) . "\n";
            }
        }

        if ($show_delivery_instructions) {
            $label = $delivery_instructions_settings['field_label'] ?? __('Delivery Instructions', 'checkout-toolkit-for-woo');
            $output = '';

            if (!empty($delivery_instructions_preset)) {
                $preset_label = $this->get_preset_label($delivery_instructions_preset, $delivery_instructions_settings);
                $output .= $preset_label;
            }

            if (!empty($delivery_instructions_custom)) {
                if (!empty($output)) {
                    $output .= ' - ';
                }
                $output .= $delivery_instructions_custom;
            }

            echo esc_html($label) . ': ' . esc_html($output) . "\n";
        }

        if ($show_time_window) {
            $label = $time_window_settings['field_label'] ?? __('Preferred Time', 'checkout-toolkit-for-woo');
            $time_label = $this->get_time_slot_label($time_window, $time_window_settings);

            echo esc_html($label) . ': ' . esc_html($time_label) . "\n";
        }

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

    /**
     * Get preset label by value
     *
     * @param string $value    Preset value.
     * @param array  $settings Delivery instructions settings.
     * @return string Preset label or value if not found.
     */
    private function get_preset_label(string $value, array $settings): string
    {
        $preset_options = $settings['preset_options'] ?? [];

        foreach ($preset_options as $option) {
            if (($option['value'] ?? '') === $value) {
                return $option['label'] ?? $value;
            }
        }

        return $value;
    }

    /**
     * Get time slot label by value
     *
     * @param string $value    Time slot value.
     * @param array  $settings Time window settings.
     * @return string Time slot label or value if not found.
     */
    private function get_time_slot_label(string $value, array $settings): string
    {
        $time_slots = $settings['time_slots'] ?? [];

        foreach ($time_slots as $slot) {
            if (($slot['value'] ?? '') === $value) {
                return $slot['label'] ?? $value;
            }
        }

        return $value;
    }

    /**
     * Get store location by ID
     *
     * @param string $location_id Location ID.
     * @param array  $settings    Store locations settings.
     * @return array|null Location data or null if not found.
     */
    private function get_store_location_by_id(string $location_id, array $settings): ?array
    {
        $locations = $settings['locations'] ?? [];

        foreach ($locations as $location) {
            if (($location['id'] ?? '') === $location_id) {
                return $location;
            }
        }

        return null;
    }
}
