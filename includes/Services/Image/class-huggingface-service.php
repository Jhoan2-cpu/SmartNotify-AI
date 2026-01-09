<?php
/**
 * Hugging Face Image Generation Service
 *
 * @package SmartNotifyAI\Services\Image
 */

namespace SmartNotifyAI\Services\Image;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hugging Face Service class
 */
class HuggingfaceService implements ImageServiceInterface {

    /**
     * API Key
     *
     * @var string
     */
    private $api_key;

    /**
     * API endpoint for Stable Diffusion
     *
     * @var string
     */
    private $api_endpoint = 'https://router.huggingface.co/hf-inference/models/stabilityai/stable-diffusion-xl-base-1.0';

    /**
     * Constructor
     *
     * @param string $api_key
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Generate image from prompt
     *
     * @param string $prompt Image description/prompt
     * @param array $options Additional options
     * @return array Image data with 'url' and 'attachment_id'
     */
    public function generateImage($prompt, array $options = []) {
        if (!$this->isAvailable()) {
            throw new \Exception('Servicio de generación de imágenes no disponible');
        }

        // Prepare request body
        $body = [
            'inputs' => $prompt,
        ];

        // Add negative prompt if provided
        if (!empty($options['negative_prompt'])) {
            $body['negative_prompt'] = $options['negative_prompt'];
        }

        // Make API request
        $response = wp_remote_post($this->api_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 120, // Hugging Face can take longer
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('Hugging Face API Error: ' . $error_message);
            throw new \Exception('Error de conexión: ' . $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Handle different response codes
        if ($response_code === 503) {
            // Model is loading, retry after a delay
            error_log('Hugging Face model is loading, retrying...');
            sleep(5);
            return $this->generateImage($prompt, $options);
        }

        if ($response_code !== 200) {
            // Try to parse error message
            $data = json_decode($response_body, true);
            $error_message = $data['error'] ?? 'Error desconocido';
            error_log('Hugging Face API Error (HTTP ' . $response_code . '): ' . $error_message);
            throw new \Exception('Error de API: ' . $error_message);
        }

        // The response is the raw image data
        if (empty($response_body)) {
            throw new \Exception('Respuesta de API vacía');
        }

        // Save image to WordPress media library
        $attachment_id = $this->saveImageToMedia($response_body, $prompt);

        return [
            'url' => wp_get_attachment_url($attachment_id),
            'attachment_id' => $attachment_id,
        ];
    }

    /**
     * Save image data to media library
     *
     * @param string $image_data Raw image binary data
     * @param string $description Image description
     * @return int Attachment ID
     */
    private function saveImageToMedia($image_data, $description) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Create temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'hf-generated-' . time() . '.png';
        $file_path = $upload_dir['path'] . '/' . $filename;

        // Write image data to file
        $result = file_put_contents($file_path, $image_data);

        if ($result === false) {
            error_log('Error writing image file: ' . $file_path);
            throw new \Exception('Error al guardar la imagen temporalmente');
        }

        // Prepare file array for WordPress
        $file_array = [
            'name' => $filename,
            'tmp_name' => $file_path,
        ];

        // Insert into media library
        $attachment_id = media_handle_sideload($file_array, 0, $description);

        if (is_wp_error($attachment_id)) {
            @unlink($file_path);
            error_log('Error creating attachment: ' . $attachment_id->get_error_message());
            throw new \Exception('Error al guardar la imagen en la biblioteca de medios');
        }

        return $attachment_id;
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
