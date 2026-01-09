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
        $excerpt = get_the_excerpt($post_id);
        $permalink = get_permalink($post_id);
        $date = get_the_date('', $post_id);
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';

        // Get taxonomies
        $categories = get_the_terms($post_id, 'smartnotify_category');
        $sentiments = get_the_terms($post_id, 'smartnotify_sentiment');

        // Get sentiment color
        $sentiment_class = '';
        $sentiment_label = '';
        if ($sentiments && !is_wp_error($sentiments)) {
            $sentiment = $sentiments[0];
            $sentiment_label = $sentiment->name;
            $sentiment_class = 'smartnotify-sentiment-' . $sentiment->slug;
        }

        ?>
        <article class="smartnotify-news-card <?php echo esc_attr($sentiment_class); ?>">
            <?php if ($thumbnail_url) : ?>
                <div class="smartnotify-card-image">
                    <a href="<?php echo esc_url($permalink); ?>">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                    </a>
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
                    <a href="<?php echo esc_url($permalink); ?>">
                        <?php echo esc_html($title); ?>
                    </a>
                </h3>

                <?php if ($excerpt) : ?>
                    <p class="smartnotify-card-excerpt">
                        <?php echo esc_html($excerpt); ?>
                    </p>
                <?php endif; ?>

                <div class="smartnotify-card-footer">
                    <time class="smartnotify-card-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                        <?php echo esc_html($date); ?>
                    </time>
                    <a href="<?php echo esc_url($permalink); ?>" class="smartnotify-card-link">
                        <?php _e('Leer más', SMARTNOTIFY_AI_TEXT_DOMAIN); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
        </article>
        <?php
    }
}
