<?php
/**
 * News Taxonomies
 *
 * @package SmartNotifyAI\Taxonomies
 */

namespace SmartNotifyAI\Taxonomies;

use SmartNotifyAI\PostTypes\NewsPostType;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * News Taxonomies class
 */
class NewsTaxonomies {

    /**
     * Category taxonomy slug
     *
     * @var string
     */
    const CATEGORY = 'smartnotify_category';

    /**
     * Tag taxonomy slug
     *
     * @var string
     */
    const TAG = 'smartnotify_tag';

    /**
     * Register taxonomies
     */
    public function register() {
        $this->register_category();
        $this->register_tag();
    }

    /**
     * Register category taxonomy
     */
    private function register_category() {
        $labels = [
            'name' => __('Categorías', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'singular_name' => __('Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'search_items' => __('Buscar Categorías', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'all_items' => __('Todas las Categorías', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'parent_item' => __('Categoría Padre', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'parent_item_colon' => __('Categoría Padre:', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'edit_item' => __('Editar Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'update_item' => __('Actualizar Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'add_new_item' => __('Agregar Nueva Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'new_item_name' => __('Nuevo Nombre de Categoría', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'menu_name' => __('Categorías', SMARTNOTIFY_AI_TEXT_DOMAIN),
        ];

        $args = [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'noticia-categoria'],
        ];

        register_taxonomy(self::CATEGORY, [NewsPostType::POST_TYPE], $args);
    }

    /**
     * Register tag taxonomy
     */
    private function register_tag() {
        $labels = [
            'name' => __('Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'singular_name' => __('Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'search_items' => __('Buscar Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'popular_items' => __('Etiquetas Populares', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'all_items' => __('Todas las Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'edit_item' => __('Editar Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'update_item' => __('Actualizar Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'add_new_item' => __('Agregar Nueva Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'new_item_name' => __('Nuevo Nombre de Etiqueta', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'separate_items_with_commas' => __('Separar etiquetas con comas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'add_or_remove_items' => __('Agregar o eliminar etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'choose_from_most_used' => __('Elegir de las más usadas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'not_found' => __('No se encontraron etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'menu_name' => __('Etiquetas', SMARTNOTIFY_AI_TEXT_DOMAIN),
        ];

        $args = [
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => ['slug' => 'noticia-etiqueta'],
        ];

        register_taxonomy(self::TAG, [NewsPostType::POST_TYPE], $args);
    }

    /**
     * Get category taxonomy slug
     *
     * @return string
     */
    public static function get_category_taxonomy() {
        return self::CATEGORY;
    }

    /**
     * Get tag taxonomy slug
     *
     * @return string
     */
    public static function get_tag_taxonomy() {
        return self::TAG;
    }
}
