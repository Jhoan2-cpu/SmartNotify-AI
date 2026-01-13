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

    /**
     * Remove capabilities from roles
     * Called on plugin uninstall (not deactivation)
     */
    public static function remove_capabilities() {
        $admin = get_role('administrator');
        $editor = get_role('editor');

        $capabilities = [
            'edit_smartnotify_news',
            'edit_others_smartnotify_news',
            'publish_smartnotify_news',
            'read_smartnotify_news',
            'read_private_smartnotify_news',
            'delete_smartnotify_news',
            'delete_others_smartnotify_news',
            'delete_private_smartnotify_news',
            'delete_published_smartnotify_news',
            'edit_private_smartnotify_news',
            'edit_published_smartnotify_news',
        ];

        if ($admin) {
            foreach ($capabilities as $cap) {
                $admin->remove_cap($cap);
            }
        }

        if ($editor) {
            foreach ($capabilities as $cap) {
                $editor->remove_cap($cap);
            }
        }
    }
}
