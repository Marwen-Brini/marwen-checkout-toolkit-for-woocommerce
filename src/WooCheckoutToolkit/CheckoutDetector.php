<?php
/**
 * Checkout type detector
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

defined('ABSPATH') || exit;

/**
 * Class CheckoutDetector
 *
 * Detects whether the store uses classic or blocks checkout.
 */
class CheckoutDetector
{
    /**
     * Cached checkout type
     *
     * @var string|null
     */
    private static ?string $checkout_type = null;

    /**
     * Check if WooCommerce Blocks is available
     */
    public static function has_wc_blocks(): bool
    {
        return class_exists('Automattic\WooCommerce\Blocks\Package');
    }

    /**
     * Get the checkout page ID
     */
    public static function get_checkout_page_id(): int
    {
        return (int) get_option('woocommerce_checkout_page_id', 0);
    }

    /**
     * Check if checkout page uses blocks
     */
    public static function is_blocks_checkout(): bool
    {
        if (self::$checkout_type !== null) {
            return self::$checkout_type === 'blocks';
        }

        $checkout_page_id = self::get_checkout_page_id();

        if (!$checkout_page_id) {
            self::$checkout_type = 'classic';
            return false;
        }

        $checkout_page = get_post($checkout_page_id);

        if (!$checkout_page) {
            self::$checkout_type = 'classic';
            return false;
        }

        // Check if the page contains the checkout block
        if (has_block('woocommerce/checkout', $checkout_page)) {
            self::$checkout_type = 'blocks';
            return true;
        }

        self::$checkout_type = 'classic';
        return false;
    }

    /**
     * Check if checkout page uses classic shortcode
     */
    public static function is_classic_checkout(): bool
    {
        return !self::is_blocks_checkout();
    }

    /**
     * Get checkout type as string
     *
     * @return string 'blocks' or 'classic'
     */
    public static function get_checkout_type(): string
    {
        self::is_blocks_checkout(); // Ensure type is detected
        return self::$checkout_type ?? 'classic';
    }

    /**
     * Reset cached checkout type (useful for testing)
     */
    public static function reset_cache(): void
    {
        self::$checkout_type = null;
    }
}
