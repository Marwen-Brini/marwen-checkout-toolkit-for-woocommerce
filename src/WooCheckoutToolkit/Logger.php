<?php
/**
 * Logger class for debugging
 *
 * @package WooCheckoutToolkit
 */

declare(strict_types=1);

namespace WooCheckoutToolkit;

defined('ABSPATH') || exit;

/**
 * Class Logger
 *
 * Handles plugin-specific logging.
 */
class Logger
{
    /**
     * Log levels
     */
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';

    /**
     * Log file path
     */
    private static ?string $log_file = null;

    /**
     * Whether logging is enabled
     */
    private static ?bool $enabled = null;

    /**
     * Check if logging is enabled
     */
    public static function is_enabled(): bool
    {
        if (self::$enabled === null) {
            self::$enabled = defined('WP_DEBUG') && WP_DEBUG;
        }

        return self::$enabled;
    }

    /**
     * Get log file path
     */
    public static function get_log_file(): string
    {
        if (self::$log_file === null) {
            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/marwchto-logs';

            if (!file_exists($log_dir)) {
                wp_mkdir_p($log_dir);
                file_put_contents($log_dir . '/.htaccess', 'deny from all');
                file_put_contents($log_dir . '/index.php', '<?php // Silence is golden.');
            }

            self::$log_file = $log_dir . '/marwchto-debug-' . gmdate('Y-m-d') . '.log';
        }

        return self::$log_file;
    }

    /**
     * Log a message
     */
    public static function log(string $message, string $level = self::DEBUG, array $context = []): void
    {
        if (!self::is_enabled()) {
            return;
        }

        $timestamp = gmdate('Y-m-d H:i:s');
        $formatted_message = "[{$timestamp}] [{$level}] {$message}";

        if (!empty($context)) {
            $formatted_message .= ' | Context: ' . wp_json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        $formatted_message .= PHP_EOL;

        // Write to plugin log file
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging class.
        error_log($formatted_message, 3, self::get_log_file());

        // Also write to WordPress debug.log
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging class.
            error_log('[WCT] ' . $formatted_message);
        }
    }

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log($message, self::DEBUG, $context);
    }

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log($message, self::INFO, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log($message, self::WARNING, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log($message, self::ERROR, $context);
    }

    /**
     * Log a variable dump
     */
    public static function dump(string $label, mixed $var): void
    {
        if (!self::is_enabled()) {
            return;
        }

        self::debug($label, ['dump' => $var]);
    }

    /**
     * Clear old log files (older than 7 days)
     */
    public static function cleanup(): void
    {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/marwchto-logs';

        if (!is_dir($log_dir)) {
            return;
        }

        $files = glob($log_dir . '/marwchto-debug-*.log');
        $cutoff = strtotime('-7 days');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                wp_delete_file($file);
            }
        }
    }
}
