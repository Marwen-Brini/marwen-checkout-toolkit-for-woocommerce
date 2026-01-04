<?php
/**
 * PSR-4 Autoloader for Checkout Toolkit
 *
 * @package CheckoutToolkitForWoo
 */

defined('ABSPATH') || exit;

spl_autoload_register(function ($class) {
    // Plugin namespace prefix
    $prefix = 'WooCheckoutToolkit\\';

    // Base directory for the namespace prefix
    $base_dir = CHECKOUT_TOOLKIT_PLUGIN_DIR . 'src/WooCheckoutToolkit/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators and append .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
