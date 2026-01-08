# SmartNotify AI - Documentación para Desarrolladores

## Índice

1. [Arquitectura del Plugin](#arquitectura-del-plugin)
2. [Patrones de Diseño](#patrones-de-diseño)
3. [Estructura de Clases](#estructura-de-clases)
4. [Extensibilidad](#extensibilidad)
5. [Testing](#testing)
6. [Seguridad](#seguridad)
7. [Performance](#performance)
8. [Buenas Prácticas](#buenas-prácticas)

---

## Arquitectura del Plugin

### Principios SOLID

El plugin está construido siguiendo los principios SOLID:

#### 1. Single Responsibility Principle (SRP)

Cada clase tiene una única responsabilidad:

```php
// ✅ CORRECTO: Clase con una sola responsabilidad
class ContentGenerator {
    public function generateTitle($content) { /* ... */ }
    public function generateSummary($content) { /* ... */ }
    public function generateTags($content) { /* ... */ }
}

// ❌ INCORRECTO: Clase con múltiples responsabilidades
class NewsManager {
    public function generateTitle() { /* ... */ }
    public function saveToDB() { /* ... */ }
    public function sendEmail() { /* ... */ }
}
```

#### 2. Open/Closed Principle (OCP)

Abierto para extensión, cerrado para modificación:

```php
// Interfaz estable
interface AIServiceInterface {
    public function generate($prompt, array $options = []);
}

// Nuevos proveedores sin modificar código existente
class NewAIProvider implements AIServiceInterface {
    public function generate($prompt, array $options = []) {
        // Implementación específica
    }
}
```

#### 3. Liskov Substitution Principle (LSP)

Las implementaciones son intercambiables:

```php
// Cualquier servicio que implemente AIServiceInterface puede usarse
$service = new OpenAIService($api_key, $config);
// O
$service = new AnthropicService($api_key, $config);

// Ambos funcionan de la misma manera
$result = $service->generate($prompt);
```

#### 4. Interface Segregation Principle (ISP)

Interfaces específicas y enfocadas:

```php
// ✅ CORRECTO: Interfaces específicas
interface AIServiceInterface {
    public function generate($prompt, array $options = []);
    public function isAvailable();
}

interface SentimentAnalyzerInterface {
    public function analyze($text);
}

// ❌ INCORRECTO: Interfaz muy grande
interface MegaInterface {
    public function generate();
    public function analyze();
    public function save();
    public function delete();
    // ... 20 métodos más
}
```

#### 5. Dependency Inversion Principle (DIP)

Dependencia de abstracciones:

```php
// ✅ CORRECTO: Depende de la abstracción
class ContentGenerator {
    public function __construct(AIServiceFactory $factory) {
        $this->factory = $factory;
    }
}

// ❌ INCORRECTO: Depende de implementación concreta
class ContentGenerator {
    public function __construct() {
        $this->service = new OpenAIService();
    }
}
```

---

## Patrones de Diseño

### 1. Factory Pattern

Usado para crear instancias de servicios de IA:

```php
class AIServiceFactory {
    public function getService() {
        $provider = $this->config->get('ai.provider');

        switch ($provider) {
            case 'openai':
                return new OpenAIService($api_key, $this->config);
            case 'anthropic':
                return new AnthropicService($api_key, $this->config);
            default:
                return new MockAIService();
        }
    }
}
```

**Ventajas:**
- Encapsula la lógica de creación
- Fácil agregar nuevos proveedores
- Testeable (mock service)

### 2. Singleton Pattern

Usado para el plugin principal y el container:

```php
final class SmartNotify_AI {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() { /* ... */ }
    private function __clone() { }
}
```

**Ventajas:**
- Una sola instancia del plugin
- Acceso global consistente
- Previene duplicación

### 3. Dependency Injection Container

```php
class Container {
    private $bindings = [];
    private $instances = [];

    public function singleton($abstract, callable $concrete) {
        $this->bind($abstract, $concrete, true);
    }

    public function get($abstract) {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $instance = call_user_func($this->bindings[$abstract]['concrete'], $this);

        if ($this->bindings[$abstract]['singleton']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }
}
```

**Ventajas:**
- Gestión centralizada de dependencias
- Fácil testing (inyectar mocks)
- Bajo acoplamiento

### 4. Strategy Pattern

Diferentes servicios de IA intercambiables:

```php
// Estrategia (interfaz)
interface AIServiceInterface {
    public function generate($prompt, array $options = []);
}

// Estrategias concretas
class OpenAIService implements AIServiceInterface { /* ... */ }
class AnthropicService implements AIServiceInterface { /* ... */ }

// Contexto
class ContentGenerator {
    public function __construct(AIServiceFactory $factory) {
        $this->service = $factory->getService();
    }
}
```

---

## Estructura de Clases

### Core Classes

#### Container

```php
namespace SmartNotifyAI\Core;

class Container {
    /**
     * Registrar un servicio singleton
     */
    public function singleton($abstract, callable $concrete);

    /**
     * Obtener una instancia del servicio
     */
    public function get($abstract);

    /**
     * Verificar si un servicio existe
     */
    public function has($abstract);
}
```

#### Config

```php
namespace SmartNotifyAI\Core;

class Config {
    /**
     * Obtener valor de configuración (soporta dot notation)
     *
     * @example $config->get('ai.provider')
     */
    public function get($key, $default = null);

    /**
     * Establecer valor de configuración
     */
    public function set($key, $value);
}
```

### Service Classes

#### AIServiceFactory

```php
namespace SmartNotifyAI\Services\AI;

class AIServiceFactory {
    /**
     * Obtener servicio de IA configurado
     */
    public function getService(): AIServiceInterface;

    /**
     * Verificar si está configurado
     */
    public function isConfigured(): bool;
}
```

#### ContentGenerator

```php
namespace SmartNotifyAI\Services\AI;

class ContentGenerator {
    public function generateTitle($content): string;
    public function generateSummary($content): string;
    public function generateTags($content): array;
    public function generateAll($content): array;
}
```

#### SentimentAnalyzer

```php
namespace SmartNotifyAI\Services\AI;

class SentimentAnalyzer {
    public function analyzePost($post_id): array;
    public function analyze($text): array;
    public function getSentimentLabel($sentiment): string;
    public function getSentimentColor($sentiment): string;
}
```

---

## Extensibilidad

### Agregar un Nuevo Proveedor de IA

1. **Crear la clase del servicio:**

```php
namespace SmartNotifyAI\Services\AI;

class CustomAIService implements AIServiceInterface {
    private $api_key;
    private $config;

    public function __construct($api_key, Config $config) {
        $this->api_key = $api_key;
        $this->config = $config;
    }

    public function generate($prompt, array $options = []) {
        // Implementar llamada a tu API
    }

    public function analyzeSentiment($text) {
        // Implementar análisis
    }

    public function generateTags($content) {
        // Implementar generación
    }

    public function generateSummary($content) {
        // Implementar generación
    }

    public function generateTitle($content) {
        // Implementar generación
    }

    public function isAvailable() {
        return !empty($this->api_key);
    }
}
```

2. **Registrar en el Factory:**

```php
// En AIServiceFactory::createService()
switch ($provider) {
    case 'custom':
        return new CustomAIService($api_key, $this->config);
    // ... otros casos
}
```

3. **Agregar opción en settings:**

```php
// En SettingsPage::renderProviderField()
<option value="custom">Custom AI Provider</option>
```

### Hooks Personalizados

#### Actions

```php
// Después de analizar sentimiento
do_action('smartnotify_after_sentiment_analysis', $post_id, $sentiment_data);

// Uso
add_action('smartnotify_after_sentiment_analysis', function($post_id, $data) {
    // Tu código aquí
}, 10, 2);
```

#### Filters

```php
// Modificar configuración de IA
$config = apply_filters('smartnotify_ai_config', $config);

// Uso
add_filter('smartnotify_ai_config', function($config) {
    $config['temperature'] = 0.9;
    return $config;
});
```

### Extender Meta Boxes

```php
add_action('add_meta_boxes', function() {
    add_meta_box(
        'custom_meta_box',
        'Mi Meta Box Personalizado',
        'render_custom_meta_box',
        'smartnotify_news',
        'side'
    );
});
```

---

## Testing

### Estructura de Tests

```
tests/
├── Unit/
│   ├── Core/
│   │   ├── ContainerTest.php
│   │   └── ConfigTest.php
│   └── Services/
│       └── ContentGeneratorTest.php
├── Integration/
│   └── AjaxHandlerTest.php
└── bootstrap.php
```

### Ejemplo de Test Unitario

```php
namespace SmartNotifyAI\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use SmartNotifyAI\Core\Container;

class ContainerTest extends TestCase {
    private $container;

    protected function setUp(): void {
        $this->container = new Container();
    }

    public function test_can_bind_and_resolve() {
        $this->container->bind('test', function() {
            return 'value';
        });

        $result = $this->container->get('test');

        $this->assertEquals('value', $result);
    }

    public function test_singleton_returns_same_instance() {
        $this->container->singleton('service', function() {
            return new \stdClass();
        });

        $instance1 = $this->container->get('service');
        $instance2 = $this->container->get('service');

        $this->assertSame($instance1, $instance2);
    }
}
```

### Mocking AI Services

```php
class MockAIService implements AIServiceInterface {
    private $responses = [];

    public function setResponse($method, $response) {
        $this->responses[$method] = $response;
    }

    public function generate($prompt, array $options = []) {
        return $this->responses['generate'] ?? 'mock response';
    }
}

// En test
$mockService = new MockAIService();
$mockService->setResponse('generate', 'test title');

$generator = new ContentGenerator($mockFactory);
$title = $generator->generateTitle('content');

$this->assertEquals('test title', $title);
```

---

## Seguridad

### Verificación de Nonces

```php
// Crear nonce
wp_nonce_field('smartnotify_ai_meta_box', 'smartnotify_ai_nonce');

// Verificar nonce
if (!wp_verify_nonce($_POST['smartnotify_ai_nonce'], 'smartnotify_ai_meta_box')) {
    return;
}
```

### Sanitización de Datos

```php
// Texto
$text = sanitize_text_field($_POST['field']);

// Textarea
$textarea = sanitize_textarea_field($_POST['content']);

// Email
$email = sanitize_email($_POST['email']);

// URL
$url = esc_url_raw($_POST['url']);

// HTML
$html = wp_kses_post($_POST['html']);
```

### Validación de Capacidades

```php
// Verificar permisos
if (!current_user_can('edit_posts')) {
    wp_die(__('Permisos insuficientes'));
}

// Verificar permisos específicos del post
if (!current_user_can('edit_post', $post_id)) {
    wp_die(__('No puedes editar este post'));
}
```

### Escape de Salida

```php
// Texto general
echo esc_html($text);

// Atributos
echo '<div class="' . esc_attr($class) . '">';

// URLs
echo '<a href="' . esc_url($url) . '">';

// JavaScript
echo '<script>var data = ' . wp_json_encode($data) . ';</script>';
```

---

## Performance

### Caché

```php
// Guardar en transient
set_transient('smartnotify_cache_' . $key, $value, HOUR_IN_SECONDS);

// Obtener de transient
$cached = get_transient('smartnotify_cache_' . $key);
if (false !== $cached) {
    return $cached;
}
```

### Optimización de Consultas

```php
// ✅ CORRECTO: Query optimizada
$args = [
    'post_type' => 'smartnotify_news',
    'posts_per_page' => 10,
    'fields' => 'ids', // Solo IDs si no necesitas el objeto completo
    'no_found_rows' => true, // Si no necesitas paginación
    'update_post_meta_cache' => false, // Si no necesitas meta
    'update_post_term_cache' => false, // Si no necesitas términos
];

// ❌ INCORRECTO: Query sin optimizar
$args = [
    'post_type' => 'smartnotify_news',
    'posts_per_page' => -1, // Trae todos los posts
];
```

### Lazy Loading

```php
// Cargar servicios solo cuando se necesiten
public function getService() {
    if ($this->service === null) {
        $this->service = $this->createService();
    }
    return $this->service;
}
```

---

## Buenas Prácticas

### Nomenclatura

```php
// Clases: PascalCase
class ContentGenerator { }

// Métodos y funciones: camelCase
public function generateTitle() { }

// Constantes: UPPER_SNAKE_CASE
const POST_TYPE = 'smartnotify_news';

// Variables: snake_case
$post_id = 123;
```

### Documentación

```php
/**
 * Generate title from content
 *
 * @param string $content Post content
 * @return string Generated title
 * @throws \Exception If generation fails
 */
public function generateTitle($content) {
    // Implementation
}
```

### Error Handling

```php
try {
    $result = $this->service->generate($prompt);

    if (empty($result)) {
        throw new \Exception('Empty result from AI service');
    }

    return $result;
} catch (\Exception $e) {
    error_log('AI Generation Error: ' . $e->getMessage());
    return '';
}
```

### Logging

```php
// Error log
error_log('SmartNotify AI Error: ' . $error_message);

// Debug (solo en desarrollo)
if (WP_DEBUG) {
    error_log('SmartNotify AI Debug: ' . print_r($data, true));
}
```

---

## Recursos Adicionales

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Design Patterns in PHP](https://refactoring.guru/design-patterns/php)

---

**¿Preguntas?** Abre un [issue en GitHub](https://github.com/yourusername/smartnotify-ai/issues)
