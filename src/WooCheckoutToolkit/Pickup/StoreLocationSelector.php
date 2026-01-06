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

        $show = apply_filters('marwchto_show_store_location', true, WC()->cart);

        if (!$show) {
            return;
        }

        // Get current delivery method to determine initial visibility
        $delivery_method_settings = Main::get_instance()->get_delivery_method_settings();
        $current_method = WC()->checkout->get_value('marwchto_delivery_method');
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

        $current_location = WC()->checkout->get_value('marwchto_store_location') ?: '';

        $required = !empty($settings['required']);
        $required_attr = $required ? ' required' : '';
        $required_mark = $required ? '<abbr class="required" title="' . esc_attr__('required', 'marwen-marwchto-for-woocommerce') . '">*</abbr>' : '';

        do_action('marwchto_before_store_location');
        ?>
        <div class="marwchto-store-location-wrapper" id="marwchto-store-location-wrapper" style="display: <?php echo esc_attr($initial_display); ?>;">
            <p class="form-row form-row-wide">
                <label for="marwchto_store_location">
                    <?php echo esc_html($settings['field_label'] ?: __('Pickup Location', 'marwen-marwchto-for-woocommerce')); ?>
                    <?php echo wp_kses_post($required_mark); ?>
                </label>
                <select name="marwchto_store_location"
                        id="marwchto_store_location"
                        class="marwchto-store-location-select"
                        <?php echo esc_attr($required_attr); ?>>
                    <option value=""><?php esc_html_e('Select a location...', 'marwen-marwchto-for-woocommerce'); ?></option>
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
            <div id="marwchto-store-location-details" class="marwchto-store-location-details" style="display: none;">
                <div class="marwchto-location-detail marwchto-location-address">
                    <strong><?php esc_html_e('Address:', 'marwen-marwchto-for-woocommerce'); ?></strong>
                    <span class="marwchto-detail-value"></span>
                </div>
                <div class="marwchto-location-detail marwchto-location-phone">
                    <strong><?php esc_html_e('Phone:', 'marwen-marwchto-for-woocommerce'); ?></strong>
                    <span class="marwchto-detail-value"></span>
                </div>
                <div class="marwchto-location-detail marwchto-location-hours">
                    <strong><?php esc_html_e('Hours:', 'marwen-marwchto-for-woocommerce'); ?></strong>
                    <span class="marwchto-detail-value"></span>
                </div>
            </div>
        </div>
        <?php
        do_action('marwchto_after_store_location');
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
                __('Please select a pickup location.', 'marwen-marwchto-for-woocommerce'),
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

        $value = isset($_POST['marwchto_delivery_method'])
            ? sanitize_key(wp_unslash($_POST['marwchto_delivery_method']))
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

        return isset($_POST['marwchto_store_location'])
            ? sanitize_key(wp_unslash($_POST['marwchto_store_location']))
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
            $order->update_meta_data('_marwchto_store_location', $store_location);
            do_action('marwchto_store_location_saved', $order->get_id(), $store_location);
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
        $settings = get_option('marwchto_store_locations_settings', []);
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
