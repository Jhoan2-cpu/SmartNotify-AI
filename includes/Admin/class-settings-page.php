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
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_api_key', [
            'sanitize_callback' => [$this, 'sanitizeApiKey']
        ]);
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_model');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_auto_tags');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_auto_summary');
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_enable_sentiment');
        register_setting('smartnotify_ai_settings', 'smartnotify_image_provider');
        register_setting('smartnotify_ai_settings', 'smartnotify_image_api_key', [
            'sanitize_callback' => [$this, 'sanitizeImageApiKey']
        ]);
        register_setting('smartnotify_ai_settings', 'smartnotify_ai_api_key_configured');
        register_setting('smartnotify_ai_settings', 'smartnotify_image_api_key_configured');

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

        // Image Generation Section
        add_settings_section(
            'smartnotify_image_section',
            __('Generación de Imágenes con IA', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderImageSection'],
            'smartnotify-settings'
        );

        add_settings_field(
            'smartnotify_image_provider',
            __('Proveedor de Imágenes', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderImageProviderField'],
            'smartnotify-settings',
            'smartnotify_image_section'
        );

        add_settings_field(
            'smartnotify_image_api_key',
            __('API Key para Imágenes', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderImageApiKeyField'],
            'smartnotify-settings',
            'smartnotify_image_section'
        );
    }

    /**
     * Render settings page
     */
    public function renderPage() {
        $provider        = get_option('smartnotify_ai_provider', 'openai');
        $model           = get_option('smartnotify_ai_model', 'gpt-4');
        $image_provider  = get_option('smartnotify_image_provider', 'dalle');
        $api_key         = get_option('smartnotify_ai_api_key', '');
        $featuresEnabled = array_filter([
            get_option('smartnotify_ai_enable_auto_summary', 'yes'),
            get_option('smartnotify_ai_enable_auto_tags', 'yes'),
            get_option('smartnotify_ai_enable_sentiment', 'yes'),
        ], function($value) {
            return $value === 'yes';
        });

        $provider_label = $provider === 'anthropic' ? 'Anthropic' : 'OpenAI';
        $image_label    = $image_provider === 'none' ? __('Desactivado', SMARTNOTIFY_AI_TEXT_DOMAIN) : strtoupper($image_provider);
        $status_label   = !empty($api_key) ? __('Conexión segura', SMARTNOTIFY_AI_TEXT_DOMAIN) : __('API pendiente', SMARTNOTIFY_AI_TEXT_DOMAIN);

        ?>
        <div class="wrap smartnotify-admin-wrap">
            <div class="smartnotify-admin-hero-card">
                <div class="smartnotify-admin-hero-text">
                    <p class="smartnotify-eyebrow"><?php _e('SmartNotify AI', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                    <h1 class="smartnotify-hero-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <p class="smartnotify-hero-subtitle">
                        <?php _e('Configura tus proveedores, credenciales y automatizaciones de IA desde un panel visual y coherente.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                    </p>
                </div>
                <div class="smartnotify-hero-pills">
                    <span class="smartnotify-hero-pill">
                        <small><?php _e('Proveedor activo', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></small>
                        <strong><?php echo esc_html($provider_label); ?></strong>
                    </span>
                    <span class="smartnotify-hero-pill">
                        <small><?php _e('Modelo seleccionado', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></small>
                        <strong><?php echo esc_html($model); ?></strong>
                    </span>
                    <span class="smartnotify-hero-pill">
                        <small><?php _e('Funciones automáticas', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></small>
                        <strong><?php echo esc_html(count($featuresEnabled)); ?></strong>
                    </span>
                    <span class="smartnotify-hero-pill">
                        <small><?php _e('Estado API', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></small>
                        <strong><?php echo esc_html($status_label); ?></strong>
                    </span>
                </div>
            </div>

            <?php settings_errors(); ?>

            <div class="smartnotify-settings-grid">
                <section class="smartnotify-card smartnotify-card-primary">
                    <header class="smartnotify-card-header">
                        <div>
                            <p class="smartnotify-eyebrow"><?php _e('Panel de control', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                            <h2><?php _e('Configuración principal', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h2>
                            <p><?php _e('Actualiza tus llaves, modelos y funciones automáticas con una interfaz clara.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                        </div>
                        <div class="smartnotify-card-badge">
                            <span><?php echo esc_html($image_label); ?></span>
                            <small><?php _e('Proveedor de imágenes', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></small>
                        </div>
                    </header>

                    <form class="smartnotify-settings-form" method="post" action="options.php">
                        <?php
                        settings_fields('smartnotify_ai_settings');
                        do_settings_sections('smartnotify-settings');
                        submit_button(__('Guardar configuración', SMARTNOTIFY_AI_TEXT_DOMAIN), 'primary smartnotify-btn-primary');
                        ?>
                    </form>
                </section>

                <section class="smartnotify-card smartnotify-card-secondary">
                    <header>
                        <p class="smartnotify-eyebrow"><?php _e('Guía rápida', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                        <h2><?php _e('Domina el flujo en minutos', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h2>
                    </header>
                    <ol class="smartnotify-guide-list">
                        <li>
                            <span class="smartnotify-guide-icon">1</span>
                            <div>
                                <h3><?php _e('Selecciona el proveedor de IA', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h3>
                                <p><?php _e('Elige entre OpenAI o Anthropic según tus necesidades de contenido.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="smartnotify-guide-icon">2</span>
                            <div>
                                <h3><?php _e('Conecta tus credenciales', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h3>
                                <p><?php _e('Guarda la API key correspondiente y realiza una prueba instantánea.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                            </div>
                        </li>
                        <li>
                            <span class="smartnotify-guide-icon">3</span>
                            <div>
                                <h3><?php _e('Activa las automatizaciones', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></h3>
                                <p><?php _e('Define si deseas resúmenes, etiquetas o análisis de sentimiento al publicar.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
                            </div>
                        </li>
                    </ol>

                    <div class="smartnotify-resource-links">
                        <a href="https://platform.openai.com/api-keys" class="smartnotify-link" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-external"></span>
                            <?php _e('Obtener claves de OpenAI', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </a>
                        <a href="https://console.anthropic.com/settings/keys" class="smartnotify-link" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-external"></span>
                            <?php _e('Obtener claves de Anthropic', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </a>
                    </div>
                </section>
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
        $is_configured = !empty($value);
        ?>
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <input
                type="password"
                name="smartnotify_ai_api_key"
                id="smartnotify_ai_api_key"
                value=""
                placeholder="<?php echo $is_configured ? esc_attr__('••••••••••••••••••••••••••', SMARTNOTIFY_AI_TEXT_DOMAIN) : esc_attr__('Ingresa tu API key', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                class="regular-text"
            />
            <input type="hidden" name="smartnotify_ai_api_key_configured" value="<?php echo $is_configured ? '1' : '0'; ?>" />
            <button
                type="button"
                id="smartnotify_test_api"
                class="button button-secondary"
                <?php echo !$is_configured ? 'disabled' : ''; ?>
            >
                <span class="dashicons dashicons-admin-plugins" style="margin-top: 3px;"></span>
                <?php _e('Probar Conexión', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </button>
        </div>
        <div id="smartnotify_api_test_result" style="margin-top: 10px;"></div>
        <p class="description">
            <?php 
            if ($is_configured) {
                _e('API key configurada. Déjala en blanco para mantenerla o ingresa una nueva para cambiarla.', SMARTNOTIFY_AI_TEXT_DOMAIN);
            } else {
                _e('Ingresa tu API key. Se almacenará de forma segura en la base de datos.', SMARTNOTIFY_AI_TEXT_DOMAIN);
            }
            ?>
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

    /**
     * Render image section description
     */
    public function renderImageSection() {
        echo '<p>' . __('Configura el servicio de IA para generar imágenes destacadas automáticamente.', SMARTNOTIFY_AI_TEXT_DOMAIN) . '</p>';
    }

    /**
     * Render image provider field
     */
    public function renderImageProviderField() {
        $value = get_option('smartnotify_image_provider', 'huggingface');
        ?>
        <select name="smartnotify_image_provider" id="smartnotify_image_provider">
            <option value="huggingface" <?php selected($value, 'huggingface'); ?>>Hugging Face (Gratis) - Recomendado</option>
            <option value="dalle" <?php selected($value, 'dalle'); ?>>DALL-E (OpenAI)</option>
            <option value="stability" <?php selected($value, 'stability'); ?>>Stable Diffusion (Stability AI)</option>
            <option value="none" <?php selected($value, 'none'); ?>>Ninguno (Deshabilitado)</option>
        </select>
        <p class="description">
            <?php _e('Selecciona el proveedor de generación de imágenes. Hugging Face es gratis y recomendado para empezar.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
        </p>
        <?php
    }

    /**
     * Render image API key field
     */
    public function renderImageApiKeyField() {
        $value = get_option('smartnotify_image_api_key', '');
        $is_configured = !empty($value);
        $provider = get_option('smartnotify_image_provider', 'huggingface');
        ?>
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <input
                type="password"
                name="smartnotify_image_api_key"
                id="smartnotify_image_api_key"
                value=""
                placeholder="<?php echo $is_configured ? esc_attr__('••••••••••••••••••••••••••', SMARTNOTIFY_AI_TEXT_DOMAIN) : esc_attr__('Ingresa tu API key', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                class="regular-text"
                <?php echo $provider === 'none' ? 'disabled' : ''; ?>
            />
            <input type="hidden" name="smartnotify_image_api_key_configured" value="<?php echo $is_configured ? '1' : '0'; ?>" />
            <button
                type="button"
                id="smartnotify_test_image_api"
                class="button button-secondary"
                <?php echo (!$is_configured || $provider === 'none') ? 'disabled' : ''; ?>
            >
                <span class="dashicons dashicons-admin-plugins" style="margin-top: 3px;"></span>
                <?php _e('Probar Conexión', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </button>
        </div>
        <div id="smartnotify_image_api_test_result" style="margin-top: 10px;"></div>
        <p class="description">
            <?php
            if ($is_configured) {
                echo '<strong>' . __('API key configurada.', SMARTNOTIFY_AI_TEXT_DOMAIN) . '</strong> ';
                echo __('Déjala en blanco para mantenerla o ingresa una nueva para cambiarla.', SMARTNOTIFY_AI_TEXT_DOMAIN);
                echo '<br><br>';
            }
            
            if ($provider === 'huggingface') {
                echo __('API Key de Hugging Face (Gratis): ', SMARTNOTIFY_AI_TEXT_DOMAIN);
                echo '<a href="https://huggingface.co/settings/tokens" target="_blank">https://huggingface.co/settings/tokens</a>';
                echo '<br><em>' . __('Hugging Face ofrece acceso gratuito a modelos de generación de imágenes como Stable Diffusion.', SMARTNOTIFY_AI_TEXT_DOMAIN) . '</em>';
            } elseif ($provider === 'dalle') {
                echo __('API Key de OpenAI (misma que usas para texto, o puedes usar una diferente): ', SMARTNOTIFY_AI_TEXT_DOMAIN);
                echo '<a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a>';
            } elseif ($provider === 'stability') {
                echo __('API Key de Stability AI: ', SMARTNOTIFY_AI_TEXT_DOMAIN);
                echo '<a href="https://platform.stability.ai/account/keys" target="_blank">https://platform.stability.ai/account/keys</a>';
            } else {
                echo __('No se requiere API Key cuando la generación de imágenes está deshabilitada.', SMARTNOTIFY_AI_TEXT_DOMAIN);
            }
            ?>
        </p>
        <?php
    }

    /**
     * Sanitize API key field
     * Only update if a new value is provided
     *
     * @param string $value New value
     * @return string Sanitized value
     */
    public function sanitizeApiKey($value) {
        // If empty and key is configured, keep the existing key
        if (empty($value) && isset($_POST['smartnotify_ai_api_key_configured']) && $_POST['smartnotify_ai_api_key_configured'] === '1') {
            return get_option('smartnotify_ai_api_key', '');
        }
        
        // Otherwise sanitize and save the new value
        return sanitize_text_field($value);
    }

    /**
     * Sanitize image API key field
     * Only update if a new value is provided
     *
     * @param string $value New value
     * @return string Sanitized value
     */
    public function sanitizeImageApiKey($value) {
        // If empty and key is configured, keep the existing key
        if (empty($value) && isset($_POST['smartnotify_image_api_key_configured']) && $_POST['smartnotify_image_api_key_configured'] === '1') {
            return get_option('smartnotify_image_api_key', '');
        }
        
        // Otherwise sanitize and save the new value
        return sanitize_text_field($value);
    }
}

