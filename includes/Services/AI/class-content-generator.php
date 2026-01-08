<?php
/**
 * Content Generator Service
 *
 * @package SmartNotifyAI\Services\AI
 */

namespace SmartNotifyAI\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Generator class
 */
class ContentGenerator {

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
     * Generate title from content
     *
     * @param string $content Post content
     * @return string
     */
    public function generateTitle($content) {
        $service = $this->ai_service_factory->getService();

        if (!$service->isAvailable()) {
            return '';
        }

        try {
            return $service->generateTitle($content);
        } catch (\Exception $e) {
            error_log('Title Generation Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate summary from content
     *
     * @param string $content Post content
     * @return string
     */
    public function generateSummary($content) {
        $service = $this->ai_service_factory->getService();

        if (!$service->isAvailable()) {
            return '';
        }

        try {
            return $service->generateSummary($content);
        } catch (\Exception $e) {
            error_log('Summary Generation Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate tags from content
     *
     * @param string $content Post content
     * @return array
     */
    public function generateTags($content) {
        $service = $this->ai_service_factory->getService();

        if (!$service->isAvailable()) {
            return [];
        }

        try {
            return $service->generateTags($content);
        } catch (\Exception $e) {
            error_log('Tags Generation Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate all content elements for a post
     *
     * @param string $content Base content
     * @return array
     */
    public function generateAll($content) {
        return [
            'title' => $this->generateTitle($content),
            'summary' => $this->generateSummary($content),
            'tags' => $this->generateTags($content),
        ];
    }
}
