<?php
/**
 * AI Service Factory
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
 * AI Service Factory class
 */
class AIServiceFactory {

    /**
     * Configuration instance
     *
     * @var Config
     */
    private $config;

    /**
     * Service instance
     *
     * @var AIServiceInterface
     */
    private $service;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Get AI service instance
     *
     * @return AIServiceInterface
     */
    public function getService() {
        if ($this->service === null) {
            $this->service = $this->createService();
        }

        return $this->service;
    }

    /**
     * Create AI service based on configuration
     *
     * @return AIServiceInterface
     */
    private function createService() {
        $provider = $this->config->get('ai.provider', 'openai');
        $api_key = $this->config->get('ai.api_key');

        switch ($provider) {
            case 'openai':
                return new OpenAIService($api_key, $this->config);

            case 'anthropic':
                return new AnthropicService($api_key, $this->config);

            default:
                // Fallback to mock service for development
                return new MockAIService();
        }
    }

    /**
     * Check if service is configured
     *
     * @return bool
     */
    public function isConfigured() {
        $api_key = $this->config->get('ai.api_key');
        return !empty($api_key);
    }
}
