<?php
/**
 * Delivery Method Toggle Handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

use WooCheckoutToolkit\CheckoutDetector;

defined('ABSPATH') || exit;

/**
 * Class DeliveryMethod
 *
 * Handles the pickup vs delivery toggle on checkout.
 */
class DeliveryMethod
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

        // Add field to checkout (classic checkout)
        add_action('woocommerce_before_order_notes', [$this, 'render_delivery_method_field'], 5);

        // Validate field
        add_action('woocommerce_checkout_process', [$this, 'validate_field']);

        // Save field to order
        add_action('woocommerce_checkout_create_order', [$this, 'save_field'], 10, 2);
    }

    /**
     * Render delivery method field
     */
    public function render_delivery_method_field(): void
    {
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        $show = apply_filters('checkout_toolkit_show_delivery_method', true, WC()->cart);

        if (!$show) {
            return;
        }

        $current_value = WC()->checkout->get_value('checkout_toolkit_delivery_method');
        if (empty($current_value)) {
            $current_value = $settings['default_method'] ?? 'delivery';
        }

        $delivery_label = $settings['delivery_label'] ?: __('Delivery', 'checkout-toolkit-for-woo');
        $pickup_label = $settings['pickup_label'] ?: __('Pickup', 'checkout-toolkit-for-woo');

        do_action('checkout_toolkit_before_delivery_method');
        ?>
        <div class="wct-delivery-method-wrapper" id="wct-delivery-method-wrapper">
            <h3><?php echo esc_html($settings['field_label'] ?: __('Fulfillment Method', 'checkout-toolkit-for-woo')); ?></h3>

            <?php if ($settings['show_as'] === 'toggle') : ?>
                <div class="wct-delivery-method-toggle">
                    <label class="wct-toggle-option <?php echo $current_value === 'delivery' ? 'active' : ''; ?>">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method"
                               value="delivery"
                               <?php checked($current_value, 'delivery'); ?>>
                        <span class="wct-toggle-label"><?php echo esc_html($delivery_label); ?></span>
                    </label>
                    <label class="wct-toggle-option <?php echo $current_value === 'pickup' ? 'active' : ''; ?>">
                        <input type="radio"
                               name="checkout_toolkit_delivery_method"
                               value="pickup"
                               <?php checked($current_value, 'pickup'); ?>>
                        <span class="wct-toggle-label"><?php echo esc_html($pickup_label); ?></span>
                    </label>
                </div>
            <?php else : ?>
                <div class="wct-delivery-method-radio">
                    <p class="form-row">
                        <label class="wct-radio-option">
                            <input type="radio"
                                   name="checkout_toolkit_delivery_method"
                                   value="delivery"
                                   <?php checked($current_value, 'delivery'); ?>>
                            <?php echo esc_html($delivery_label); ?>
                        </label>
                    </p>
                    <p class="form-row">
                        <label class="wct-radio-option">
                            <input type="radio"
                                   name="checkout_toolkit_delivery_method"
                                   value="pickup"
                                   <?php checked($current_value, 'pickup'); ?>>
                            <?php echo esc_html($pickup_label); ?>
                        </label>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .wct-delivery-method-wrapper {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e0e0e0;
            }
            .wct-delivery-method-wrapper h3 {
                margin-bottom: 15px;
            }
            .wct-delivery-method-toggle {
                display: flex;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }
            .wct-toggle-option {
                flex: 1;
                text-align: center;
                padding: 12px 20px;
                cursor: pointer;
                background: #f9f9f9;
                border: none;
                margin: 0;
                transition: all 0.2s ease;
            }
            .wct-toggle-option:first-child {
                border-right: 1px solid #ddd;
            }
            .wct-toggle-option.active {
                background: #2271b1;
                color: #fff;
            }
            .wct-toggle-option input[type="radio"] {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .wct-toggle-label {
                font-weight: 500;
            }
            .wct-delivery-method-radio .wct-radio-option {
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }
        </style>

        <script>
        jQuery(function($) {
            $('.wct-toggle-option input[type="radio"]').on('change', function() {
                $('.wct-toggle-option').removeClass('active');
                $(this).closest('.wct-toggle-option').addClass('active');

                // Trigger event for other components to react
                $(document.body).trigger('wct_delivery_method_changed', [$(this).val()]);
            });

            $('.wct-radio-option input[type="radio"]').on('change', function() {
                $(document.body).trigger('wct_delivery_method_changed', [$(this).val()]);
            });
        });
        </script>
        <?php
        do_action('checkout_toolkit_after_delivery_method');
    }

    /**
     * Validate delivery method field
     */
    public function validate_field(): void
    {
        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        $value = $this->get_posted_value();

        // Ensure a valid value is selected
        if (!in_array($value, ['delivery', 'pickup'], true)) {
            wc_add_notice(
                __('Please select a fulfillment method.', 'checkout-toolkit-for-woo'),
                'error'
            );
        }
    }

    /**
     * Get posted field value
     *
     * @return string Field value.
     */
    private function get_posted_value(): string
    {
        $value = isset($_POST['checkout_toolkit_delivery_method'])
            ? sanitize_key($_POST['checkout_toolkit_delivery_method'])
            : '';

        // Default to delivery if not set
        if (empty($value)) {
            $value = $this->settings['default_method'] ?? 'delivery';
        }

        return $value;
    }

    /**
     * Save delivery method to order
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

        $value = $this->get_posted_value();

        if (in_array($value, ['delivery', 'pickup'], true)) {
            $order->update_meta_data('_wct_delivery_method', $value);

            do_action('checkout_toolkit_delivery_method_saved', $order->get_id(), $value);
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
        $settings = get_option('checkout_toolkit_delivery_method_settings', []);
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
            'default_method' => 'delivery',
            'field_label' => 'Fulfillment Method',
            'delivery_label' => 'Delivery',
            'pickup_label' => 'Pickup',
            'show_as' => 'toggle',
            'show_in_admin' => true,
            'show_in_emails' => true,
        ];
    }
}
