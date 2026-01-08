<?php
/**
 * Plugin activation handler
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Activator class
 */
class Activator {

    /**
     * Plugin activation
     */
    public static function activate() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            wp_die(__('Este plugin requiere WordPress 6.0 o superior.', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(__('Este plugin requiere PHP 7.4 o superior.', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }

        // Create database tables if needed
        self::create_tables();

        // Set default options
        self::set_default_options();

        // Add capabilities to administrator role
        self::add_capabilities();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation timestamp
        update_option('smartnotify_ai_activated_at', current_time('mysql'));
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'smartnotify_ai_logs';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            action varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL,
            metadata longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY action (action)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        $defaults = [
            'smartnotify_ai_provider' => 'openai',
            'smartnotify_ai_model' => 'gpt-4',
            'smartnotify_ai_api_key' => '',
            'smartnotify_ai_enable_auto_tags' => 'yes',
            'smartnotify_ai_enable_auto_summary' => 'yes',
            'smartnotify_ai_enable_sentiment' => 'yes',
            'smartnotify_ai_version' => SMARTNOTIFY_AI_VERSION,
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Add capabilities to roles
     */
    private static function add_capabilities() {
        $admin = get_role('administrator');
        $editor = get_role('editor');

        $capabilities = [
            'edit_smartnotify_news',
            'edit_others_smartnotify_news',
            'publish_smartnotify_news',
            'read_smartnotify_news',
            'delete_smartnotify_news',
            'delete_others_smartnotify_news',
            'edit_published_smartnotify_news',
            'delete_published_smartnotify_news',
        ];

        if ($admin) {
            foreach ($capabilities as $cap) {
                $admin->add_cap($cap);
            }
        }

        if ($editor) {
            foreach ($capabilities as $cap) {
                $editor->add_cap($cap);
            }
        }
    }
}
