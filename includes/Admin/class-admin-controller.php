<?php
/**
 * Admin Controller
 *
 * @package SmartNotifyAI\Admin
 */

namespace SmartNotifyAI\Admin;

use SmartNotifyAI\Core\Container;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Controller class
 */
class AdminController {

    /**
     * Container instance
     *
     * @var Container
     */
    private $container;

    /**
     * Settings page handler
     *
     * @var SettingsPage
     */
    private $settings_page;

    /**
     * News form handler
     *
     * @var NewsForm
     */
    private $news_form;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
        $this->settings_page = new SettingsPage($container);
        $this->news_form = new NewsForm($container);
    }

    /**
     * Initialize admin
     */
    public function init() {
        // Add settings page
        add_action('admin_menu', [$this->settings_page, 'addMenuPage']);
        add_action('admin_init', [$this->settings_page, 'registerSettings']);

        // Initialize news form modal
        $this->news_form->init();

        // Add custom filters
        add_action('restrict_manage_posts', [$this, 'addFilters']);
        add_filter('parse_query', [$this, 'filterPosts']);

        // Add admin notices
        add_action('admin_notices', [$this, 'adminNotices']);
    }

    /**
     * Add custom filters to posts list
     *
     * @param string $post_type Post type
     */
    public function addFilters($post_type) {
        if ($post_type !== 'smartnotify_news') {
            return;
        }

        // Sentiment filter
        $sentiment = isset($_GET['sentiment_filter']) ? sanitize_text_field($_GET['sentiment_filter']) : '';
        ?>
        <select name="sentiment_filter">
            <option value=""><?php _e('Todos los sentimientos', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></option>
            <option value="positive" <?php selected($sentiment, 'positive'); ?>>
                <?php _e('Positivo', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </option>
            <option value="neutral" <?php selected($sentiment, 'neutral'); ?>>
                <?php _e('Neutral', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </option>
            <option value="negative" <?php selected($sentiment, 'negative'); ?>>
                <?php _e('Negativo', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </option>
        </select>
        <?php
    }

    /**
     * Filter posts by custom criteria
     *
     * @param \WP_Query $query
     */
    public function filterPosts($query) {
        global $pagenow;

        if (!is_admin() || $pagenow !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'smartnotify_news') {
            return;
        }

        // Filter by sentiment
        if (isset($_GET['sentiment_filter']) && !empty($_GET['sentiment_filter'])) {
            $sentiment = sanitize_text_field($_GET['sentiment_filter']);
            $query->set('meta_key', '_smartnotify_sentiment');
            $query->set('meta_value', $sentiment);
        }
    }

    /**
     * Display admin notices
     */
    public function adminNotices() {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'smartnotify_news') {
            return;
        }

        // Check if API key is configured
        $api_key = get_option('smartnotify_ai_api_key');

        if (empty($api_key)) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php
                    printf(
                        __('SmartNotify AI: No has configurado tu API key. %sConfigúrala aquí%s para habilitar las funciones de IA.', SMARTNOTIFY_AI_TEXT_DOMAIN),
                        '<a href="' . admin_url('edit.php?post_type=smartnotify_news&page=smartnotify-settings') . '">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
}
