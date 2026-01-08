<?php
/**
 * Configuration management
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuration class
 */
class Config {

    /**
     * Configuration values
     *
     * @var array
     */
    private $config = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_config();
    }

    /**
     * Load configuration
     */
    private function load_config() {
        $this->config = [
            'post_type' => [
                'slug' => 'smartnotify_news',
                'labels' => [
                    'name' => __('Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'singular_name' => __('Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'add_new' => __('Agregar Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'add_new_item' => __('Agregar Nueva Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'edit_item' => __('Editar Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'new_item' => __('Nueva Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'view_item' => __('Ver Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'search_items' => __('Buscar Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'not_found' => __('No se encontraron noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'all_items' => __('Todas las Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
                ],
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'],
            ],
            'taxonomies' => [
                'category' => [
                    'slug' => 'smartnotify_category',
                    'labels' => [
                        'name' => __('Categorías', SMARTNOTIFY_AI_TEXT_DOMAIN),
                        'singular_name' => __('Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    ],
                ],
                'tag' => [
                    'slug' => 'smartnotify_tag',
                    'labels' => [
                        'name' => __('Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
                        'singular_name' => __('Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    ],
                ],
            ],
            'ai' => [
                'provider' => get_option('smartnotify_ai_provider', 'openai'),
                'api_key' => get_option('smartnotify_ai_api_key', ''),
                'model' => get_option('smartnotify_ai_model', 'gpt-4'),
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ],
            'capabilities' => [
                'edit_news' => 'edit_smartnotify_news',
                'edit_others_news' => 'edit_others_smartnotify_news',
                'delete_news' => 'delete_smartnotify_news',
                'publish_news' => 'publish_smartnotify_news',
            ],
        ];
    }

    /**
     * Get configuration value
     *
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Value
     */
    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Get all configuration
     *
     * @return array
     */
    public function all() {
        return $this->config;
    }
}
