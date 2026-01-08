<?php
/**
 * AI Service Interface
 *
 * @package SmartNotifyAI\Services\AI
 */

namespace SmartNotifyAI\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Service Interface
 */
interface AIServiceInterface {

    /**
     * Generate completion
     *
     * @param string $prompt Prompt text
     * @param array $options Additional options
     * @return string
     */
    public function generate($prompt, array $options = []);

    /**
     * Analyze sentiment
     *
     * @param string $text Text to analyze
     * @return array
     */
    public function analyzeSentiment($text);

    /**
     * Generate tags
     *
     * @param string $content Content to analyze
     * @return array
     */
    public function generateTags($content);

    /**
     * Generate summary
     *
     * @param string $content Content to summarize
     * @return string
     */
    public function generateSummary($content);

    /**
     * Generate title
     *
     * @param string $content Content to generate title from
     * @return string
     */
    public function generateTitle($content);

    /**
     * Check if service is available
     *
     * @return bool
     */
    public function isAvailable();
}
