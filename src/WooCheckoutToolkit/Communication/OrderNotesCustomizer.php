<?php
/**
 * Order notes customizer
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit\Communication;

use WooCheckoutToolkit\Main;

defined('ABSPATH') || exit;

/**
 * Class OrderNotesCustomizer
 *
 * Customizes the WooCommerce order notes field placeholder and label.
 */
class OrderNotesCustomizer
{
    /**
     * Initialize order notes customizer
     */
    public function init(): void
    {
        add_filter('woocommerce_checkout_fields', [$this, 'customize_order_notes'], 20);
    }

    /**
     * Customize order notes field
     *
     * @param array $fields Checkout fields.
     * @return array Modified checkout fields.
     */
    public function customize_order_notes(array $fields): array
    {
        $settings = $this->get_settings();

        if (empty($settings['enabled'])) {
            return $fields;
        }

        // Only modify if the order notes field exists
        if (!isset($fields['order']['order_comments'])) {
            return $fields;
        }

        // Customize placeholder
        if (!empty($settings['custom_placeholder'])) {
            $fields['order']['order_comments']['placeholder'] = $settings['custom_placeholder'];
        }

        // Customize label
        if (!empty($settings['custom_label'])) {
            $fields['order']['order_comments']['label'] = $settings['custom_label'];
        }

        return $fields;
    }

    /**
     * Get order notes settings
     *
     * @return array Settings array.
     */
    public function get_settings(): array
    {
        $defaults = $this->get_default_settings();
        $settings = get_option('checkout_toolkit_order_notes_settings', []);
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
            'custom_placeholder' => '',
            'custom_label' => '',
        ];
    }
}
