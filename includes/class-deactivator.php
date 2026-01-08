<?php
/**
 * Plugin deactivation handler
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Deactivator class
 */
class Deactivator {

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled events
        wp_clear_scheduled_hook('smartnotify_ai_cleanup');

        // Note: We don't remove capabilities or delete data on deactivation
        // This is intentional - data should persist across deactivation/reactivation
    }
}
