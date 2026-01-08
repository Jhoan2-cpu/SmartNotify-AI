<?php
/**
 * News Form Handler
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
 * News Form class
 */
class NewsForm {

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
     * Initialize form
     */
    public function init() {
        // Inject modal HTML in admin footer
        add_action('admin_footer', [$this, 'renderModal']);

        // Handle the "Add New" button click
        add_action('admin_head', [$this, 'overrideAddNewButton']);

        // Redirect edit page to list with modal
        add_action('admin_head', [$this, 'redirectEditToModal']);
    }

    /**
     * Override the default "Add New" button behavior
     */
    public function overrideAddNewButton() {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'smartnotify_news') {
            return;
        }

        // Only on the list page
        if ($screen->base !== 'edit') {
            return;
        }

        ?>
        <script>
        jQuery(document).ready(function($) {
            // Override "Add New" button
            $('.page-title-action').first().attr('href', '#').on('click', function(e) {
                e.preventDefault();
                $('#smartnotify-news-modal').fadeIn(300);
                $('body').addClass('modal-open');
            });

            // Override row actions (Edit link)
            $('.row-actions .edit a').on('click', function(e) {
                e.preventDefault();
                var postId = $(this).closest('tr').attr('id').replace('post-', '');
                window.location.href = '<?php echo admin_url('edit.php?post_type=smartnotify_news&edit_news='); ?>' + postId;
            });
        });
        </script>
        <?php
    }

    /**
     * Redirect edit page to list with modal
     */
    public function redirectEditToModal() {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'smartnotify_news') {
            return;
        }

        // Check if we're on the edit page
        if ($screen->base === 'post' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            ?>
            <script>
            window.location.href = '<?php echo admin_url('edit.php?post_type=smartnotify_news&edit_news=' . $post_id); ?>';
            </script>
            <?php
            exit;
        }

        // Check if we have edit_news parameter
        if ($screen->base === 'edit' && isset($_GET['edit_news'])) {
            $post_id = intval($_GET['edit_news']);
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Load post data and open modal
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'smartnotify_get_news',
                        nonce: '<?php echo wp_create_nonce('smartnotify_ai_nonce'); ?>',
                        post_id: <?php echo $post_id; ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate form fields
                            $('#news_title').val(response.data.title);
                            $('#news_content').val(response.data.content);
                            $('#news_summary').val(response.data.summary);
                            $('#news_tags').val(response.data.tags).trigger('input');
                            $('#news_category').val(response.data.category);

                            // Store post ID for update
                            $('#smartnotify-news-form').data('post-id', <?php echo $post_id; ?>);

                            // Change modal title
                            $('#smartnotify-news-modal .smartnotify-modal-header h2').html(
                                '<span class="dashicons dashicons-edit-large"></span> Editar Noticia'
                            );

                            // Change button text
                            $('#smartnotify-publish-news').html(
                                '<span class="dashicons dashicons-yes"></span> Actualizar Noticia'
                            );

                            // Open modal
                            $('#smartnotify-news-modal').fadeIn(300);
                            $('body').addClass('modal-open');
                        }
                    }
                });
            });
            </script>
            <?php
        }
    }

    /**
     * Render modal HTML
     */
    public function renderModal() {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'smartnotify_news') {
            return;
        }

        ?>
        <div id="smartnotify-news-modal" class="smartnotify-modal" style="display: none;">
            <div class="smartnotify-modal-backdrop"></div>
            <div class="smartnotify-modal-content">
                <div class="smartnotify-modal-header">
                    <h2>
                        <span class="dashicons dashicons-edit-large"></span>
                        <?php _e('Crear Nueva Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                    </h2>
                    <button type="button" class="smartnotify-modal-close">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>

                <form id="smartnotify-news-form" class="smartnotify-modal-body">
                    <?php wp_nonce_field('smartnotify_create_news', 'smartnotify_news_nonce'); ?>

                    <!-- Title Field -->
                    <div class="smartnotify-form-group">
                        <label for="news_title">
                            <?php _e('Título de la Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                            <span class="required">*</span>
                        </label>
                        <div class="smartnotify-input-with-button">
                            <input
                                type="text"
                                id="news_title"
                                name="news_title"
                                class="smartnotify-input"
                                placeholder="<?php esc_attr_e('Ingresa el título de la noticia...', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                                required
                            />
                            <button type="button" class="smartnotify-ai-btn smartnotify-generate-title-modal" title="<?php esc_attr_e('Generar con IA', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>">
                                <span class="dashicons dashicons-lightbulb"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Content Field -->
                    <div class="smartnotify-form-group">
                        <label for="news_content">
                            <?php _e('Contenido', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                            <span class="required">*</span>
                        </label>
                        <textarea
                            id="news_content"
                            name="news_content"
                            class="smartnotify-textarea"
                            rows="8"
                            placeholder="<?php esc_attr_e('Escribe el contenido de la noticia...', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                            required
                        ></textarea>
                        <p class="smartnotify-help-text">
                            <?php _e('Escribe el contenido principal de la noticia. Puedes usar la IA para generar resúmenes y etiquetas.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </p>
                    </div>

                    <!-- Summary Field -->
                    <div class="smartnotify-form-group">
                        <label for="news_summary">
                            <?php _e('Resumen', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </label>
                        <div class="smartnotify-input-with-button">
                            <textarea
                                id="news_summary"
                                name="news_summary"
                                class="smartnotify-textarea"
                                rows="3"
                                placeholder="<?php esc_attr_e('Resumen breve de la noticia...', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                            ></textarea>
                            <button type="button" class="smartnotify-ai-btn smartnotify-generate-summary-modal" title="<?php esc_attr_e('Generar con IA', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>">
                                <span class="dashicons dashicons-media-text"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Tags Field -->
                    <div class="smartnotify-form-group">
                        <label for="news_tags">
                            <?php _e('Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </label>
                        <div class="smartnotify-input-with-button">
                            <input
                                type="text"
                                id="news_tags"
                                name="news_tags"
                                class="smartnotify-input"
                                placeholder="<?php esc_attr_e('Etiquetas separadas por comas...', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>"
                            />
                            <button type="button" class="smartnotify-ai-btn smartnotify-generate-tags-modal" title="<?php esc_attr_e('Generar con IA', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>">
                                <span class="dashicons dashicons-tag"></span>
                            </button>
                        </div>
                        <div id="news_tags_preview" class="smartnotify-tags-preview"></div>
                    </div>

                    <!-- Category Field -->
                    <div class="smartnotify-form-group">
                        <label for="news_category">
                            <?php _e('Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </label>
                        <select id="news_category" name="news_category" class="smartnotify-select">
                            <option value=""><?php _e('Selecciona una categoría', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></option>
                            <?php
                            $categories = get_terms([
                                'taxonomy' => 'smartnotify_category',
                                'hide_empty' => false,
                            ]);
                            if (!is_wp_error($categories)) {
                                foreach ($categories as $category) {
                                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Sentiment Analysis Section -->
                    <div class="smartnotify-form-group smartnotify-sentiment-section">
                        <label>
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Análisis de Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </label>
                        <div class="smartnotify-sentiment-controls">
                            <button type="button" class="smartnotify-btn smartnotify-btn-outline smartnotify-analyze-sentiment-modal">
                                <span class="dashicons dashicons-analytics"></span>
                                <?php _e('Analizar Sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                            </button>
                            <div id="smartnotify-sentiment-result" class="smartnotify-sentiment-display" style="display: none;"></div>
                            <!-- Hidden fields to store analyzed sentiment -->
                            <input type="hidden" id="analyzed_sentiment" name="analyzed_sentiment" value="">
                            <input type="hidden" id="analyzed_sentiment_confidence" name="analyzed_sentiment_confidence" value="">
                        </div>
                        <p class="smartnotify-help-text">
                            <?php _e('Analiza el sentimiento del contenido para categorizar la noticia.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        </p>
                    </div>

                    <!-- Status Messages -->
                    <div id="smartnotify-form-status" class="smartnotify-form-status" style="display: none;"></div>
                </form>

                <div class="smartnotify-modal-footer">
                    <button type="button" class="smartnotify-btn smartnotify-btn-secondary smartnotify-modal-close">
                        <span class="dashicons dashicons-no-alt"></span>
                        <?php _e('Cancelar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                    </button>
                    <button type="button" id="smartnotify-save-draft" class="smartnotify-btn smartnotify-btn-outline">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Guardar Borrador', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                    </button>
                    <button type="submit" form="smartnotify-news-form" id="smartnotify-publish-news" class="smartnotify-btn smartnotify-btn-primary">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Publicar Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
