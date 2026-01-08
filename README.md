# SmartNotify AI

![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%2B-green)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-orange)

**Sistema avanzado de gestión de noticias con capacidades de IA para generación automática de contenido, análisis de sentimiento y categorización inteligente.**

---

## 🚀 Características

### Gestión Inteligente de Noticias
- ✅ **Custom Post Type** especializado para noticias
- ✅ **Taxonomías personalizadas** (Categorías y Etiquetas)
- ✅ **Campos personalizados** con meta boxes intuitivos
- ✅ **Sistema de filtros avanzado** por fecha, autor, estado y sentimiento
- ✅ **Columnas personalizadas** en la lista de noticias

### Capacidades de IA

#### Generación de Contenido
- 🤖 **Generación automática de títulos** basada en el contenido
- 🤖 **Resúmenes automáticos** concisos y relevantes
- 🤖 **Etiquetas inteligentes** generadas por IA
- 🤖 **Análisis de sentimiento** (Positivo, Neutral, Negativo)

#### Proveedores Soportados
- **OpenAI** (GPT-4, GPT-4 Turbo, GPT-3.5)
- **Anthropic** (Claude 3.5 Sonnet, Claude 3 Opus, Claude 3 Sonnet)

### Interfaz Moderna
- 🎨 **Tailwind CSS** para estilos modernos y responsivos
- 🎨 **Iconos Dashicons** de WordPress
- 🎨 **Componentes de WordPress** para consistencia
- 🎨 **Diseño intuitivo** y fácil de usar

### Arquitectura Profesional
- 🏗️ **Principios SOLID** aplicados
- 🏗️ **PSR-4 Autoloading**
- 🏗️ **Dependency Injection Container**
- 🏗️ **Separación de responsabilidades**
- 🏗️ **Código modular y extensible**

### Seguridad
- 🔒 **Nonces de WordPress** para todas las operaciones AJAX
- 🔒 **Sanitización y validación** de todos los inputs
- 🔒 **Capability checks** para permisos
- 🔒 **Prepared statements** para consultas de base de datos

---

## 📋 Requisitos

- **WordPress:** 6.0 o superior
- **PHP:** 7.4 o superior
- **Credenciales de IA:** API Key de OpenAI o Anthropic

---

## 📦 Instalación

### Método 1: Instalación Manual

1. Descarga el plugin como archivo ZIP
2. Ve a **WordPress Admin → Plugins → Añadir nuevo**
3. Haz clic en **Subir plugin**
4. Selecciona el archivo ZIP descargado
5. Haz clic en **Instalar ahora**
6. Activa el plugin

### Método 2: Instalación desde Git

```bash
cd wp-content/plugins
git clone https://github.com/yourusername/smartnotify-ai.git
cd smartnotify-ai
```

Luego activa el plugin desde el panel de WordPress.

---

## ⚙️ Configuración

### 1. Configurar API de IA

1. Ve a **SmartNotify AI → Configuración**
2. Selecciona tu proveedor de IA (OpenAI o Anthropic)
3. Ingresa tu API Key
4. Selecciona el modelo que deseas usar
5. Habilita las funciones automáticas que necesites
6. Guarda los cambios

### 2. Obtener API Keys

#### OpenAI
1. Visita [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys)
2. Inicia sesión o crea una cuenta
3. Crea una nueva API Key
4. Copia la clave y pégala en la configuración del plugin

