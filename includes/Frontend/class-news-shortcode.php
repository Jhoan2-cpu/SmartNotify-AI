<?php
/**
 * News Shortcode
 *
 * @package SmartNotifyAI\Frontend
 */

namespace SmartNotifyAI\Frontend;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * News Shortcode class
 */
class NewsShortcode {

    /**
     * Register shortcode
     */
    public function register() {
        add_shortcode('smartnotify_news', [$this, 'render']);
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts([
            'limit' => 6,
            'columns' => 3,
            'category' => '',
            'sentiment' => '',
            'order' => 'DESC',
            'orderby' => 'date',
        ], $atts);

        // Build query arguments
        $args = [
            'post_type' => 'smartnotify_news',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'order' => $atts['order'],
            'orderby' => $atts['orderby'],
        ];

        // Add taxonomy filters if specified
        $tax_query = [];

        if (!empty($atts['category'])) {
            $tax_query[] = [
                'taxonomy' => 'smartnotify_category',
                'field' => 'slug',
                'terms' => explode(',', $atts['category']),
            ];
        }

        if (!empty($atts['sentiment'])) {
            $tax_query[] = [
                'taxonomy' => 'smartnotify_sentiment',
                'field' => 'slug',
                'terms' => explode(',', $atts['sentiment']),
            ];
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
            if (count($tax_query) > 1) {
                $args['tax_query']['relation'] = 'AND';
            }
        }

        // Execute query
        $query = new \WP_Query($args);

        // Start output buffering
        ob_start();

        if ($query->have_posts()) {
            $columns_class = 'smartnotify-grid-' . intval($atts['columns']);
            ?>
            <div class="smartnotify-news-container">
                <div class="smartnotify-news-grid <?php echo esc_attr($columns_class); ?>">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php $this->renderCard(get_the_ID()); ?>
                    <?php endwhile; ?>
                </div>

                <!-- Modal for news details -->
                <?php $this->renderModal(); ?>
            </div>
            <?php
            wp_reset_postdata();
        } else {
            ?>
            <div class="smartnotify-no-news">
                <p><?php _e('No hay noticias disponibles en este momento.', SMARTNOTIFY_AI_TEXT_DOMAIN); ?></p>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Render single news card
     *
     * @param int $post_id Post ID
     */
    private function renderCard($post_id) {
        // Get post data
        $title = get_the_title($post_id);
        $content = get_the_content(null, false, $post_id);
        $content = apply_filters('the_content', $content);
        $date = get_the_date('', $post_id);
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';

        // Get custom fields
        $summary = get_post_meta($post_id, '_smartnotify_summary', true);

        // Get author
        $author_id = get_post_field('post_author', $post_id);
        $author_name = get_the_author_meta('display_name', $author_id);

        // Get taxonomies
        $categories = get_the_terms($post_id, 'smartnotify_category');
        $sentiments = get_the_terms($post_id, 'smartnotify_sentiment');

        // Get tags
        $tags = get_the_terms($post_id, 'post_tag');
        $tags_array = [];
        if ($tags && !is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $tags_array[] = $tag->name;
            }
        }

        // Get sentiment color
        $sentiment_class = '';
        $sentiment_label = '';
        if ($sentiments && !is_wp_error($sentiments)) {
            $sentiment = $sentiments[0];
            $sentiment_label = $sentiment->name;
            $sentiment_class = 'smartnotify-sentiment-' . $sentiment->slug;
        }

        // Prepare categories array
        $categories_array = [];
        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $categories_array[] = $category->name;
            }
        }

        // Use summary if available, otherwise use excerpt
        $display_excerpt = !empty($summary) ? $summary : get_the_excerpt($post_id);

        ?>
        <article class="smartnotify-news-card <?php echo esc_attr($sentiment_class); ?>"
                 data-post-id="<?php echo esc_attr($post_id); ?>"
                 data-title="<?php echo esc_attr($title); ?>"
                 data-content="<?php echo esc_attr($content); ?>"
                 data-summary="<?php echo esc_attr($summary); ?>"
                 data-image="<?php echo esc_attr($thumbnail_url); ?>"
                 data-date="<?php echo esc_attr($date); ?>"
                 data-author="<?php echo esc_attr($author_name); ?>"
                 data-sentiment="<?php echo esc_attr($sentiment_label); ?>"
                 data-sentiment-class="<?php echo esc_attr($sentiment_class); ?>"
                 data-categories="<?php echo esc_attr(implode(',', $categories_array)); ?>"
                 data-tags="<?php echo esc_attr(implode(',', $tags_array)); ?>">

            <?php if ($thumbnail_url) : ?>
                <div class="smartnotify-card-image">
                    <div class="smartnotify-card-image-link">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                    </div>
                    <?php if ($sentiment_label) : ?>
                        <span class="smartnotify-card-sentiment">
                            <?php echo esc_html($sentiment_label); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="smartnotify-card-content">
                <?php if ($categories && !is_wp_error($categories)) : ?>
                    <div class="smartnotify-card-categories">
                        <?php foreach ($categories as $category) : ?>
                            <span class="smartnotify-card-category">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h3 class="smartnotify-card-title">
                    <span class="smartnotify-card-title-text">
                        <?php echo esc_html($title); ?>
                    </span>
                </h3>

                <?php if ($display_excerpt) : ?>
                    <p class="smartnotify-card-excerpt">
                        <?php echo esc_html($display_excerpt); ?>
                    </p>
                <?php endif; ?>

                <div class="smartnotify-card-footer">
                    <time class="smartnotify-card-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                        <?php echo esc_html($date); ?>
                    </time>
                    <button type="button" class="smartnotify-card-link smartnotify-open-modal">
                        <?php _e('Leer más', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        </article>
        <?php
    }

    /**
     * Render modal structure
     */
    private function renderModal() {
        ?>
        <div id="smartnotify-news-modal" class="smartnotify-modal" style="display: none;">
            <div class="smartnotify-modal-overlay"></div>
            <div class="smartnotify-modal-container">
                <div class="smartnotify-modal-header">
                    <button type="button" class="smartnotify-modal-close" aria-label="<?php esc_attr_e('Cerrar', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="smartnotify-modal-body">
                    <div class="smartnotify-modal-image-container">
                        <img src="" alt="" class="smartnotify-modal-image">
                        <span class="smartnotify-modal-sentiment"></span>
                    </div>
                    <div class="smartnotify-modal-content-wrapper">
                        <div class="smartnotify-modal-categories"></div>
                        <h2 class="smartnotify-modal-title"></h2>
                        <div class="smartnotify-modal-meta">
                            <span class="smartnotify-modal-author"></span>
                            <span class="smartnotify-modal-separator">•</span>
                            <time class="smartnotify-modal-date"></time>
                        </div>
                        <div class="smartnotify-modal-summary"></div>
                        <div class="smartnotify-modal-content"></div>
                        <div class="smartnotify-modal-tags"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
