<?php
/**
 * AJAX Handler
 *
 * @package SmartNotifyAI\Ajax
 */

namespace SmartNotifyAI\Ajax;

use SmartNotifyAI\Core\Container;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler class
 */
class AjaxHandler {

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
     * Initialize AJAX handlers
     */
    public function init() {
        add_action('wp_ajax_smartnotify_generate_title', [$this, 'generateTitle']);
        add_action('wp_ajax_smartnotify_generate_summary', [$this, 'generateSummary']);
        add_action('wp_ajax_smartnotify_generate_tags', [$this, 'generateTags']);
        add_action('wp_ajax_smartnotify_analyze_sentiment', [$this, 'analyzeSentiment']);
        add_action('wp_ajax_smartnotify_test_api', [$this, 'testApiConnection']);

        // Modal AJAX handlers
        add_action('wp_ajax_smartnotify_generate_title_from_content', [$this, 'generateTitleFromContent']);
        add_action('wp_ajax_smartnotify_generate_summary_from_content', [$this, 'generateSummaryFromContent']);
        add_action('wp_ajax_smartnotify_generate_tags_from_content', [$this, 'generateTagsFromContent']);
        add_action('wp_ajax_smartnotify_analyze_sentiment_from_content', [$this, 'analyzeSentimentFromContent']);
        add_action('wp_ajax_smartnotify_save_news', [$this, 'saveNews']);
        add_action('wp_ajax_smartnotify_update_news', [$this, 'updateNews']);
        add_action('wp_ajax_smartnotify_get_news', [$this, 'getNews']);

        // Image generation handlers
        add_action('wp_ajax_smartnotify_generate_image', [$this, 'generateImage']);
        add_action('wp_ajax_smartnotify_test_image_api', [$this, 'testImageApiConnection']);

        // Image upload handler
        add_action('wp_ajax_smartnotify_upload_image', [$this, 'uploadImage']);
    }

