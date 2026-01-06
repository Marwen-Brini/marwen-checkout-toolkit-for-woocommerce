<?php
/**
 * Delivery Instructions Field Handler
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Delivery;

use WooCheckoutToolkit\CheckoutDetector;
use WooCheckoutToolkit\Main;

defined('ABSPATH') || exit;

/**
 * Class DeliveryInstructions
 *
 * Handles the delivery instructions field on checkout.
 * Shows preset options (dropdown) + custom textarea.
 * Only visible when delivery is selected (hidden for pickup).
 */
class DeliveryInstructions
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
        add_action('woocommerce_before_order_notes', [$this, 'render_delivery_instructions_field'], 10);

        // Validate field
        add_action('woocommerce_checkout_process', [$this, 'validate_field']);

        // Save field to order
        add_action('woocommerce_checkout_create_order', [$this, 'save_field'], 10, 2);
    }

    /**
     * Render delivery instructions field
     */
    public function render_delivery_instructions_field(): void
    {
        // Skip rendering for blocks checkout - handled by BlocksIntegration
        if (CheckoutDetector::is_blocks_checkout()) {
            return;
        }

        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        $show = apply_filters('marwchto_show_delivery_instructions', true, WC()->cart);

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

        // If delivery method is not enabled, always show (assume delivery)
        // If delivery method is enabled and pickup is selected, hide initially
        $initial_display = 'block';
        if ($delivery_method_enabled && $current_method === 'pickup') {
            $initial_display = 'none';
        }

        $current_preset = WC()->checkout->get_value('marwchto_delivery_instructions_preset') ?: '';
        $current_custom = WC()->checkout->get_value('marwchto_delivery_instructions_custom') ?: '';

        $preset_options = $settings['preset_options'] ?? [];
        $required = !empty($settings['required']);
        $required_attr = $required ? ' required' : '';
        $required_mark = $required ? '<abbr class="required" title="' . esc_attr__('required', 'marwen-marwchto-for-woocommerce') . '">*</abbr>' : '';

        do_action('marwchto_before_delivery_instructions');
        ?>
        <div class="marwchto-delivery-instructions-wrapper" id="marwchto-delivery-instructions-wrapper" style="display: <?php echo esc_attr($initial_display); ?>;">
            <h3>
                <?php echo esc_html($settings['field_label'] ?: __('Delivery Instructions', 'marwen-marwchto-for-woocommerce')); ?>
                <?php echo wp_kses_post($required_mark); ?>
            </h3>

            <!-- Preset Dropdown -->
            <p class="form-row form-row-wide">
                <label for="marwchto_delivery_instructions_preset">
                    <?php echo esc_html($settings['preset_label'] ?: __('Common Instructions', 'marwen-marwchto-for-woocommerce')); ?>
                </label>
                <select name="marwchto_delivery_instructions_preset"
                        id="marwchto_delivery_instructions_preset"
                        class="marwchto-delivery-instructions-preset"
                        <?php echo esc_attr($required_attr); ?>>
                    <option value=""><?php esc_html_e('Select an option...', 'marwen-marwchto-for-woocommerce'); ?></option>
                    <?php foreach ($preset_options as $option) : ?>
                        <?php if (!empty($option['label'])) : ?>
                            <option value="<?php echo esc_attr($option['value'] ?? ''); ?>"
                                    <?php selected($current_preset, $option['value'] ?? ''); ?>>
                                <?php echo esc_html($option['label']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </p>

            <!-- Custom Textarea -->
            <p class="form-row form-row-wide">
                <label for="marwchto_delivery_instructions_custom">
                    <?php echo esc_html($settings['custom_label'] ?: __('Additional Instructions', 'marwen-marwchto-for-woocommerce')); ?>
                </label>
                <textarea name="marwchto_delivery_instructions_custom"
                          id="marwchto_delivery_instructions_custom"
                          class="marwchto-delivery-instructions-custom"
                          placeholder="<?php echo esc_attr($settings['custom_placeholder'] ?: ''); ?>"
                          rows="3"
                          <?php if (!empty($settings['max_length'])) : ?>
                              maxlength="<?php echo esc_attr($settings['max_length']); ?>"
                          <?php endif; ?>
                ><?php echo esc_textarea($current_custom); ?></textarea>
                <?php if (!empty($settings['max_length'])) : ?>
                    <span class="marwchto-char-counter marwchto-di-char-counter"></span>
                <?php endif; ?>
            </p>
        </div>
        <?php
        do_action('marwchto_after_delivery_instructions');
    }

    /**
     * Validate delivery instructions field
     */
    public function validate_field(): void
    {
        $settings = $this->settings;

        if (empty($settings['enabled'])) {
            return;
        }

        // Check if pickup is selected - skip validation
        $delivery_method = $this->get_posted_delivery_method();
        if ($delivery_method === 'pickup') {
            return;
        }

        // Only validate if required
        if (empty($settings['required'])) {
            return;
        }

        $preset = $this->get_posted_preset_value();
        $custom = $this->get_posted_custom_value();

        // At least one of preset or custom must be filled
        if (empty($preset) && empty($custom)) {
            wc_add_notice(
                /* translators: %s: field label */
                sprintf(__('%s is required.', 'marwen-marwchto-for-woocommerce'), $settings['field_label']),
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
     * Get posted preset value
     *
     * @return string Preset value.
     */
    private function get_posted_preset_value(): string
    {
        // Verify WooCommerce checkout nonce
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return '';
        }

        return isset($_POST['marwchto_delivery_instructions_preset'])
            ? sanitize_key(wp_unslash($_POST['marwchto_delivery_instructions_preset']))
            : '';
    }

    /**
     * Get posted custom value
     *
     * @return string Custom value.
     */
    private function get_posted_custom_value(): string
    {
        // Verify WooCommerce checkout nonce
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return '';
        }

        $value = isset($_POST['marwchto_delivery_instructions_custom'])
            ? sanitize_textarea_field(wp_unslash($_POST['marwchto_delivery_instructions_custom']))
            : '';

        // Apply max length
        $max_length = (int) ($this->settings['max_length'] ?? 0);
        if ($max_length > 0 && mb_strlen($value) > $max_length) {
            $value = mb_substr($value, 0, $max_length);
        }

        return $value;
    }

    /**
     * Save delivery instructions to order
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

        // Check if pickup is selected - don't save instructions
        $delivery_method = $this->get_posted_delivery_method();
        if ($delivery_method === 'pickup') {
            return;
        }

        $preset = $this->get_posted_preset_value();
        $custom = $this->get_posted_custom_value();

        // Save preset value
        if (!empty($preset)) {
            $order->update_meta_data('_marwchto_delivery_instructions_preset', $preset);
            do_action('marwchto_delivery_instructions_preset_saved', $order->get_id(), $preset);
        }

        // Save custom value
        if (!empty($custom)) {
            $order->update_meta_data('_marwchto_delivery_instructions_custom', $custom);
            do_action('marwchto_delivery_instructions_custom_saved', $order->get_id(), $custom);
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
        $settings = get_option('marwchto_delivery_instructions_settings', []);
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
            'required' => false,
            'field_label' => 'Delivery Instructions',
            'preset_label' => 'Common Instructions',
            'preset_options' => [
                ['value' => 'leave_door', 'label' => 'Leave at door'],
                ['value' => 'ring_bell', 'label' => 'Ring doorbell'],
                ['value' => 'call_arrival', 'label' => 'Call on arrival'],
                ['value' => 'front_desk', 'label' => 'Leave with front desk/reception'],
            ],
            'custom_label' => 'Additional Instructions',
            'custom_placeholder' => 'Any other delivery instructions...',
            'max_length' => 500,
            'show_in_emails' => true,
            'show_in_admin' => true,
        ];
    }

    /**
     * Get preset label by value
     *
     * @param string $value Preset value.
     * @return string Preset label or value if not found.
     */
    public function get_preset_label(string $value): string
    {
        $settings = $this->get_settings();
        $preset_options = $settings['preset_options'] ?? [];

        foreach ($preset_options as $option) {
            if (($option['value'] ?? '') === $value) {
                return $option['label'] ?? $value;
            }
        }

        return $value;
    }
}
