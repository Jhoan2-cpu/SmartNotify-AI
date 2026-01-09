<?php
/**
 * Image Generation Service Interface
 *
 * @package SmartNotifyAI\Services\Image
 */

namespace SmartNotifyAI\Services\Image;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Image Service Interface
 */
interface ImageServiceInterface {

    /**
     * Generate image from prompt
     *
     * @param string $prompt Image description/prompt
     * @param array $options Additional options
     * @return array Image data with 'url' and 'id'
     */
    public function generateImage($prompt, array $options = []);

    /**
     * Check if service is available
     *
     * @return bool
     */
    public function isAvailable();
}
