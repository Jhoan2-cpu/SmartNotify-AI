<?php
/**
 * Plugin Name: SmartNotify AI
 * Plugin URI: https://github.com/yourusername/smartnotify-ai
 * Description: Sistema avanzado de gestión de noticias con capacidades de IA para generación automática de contenido, análisis de sentimiento y categorización inteligente.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tu-sitio.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smartnotify-ai
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package SmartNotifyAI
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMARTNOTIFY_AI_VERSION', '1.0.0');
define('SMARTNOTIFY_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMARTNOTIFY_AI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMARTNOTIFY_AI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SMARTNOTIFY_AI_TEXT_DOMAIN', 'smartnotify-ai');

// Require Composer autoloader
if (file_exists(SMARTNOTIFY_AI_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once SMARTNOTIFY_AI_PLUGIN_DIR . 'vendor/autoload.php';
}

// Require manual autoloader for plugin classes
require_once SMARTNOTIFY_AI_PLUGIN_DIR . 'includes/class-autoloader.php';

/**
 * Main plugin class
 */
final class SmartNotify_AI {

    /**
     * Plugin instance
     *
     * @var SmartNotify_AI
     */
    private static $instance = null;

    /**
     * Plugin container for dependency injection
     *
     * @var \SmartNotifyAI\Core\Container
     */
    private $container;

    /**
     * Get plugin instance
     *
     * @return SmartNotify_AI
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get container instance
     *
     * @return \SmartNotifyAI\Core\Container|null
     */
    public function get_container() {
        return $this->container;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_autoloader();
        $this->init_hooks();
    }

    /**
     * Initialize autoloader
     */
    private function init_autoloader() {
        \SmartNotifyAI\Core\Autoloader::register();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_action('init', [$this, 'load_textdomain']);
    }

    /**
     * Initialize plugin
     */
    public function init_plugin() {
        $this->container = new \SmartNotifyAI\Core\Container();
        $this->register_services();

        // Initialize the plugin
        $plugin = new \SmartNotifyAI\Core\Plugin($this->container);
        $plugin->init();
    }

    /**
     * Register services in container
     */
    private function register_services() {
        // Register core services
        $this->container->singleton('config', function() {
            return new \SmartNotifyAI\Core\Config();
        });

        $this->container->singleton('ai.service', function($c) {
            return new \SmartNotifyAI\Services\AI\AIServiceFactory($c->get('config'));
        });

        $this->container->singleton('sentiment.analyzer', function($c) {
            return new \SmartNotifyAI\Services\AI\SentimentAnalyzer($c->get('ai.service'));
        });

        $this->container->singleton('content.generator', function($c) {
            return new \SmartNotifyAI\Services\AI\ContentGenerator($c->get('ai.service'));
        });

        // Register post type
        $this->container->singleton('post.type', function() {
            return new \SmartNotifyAI\PostTypes\NewsPostType();
        });

        // Register taxonomies
        $this->container->singleton('taxonomies', function() {
            return new \SmartNotifyAI\Taxonomies\NewsTaxonomies();
        });

        // Register admin
        $this->container->singleton('admin', function($c) {
            return new \SmartNotifyAI\Admin\AdminController($c);
        });

        // Register assets
        $this->container->singleton('assets', function() {
            return new \SmartNotifyAI\Core\Assets();
        });

        // Register AJAX handlers
        $this->container->singleton('ajax', function($c) {
            return new \SmartNotifyAI\Ajax\AjaxHandler($c);
        });

        // Register frontend shortcode
        $this->container->singleton('frontend.shortcode', function() {
            return new \SmartNotifyAI\Frontend\NewsShortcode();
        });
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            SMARTNOTIFY_AI_TEXT_DOMAIN,
            false,
            dirname(SMARTNOTIFY_AI_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        require_once SMARTNOTIFY_AI_PLUGIN_DIR . 'includes/class-activator.php';
        \SmartNotifyAI\Core\Activator::activate();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        require_once SMARTNOTIFY_AI_PLUGIN_DIR . 'includes/class-deactivator.php';
        \SmartNotifyAI\Core\Deactivator::deactivate();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}

/**
 * Initialize the plugin
 */
function smartnotify_ai() {
    return SmartNotify_AI::instance();
}

// Start the plugin
smartnotify_ai();
