# SmartNotify AI - Ejemplos de Uso

Esta guía contiene ejemplos prácticos para usar SmartNotify AI programáticamente.

---

## Índice

1. [Crear Noticia Programáticamente](#crear-noticia-programáticamente)
2. [Generar Contenido con IA](#generar-contenido-con-ia)
3. [Analizar Sentimiento](#analizar-sentimiento)
4. [Trabajar con Taxonomías](#trabajar-con-taxonomías)
5. [Hooks y Filtros](#hooks-y-filtros)
6. [Integración con REST API](#integración-con-rest-api)

---

## Crear Noticia Programáticamente

### Ejemplo Básico

```php
<?php
// Crear una nueva noticia
$post_data = [
    'post_title'   => 'Mi Primera Noticia',
    'post_content' => 'Este es el contenido de la noticia...',
    'post_status'  => 'publish',
    'post_author'  => 1,
    'post_type'    => 'smartnotify_news',
];

$post_id = wp_insert_post($post_data);

if (is_wp_error($post_id)) {
    echo 'Error: ' . $post_id->get_error_message();
} else {
    echo 'Noticia creada con ID: ' . $post_id;
}
```

### Ejemplo Completo con Metadatos

```php
<?php
// Crear noticia completa
$post_data = [
    'post_title'   => 'Avances en Inteligencia Artificial',
    'post_content' => 'La inteligencia artificial continúa revolucionando...',
    'post_excerpt' => 'Resumen breve de la noticia',
    'post_status'  => 'publish',
    'post_author'  => get_current_user_id(),
    'post_type'    => 'smartnotify_news',
];

$post_id = wp_insert_post($post_data);

if (!is_wp_error($post_id)) {
    // Agregar imagen destacada
    $attachment_id = media_sideload_image(
        'https://example.com/image.jpg',
        $post_id,
        'Descripción de la imagen',
        'id'
    );

    if (!is_wp_error($attachment_id)) {
        set_post_thumbnail($post_id, $attachment_id);
    }

    // Agregar categorías
    wp_set_post_terms($post_id, ['Tecnología', 'IA'], 'smartnotify_category');

    // Agregar etiquetas
    wp_set_post_terms($post_id, ['inteligencia-artificial', 'innovacion'], 'smartnotify_tag');

    // Agregar resumen personalizado
    update_post_meta($post_id, '_smartnotify_summary', 'Resumen generado por IA');

    // Agregar sentimiento
    update_post_meta($post_id, '_smartnotify_sentiment', 'positive');
    update_post_meta($post_id, '_smartnotify_sentiment_confidence', 0.95);

    echo 'Noticia creada exitosamente con ID: ' . $post_id;
}
```

---

## Generar Contenido con IA

### Generar Título

```php
<?php
// Obtener el container
$container = smartnotify_ai()->container;

// Obtener el generador de contenido
$generator = $container->get('content.generator');

// Generar título desde contenido
$content = 'La inteligencia artificial está transformando la industria...';
$title = $generator->generateTitle($content);

echo 'Título generado: ' . $title;
```

### Generar Resumen

```php
<?php
$container = smartnotify_ai()->container;
$generator = $container->get('content.generator');

$content = 'Un artículo largo sobre tecnología...';
$summary = $generator->generateSummary($content);

echo 'Resumen: ' . $summary;
```

### Generar Etiquetas

```php
<?php
$container = smartnotify_ai()->container;
$generator = $container->get('content.generator');

$content = 'Artículo sobre desarrollo web y JavaScript...';
$tags = $generator->generateTags($content);

echo 'Etiquetas generadas: ' . implode(', ', $tags);
```

### Generar Todo de una Vez

```php
<?php
$container = smartnotify_ai()->container;
$generator = $container->get('content.generator');

$content = 'Tu contenido aquí...';
$generated = $generator->generateAll($content);

// $generated contiene:
// [
//     'title' => 'Título generado',
//     'summary' => 'Resumen generado',
//     'tags' => ['tag1', 'tag2', 'tag3']
// ]

print_r($generated);
```

---

## Analizar Sentimiento

### Analizar Post Existente

```php
<?php
$container = smartnotify_ai()->container;
$analyzer = $container->get('sentiment.analyzer');

$post_id = 123;
$result = $analyzer->analyzePost($post_id);

// $result contiene:
// [
//     'sentiment' => 'positive', // positive, neutral, negative
//     'confidence' => 0.85
// ]

echo 'Sentimiento: ' . $result['sentiment'];
echo 'Confianza: ' . ($result['confidence'] * 100) . '%';
```

### Analizar Texto Directamente

```php
<?php
$container = smartnotify_ai()->container;
$analyzer = $container->get('sentiment.analyzer');

$text = 'Este es un gran día para la tecnología!';
$result = $analyzer->analyze($text);

echo 'Sentimiento: ' . $result['sentiment'];
```

### Obtener Etiqueta y Color

```php
<?php
$container = smartnotify_ai()->container;
$analyzer = $container->get('sentiment.analyzer');

$sentiment = 'positive';

$label = $analyzer->getSentimentLabel($sentiment);
// Retorna: 'Positivo'

$color = $analyzer->getSentimentColor($sentiment);
// Retorna: '#10b981'

echo '<span style="color: ' . $color . '">' . $label . '</span>';
```

---

## Trabajar con Taxonomías

### Obtener Categorías

```php
<?php
$categories = get_terms([
    'taxonomy' => 'smartnotify_category',
    'hide_empty' => false,
]);

foreach ($categories as $category) {
    echo $category->name . '<br>';
}
```

### Obtener Etiquetas

```php
<?php
$tags = get_terms([
    'taxonomy' => 'smartnotify_tag',
    'hide_empty' => false,
]);

foreach ($tags as $tag) {
    echo $tag->name . '<br>';
}
```

### Asignar Taxonomías a Post

```php
<?php
$post_id = 123;

// Asignar categorías (reemplaza existentes)
wp_set_post_terms($post_id, ['Tecnología', 'Noticias'], 'smartnotify_category');

// Asignar etiquetas (reemplaza existentes)
wp_set_post_terms($post_id, ['ia', 'desarrollo', 'web'], 'smartnotify_tag');

// Agregar sin reemplazar (append)
wp_set_post_terms($post_id, ['nueva-etiqueta'], 'smartnotify_tag', true);
```

### Obtener Taxonomías de un Post

```php
<?php
$post_id = 123;

// Obtener categorías
$categories = wp_get_post_terms($post_id, 'smartnotify_category');
foreach ($categories as $cat) {
    echo $cat->name . '<br>';
}

// Obtener etiquetas
$tags = wp_get_post_terms($post_id, 'smartnotify_tag');
foreach ($tags as $tag) {
    echo $tag->name . '<br>';
}
```

---

## Hooks y Filtros

### Action: Después de Análisis de Sentimiento

```php
<?php
add_action('smartnotify_after_sentiment_analysis', function($post_id, $sentiment_data) {
    // Hacer algo después del análisis
    if ($sentiment_data['sentiment'] === 'negative') {
        // Notificar al administrador
        wp_mail(
            get_option('admin_email'),
            'Noticia con sentimiento negativo',
            'Se detectó una noticia con sentimiento negativo: ' . get_permalink($post_id)
        );
    }
}, 10, 2);
```

### Filter: Modificar Configuración de IA

```php
<?php
add_filter('smartnotify_ai_config', function($config) {
    // Ajustar temperatura para respuestas más creativas
    $config['temperature'] = 0.9;

    // Aumentar tokens máximos
    $config['max_tokens'] = 3000;

    return $config;
});
```

### Filter: Modificar Prompt de Título

```php
<?php
add_filter('smartnotify_title_prompt', function($prompt, $content) {
    // Personalizar el prompt
    return "Crea un título viral y llamativo para el siguiente contenido:\n\n" . $content;
}, 10, 2);
```

### Action: Al Guardar Noticia

```php
<?php
add_action('save_post_smartnotify_news', function($post_id, $post, $update) {
    // Solo en actualizaciones (no en creación)
    if (!$update) {
        return;
    }

    // Generar contenido automáticamente si está vacío
    $summary = get_post_meta($post_id, '_smartnotify_summary', true);

    if (empty($summary)) {
        $container = smartnotify_ai()->container;
        $generator = $container->get('content.generator');

        $generated_summary = $generator->generateSummary($post->post_content);
        update_post_meta($post_id, '_smartnotify_summary', $generated_summary);
    }
}, 10, 3);
```

---

## Integración con REST API

### Endpoint Personalizado para Generar Contenido

```php
<?php
add_action('rest_api_init', function() {
    register_rest_route('smartnotify/v1', '/generate', [
        'methods' => 'POST',
        'callback' => 'smartnotify_generate_content_endpoint',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ]);
});

function smartnotify_generate_content_endpoint($request) {
    $content = $request->get_param('content');

    if (empty($content)) {
        return new WP_Error('missing_content', 'Content is required', ['status' => 400]);
    }

    $container = smartnotify_ai()->container;
    $generator = $container->get('content.generator');

    $result = [
        'title' => $generator->generateTitle($content),
        'summary' => $generator->generateSummary($content),
        'tags' => $generator->generateTags($content),
    ];

    return rest_ensure_response($result);
}
```

### Usar el Endpoint

```javascript
// JavaScript
fetch('/wp-json/smartnotify/v1/generate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        content: 'Tu contenido aquí...'
    })
})
.then(response => response.json())
.then(data => {
    console.log('Título:', data.title);
    console.log('Resumen:', data.summary);
    console.log('Etiquetas:', data.tags);
});
```

```php
// PHP
$response = wp_remote_post(rest_url('smartnotify/v1/generate'), [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'body' => wp_json_encode([
        'content' => 'Tu contenido aquí...',
    ]),
]);

$body = json_decode(wp_remote_retrieve_body($response), true);
print_r($body);
```

---

## Query Personalizado de Noticias

### Obtener Noticias Recientes

```php
<?php
$args = [
    'post_type' => 'smartnotify_news',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
];

$query = new WP_Query($args);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();

        echo '<h2>' . get_the_title() . '</h2>';
        echo '<div>' . get_the_excerpt() . '</div>';

        // Mostrar sentimiento
        $sentiment = get_post_meta(get_the_ID(), '_smartnotify_sentiment', true);
        if ($sentiment) {
            echo '<span class="sentiment-' . $sentiment . '">' . $sentiment . '</span>';
        }
    }
    wp_reset_postdata();
}
```

### Filtrar por Sentimiento

```php
<?php
$args = [
    'post_type' => 'smartnotify_news',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_smartnotify_sentiment',
            'value' => 'positive',
            'compare' => '=',
        ],
    ],
];

$positive_news = new WP_Query($args);
```

### Filtrar por Categoría y Sentimiento

```php
<?php
$args = [
    'post_type' => 'smartnotify_news',
    'posts_per_page' => 10,
    'tax_query' => [
        [
            'taxonomy' => 'smartnotify_category',
            'field' => 'slug',
            'terms' => 'tecnologia',
        ],
    ],
    'meta_query' => [
        [
            'key' => '_smartnotify_sentiment',
            'value' => 'negative',
            'compare' => '!=',
        ],
    ],
];

$filtered_news = new WP_Query($args);
```

---

## Shortcodes Personalizados

### Mostrar Últimas Noticias

```php
<?php
add_shortcode('smartnotify_news', function($atts) {
    $atts = shortcode_atts([
        'count' => 5,
        'category' => '',
        'sentiment' => '',
    ], $atts);

    $args = [
        'post_type' => 'smartnotify_news',
        'posts_per_page' => intval($atts['count']),
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'smartnotify_category',
                'field' => 'slug',
                'terms' => $atts['category'],
            ],
        ];
    }

    if (!empty($atts['sentiment'])) {
        $args['meta_query'] = [
            [
                'key' => '_smartnotify_sentiment',
                'value' => $atts['sentiment'],
            ],
        ];
    }

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="smartnotify-news-list">';
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <article class="smartnotify-news-item">
                <h3><?php the_title(); ?></h3>
                <div class="excerpt"><?php the_excerpt(); ?></div>
                <a href="<?php the_permalink(); ?>">Leer más</a>
            </article>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
    }

    return ob_get_clean();
});
```

### Uso del Shortcode

```
[smartnotify_news count="5"]
[smartnotify_news count="10" category="tecnologia"]
[smartnotify_news sentiment="positive"]
```

---

## Widgets Personalizados

### Widget de Noticias Recientes

```php
<?php
class SmartNotify_Recent_News_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'smartnotify_recent_news',
            'SmartNotify - Noticias Recientes',
            ['description' => 'Muestra noticias recientes']
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . $instance['title'] . $args['after_title'];
        }

        $query_args = [
            'post_type' => 'smartnotify_news',
            'posts_per_page' => $instance['count'] ?? 5,
        ];

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            echo '<ul class="smartnotify-widget-news">';
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <span class="date"><?php echo get_the_date(); ?></span>
                </li>
                <?php
            }
            echo '</ul>';
            wp_reset_postdata();
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Noticias Recientes';
        $count = !empty($instance['count']) ? $instance['count'] : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Título:</label>
            <input class="widefat" type="text"
                id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">Cantidad:</label>
            <input class="widefat" type="number"
                id="<?php echo $this->get_field_id('count'); ?>"
                name="<?php echo $this->get_field_name('count'); ?>"
                value="<?php echo esc_attr($count); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['count'] = (!empty($new_instance['count'])) ? intval($new_instance['count']) : 5;
        return $instance;
    }
}

// Registrar widget
add_action('widgets_init', function() {
    register_widget('SmartNotify_Recent_News_Widget');
});
```

---

## Cron Jobs

### Generar Resúmenes Automáticamente

```php
<?php
// Registrar cron job
add_action('init', function() {
    if (!wp_next_scheduled('smartnotify_generate_summaries')) {
        wp_schedule_event(time(), 'daily', 'smartnotify_generate_summaries');
    }
});

// Callback del cron
add_action('smartnotify_generate_summaries', function() {
    $args = [
        'post_type' => 'smartnotify_news',
        'posts_per_page' => 20,
        'meta_query' => [
            [
                'key' => '_smartnotify_summary',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $container = smartnotify_ai()->container;
        $generator = $container->get('content.generator');

        while ($query->have_posts()) {
            $query->the_post();

            $summary = $generator->generateSummary(get_the_content());

            if (!empty($summary)) {
                update_post_meta(get_the_ID(), '_smartnotify_summary', $summary);
            }

            // Evitar rate limits
            sleep(2);
        }

        wp_reset_postdata();
    }
});
```

---

**¿Necesitas más ejemplos?** Visita nuestra [documentación completa](https://github.com/yourusername/smartnotify-ai/wiki)
