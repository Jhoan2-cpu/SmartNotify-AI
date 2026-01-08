<?php
/**
 * Autoloader for plugin classes
 *
 * @package SmartNotifyAI
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PSR-4 Autoloader
 */
class Autoloader {

    /**
     * Namespace prefix
     */
    const NAMESPACE_PREFIX = 'SmartNotifyAI\\';

    /**
     * Base directory
     */
    private static $base_dir;

    /**
     * Register autoloader
     */
    public static function register() {
        self::$base_dir = SMARTNOTIFY_AI_PLUGIN_DIR . 'includes/';

        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload class
     *
     * @param string $class Class name
     */
    public static function autoload($class) {
        // Check if class uses our namespace
        if (strpos($class, self::NAMESPACE_PREFIX) !== 0) {
            return;
        }

        // Remove namespace prefix
        $relative_class = substr($class, strlen(self::NAMESPACE_PREFIX));

        // Convert namespace to file path
        $file = self::$base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Convert CamelCase to kebab-case for file names
        $file = self::convert_to_file_path($file);

        // Load file if exists
        if (file_exists($file)) {
            require_once $file;
        }
    }

    /**
     * Convert class name to file path
     *
     * @param string $file File path
     * @return string
     */
    private static function convert_to_file_path($file) {
        $path_parts = pathinfo($file);
        $filename = $path_parts['filename'];

        // Convert PascalCase to kebab-case format
        // Example: AIServiceFactory -> ai-service-factory
        $filename = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $filename);
        $filename = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $filename);
        $filename = strtolower($filename);

        // Check if it's an interface (ends with Interface)
        if (substr($filename, -10) === '-interface') {
            $filename = 'interface-' . substr($filename, 0, -10);
        } else {
            $filename = 'class-' . $filename;
        }

        return $path_parts['dirname'] . '/' . $filename . '.php';
    }
}
