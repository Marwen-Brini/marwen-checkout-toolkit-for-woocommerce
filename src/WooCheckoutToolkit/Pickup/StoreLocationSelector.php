<?php
/**
 * Store Location Selector Field Handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Pickup;

use WooCheckoutToolkit\CheckoutDetector;
use WooCheckoutToolkit\Main;

defined('ABSPATH') || exit;

/**
 * Class StoreLocationSelector
 *
 * Handles the store location selector field on checkout.
 * Only visible when pickup is selected (hidden for delivery).
 * This is the OPPOSITE of DeliveryInstructions visibility.
 */
class StoreLocationSelector
{
    /**
     * Settings
     */
    private array $settings;

    /**
     * Initialize the class
     */
    public function init(): void
    {
        $this->settings = $this->get_settings();

        if (empty($this->settings['enabled'])) {
            return;
        }

        // Add field to checkout (classic checkout) - after delivery method
        add_action('woocommerce_before_order_notes', [$this, 'render_store_location_field'], 5);

        // Validate field
        add_action('woocommerce_checkout_process', [$this, 'validate_field']);

        // Save field to order
        add_action('woocommerce_checkout_create_order', [$this, 'save_field'], 10, 2);
    }

    /**
     * Render store location selector field
     */
    public function render_store_location_field(): void
    {
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if there are any locations configured
        $locations = $settings['locations'] ?? [];
        if (empty($locations)) {
            return;
        }

        $show = apply_filters('checkout_toolkit_show_store_location', true, WC()->cart);

        if (!$show) {
            return;
        }

        // Get current delivery method to determine initial visibility
        $delivery_method_settings = Main::get_instance()->get_delivery_method_settings();
        $current_method = WC()->checkout->get_value('checkout_toolkit_delivery_method');
        if (empty($current_method)) {
            $current_method = $delivery_method_settings['default_method'] ?? 'delivery';
        }

        // Determine if delivery method feature is enabled
        $delivery_method_enabled = !empty($delivery_method_settings['enabled']);

        // OPPOSITE visibility: Show when pickup, hide when delivery
        // If delivery method is not enabled, hide (assume delivery means no pickup)
        $initial_display = 'none';
        if ($delivery_method_enabled && $current_method === 'pickup') {
            $initial_display = 'block';
        }

        $current_location = WC()->checkout->get_value('checkout_toolkit_store_location') ?: '';

        $required = !empty($settings['required']);
        $required_attr = $required ? ' required' : '';
        $required_mark = $required ? '<abbr class="required" title="' . esc_attr__('required', 'checkout-toolkit-for-woo') . '">*</abbr>' : '';

        do_action('checkout_toolkit_before_store_location');
        ?>
        <div class="wct-store-location-wrapper" id="wct-store-location-wrapper" style="display: <?php echo esc_attr($initial_display); ?>;">
            <p class="form-row form-row-wide">
                <label for="checkout_toolkit_store_location">
                    <?php echo esc_html($settings['field_label'] ?: __('Pickup Location', 'checkout-toolkit-for-woo')); ?>
                    <?php echo wp_kses_post($required_mark); ?>
                </label>
                <select name="checkout_toolkit_store_location"
                        id="checkout_toolkit_store_location"
                        class="wct-store-location-select"
                        <?php echo esc_attr($required_attr); ?>>
                    <option value=""><?php esc_html_e('Select a location...', 'checkout-toolkit-for-woo'); ?></option>
                    <?php foreach ($locations as $location) : ?>
                        <?php if (!empty($location['name'])) : ?>
                            <option value="<?php echo esc_attr($location['id'] ?? ''); ?>"
                                    <?php selected($current_location, $location['id'] ?? ''); ?>
                                    data-address="<?php echo esc_attr($location['address'] ?? ''); ?>"
                                    data-phone="<?php echo esc_attr($location['phone'] ?? ''); ?>"
                                    data-hours="<?php echo esc_attr($location['hours'] ?? ''); ?>">
                                <?php echo esc_html($location['name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </p>

            <!-- Location Details Preview -->
            <div id="wct-store-location-details" class="wct-store-location-details" style="display: none;">
                <div class="wct-location-detail wct-location-address">
                    <strong><?php esc_html_e('Address:', 'checkout-toolkit-for-woo'); ?></strong>
                    <span class="wct-detail-value"></span>
                </div>
                <div class="wct-location-detail wct-location-phone">
                    <strong><?php esc_html_e('Phone:', 'checkout-toolkit-for-woo'); ?></strong>
                    <span class="wct-detail-value"></span>
                </div>
                <div class="wct-location-detail wct-location-hours">
                    <strong><?php esc_html_e('Hours:', 'checkout-toolkit-for-woo'); ?></strong>
                    <span class="wct-detail-value"></span>
                </div>
            </div>
        </div>

        <style>
            .wct-store-location-wrapper {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e0e0e0;
            }
            .wct-store-location-wrapper .form-row {
                margin-bottom: 15px;
            }
            .wct-store-location-wrapper label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
            }
            .wct-store-location-wrapper select {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .wct-store-location-details {
                background: #f9f9f9;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                padding: 15px;
                margin-top: 10px;
            }
            .wct-location-detail {
                margin-bottom: 8px;
            }
            .wct-location-detail:last-child {
                margin-bottom: 0;
            }
            .wct-location-detail strong {
                display: inline-block;
                min-width: 70px;
            }
        </style>

        <script>
        jQuery(function($) {
            // Handle delivery method change - show/hide store location (OPPOSITE of delivery fields)
            $(document.body).on('wct_delivery_method_changed', function(e, method) {
                if (method === 'pickup') {
                    $('#wct-store-location-wrapper').slideDown(200);
                } else {
                    $('#wct-store-location-wrapper').slideUp(200);
                }
            });

            // Show location details when a location is selected
            var $select = $('#checkout_toolkit_store_location');
            var $details = $('#wct-store-location-details');

            $select.on('change', function() {
                var $selected = $(this).find('option:selected');
                var address = $selected.data('address');
                var phone = $selected.data('phone');
                var hours = $selected.data('hours');

                if ($(this).val() && (address || phone || hours)) {
                    $('.wct-location-address').toggle(!!address);
                    $('.wct-location-address .wct-detail-value').text(address || '');

                    $('.wct-location-phone').toggle(!!phone);
                    $('.wct-location-phone .wct-detail-value').text(phone || '');

                    $('.wct-location-hours').toggle(!!hours);
                    $('.wct-location-hours .wct-detail-value').text(hours || '');

                    $details.slideDown(200);
                } else {
                    $details.slideUp(200);
                }
            });

            // Trigger initial update if a location is pre-selected
            if ($select.val()) {
                $select.trigger('change');
            }
        });
        </script>
        <?php
        do_action('checkout_toolkit_after_store_location');
    }

    /**
     * Validate store location field
     */
    public function validate_field(): void
    {
        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if delivery is selected - skip validation (field not shown for delivery)
        $delivery_method = $this->get_posted_delivery_method();
        if ($delivery_method !== 'pickup') {
            return;
        }

        // Only validate if required
        if (empty($settings['required'])) {
            return;
        }

        $store_location = $this->get_posted_store_location();

        if (empty($store_location)) {
            wc_add_notice(
                __('Please select a pickup location.', 'checkout-toolkit-for-woo'),
                'error'
            );
        }
    }

    /**
     * Get posted delivery method
     *
     * @return string Delivery method (delivery or pickup).
     */
    private function get_posted_delivery_method(): string
    {
        // Verify WooCommerce checkout nonce
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return 'delivery';
        }

        $value = isset($_POST['checkout_toolkit_delivery_method'])
            ? sanitize_key(wp_unslash($_POST['checkout_toolkit_delivery_method']))
            : '';

        if (empty($value) || !in_array($value, ['delivery', 'pickup'], true)) {
            // Check if delivery method is enabled, if not assume delivery
            $dm_settings = Main::get_instance()->get_delivery_method_settings();
            return $dm_settings['default_method'] ?? 'delivery';
        }

        return $value;
    }

    /**
     * Get posted store location
     *
     * @return string Store location ID.
     */
    private function get_posted_store_location(): string
    {
        // Verify WooCommerce checkout nonce
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return '';
        }

        return isset($_POST['checkout_toolkit_store_location'])
            ? sanitize_key(wp_unslash($_POST['checkout_toolkit_store_location']))
            : '';
    }