#### Anthropic
1. Visita [https://console.anthropic.com/settings/keys](https://console.anthropic.com/settings/keys)
2. Inicia sesión o crea una cuenta
3. Crea una nueva API Key
4. Copia la clave y pégala en la configuración del plugin

---

## 📖 Uso

### Crear una Noticia

1. Ve a **SmartNotify AI → Agregar Noticia**
2. Escribe el contenido de la noticia
3. Usa los botones de IA en el panel lateral:
   - **Generar Título:** Crea un título atractivo basado en el contenido
   - **Generar Resumen:** Crea un resumen conciso
   - **Generar Etiquetas:** Sugiere etiquetas relevantes
   - **Analizar Sentimiento:** Determina si es positiva, neutral o negativa
4. Ajusta manualmente el contenido generado si es necesario
5. Establece la imagen destacada
6. Selecciona categorías y etiquetas
7. Publica o guarda como borrador

### Gestionar Noticias

1. Ve a **SmartNotify AI → Todas las Noticias**
2. Usa los filtros para encontrar noticias específicas:
   - Filtrar por sentimiento
   - Filtrar por fecha
   - Filtrar por autor
   - Filtrar por estado
3. Para cada noticia puedes:
   - **Editar:** Modificar el contenido
   - **Ver:** Vista previa de la noticia
   - **Borrar:** Eliminar la noticia

### Ver Análisis de Sentimiento

El análisis de sentimiento aparece en:
- **Columna "Sentimiento"** en la lista de noticias
- **Meta Box "Análisis de Sentimiento"** en el editor de noticias
- Indicador visual con colores:
  - 🟢 Verde: Positivo
  - ⚪ Gris: Neutral
  - 🔴 Rojo: Negativo

---

## 🏗️ Arquitectura del Plugin

### Estructura de Directorios

```
smartnotify-ai/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   ├── Admin/
│   │   ├── class-admin-controller.php
│   │   ├── class-meta-boxes.php
│   │   └── class-settings-page.php
│   ├── Ajax/
│   │   └── class-ajax-handler.php
│   ├── Core/
│   │   ├── class-assets.php
│   │   ├── class-config.php
│   │   ├── class-container.php
│   │   └── class-plugin.php
│   ├── PostTypes/
│   │   └── class-news-post-type.php
│   ├── Services/
│   │   └── AI/
│   │       ├── interface-ai-service.php
│   │       ├── class-ai-service-factory.php
│   │       ├── class-anthropic-service.php
│   │       ├── class-content-generator.php
│   │       ├── class-mock-ai-service.php
│   │       ├── class-open-ai-service.php
│   │       └── class-sentiment-analyzer.php
│   ├── Taxonomies/
│   │   └── class-news-taxonomies.php
│   ├── class-activator.php
│   ├── class-autoloader.php
│   └── class-deactivator.php
├── languages/
├── .gitignore
├── composer.json
├── README.md
└── smartnotify-ai.php
```

### Principios de Diseño

#### SOLID Principles

1. **Single Responsibility Principle (SRP)**
   - Cada clase tiene una única responsabilidad
   - Ejemplo: `ContentGenerator` solo genera contenido, `SentimentAnalyzer` solo analiza sentimiento

2. **Open/Closed Principle (OCP)**
   - Abierto para extensión, cerrado para modificación
   - Ejemplo: Nuevos proveedores de IA pueden agregarse sin modificar código existente

3. **Liskov Substitution Principle (LSP)**
   - Las implementaciones de interfaces son intercambiables
   - Ejemplo: `OpenAIService` y `AnthropicService` implementan `AIServiceInterface`

4. **Interface Segregation Principle (ISP)**
   - Interfaces específicas y enfocadas
   - Ejemplo: `AIServiceInterface` define métodos específicos para servicios de IA

5. **Dependency Inversion Principle (DIP)**
   - Dependencia de abstracciones, no de implementaciones concretas
   - Ejemplo: Uso del `Container` para inyección de dependencias

#### Design Patterns

- **Factory Pattern:** `AIServiceFactory` para crear instancias de servicios
- **Singleton Pattern:** Plugin principal y Container
- **Dependency Injection:** Container para gestión de dependencias
- **Strategy Pattern:** Diferentes servicios de IA intercambiables

---

## 🔧 API para Desarrolladores

### Hooks

#### Actions

```php
// Después de analizar el sentimiento
do_action('smartnotify_after_sentiment_analysis', $post_id, $sentiment_data);

// Después de generar contenido
do_action('smartnotify_after_content_generation', $post_id, $generated_content);
```

#### Filters

```php
// Modificar la configuración de IA
add_filter('smartnotify_ai_config', function($config) {
    $config['temperature'] = 0.8;
    return $config;
});

// Modificar el prompt para generación de títulos
add_filter('smartnotify_title_prompt', function($prompt, $content) {
    return "Genera un título creativo para: " . $content;
}, 10, 2);
```

### Usar Servicios de IA en tu Código

```php
// Obtener el container
$container = smartnotify_ai()->container;

// Generar contenido
$generator = $container->get('content.generator');
$title = $generator->generateTitle($content);
$summary = $generator->generateSummary($content);
$tags = $generator->generateTags($content);

// Analizar sentimiento
$analyzer = $container->get('sentiment.analyzer');
$sentiment = $analyzer->analyzePost($post_id);
```

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Changelog

### Version 1.0.0 (2026-01-08)
- ✅ Release inicial
- ✅ Custom Post Type para noticias
- ✅ Integración con OpenAI y Anthropic
- ✅ Generación automática de contenido
- ✅ Análisis de sentimiento
- ✅ Sistema de filtros avanzado
- ✅ Interfaz moderna con Tailwind CSS

---

## 📄 Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

---

## 👨‍💻 Autor

**Tu Nombre**
- Website: [https://tu-sitio.com](https://tu-sitio.com)
- GitHub: [@yourusername](https://github.com/yourusername)

---

## 🙏 Agradecimientos

- WordPress Community
- OpenAI por GPT-4
- Anthropic por Claude
- Tailwind CSS

---

## 📞 Soporte

Si encuentras algún problema o tienes preguntas:

- [Abrir un Issue](https://github.com/yourusername/smartnotify-ai/issues)
- [Documentación](https://github.com/yourusername/smartnotify-ai/wiki)

---

**¡Hecho con ❤️ para la comunidad de WordPress!**
