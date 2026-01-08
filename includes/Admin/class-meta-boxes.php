<?php
/**
 * Meta Boxes Handler
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
 * Meta Boxes class
 */
class MetaBoxes {

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
     * Register meta boxes
     */
    public function register() {
        add_meta_box(
            'smartnotify_ai_controls',
            __('Asistente de IA', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderAIControls'],
            'smartnotify_news',
            'side',
            'high'
        );

        add_meta_box(
            'smartnotify_sentiment',
            __('Análisis de Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderSentiment'],
            'smartnotify_news',
            'side',
            'default'
        );

        add_meta_box(
            'smartnotify_summary',
            __('Resumen Automático', SMARTNOTIFY_AI_TEXT_DOMAIN),
            [$this, 'renderSummary'],
            'smartnotify_news',
            'normal',
            'default'
        );
    }

    /**
     * Render AI controls meta box
     *
     * @param \WP_Post $post Post object
     */
    public function renderAIControls($post) {
        wp_nonce_field('smartnotify_ai_meta_box', 'smartnotify_ai_nonce');
        ?>
        <div class="smartnotify-ai-controls">
            <p class="description">
                <?php _e('Genera contenido automáticamente usando IA:', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </p>

            <div class="smartnotify-ai-buttons" style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <button type="button" class="button button-secondary smartnotify-generate-title" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <span class="dashicons dashicons-lightbulb" style="margin-top: 3px;"></span>
                    <?php _e('Generar Título', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                </button>

                <button type="button" class="button button-secondary smartnotify-generate-summary" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <span class="dashicons dashicons-media-text" style="margin-top: 3px;"></span>
                    <?php _e('Generar Resumen', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                </button>

                <button type="button" class="button button-secondary smartnotify-generate-tags" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <span class="dashicons dashicons-tag" style="margin-top: 3px;"></span>
                    <?php _e('Generar Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                </button>

                <button type="button" class="button button-secondary smartnotify-analyze-sentiment" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <span class="dashicons dashicons-chart-bar" style="margin-top: 3px;"></span>
                    <?php _e('Analizar Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                </button>
            </div>

            <div class="smartnotify-ai-status" style="margin-top: 15px; display: none;">
                <div class="notice notice-info inline" style="margin: 0;">
                    <p class="smartnotify-status-message"></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render sentiment meta box
     *
     * @param \WP_Post $post Post object
     */
    public function renderSentiment($post) {
        $sentiment = get_post_meta($post->ID, '_smartnotify_sentiment', true);
        $confidence = get_post_meta($post->ID, '_smartnotify_sentiment_confidence', true);

        $sentiment_analyzer = $this->container->get('sentiment.analyzer');
        ?>
        <div class="smartnotify-sentiment-display">
            <?php if ($sentiment): ?>
                <div class="sentiment-result" style="text-align: center; padding: 20px;">
                    <div class="sentiment-badge" style="display: inline-block; padding: 12px 24px; border-radius: 9999px; background-color: <?php echo esc_attr($sentiment_analyzer->getSentimentColor($sentiment)); ?>; color: white; font-size: 16px; font-weight: 600; margin-bottom: 10px;">
                        <?php echo esc_html($sentiment_analyzer->getSentimentLabel($sentiment)); ?>
                    </div>

                    <?php if ($confidence): ?>
                        <p class="sentiment-confidence" style="color: #6b7280; font-size: 14px; margin: 0;">
                            <?php printf(__('Confianza: %d%%', SMARTNOTIFY_AI_TEXT_DOMAIN), round($confidence * 100)); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="description" style="text-align: center; color: #9ca3af;">
                    <?php _e('No se ha analizado el sentimiento aún. Usa el botón "Analizar Sentimiento" para generarlo.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render summary meta box
     *
     * @param \WP_Post $post Post object
     */
    public function renderSummary($post) {
        $summary = get_post_meta($post->ID, '_smartnotify_summary', true);
        ?>
        <div class="smartnotify-summary-container">
            <textarea
                id="smartnotify_summary"
                name="smartnotify_summary"
                rows="4"
                class="large-text"
                placeholder="<?php esc_attr_e('El resumen se generará automáticamente con IA...', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
            ><?php echo esc_textarea($summary); ?></textarea>
            <p class="description">
                <?php _e('Resumen breve de la noticia. Puedes editarlo manualmente o generarlo con IA.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save meta box data
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     */
    public function save($post_id, $post) {
        // Check nonce
        if (!isset($_POST['smartnotify_ai_nonce']) || !wp_verify_nonce($_POST['smartnotify_ai_nonce'], 'smartnotify_ai_meta_box')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save summary
        if (isset($_POST['smartnotify_summary'])) {
            $summary = sanitize_textarea_field($_POST['smartnotify_summary']);
            update_post_meta($post_id, '_smartnotify_summary', $summary);
        }

        // Auto-generate on publish if enabled
        if ($post->post_status === 'publish' && get_option('smartnotify_ai_enable_auto_summary') === 'yes') {
            $this->autoGenerateContent($post_id);
        }
    }

    /**
     * Auto-generate content
     *
     * @param int $post_id Post ID
     */
    private function autoGenerateContent($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return;
        }

        $content_generator = $this->container->get('content.generator');
        $sentiment_analyzer = $this->container->get('sentiment.analyzer');

        // Generate summary if empty
        $summary = get_post_meta($post_id, '_smartnotify_summary', true);
        if (empty($summary)) {
            $generated_summary = $content_generator->generateSummary($post->post_content);
            if (!empty($generated_summary)) {
                update_post_meta($post_id, '_smartnotify_summary', $generated_summary);
            }
        }

        // Analyze sentiment
        $sentiment = get_post_meta($post_id, '_smartnotify_sentiment', true);
        if (empty($sentiment)) {
            $sentiment_result = $sentiment_analyzer->analyzePost($post_id);
            if (!empty($sentiment_result['sentiment'])) {
                update_post_meta($post_id, '_smartnotify_sentiment', $sentiment_result['sentiment']);
                update_post_meta($post_id, '_smartnotify_sentiment_confidence', $sentiment_result['confidence']);
            }
        }
    }
}
