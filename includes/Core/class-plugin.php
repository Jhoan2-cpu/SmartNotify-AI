<?php
/**
 * Main plugin initialization
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin initialization class
 */
class Plugin {

    /**
     * Container instance
     *
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Register post type
        add_action('init', [$this, 'register_post_type']);

        // Register taxonomies
        add_action('init', [$this, 'register_taxonomies']);

        // Initialize admin
        if (is_admin()) {
            $this->container->get('admin')->init();
        }

        // Register assets
        add_action('admin_enqueue_scripts', [$this, 'register_assets']);

        // Initialize AJAX handlers
        $this->container->get('ajax')->init();

        // Initialize frontend shortcode
        add_action('init', [$this, 'register_shortcode']);

        // Enqueue frontend styles
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);

        // Add custom columns to post list
        add_filter('manage_smartnotify_news_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_smartnotify_news_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);

        // Make columns sortable
        add_filter('manage_edit-smartnotify_news_sortable_columns', [$this, 'make_columns_sortable']);
    }

    /**
     * Register post type
     */
    public function register_post_type() {
        $this->container->get('post.type')->register();
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        $this->container->get('taxonomies')->register();
    }

    /**
     * Register assets
     */
    public function register_assets($hook) {
        $this->container->get('assets')->enqueue($hook);
    }

    /**
     * Register frontend shortcode
     */
    public function register_shortcode() {
        $this->container->get('frontend.shortcode')->register();
    }

    /**
     * Register frontend assets
     */
    public function register_frontend_assets() {
        wp_enqueue_style(
            'smartnotify-frontend',
            SMARTNOTIFY_AI_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            SMARTNOTIFY_AI_VERSION
        );

        // Enqueue dashicons for frontend
        wp_enqueue_style('dashicons');

        // Enqueue frontend JavaScript
        wp_enqueue_script(
            'smartnotify-frontend',
            SMARTNOTIFY_AI_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            SMARTNOTIFY_AI_VERSION,
            true
        );
    }

    /**
     * Add custom columns to news list
     *
     * @param array $columns Existing columns
     * @return array
     */
    public function add_custom_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            if ($key === 'title') {
                $new_columns['featured_image'] = __('Imagen', SMARTNOTIFY_AI_TEXT_DOMAIN);
                $new_columns['sentiment'] = __('Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN);
                $new_columns['summary'] = __('Resumen', SMARTNOTIFY_AI_TEXT_DOMAIN);
            }
        }

        return $new_columns;
    }

    /**
     * Render custom columns
     *
     * @param string $column Column name
     * @param int $post_id Post ID
     */
    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'featured_image':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [50, 50]);
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="font-size: 50px; color: #ccc;"></span>';
                }
                break;

            case 'sentiment':
                $sentiment = get_post_meta($post_id, '_smartnotify_sentiment', true);
                if ($sentiment) {
                    $colors = [
                        'positive' => '#10b981',
                        'neutral' => '#6b7280',
                        'negative' => '#ef4444',
                    ];
                    $labels = [
                        'positive' => __('Positivo', SMARTNOTIFY_AI_TEXT_DOMAIN),
                        'neutral' => __('Neutral', SMARTNOTIFY_AI_TEXT_DOMAIN),
                        'negative' => __('Negativo', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    ];
                    $color = $colors[$sentiment] ?? '#6b7280';
                    $label = $labels[$sentiment] ?? __('No analizado', SMARTNOTIFY_AI_TEXT_DOMAIN);

                    echo sprintf(
                        '<span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; background-color: %s; color: white; font-size: 12px; font-weight: 600;">%s</span>',
                        esc_attr($color),
                        esc_html($label)
                    );
                } else {
                    echo '<span style="color: #9ca3af;">—</span>';
                }
                break;

            case 'summary':
                $summary = get_post_meta($post_id, '_smartnotify_summary', true);
                if ($summary) {
                    echo '<div style="max-width: 300px;">' . esc_html(wp_trim_words($summary, 15)) . '</div>';
                } else {
                    echo '<span style="color: #9ca3af;">—</span>';
                }
                break;
        }
    }

    /**
     * Make columns sortable
     *
     * @param array $columns Existing sortable columns
     * @return array
     */
    public function make_columns_sortable($columns) {
        $columns['sentiment'] = 'sentiment';
        return $columns;
    }
}