    /**
     * Generate title via AJAX
     */
    public function generateTitle() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => __('ID de post inválido', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(['message' => __('Post no encontrado', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $content = wp_strip_all_tags($post->post_content);

        if (empty($content)) {
            wp_send_json_error(['message' => __('El contenido está vacío. Agrega contenido primero.', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $generator = $this->container->get('content.generator');
            $title = $generator->generateTitle($content);

            if (empty($title)) {
                wp_send_json_error(['message' => __('No se pudo generar el título', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            wp_send_json_success([
                'title' => $title,
                'message' => __('Título generado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate summary via AJAX
     */
    public function generateSummary() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => __('ID de post inválido', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(['message' => __('Post no encontrado', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $content = wp_strip_all_tags($post->post_content);

        if (empty($content)) {
            wp_send_json_error(['message' => __('El contenido está vacío. Agrega contenido primero.', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $generator = $this->container->get('content.generator');
            $summary = $generator->generateSummary($content);

            if (empty($summary)) {
                wp_send_json_error(['message' => __('No se pudo generar el resumen', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            // Save summary
            update_post_meta($post_id, '_smartnotify_summary', $summary);

            wp_send_json_success([
                'summary' => $summary,
                'message' => __('Resumen generado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate tags via AJAX
     */
    public function generateTags() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => __('ID de post inválido', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(['message' => __('Post no encontrado', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $content = wp_strip_all_tags($post->post_content);

        if (empty($content)) {
            wp_send_json_error(['message' => __('El contenido está vacío. Agrega contenido primero.', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $generator = $this->container->get('content.generator');
            $tags = $generator->generateTags($content);

            if (empty($tags)) {
                wp_send_json_error(['message' => __('No se pudieron generar etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            // Set tags
            wp_set_post_terms($post_id, $tags, 'smartnotify_tag');

            wp_send_json_success([
                'tags' => $tags,
                'message' => __('Etiquetas generadas exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Analyze sentiment via AJAX
     */
    public function analyzeSentiment() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => __('ID de post inválido', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(['message' => __('Post no encontrado', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $analyzer = $this->container->get('sentiment.analyzer');
            $result = $analyzer->analyzePost($post_id);

            if (!$result || isset($result['error'])) {
                wp_send_json_error(['message' => $result['error'] ?? __('Error al analizar sentimiento', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            // Save sentiment
            update_post_meta($post_id, '_smartnotify_sentiment', $result['sentiment']);
            update_post_meta($post_id, '_smartnotify_sentiment_confidence', $result['confidence']);

            wp_send_json_success([
                'sentiment' => $result['sentiment'],
                'confidence' => $result['confidence'],
                'label' => $analyzer->getSentimentLabel($result['sentiment']),
                'color' => $analyzer->getSentimentColor($result['sentiment']),
                'message' => __('Sentimiento analizado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Test API connection
     */
    public function testApiConnection() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $ai_factory = $this->container->get('ai.service');
            $service = $ai_factory->getService();

            if (!$service->isAvailable()) {
                wp_send_json_error([
                    'message' => __('API Key no configurada', SMARTNOTIFY_AI_TEXT_DOMAIN)
                ]);
            }

            // Test with a simple prompt
            $test_prompt = "Responde con la palabra 'OK' si puedes leer este mensaje.";
            $result = $service->generate($test_prompt, [
                'max_tokens' => 10,
                'temperature' => 0.1
            ]);

            if (empty($result)) {
                wp_send_json_error([
                    'message' => __('La API no respondió. Verifica tu API Key y que tengas créditos disponibles.', SMARTNOTIFY_AI_TEXT_DOMAIN)
                ]);
            }

            // Get provider info
            $provider = get_option('smartnotify_ai_provider', 'openai');
            $model = get_option('smartnotify_ai_model', 'gpt-4');

            wp_send_json_success([
                'message' => __('Conexión exitosa con la API', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'provider' => $provider,
                'model' => $model,
                'response' => $result
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Error al conectar con la API: %s', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    $e->getMessage()
                )
            ]);
        }
    }

    /**
     * Generate title from content
     */
    public function generateTitleFromContent() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(['message' => __('Contenido vacío', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $ai_factory = $this->container->get('ai.service');
            $service = $ai_factory->getService();

            if (!$service->isAvailable()) {
                wp_send_json_error(['message' => __('Servicio de IA no disponible', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            $title = $service->generateTitle($content);

            wp_send_json_success([
                'title' => $title,
                'message' => __('Título generado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate summary from content
     */
    public function generateSummaryFromContent() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(['message' => __('Contenido vacío', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $ai_factory = $this->container->get('ai.service');
            $service = $ai_factory->getService();

            if (!$service->isAvailable()) {
                wp_send_json_error(['message' => __('Servicio de IA no disponible', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            $summary = $service->generateSummary($content);

            wp_send_json_success([
                'summary' => $summary,
                'message' => __('Resumen generado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate tags from content
     */
    public function generateTagsFromContent() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(['message' => __('Contenido vacío', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $ai_factory = $this->container->get('ai.service');
            $service = $ai_factory->getService();

            if (!$service->isAvailable()) {
                wp_send_json_error(['message' => __('Servicio de IA no disponible', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            $tags = $service->generateTags($content);

            wp_send_json_success([
                'tags' => $tags,
                'message' => __('Etiquetas generadas exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Analyze sentiment from content
     */
    public function analyzeSentimentFromContent() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(['message' => __('Contenido vacío', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $ai_factory = $this->container->get('ai.service');
            $service = $ai_factory->getService();

            if (!$service->isAvailable()) {
                wp_send_json_error(['message' => __('Servicio de IA no disponible', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            }

            $result = $service->analyzeSentiment($content);
            $sentiment = $result['sentiment'] ?? 'neutral';
            $confidence = $result['confidence'] ?? null;

            // Map sentiment to colors
            $colors = [
                'positive' => '#10b981',
                'neutral' => '#6b7280',
                'negative' => '#ef4444',
            ];

            // Map sentiment to labels
            $labels = [
                'positive' => __('Positivo', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'neutral' => __('Neutral', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'negative' => __('Negativo', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ];

            $color = $colors[$sentiment] ?? '#6b7280';
            $label = $labels[$sentiment] ?? __('No analizado', SMARTNOTIFY_AI_TEXT_DOMAIN);

            wp_send_json_success([
                'sentiment' => $sentiment,
                'color' => $color,
                'label' => $label,
                'confidence' => $confidence,
                'message' => __('Sentimiento analizado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Save news from modal
     */
    public function saveNews() {
        check_ajax_referer('smartnotify_create_news', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $summary = isset($_POST['summary']) ? sanitize_textarea_field($_POST['summary']) : '';
        $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'draft';
        $analyzed_sentiment = isset($_POST['analyzed_sentiment']) ? sanitize_text_field($_POST['analyzed_sentiment']) : '';
        $analyzed_sentiment_confidence = isset($_POST['analyzed_sentiment_confidence']) ? floatval($_POST['analyzed_sentiment_confidence']) : 0;
        $featured_image_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;

        if (empty($title) || empty($content)) {
            wp_send_json_error(['message' => __('Título y contenido son requeridos', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            // Create post
            $post_data = [
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'smartnotify_news',
                'post_status' => $status,
            ];

            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                throw new \Exception($post_id->get_error_message());
            }

            // Save summary
            if (!empty($summary)) {
                update_post_meta($post_id, '_smartnotify_summary', $summary);
            }

            // Save tags
            if (!empty($tags)) {
                $tags_array = array_map('trim', explode(',', $tags));
                wp_set_object_terms($post_id, $tags_array, 'smartnotify_tag');
            }

            // Save category
            if ($category > 0) {
                wp_set_object_terms($post_id, [$category], 'smartnotify_category');
            }

            // Save sentiment if it was analyzed in the modal
            if (!empty($analyzed_sentiment)) {
                update_post_meta($post_id, '_smartnotify_sentiment', $analyzed_sentiment);
                if ($analyzed_sentiment_confidence > 0) {
                    update_post_meta($post_id, '_smartnotify_sentiment_confidence', $analyzed_sentiment_confidence);
                }
            }

            // Set featured image
            if ($featured_image_id > 0) {
                set_post_thumbnail($post_id, $featured_image_id);
            }

            $message = $status === 'publish'
                ? __('Noticia publicada exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN)
                : __('Borrador guardado exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN);

            wp_send_json_success([
                'message' => $message,
                'post_id' => $post_id,
                'edit_url' => get_edit_post_link($post_id, 'raw'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Get news data for editing
     */
    public function getNews() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (empty($post_id)) {
            wp_send_json_error(['message' => __('ID de noticia inválido', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'smartnotify_news') {
            wp_send_json_error(['message' => __('Noticia no encontrada', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        // Get metadata
        $summary = get_post_meta($post_id, '_smartnotify_summary', true);
        $sentiment = get_post_meta($post_id, '_smartnotify_sentiment', true);
        $sentiment_confidence = get_post_meta($post_id, '_smartnotify_sentiment_confidence', true);

        // Get tags
        $tags = wp_get_object_terms($post_id, 'smartnotify_tag', ['fields' => 'names']);
        $tags_string = is_array($tags) ? implode(', ', $tags) : '';

        // Get category
        $categories = wp_get_object_terms($post_id, 'smartnotify_category', ['fields' => 'ids']);
        $category_id = !empty($categories) && is_array($categories) ? $categories[0] : 0;

        // Get featured image
        $featured_image_id = get_post_thumbnail_id($post_id);
        $featured_image_url = $featured_image_id ? wp_get_attachment_url($featured_image_id) : '';

        // Map sentiment to colors and labels
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

        wp_send_json_success([
            'title' => $post->post_title,
            'content' => $post->post_content,
            'summary' => $summary,
            'tags' => $tags_string,
            'category' => $category_id,
            'sentiment' => $sentiment,
            'sentiment_confidence' => $sentiment_confidence ? floatval($sentiment_confidence) : 0,
            'sentiment_color' => !empty($sentiment) ? ($colors[$sentiment] ?? '#6b7280') : '',
            'sentiment_label' => !empty($sentiment) ? ($labels[$sentiment] ?? '') : '',
            'featured_image_id' => $featured_image_id,
            'featured_image_url' => $featured_image_url,
        ]);
    }

    /**
     * Update existing news
     */
    public function updateNews() {
        check_ajax_referer('smartnotify_create_news', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $summary = isset($_POST['summary']) ? sanitize_textarea_field($_POST['summary']) : '';
        $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'draft';
        $analyzed_sentiment = isset($_POST['analyzed_sentiment']) ? sanitize_text_field($_POST['analyzed_sentiment']) : '';
        $analyzed_sentiment_confidence = isset($_POST['analyzed_sentiment_confidence']) ? floatval($_POST['analyzed_sentiment_confidence']) : 0;
        $featured_image_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;

        if (empty($post_id) || empty($title) || empty($content)) {
            wp_send_json_error(['message' => __('Datos incompletos', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        // Verify post exists
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'smartnotify_news') {
            wp_send_json_error(['message' => __('Noticia no encontrada', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            // Update post
            $post_data = [
                'ID' => $post_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $status,
            ];

            $result = wp_update_post($post_data);

            if (is_wp_error($result)) {
                throw new \Exception($result->get_error_message());
            }

            // Update summary
            if (!empty($summary)) {
                update_post_meta($post_id, '_smartnotify_summary', $summary);
            } else {
                delete_post_meta($post_id, '_smartnotify_summary');
            }

            // Update tags
            if (!empty($tags)) {
                $tags_array = array_map('trim', explode(',', $tags));
                wp_set_object_terms($post_id, $tags_array, 'smartnotify_tag');
            } else {
                wp_set_object_terms($post_id, [], 'smartnotify_tag');
            }

            // Update category
            if ($category > 0) {
                wp_set_object_terms($post_id, [$category], 'smartnotify_category');
            } else {
                wp_set_object_terms($post_id, [], 'smartnotify_category');
            }

            // Save sentiment if it was analyzed in the modal
            if (!empty($analyzed_sentiment)) {
                update_post_meta($post_id, '_smartnotify_sentiment', $analyzed_sentiment);
                if ($analyzed_sentiment_confidence > 0) {
                    update_post_meta($post_id, '_smartnotify_sentiment_confidence', $analyzed_sentiment_confidence);
                }
            }

            // Update featured image
            if ($featured_image_id > 0) {
                set_post_thumbnail($post_id, $featured_image_id);
            } else {
                // If 0, remove featured image
                delete_post_thumbnail($post_id);
            }

            wp_send_json_success([
                'message' => __('Noticia actualizada exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'post_id' => $post_id,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate image via AJAX
     */
    public function generateImage() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';

            if (empty($prompt)) {
                throw new \Exception(__('El prompt no puede estar vacío', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Get image service
            $image_provider = get_option('smartnotify_image_provider', 'huggingface');

            if ($image_provider === 'none') {
                throw new \Exception(__('La generación de imágenes está deshabilitada', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            $api_key = get_option('smartnotify_image_api_key', '');

            if (empty($api_key)) {
                throw new \Exception(__('No se ha configurado la API Key para generación de imágenes', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Create image service
            $image_service = null;

            if ($image_provider === 'huggingface') {
                $image_service = new \SmartNotifyAI\Services\Image\HuggingfaceService($api_key);
            } elseif ($image_provider === 'dalle') {
                $image_service = new \SmartNotifyAI\Services\Image\DalleService($api_key);
            } elseif ($image_provider === 'stability') {
                // TODO: Implement Stability AI service in the future
                throw new \Exception(__('Stability AI no está implementado aún', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            if (!$image_service || !$image_service->isAvailable()) {
                throw new \Exception(__('El servicio de generación de imágenes no está disponible', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Generate image
            $result = $image_service->generateImage($prompt, [
                'size' => '1024x1024',
                'quality' => 'standard',
            ]);

            wp_send_json_success([
                'message' => __('Imagen generada exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN),
                'attachment_id' => $result['attachment_id'],
                'url' => wp_get_attachment_url($result['attachment_id']),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Test image API connection
     */
    public function testImageApiConnection() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permisos insuficientes', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
        }

        try {
            $image_provider = get_option('smartnotify_image_provider', 'huggingface');
            $api_key = get_option('smartnotify_image_api_key', '');

            if ($image_provider === 'none') {
                throw new \Exception(__('La generación de imágenes está deshabilitada', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            if (empty($api_key)) {
                throw new \Exception(__('No se ha configurado la API Key', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Create a simple test prompt
            $test_prompt = 'A simple test image';

            // Create image service based on provider
            $image_service = null;

            if ($image_provider === 'huggingface') {
                $image_service = new \SmartNotifyAI\Services\Image\HuggingfaceService($api_key);
            } elseif ($image_provider === 'dalle') {
                $image_service = new \SmartNotifyAI\Services\Image\DalleService($api_key);
            } elseif ($image_provider === 'stability') {
                throw new \Exception(__('Stability AI no está implementado aún', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            if (!$image_service || !$image_service->isAvailable()) {
                throw new \Exception(__('El servicio de generación de imágenes no está disponible', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // For Hugging Face, test with a POST request (inference API requires POST)
            if ($image_provider === 'huggingface') {
                $response = wp_remote_post('https://router.huggingface.co/hf-inference/models/stabilityai/stable-diffusion-xl-base-1.0', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $api_key,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => wp_json_encode([
                        'inputs' => 'test connection',
                    ]),
                    'timeout' => 30,
                ]);

                if (is_wp_error($response)) {
                    throw new \Exception(__('Error de conexión: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $response->get_error_message());
                }

                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);

                // Log para depuración
                error_log('Hugging Face test response code: ' . $response_code);
                error_log('Hugging Face test response body: ' . substr($response_body, 0, 500));

                if ($response_code === 401 || $response_code === 403) {
                    $data = json_decode($response_body, true);
                    $detail = isset($data['error']) ? ' - ' . $data['error'] : '';
                    throw new \Exception(__('API Key inválida o sin permisos', SMARTNOTIFY_AI_TEXT_DOMAIN) . $detail . __(' Verifica que tu token tenga permisos de lectura en https://huggingface.co/settings/tokens', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 404) {
                    throw new \Exception(__('Modelo no encontrado (HTTP 404). El modelo stabilityai/stable-diffusion-xl-base-1.0 no está disponible en la Inference API o requiere una cuenta PRO de Hugging Face. Por favor, verifica tu suscripción en https://huggingface.co/pricing', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 410) {
                    throw new \Exception(__('El modelo ha sido deprecado (HTTP 410). Código de respuesta: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $response_code . __(' - Respuesta: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . substr($response_body, 0, 200));
                }

                if ($response_code !== 200 && $response_code !== 503) {
                    $data = json_decode($response_body, true);

                    // Intentar obtener el mensaje de error de diferentes formas
                    $error_message = 'Error desconocido';

                    if (isset($data['error'])) {
                        if (is_string($data['error'])) {
                            $error_message = $data['error'];
                        } elseif (is_array($data['error']) && isset($data['error']['message'])) {
                            $error_message = $data['error']['message'];
                        }
                    } elseif (isset($data['message'])) {
                        $error_message = $data['message'];
                    } elseif (!empty($response_body) && strlen($response_body) < 200) {
                        // Si la respuesta es corta, mostrarla directamente
                        $error_message = $response_body;
                    }

                    throw new \Exception(__('Error HTTP ' . $response_code . ': ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $error_message);
                }

                wp_send_json_success([
                    'message' => __('✓ Conexión exitosa con Hugging Face', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'provider' => 'Hugging Face',
                    'model' => 'Stable Diffusion XL 1.0'
                ]);
            } elseif ($image_provider === 'dalle') {
                // For DALL-E, test with a simple API call
                $response = wp_remote_get('https://api.openai.com/v1/models', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $api_key,
                    ],
                    'timeout' => 10,
                ]);

                if (is_wp_error($response)) {
                    throw new \Exception(__('Error de conexión: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $response->get_error_message());
                }

                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);

                // Debug logging
                error_log('DALL-E test response code: ' . $response_code);
                error_log('DALL-E test response body: ' . $response_body);

                // Specific HTTP status code handling
                if ($response_code === 401) {
                    throw new \Exception(__('API Key de OpenAI inválida. Verifica tu clave de API.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 403) {
                    throw new \Exception(__('Acceso denegado. Verifica que tu API Key tenga permisos para DALL-E.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 404) {
                    throw new \Exception(__('Endpoint no encontrado. Verifica la configuración del servicio.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code !== 200) {
                    $data = json_decode($response_body, true);

                    // Multiple ways to extract error message
                    $error_message = 'Error desconocido';

                    if (isset($data['error'])) {
                        if (is_string($data['error'])) {
                            $error_message = $data['error'];
                        } elseif (is_array($data['error']) && isset($data['error']['message'])) {
                            $error_message = $data['error']['message'];
                        }
                    } elseif (isset($data['message'])) {
                        $error_message = $data['message'];
                    } elseif (!empty($response_body) && strlen($response_body) < 200) {
                        $error_message = $response_body;
                    }

                    throw new \Exception(__('Error HTTP ' . $response_code . ': ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $error_message);
                }

                wp_send_json_success([
                    'message' => __('✓ Conexión exitosa con OpenAI DALL-E', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'provider' => 'OpenAI',
                    'model' => 'DALL-E 3'
                ]);
            } elseif ($image_provider === 'stability') {
                // For Stability AI, test with account balance endpoint
                $response = wp_remote_get('https://api.stability.ai/v1/user/balance', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $api_key,
                    ],
                    'timeout' => 10,
                ]);

                if (is_wp_error($response)) {
                    throw new \Exception(__('Error de conexión: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $response->get_error_message());
                }

                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);

                // Debug logging
                error_log('Stability AI test response code: ' . $response_code);
                error_log('Stability AI test response body: ' . $response_body);

                // Specific HTTP status code handling
                if ($response_code === 401) {
                    throw new \Exception(__('API Key de Stability AI inválida. Verifica tu clave de API.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 403) {
                    throw new \Exception(__('Acceso denegado. Verifica los permisos de tu API Key.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code === 404) {
                    throw new \Exception(__('Endpoint no encontrado. Verifica la configuración del servicio.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }

                if ($response_code !== 200) {
                    $data = json_decode($response_body, true);

                    // Multiple ways to extract error message
                    $error_message = 'Error desconocido';

                    if (isset($data['message'])) {
                        $error_message = $data['message'];
                    } elseif (isset($data['error'])) {
                        if (is_string($data['error'])) {
                            $error_message = $data['error'];
                        } elseif (is_array($data['error']) && isset($data['error']['message'])) {
                            $error_message = $data['error']['message'];
                        }
                    } elseif (!empty($response_body) && strlen($response_body) < 200) {
                        $error_message = $response_body;
                    }

                    throw new \Exception(__('Error HTTP ' . $response_code . ': ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $error_message);
                }

                wp_send_json_success([
                    'message' => __('✓ Conexión exitosa con Stability AI', SMARTNOTIFY_AI_TEXT_DOMAIN),
                    'provider' => 'Stability AI',
                    'model' => 'Stable Diffusion'
                ]);
            } else {
                throw new \Exception(__('Proveedor de imágenes no configurado', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Upload image via AJAX
     */
    public function uploadImage() {
        check_ajax_referer('smartnotify_ai_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => __('Permisos insuficientes para subir archivos', SMARTNOTIFY_AI_TEXT_DOMAIN)]);
            return;
        }

        try {
            if (empty($_POST['image_data'])) {
                throw new \Exception(__('No se proporcionaron datos de imagen', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            if (empty($_POST['filename'])) {
                throw new \Exception(__('No se proporcionó nombre de archivo', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            $image_data = sanitize_text_field($_POST['image_data']);
            $filename = sanitize_file_name($_POST['filename']);

            // Decode base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $matches)) {
                $image_type = $matches[1];
                $image_data = substr($image_data, strpos($image_data, ',') + 1);
                $image_data = base64_decode($image_data);

                if ($image_data === false) {
                    throw new \Exception(__('Error al decodificar la imagen', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }
            } else {
                throw new \Exception(__('Formato de imagen inválido', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Prepare filename
            $upload_dir = wp_upload_dir();
            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
            $new_filename = 'smartnotify-' . time() . '-' . wp_generate_password(8, false) . '.' . $file_ext;
            $file_path = $upload_dir['path'] . '/' . $new_filename;

            // Save file
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $result = file_put_contents($file_path, $image_data);

            if ($result === false) {
                throw new \Exception(__('Error al guardar el archivo', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }

            // Create attachment
            $file_type = wp_check_filetype($new_filename, null);
            $attachment = [
                'post_mime_type' => $file_type['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $new_filename),
                'post_content' => '',
                'post_status' => 'inherit'
            ];

            $attachment_id = wp_insert_attachment($attachment, $file_path);

            if (is_wp_error($attachment_id)) {
                @unlink($file_path);
                throw new \Exception(__('Error al crear el attachment: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $attachment_id->get_error_message());
            }

            // Generate metadata
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            wp_send_json_success([
                'attachment_id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'message' => __('Imagen subida exitosamente', SMARTNOTIFY_AI_TEXT_DOMAIN)
            ]);

        } catch (\Exception $e) {
            error_log('SmartNotify Upload Image Error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
