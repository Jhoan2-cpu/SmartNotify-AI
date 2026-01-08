<?php
/**
 * Sentiment Analyzer Service
 *
 * @package SmartNotifyAI\Services\AI
 */

namespace SmartNotifyAI\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sentiment Analyzer class
 */
class SentimentAnalyzer {

    /**
     * AI Service Factory
     *
     * @var AIServiceFactory
     */
    private $ai_service_factory;

    /**
     * Constructor
     *
     * @param AIServiceFactory $ai_service_factory
     */
    public function __construct(AIServiceFactory $ai_service_factory) {
        $this->ai_service_factory = $ai_service_factory;
    }

    /**
     * Analyze post sentiment
     *
     * @param int $post_id Post ID
     * @return array|false
     */
    public function analyzePost($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return false;
        }

        $content = $post->post_title . ' ' . $post->post_content;
        $content = wp_strip_all_tags($content);

        return $this->analyze($content);
    }

    /**
     * Analyze text sentiment
     *
     * @param string $text Text to analyze
     * @return array
     */
    public function analyze($text) {
        $service = $this->ai_service_factory->getService();

        if (!$service->isAvailable()) {
            return [
                'sentiment' => 'neutral',
                'confidence' => 0,
                'error' => __('Servicio de IA no disponible', SMARTNOTIFY_AI_TEXT_DOMAIN),
            ];
        }

        try {
            $result = $service->analyzeSentiment($text);

            return [
                'sentiment' => $result['sentiment'] ?? 'neutral',
                'confidence' => $result['confidence'] ?? 0,
            ];
        } catch (\Exception $e) {
            error_log('Sentiment Analysis Error: ' . $e->getMessage());

            return [
                'sentiment' => 'neutral',
                'confidence' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get sentiment label
     *
     * @param string $sentiment Sentiment value
     * @return string
     */
    public function getSentimentLabel($sentiment) {
        $labels = [
            'positive' => __('Positivo', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'neutral' => __('Neutral', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'negative' => __('Negativo', SMARTNOTIFY_AI_TEXT_DOMAIN),
        ];

        return $labels[$sentiment] ?? $labels['neutral'];
    }

    /**
     * Get sentiment color
     *
     * @param string $sentiment Sentiment value
     * @return string
     */
    public function getSentimentColor($sentiment) {
        $colors = [
            'positive' => '#10b981',
            'neutral' => '#6b7280',
            'negative' => '#ef4444',
        ];

        return $colors[$sentiment] ?? $colors['neutral'];
    }
}
