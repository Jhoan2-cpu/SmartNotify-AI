<?php
/**
 * Assets Manager
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class
 */
class Assets {

    /**
     * Enqueue assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue($hook) {
        // Only load on our post type pages
        if (!$this->shouldEnqueue($hook)) {
            return;
        }

        // Enqueue Tailwind CSS from CDN
        wp_enqueue_style(
            'smartnotify-tailwind',
            'https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css',
            [],
            '3.4.0'
        );

        // Enqueue custom admin CSS
        wp_enqueue_style(
            'smartnotify-admin',
            SMARTNOTIFY_AI_PLUGIN_URL . 'assets/css/admin.css',
            ['smartnotify-tailwind'],
            SMARTNOTIFY_AI_VERSION
        );

        // Enqueue admin JavaScript
        wp_enqueue_script(
            'smartnotify-admin',
            SMARTNOTIFY_AI_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-util'],
            SMARTNOTIFY_AI_VERSION,
            true
        );

        // Localize script
        wp_localize_script('smartnotify-admin', 'smartnotifyAI', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smartnotify_ai_nonce'),
            'i18n' => [
                'generating' => __('Generando...', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'analyzing' => __('Analizando...', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'error' => __('Error', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'success' => __('Éxito', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'confirmDelete' => __('¿Estás seguro de eliminar esta noticia?', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ],
        ]);
    }

    /**
     * Check if assets should be enqueued
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function shouldEnqueue($hook) {
        global $post_type, $typenow;

        $current_post_type = $post_type ?? $typenow ?? '';

        // Load on news post type pages
        if ($current_post_type === 'smartnotify_news') {
            return true;
        }

        // Load on settings page
        if (isset($_GET['page']) && $_GET['page'] === 'smartnotify-settings') {
            return true;
        }

        return false;
    }
}
