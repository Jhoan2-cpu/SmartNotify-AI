<?php
/**
 * News Custom Post Type
 *
 * @package SmartNotifyAI\PostTypes
 */

namespace SmartNotifyAI\PostTypes;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * News Post Type class
 */
class NewsPostType {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'smartnotify_news';

    /**
     * Register post type
     */
    public function register() {
        $labels = [
            'name' => __('Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'singular_name' => __('Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'menu_name' => __('SmartNotify AI', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'name_admin_bar' => __('Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'add_new' => __('Agregar Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'add_new_item' => __('Agregar Nueva Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'new_item' => __('Nueva Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'edit_item' => __('Editar Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'view_item' => __('Ver Noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'view_items' => __('Ver Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'all_items' => __('Todas las Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'search_items' => __('Buscar Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'parent_item_colon' => __('Noticia Padre:', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'not_found' => __('No se encontraron noticias.', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'not_found_in_trash' => __('No se encontraron noticias en la papelera.', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'featured_image' => __('Imagen Destacada', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'set_featured_image' => __('Establecer imagen destacada', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'remove_featured_image' => __('Eliminar imagen destacada', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'use_featured_image' => __('Usar como imagen destacada', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'archives' => __('Archivo de Noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'insert_into_item' => __('Insertar en noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'uploaded_to_this_item' => __('Subido a esta noticia', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'filter_items_list' => __('Filtrar lista de noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'items_list_navigation' => __('Navegación de lista de noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'items_list' => __('Lista de noticias', SMARTNOTIFY_AI_TEXT_DOMAIN),
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Noticias gestionadas con IA', SMARTNOTIFY_AI_TEXT_DOMAIN),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'noticias'],
            'capability_type' => 'post',
            'capabilities' => [
                'edit_post' => 'edit_smartnotify_news',
                'edit_posts' => 'edit_smartnotify_news',
                'edit_others_posts' => 'edit_others_smartnotify_news',
                'publish_posts' => 'publish_smartnotify_news',
                'read_post' => 'read_smartnotify_news',
                'read_private_posts' => 'read_private_smartnotify_news',
                'delete_post' => 'delete_smartnotify_news',
                'delete_posts' => 'delete_smartnotify_news',
                'delete_others_posts' => 'delete_others_smartnotify_news',
                'delete_private_posts' => 'delete_private_smartnotify_news',
                'delete_published_posts' => 'delete_published_smartnotify_news',
                'edit_private_posts' => 'edit_private_smartnotify_news',
                'edit_published_posts' => 'edit_published_smartnotify_news',
            ],
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions', 'custom-fields'],
            'taxonomies' => ['smartnotify_category', 'smartnotify_tag'],
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Get post type slug
     *
     * @return string
     */
    public static function get_post_type() {
        return self::POST_TYPE;
    }
}
