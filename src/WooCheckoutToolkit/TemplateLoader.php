<?php
/**
 * Template loader
 *
 * @package CheckoutToolkitForWoo
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

defined('ABSPATH') || exit;

/**
 * Class TemplateLoader
 *
 * Handles loading templates with theme override support.
 */
class TemplateLoader
{
    /**
     * Template path in theme
     */
    private const THEME_TEMPLATE_PATH = 'woocommerce/marwen-checkout-toolkit-for-woocommerce/';

    /**
     * Get template part
     *
     * Looks for template in:
     * 1. yourtheme/woocommerce/marwen-checkout-toolkit-for-woocommerce/$template_name
     * 2. plugin/templates/$template_name
     *
     * @param string $template_name Template name (e.g., 'checkout/delivery-date-field.php')
     * @param array  $args          Variables to pass to template
     * @param bool   $return        Whether to return output instead of echoing
     * @return string|void
     */
    public static function get_template(string $template_name, array $args = [], bool $return = false)
    {
        $template = self::locate_template($template_name);

        if (!$template) {
            return $return ? '' : null;
        }

        if ($return) {
            ob_start();
        }

        // Extract args to variables for template
        if (!empty($args)) {
            // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables.
            extract($args);
        }

        include $template;

        if ($return) {
            return ob_get_clean();
        }
    }

    /**
     * Locate a template file
     *
     * @param string $template_name Template name
     * @return string|false Template path or false if not found
     */
    public static function locate_template(string $template_name)
    {
        // Look in theme first
        $theme_template = locate_template([
            self::THEME_TEMPLATE_PATH . $template_name,
        ]);

        if ($theme_template) {
            return $theme_template;
        }

        // Fall back to plugin templates
        $plugin_template = CHECKOUT_TOOLKIT_PLUGIN_DIR . 'templates/' . $template_name;

        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return false;
    }

    /**
     * Get template path for theme overrides
     *
     * @return string
     */
    public static function get_theme_template_path(): string
    {
        return self::THEME_TEMPLATE_PATH;
    }
}