    /**
     * Save store location to order
     *
     * @param \WC_Order $order Order object.
     * @param array     $data  Checkout data.
     */
    public function save_field(\WC_Order $order, array $data): void
    {
        // Skip for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if delivery is selected - don't save store location
        $delivery_method = $this->get_posted_delivery_method();
        if ($delivery_method !== 'pickup') {
            return;
        }

        $store_location = $this->get_posted_store_location();

        if (!empty($store_location)) {
            $order->update_meta_data('_wct_store_location', $store_location);
            do_action('checkout_toolkit_store_location_saved', $order->get_id(), $store_location);
        }
    }

    /**
     * Get settings
     *
     * @return array Settings array.
     */
    public function get_settings(): array
    {
        $defaults = $this->get_default_settings();
        $settings = get_option('checkout_toolkit_store_locations_settings', []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Get default settings
     *
     * @return array Default settings.
     */
    public function get_default_settings(): array
    {
        return [
            'enabled' => false,
            'required' => true,
            'field_label' => 'Pickup Location',
            'locations' => [],
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Get location by ID
     *
     * @param string $location_id Location ID.
     * @return array|null Location data or null if not found.
     */
    public function get_location_by_id(string $location_id): ?array
    {
        $settings = $this->get_settings();
        $locations = $settings['locations'] ?? [];

        foreach ($locations as $location) {
            if (($location['id'] ?? '') === $location_id) {
                return $location;
            }
        }

        return null;
    }

    /**
     * Get location name by ID
     *
     * @param string $location_id Location ID.
     * @return string Location name or ID if not found.
     */
    public function get_location_name(string $location_id): string
    {
        $location = $this->get_location_by_id($location_id);
        return $location['name'] ?? $location_id;
    }
}
