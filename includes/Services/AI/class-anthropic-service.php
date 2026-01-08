<?php
/**
 * Anthropic (Claude) Service Implementation
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
 * Anthropic Service class
 */
class AnthropicService implements AIServiceInterface {

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
    private $api_endpoint = 'https://api.anthropic.com/v1/messages';

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
            return '';
        }

        $model = $options['model'] ?? 'claude-sonnet-4-20250514';
        $max_tokens = $options['max_tokens'] ?? $this->config->get('ai.max_tokens', 2000);
        $temperature = $options['temperature'] ?? $this->config->get('ai.temperature', 0.7);

        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ];

        $response = wp_remote_post($this->api_endpoint, [
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('Anthropic API Error: ' . $error_message);
            throw new \Exception('Error de conexión: ' . $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $body = json_decode($response_body, true);

        // Check for API errors
        if ($response_code !== 200) {
            $error_message = $body['error']['message'] ?? 'Error desconocido';
            error_log('Anthropic API Error (HTTP ' . $response_code . '): ' . $error_message);

            if ($response_code === 401) {
                throw new \Exception('API Key inválida. Verifica tu clave de Anthropic.');
            } elseif ($response_code === 429) {
                throw new \Exception('Límite de rate excedido. Espera un momento e intenta de nuevo.');
            } elseif ($response_code === 400) {
                throw new \Exception('Solicitud inválida: ' . $error_message);
            } else {
                throw new \Exception('Error de API (HTTP ' . $response_code . '): ' . $error_message);
            }
        }

        if (isset($body['content'][0]['text'])) {
            return trim($body['content'][0]['text']);
        }

        throw new \Exception('Respuesta de API vacía o formato inesperado');
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
