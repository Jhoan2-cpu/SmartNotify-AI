<?php
/**
 * Settings Page Handler
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
 * Settings Page class
 */
class SettingsPage {

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
     * Add menu page
     */
    public function addMenuPage() {
        add_submenu_page(
            'edit.php?post_type=smartnotify_news',
            __('Configuración', SMARTNOTIFY_AI_TEXT_DOMAIN),
            __('Configuración', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'manage_options',
            'smartnotify-settings',
            [$this, 'renderPage']
        );
    }

    /**
     * Register settings
     */
    public function registerSettings() {
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_provider');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_api_key');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_model');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_auto_tags');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_auto_summary');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_sentiment');

        // AI Settings Section
        add_settings_section(
            'smartnotify_ai_section',
            __('Configuración de IA', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderSection'],
            'smartnotify-settings'
        );

        add_settings_field(
            'smartnotify_ai_provider',
            __('Proveedor de IA', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderProviderField'],
            'smartnotify-settings',
            'smartnotify_ai_section'
        );

        add_settings_field(
            'smartnotify_ai_api_key',
            __('API Key', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderApiKeyField'],
            'smartnotify-settings',
            'smartnotify_ai_section'
        );

        add_settings_field(
            'smartnotify_ai_model',
            __('Modelo', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderModelField'],
            'smartnotify-settings',
            'smartnotify_ai_section'
        );

        // Features Section
        add_settings_section(
            'smartnotify_features_section',
            __('Funciones Automáticas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            null,
            'smartnotify-settings'
        );

        add_settings_field(
            'smartnotify_ai_enable_auto_summary',
            __('Resumen Automático', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderAutoSummaryField'],
            'smartnotify-settings',
            'smartnotify_features_section'
        );

        add_settings_field(
            'smartnotify_ai_enable_auto_tags',
            __('Etiquetas Automáticas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderAutoTagsField'],
            'smartnotify-settings',
            'smartnotify_features_section'
        );

        add_settings_field(
            'smartnotify_ai_enable_sentiment',
            __('Análisis de Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderSentimentField'],
            'smartnotify-settings',
            'smartnotify_features_section'
        );
    }

    /**
     * Render settings page
     */
    public function renderPage() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('smartnotify_ai_settings');
                do_settings_sections('smartnotify-settings');
                submit_button();
                ?>
            </form>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('Cómo Usar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h2>
                <ol>
                    <li><?php _e('Selecciona tu proveedor de IA preferido (OpenAI o Anthropic)', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></li>
                    <li><?php _e('Ingresa tu API Key del proveedor seleccionado', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></li>
                    <li><?php _e('Selecciona el modelo que deseas usar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></li>
                    <li><?php _e('Habilita las funciones automáticas que necesites', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></li>
                </ol>

                <h3><?php _e('Obtener API Keys', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h3>
                <ul>
                    <li><strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a></li>
                    <li><strong>Anthropic:</strong> <a href="https://console.anthropic.com/settings/keys" target="_blank">https://console.anthropic.com/settings/keys</a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render section description
     */
    public function renderSection() {
        echo '<p>' . __('Configura el servicio de IA para generar contenido automáticamente.', SMARTNOTIFY_AI_TEXT_DOMAIN) . '</p>';
    }

    /**
     * Render provider field
     */
    public function renderProviderField() {
        $value = get_option('smartnotify_ai_provider', 'openai');
        ?>
        <select name="smartnotify_ai_provider" id="smartnotify_ai_provider">
            <option value="openai" <?php selected($value, 'openai'); ?>>OpenAI (GPT-4)</option>
            <option value="anthropic" <?php selected($value, 'anthropic'); ?>>Anthropic (Claude)</option>
        </select>
        <?php
    }

    /**
     * Render API key field
     */
    public function renderApiKeyField() {
        $value = get_option('smartnotify_ai_api_key', '');
        ?>
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <input
                type="password"
                name="smartnotify_ai_api_key"
                id="smartnotify_ai_api_key"
                value="<?php echo esc_attr($value); ?>"
                class="regular-text"
            />
            <button
                type="button"
                id="smartnotify_test_api"
                class="button button-secondary"
                <?php echo empty($value) ? 'disabled' : ''; ?>
            >
                <span class="dashicons dashicons-admin-plugins" style="margin-top: 3px;"></span>
                <?php _e('Probar Conexión', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </button>
        </div>
        <div id="smartnotify_api_test_result" style="margin-top: 10px;"></div>
        <p class="description">
            <?php _e('Tu API key se almacena de forma segura en la base de datos.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
        </p>
        <?php
    }

    /**
     * Render model field
     */
    public function renderModelField() {
        $value = get_option('smartnotify_ai_model', 'gpt-4');
        $provider = get_option('smartnotify_ai_provider', 'openai');
        ?>
        <select name="smartnotify_ai_model" id="smartnotify_ai_model">
            <?php if ($provider === 'openai'): ?>
                <option value="gpt-4" <?php selected($value, 'gpt-4'); ?>>GPT-4</option>
                <option value="gpt-4-turbo" <?php selected($value, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                <option value="gpt-3.5-turbo" <?php selected($value, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
            <?php else: ?>
                <option value="claude-sonnet-4-20250514" <?php selected($value, 'claude-sonnet-4-20250514'); ?>>Claude Sonnet 4.5 (Recomendado)</option>
                <option value="claude-opus-4-20250514" <?php selected($value, 'claude-opus-4-20250514'); ?>>Claude Opus 4.5</option>
                <option value="claude-haiku-4-20250417" <?php selected($value, 'claude-haiku-4-20250417'); ?>>Claude Haiku 4 (Rápido)</option>
                <option value="claude-3-7-sonnet-20250219" <?php selected($value, 'claude-3-7-sonnet-20250219'); ?>>Claude Sonnet 3.7</option>
                <option value="claude-3-5-haiku-20241022" <?php selected($value, 'claude-3-5-haiku-20241022'); ?>>Claude Haiku 3.5</option>
            <?php endif; ?>
        </select>
        <?php
    }

    /**
     * Render auto summary field
     */
    public function renderAutoSummaryField() {
        $value = get_option('smartnotify_ai_enable_auto_summary', 'yes');
        ?>
        <label>
            <input type="checkbox" name="smartnotify_ai_enable_auto_summary" value="yes" <?php checked($value, 'yes'); ?> />
            <?php _e('Generar resumen automáticamente al publicar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
        </label>
        <?php
    }

    /**
     * Render auto tags field
     */
    public function renderAutoTagsField() {
        $value = get_option('smartnotify_ai_enable_auto_tags', 'yes');
        ?>
        <label>
            <input type="checkbox" name="smartnotify_ai_enable_auto_tags" value="yes" <?php checked($value, 'yes'); ?> />
            <?php _e('Generar etiquetas automáticamente al publicar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
        </label>
        <?php
    }

    /**
     * Render sentiment field
     */
    public function renderSentimentField() {
        $value = get_option('smartnotify_ai_enable_sentiment', 'yes');
        ?>
        <label>
            <input type="checkbox" name="smartnotify_ai_enable_sentiment" value="yes" <?php checked($value, 'yes'); ?> />
            <?php _e('Analizar sentimiento automáticamente al publicar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
        </label>
        <?php
    }
}
