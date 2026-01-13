<?php
/**
 * OpenAI Service Implementation
 *
 * @package SmartNotifyAI\Services\AI
 */

namespace SmartNotifyAI\Services\AI;

use SmartNotifyAI\Core\Config;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Service class
 */
class OpenAIService implements AIServiceInterface {

    /**
     * API Key
     *
     * @var string
     */
    private $api_key;

    /**
     * Configuration
     *
     * @var Config
     */
    private $config;

    /**
     * API endpoint
     *
     * @var string
     */
    private $api_endpoint = 'https://api.openai.com/v1/chat/completions';

    /**
     * Constructor
     *
     * @param string $api_key
     * @param Config $config
     */
    public function __construct($api_key, Config $config) {
        $this->api_key = $api_key;
        $this->config = $config;
    }

    /**
     * Generate completion
     *
     * @param string $prompt Prompt text
     * @param array $options Additional options
     * @return string
     */
    public function generate($prompt, array $options = []) {
        if (!$this->isAvailable()) {
            throw new \Exception(__('API Key no configurada. Configura tu API key en Ajustes.', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }

        // Validate prompt
        if (empty(trim($prompt))) {
            throw new \Exception(__('El prompt está vacío', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }

        $model = $options['model'] ?? $this->config->get('ai.model', 'gpt-4');
        $max_tokens = $options['max_tokens'] ?? $this->config->get('ai.max_tokens', 2000);
        $temperature = $options['temperature'] ?? $this->config->get('ai.temperature', 0.7);
        $timeout = $options['timeout'] ?? 60;

        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ];

        // Log request if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SmartNotify AI - OpenAI Request: ' . wp_json_encode([
                'model' => $model,
                'max_tokens' => $max_tokens,
                'prompt_length' => strlen($prompt),
            ]));
        }

        $response = wp_remote_post($this->api_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => $timeout,
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('SmartNotify AI - OpenAI Connection Error: ' . $error_code . ' - ' . $error_message);
            }
            
            // Handle specific WP errors
            if ($error_code === 'http_request_failed') {
                throw new \Exception(__('No se pudo conectar con OpenAI. Verifica tu conexión a internet.', SMARTNOTIFY_AI_TEXT_DOMAIN));
            }
            
            throw new \Exception(__('Error de conexión: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Handle empty response
        if (empty($response_body)) {
            throw new \Exception(__('OpenAI devolvió una respuesta vacía', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }
        
        $body = json_decode($response_body, true);
        
        // Handle JSON decode errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('SmartNotify AI - JSON Decode Error: ' . json_last_error_msg());
            }
            throw new \Exception(__('Error al procesar respuesta de OpenAI', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }

        // Check for API errors
        if ($response_code !== 200) {
            $error_message = $body['error']['message'] ?? __('Error desconocido', SMARTNOTIFY_AI_TEXT_DOMAIN);
            $error_type = $body['error']['type'] ?? 'unknown';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('SmartNotify AI - OpenAI API Error (HTTP ' . $response_code . '): ' . $error_type . ' - ' . $error_message);
            }

            if ($response_code === 401) {
                throw new \Exception(__('API Key inválida. Verifica tu clave de OpenAI en Ajustes.', SMARTNOTIFY_AI_TEXT_DOMAIN));
            } elseif ($response_code === 429) {
                // Check if it's rate limit or quota
                if (strpos($error_message, 'quota') !== false || strpos($error_message, 'billing') !== false) {
                    throw new \Exception(__('Sin créditos en tu cuenta de OpenAI. Recarga tu cuenta.', SMARTNOTIFY_AI_TEXT_DOMAIN));
                }
                throw new \Exception(__('Límite de solicitudes excedido. Espera 1 minuto e intenta de nuevo.', SMARTNOTIFY_AI_TEXT_DOMAIN));
            } elseif ($response_code === 400) {
                throw new \Exception(__('Solicitud inválida: ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $error_message);
            } elseif ($response_code === 402) {
                throw new \Exception(__('Sin créditos. Recarga tu cuenta de OpenAI.', SMARTNOTIFY_AI_TEXT_DOMAIN));
            } elseif ($response_code === 500 || $response_code === 503) {
                throw new \Exception(__('OpenAI está experimentando problemas. Intenta de nuevo en unos minutos.', SMARTNOTIFY_AI_TEXT_DOMAIN));
            } else {
                throw new \Exception(__('Error de API (HTTP ', SMARTNOTIFY_AI_TEXT_DOMAIN) . $response_code . '): ' . $error_message);
            }
        }

        // Validate response structure
        if (!isset($body['choices']) || !is_array($body['choices']) || empty($body['choices'])) {
            throw new \Exception(__('Respuesta de API con formato inesperado (sin choices)', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }
        
        if (!isset($body['choices'][0]['message']['content'])) {
            throw new \Exception(__('Respuesta de API con formato inesperado (sin content)', SMARTNOTIFY_AI_TEXT_DOMAIN));
        }
        
        $content = trim($body['choices'][0]['message']['content']);
        
        // Log success if WP_DEBUG
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SmartNotify AI - OpenAI Success: ' . strlen($content) . ' characters generated');
        }
        
        return $content;
    }

    /**
     * Analyze sentiment
     *
     * @param string $text Text to analyze
     * @return array
     */
    public function analyzeSentiment($text) {
        $prompt = "Analiza el sentimiento del siguiente texto y responde ÚNICAMENTE con una de estas palabras: positivo, neutral, o negativo.\n\nTexto: {$text}";

        $result = $this->generate($prompt, ['temperature' => 0.3, 'max_tokens' => 10]);

        $sentiment = strtolower(trim($result));

        // Map Spanish to English
        $sentiment_map = [
            'positivo' => 'positive',
            'neutral' => 'neutral',
            'negativo' => 'negative',
        ];

        return [
            'sentiment' => $sentiment_map[$sentiment] ?? 'neutral',
            'confidence' => 0.85,
        ];
    }

    /**
     * Generate tags
     *
     * @param string $content Content to analyze
     * @return array
     */
    public function generateTags($content) {
        $prompt = "Genera de 3 a 5 etiquetas relevantes para el siguiente contenido. Devuelve solo las etiquetas separadas por comas, sin numeración ni explicaciones.\n\nContenido: {$content}";

        $result = $this->generate($prompt, ['temperature' => 0.5]);

        if (empty($result)) {
            return [];
        }

        $tags = array_map('trim', explode(',', $result));
        return array_filter($tags);
    }

    /**
     * Generate summary
     *
     * @param string $content Content to summarize
     * @return string
     */
    public function generateSummary($content) {
        $prompt = "Crea un resumen conciso del siguiente contenido en 2-3 oraciones. El resumen debe capturar los puntos principales.\n\nContenido: {$content}";

        return $this->generate($prompt, ['temperature' => 0.5]);
    }

    /**
     * Generate title
     *
     * @param string $content Content to generate title from
     * @return string
     */
    public function generateTitle($content) {
        $prompt = "Genera un título atractivo y conciso para el siguiente contenido. El título debe ser informativo y captar la atención. Devuelve solo el título, sin comillas ni explicaciones.\n\nContenido: {$content}";

        return $this->generate($prompt, ['temperature' => 0.7, 'max_tokens' => 100]);
    }

    /**
     * Check if service is available
     *
     * @return bool
     */
    public function isAvailable() {
        return !empty($this->api_key);
    }
}
