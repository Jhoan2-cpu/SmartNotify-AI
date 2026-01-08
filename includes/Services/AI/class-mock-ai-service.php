<?php
/**
 * Mock AI Service for Development
 *
 * @package SmartNotifyAI\Services\AI
 */

namespace SmartNotifyAI\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mock AI Service class for development and testing
 */
class MockAIService implements AIServiceInterface {

    /**
     * Generate completion
     *
     * @param string $prompt Prompt text
     * @param array $options Additional options
     * @return string
     */
    public function generate($prompt, array $options = []) {
        return 'Mock AI response: ' . substr($prompt, 0, 50) . '...';
    }

    /**
     * Analyze sentiment
     *
     * @param string $text Text to analyze
     * @return array
     */
    public function analyzeSentiment($text) {
        // Simple keyword-based mock sentiment analysis
        $positive_keywords = ['bueno', 'excelente', 'genial', 'fantástico', 'positivo', 'alegre'];
        $negative_keywords = ['malo', 'terrible', 'pésimo', 'negativo', 'triste', 'problema'];

        $text_lower = strtolower($text);
        $sentiment = 'neutral';

        foreach ($positive_keywords as $keyword) {
            if (strpos($text_lower, $keyword) !== false) {
                $sentiment = 'positive';
                break;
            }
        }

        foreach ($negative_keywords as $keyword) {
            if (strpos($text_lower, $keyword) !== false) {
                $sentiment = 'negative';
                break;
            }
        }

        return [
            'sentiment' => $sentiment,
            'confidence' => 0.75,
        ];
    }

    /**
     * Generate tags
     *
     * @param string $content Content to analyze
     * @return array
     */
    public function generateTags($content) {
        return ['tecnología', 'noticias', 'actualidad', 'información'];
    }

    /**
     * Generate summary
     *
     * @param string $content Content to summarize
     * @return string
     */
    public function generateSummary($content) {
        $words = str_word_count($content, 1, 'áéíóúñÁÉÍÓÚÑ');
        $summary_words = array_slice($words, 0, min(30, count($words)));
        return implode(' ', $summary_words) . '...';
    }

    /**
     * Generate title
     *
     * @param string $content Content to generate title from
     * @return string
     */
    public function generateTitle($content) {
        $words = str_word_count($content, 1, 'áéíóúñÁÉÍÓÚÑ');
        $title_words = array_slice($words, 0, min(8, count($words)));
        return ucfirst(implode(' ', $title_words));
    }

    /**
     * Check if service is available
     *
     * @return bool
     */
    public function isAvailable() {
        return true; // Mock service is always available
    }
}
