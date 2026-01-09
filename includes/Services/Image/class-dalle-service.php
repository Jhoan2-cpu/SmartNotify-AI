<?php
/**
 * DALL-E Image Generation Service
 *
 * @package SmartNotifyAI\Services\Image
 */

namespace SmartNotifyAI\Services\Image;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * DALL-E Service class
 */
class DalleService implements ImageServiceInterface {

    /**
     * API Key
     *
     * @var string
     */
    private $api_key;

    /**
     * API endpoint
     *
     * @var string
     */
    private $api_endpoint = 'https://api.openai.com/v1/images/generations';

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

        $size = $options['size'] ?? '1024x1024';
        $quality = $options['quality'] ?? 'standard';

        $body = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'quality' => $quality,
        ];

        $response = wp_remote_post($this->api_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('DALL-E API Error: ' . $error_message);
            throw new \Exception('Error de conexión: ' . $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = $data['error']['message'] ?? 'Error desconocido';
            error_log('DALL-E API Error (HTTP ' . $response_code . '): ' . $error_message);
            throw new \Exception('Error de API: ' . $error_message);
        }

        if (!isset($data['data'][0]['url'])) {
            throw new \Exception('Respuesta de API vacía o formato inesperado');
        }

        $image_url = $data['data'][0]['url'];

        // Download and save image to WordPress media library
        $attachment_id = $this->downloadImageToMedia($image_url, $prompt);

        return [
            'url' => $image_url,
            'attachment_id' => $attachment_id,
        ];
    }

    /**
     * Download image and save to media library
     *
     * @param string $image_url
     * @param string $description
     * @return int Attachment ID
     */
    private function downloadImageToMedia($image_url, $description) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $tmp = download_url($image_url);

        if (is_wp_error($tmp)) {
            error_log('Error downloading image: ' . $tmp->get_error_message());
            throw new \Exception('Error al descargar la imagen');
        }

        $file_array = [
            'name' => 'dalle-generated-' . time() . '.png',
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $description);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            error_log('Error creating attachment: ' . $attachment_id->get_error_message());
            throw new \Exception('Error al guardar la imagen');
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
