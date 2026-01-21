# Diagrama de Componentes - SmartNotify AI

## 📊 Diagrama de Componentes del Sistema

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           WORDPRESS CORE SYSTEM                              │
│                     (wp-admin, wp-includes, wp-content)                      │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↕
        ┌─────────────────────────────────────────────────────────────┐
        │                    SMARTNOTIFY AI PLUGIN                     │
        │                         (smartnotify-ai/)                    │
        └─────────────────────────────────────────────────────────────┘
                                      │
                ┌─────────────────────┼─────────────────────┐
                │                     │                     │
                ↓                     ↓                     ↓
┌───────────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│   INITIALIZATION      │  │   CORE LAYER     │  │   DATA LAYER     │
│                       │  │                  │  │                  │
│ ┌───────────────────┐ │  │ ┌──────────────┐ │  │ ┌──────────────┐ │
│ │ smartnotify-ai.php│ │  │ │  Container   │ │  │ │  Post Types  │ │
│ └───────────────────┘ │  │ │     (DI)     │ │  │ └──────────────┘ │
│          │            │  │ └──────────────┘ │  │        │         │
│          ↓            │  │        │         │  │        ↓         │
│ ┌───────────────────┐ │  │ ┌──────────────┐ │  │ ┌──────────────┐ │
│ │   Autoloader      │ │  │ │   Config     │ │  │ │  Taxonomies  │ │
│ └───────────────────┘ │  │ └──────────────┘ │  │ └──────────────┘ │
│          │            │  │        │         │  │        │         │
│          ↓            │  │ ┌──────────────┐ │  │        ↓         │
│ ┌───────────────────┐ │  │ │   Assets     │ │  │ ┌──────────────┐ │
│ │   Activator       │ │  │ └──────────────┘ │  │ │  Meta Fields │ │
│ └───────────────────┘ │  │        │         │  │ └──────────────┘ │
│          │            │  │ ┌──────────────┐ │  │                  │
│          ↓            │  │ │   Plugin     │ │  │                  │
│ ┌───────────────────┐ │  │ │     Init     │ │  │                  │
│ │   Deactivator     │ │  │ └──────────────┘ │  │                  │
│ └───────────────────┘ │  │                  │  │                  │
└───────────────────────┘  └──────────────────┘  └──────────────────┘
                                      │
                ┌─────────────────────┼─────────────────────┐
                │                     │                     │
                ↓                     ↓                     ↓
┌───────────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│   ADMIN LAYER         │  │  SERVICES LAYER  │  │  FRONTEND LAYER  │
│                       │  │                  │  │                  │
│ ┌───────────────────┐ │  │ ┌──────────────┐ │  │ ┌──────────────┐ │
│ │ AdminController   │ │  │ │ AI Services  │ │  │ │  Shortcode   │ │
│ └───────────────────┘ │  │ └──────────────┘ │  │ └──────────────┘ │
│          │            │  │        │         │  │        │         │
│    ┌─────┼─────┐      │  │   ┌────┴────┐   │  │        ↓         │
│    ↓     ↓     ↓      │  │   ↓         ↓   │  │ ┌──────────────┐ │
│ ┌────┐┌────┐┌────┐    │  │ ┌────┐   ┌────┐ │  │ │   Frontend   │ │
│ │Set ││News││Meta│    │  │ │Open││Ant │ │  │ │   Assets    │ │
│ │tng ││Form││Box │    │  │ │ AI ││hrop│ │  │ └──────────────┘ │
│ │Page││    ││es  │    │  │ │Serv││ic  │ │  │                  │
│ └────┘└────┘└────┘    │  │ │ice ││Serv│ │  │                  │
│                       │  │ └────┘│ice │ │  │                  │
│                       │  │       └────┘ │  │                  │
│                       │  │        │     │  │                  │
│                       │  │   ┌────┴────┐│  │                  │
│                       │  │   ↓         ││  │                  │
│                       │  │ ┌────┐┌────┐││  │                  │
│                       │  │ │Cont││Sent│││  │                  │
│                       │  │ │ent ││iment│││  │                  │
│                       │  │ │Gen ││Anal│││  │                  │
│                       │  │ └────┘│yzer│││  │                  │
│                       │  │       └────┘││  │                  │
│                       │  │             ││  │                  │
│                       │  │ ┌──────────┐││  │                  │
│                       │  │ │  Image   │││  │                  │
│                       │  │ │ Services │││  │                  │
│                       │  │ └──────────┘││  │                  │
└───────────────────────┘  └─────────────┘│  └──────────────────┘
                                          │
                ┌─────────────────────────┘
                │
                ↓
┌───────────────────────────────────────────────────────────────┐
│                       AJAX HANDLER                             │
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Generate   │  │   Generate   │  │   Generate   │         │
│  │    Title     │  │   Summary    │  │     Tags     │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Analyze    │  │     Save     │  │   Generate   │         │
│  │  Sentiment   │  │     News     │  │    Image     │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
└───────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────┼───────────────┐
                ↓               ↓               ↓
┌─────────────────────┐  ┌──────────────┐  ┌──────────────┐
│   WORDPRESS DB      │  │  OPENAI API  │  │ ANTHROPIC API│
│                     │  │              │  │              │
│ ┌─────────────────┐ │  │ ┌──────────┐ │  │ ┌──────────┐ │
│ │    wp_posts     │ │  │ │  GPT-4   │ │  │ │ Claude   │ │
│ └─────────────────┘ │  │ │ GPT-3.5  │ │  │ │  3.5     │ │
│ ┌─────────────────┐ │  │ └──────────┘ │  │ │ Sonnet   │ │
│ │  wp_postmeta    │ │  │ ┌──────────┐ │  │ └──────────┘ │
│ └─────────────────┘ │  │ │  DALL-E  │ │  │              │
│ ┌─────────────────┐ │  │ └──────────┘ │  │              │
│ │    wp_terms     │ │  │              │  │              │
│ └─────────────────┘ │  └──────────────┘  └──────────────┘
│ ┌─────────────────┐ │
│ │ wp_term_taxonomy│ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │wp_term_relation │ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │   wp_options    │ │
│ └─────────────────┘ │
└─────────────────────┘
```

---

## 🎯 Descripción de Componentes Principales

### 1. **INITIALIZATION LAYER** (Capa de Inicialización)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **Main Plugin File** | `smartnotify-ai.php` | Punto de entrada del plugin |
| **Autoloader** | `class-autoloader.php` | Carga automática de clases (PSR-4) |
| **Activator** | `class-activator.php` | Lógica de activación del plugin |
| **Deactivator** | `class-deactivator.php` | Lógica de desactivación del plugin |

**Flujo:**
```
smartnotify-ai.php → Autoloader → Container → Plugin Init
```

---

### 2. **CORE LAYER** (Capa Núcleo)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **Container** | `Core/class-container.php` | Dependency Injection Container |
| **Config** | `Core/class-config.php` | Gestión de configuración |
| **Assets** | `Core/class-assets.php` | Gestión de CSS/JS |
| **Plugin** | `Core/class-plugin.php` | Inicialización principal |

**Interacciones:**
```
Container ← Config
Container → Todos los servicios
Assets → Admin/Frontend
Plugin → Coordina todo
```

---

### 3. **DATA LAYER** (Capa de Datos)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **NewsPostType** | `PostTypes/class-news-post-type.php` | Registro del CPT |
| **NewsTaxonomies** | `Taxonomies/class-news-taxonomies.php` | Registro de taxonomías |
| **Meta Fields** | Varios meta boxes | Campos personalizados |

**Base de Datos:**
```
wp_posts (post_type = 'smartnotify_news')
    ↓
wp_postmeta (_smartnotify_sentiment, _smartnotify_ai_*)
    ↓
wp_terms / wp_term_taxonomy (smartnotify_category, smartnotify_tag)
```

---

### 4. **ADMIN LAYER** (Capa Administrativa)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **AdminController** | `Admin/class-admin-controller.php` | Controlador principal del admin |
| **SettingsPage** | `Admin/class-settings-page.php` | Página de configuración |
| **NewsForm** | `Admin/class-news-form.php` | Formulario de noticias |
| **MetaBoxes** | `Admin/class-meta-boxes.php` | Meta boxes personalizados |

**Flujo de Trabajo:**
```
Usuario Admin → AdminController → NewsForm/SettingsPage → MetaBoxes → AJAX Handler
```

---

### 5. **SERVICES LAYER** (Capa de Servicios)

#### 5.1 AI Services

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **AIServiceFactory** | `Services/AI/class-ai-service-factory.php` | Factory para crear servicios de IA |
| **OpenAIService** | `Services/AI/class-open-ai-service.php` | Implementación de OpenAI |
| **AnthropicService** | `Services/AI/class-anthropic-service.php` | Implementación de Anthropic |
| **ContentGenerator** | `Services/AI/class-content-generator.php` | Generación de contenido |
| **SentimentAnalyzer** | `Services/AI/class-sentiment-analyzer.php` | Análisis de sentimiento |
| **AIServiceInterface** | `Services/AI/interface-ai-service.php` | Contrato de servicios |

**Patrón Factory:**
```
AIServiceFactory → Crea → OpenAIService | AnthropicService | MockAIService
                      ↓
            Implementan AIServiceInterface
                      ↓
            Usados por ContentGenerator
```

#### 5.2 Image Services

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **DALLEService** | `Services/Image/class-dalle-service.php` | Generación con DALL-E |
| **HuggingFaceService** | `Services/Image/class-huggingface-service.php` | Generación con HuggingFace |
| **ImageServiceInterface** | `Services/Image/interface-image-service.php` | Contrato de servicios |

---

### 6. **FRONTEND LAYER** (Capa Frontend)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **NewsShortcode** | `Frontend/class-news-shortcode.php` | Shortcode `[smartnotify_news]` |
| **Frontend Assets** | `assets/css/frontend.css` | Estilos públicos |
| **Frontend Scripts** | `assets/js/frontend.js` | Scripts públicos |

**Renderizado:**
```
[smartnotify_news] → NewsShortcode::render() → WP_Query → HTML Cards
```

---

### 7. **AJAX HANDLER** (Manejador AJAX)

| Componente | Archivo | Responsabilidad |
|------------|---------|----------------|
| **AjaxHandler** | `Ajax/class-ajax-handler.php` | Maneja todas las peticiones AJAX |

**Endpoints:**
```php
wp_ajax_smartnotify_generate_title      → ContentGenerator::generateTitle()
wp_ajax_smartnotify_generate_summary    → ContentGenerator::generateSummary()
wp_ajax_smartnotify_generate_tags       → ContentGenerator::generateTags()
wp_ajax_smartnotify_analyze_sentiment   → SentimentAnalyzer::analyze()
wp_ajax_smartnotify_save_news           → Guarda noticia
wp_ajax_smartnotify_generate_image      → Genera imagen
```

---

## 🔄 Flujos de Interacción

### Flujo 1: Generación de Título con IA

```
┌──────────┐     ┌─────────┐     ┌──────────┐     ┌──────────┐     ┌─────────┐
│ Usuario  │────→│ Admin   │────→│  AJAX    │────→│ Content  │────→│ OpenAI  │
│   UI     │     │   JS    │     │ Handler  │     │Generator │     │   API   │
└──────────┘     └─────────┘     └──────────┘     └──────────┘     └─────────┘
     ↑                                                                    │
     │                                                                    │
     └────────────────────────────────────────────────────────────────────┘
                            Respuesta con título
```

### Flujo 2: Crear Nueva Noticia

```
┌──────────┐     ┌─────────┐     ┌──────────┐     ┌──────────┐     ┌─────────┐
│ Usuario  │────→│ News    │────→│  AJAX    │────→│WordPress │────→│   DB    │
│  Admin   │     │  Form   │     │ Handler  │     │   Core   │     │wp_posts │
└──────────┘     └─────────┘     └──────────┘     └──────────┘     └─────────┘
                                      │
                                      ↓
                               ┌──────────┐
                               │ Services │
                               │   AI     │
                               └──────────┘
```

### Flujo 3: Mostrar Noticias en Frontend

```
┌──────────┐     ┌─────────┐     ┌──────────┐     ┌──────────┐     ┌─────────┐
│ Usuario  │────→│Shortcode│────→│ WP_Query │────→│   DB     │────→│  HTML   │
│ Público  │     │ Handler │     │          │     │wp_posts  │     │  Cards  │
└──────────┘     └─────────┘     └──────────┘     └──────────┘     └─────────┘
```

---

## 🎨 Patrones de Diseño Aplicados

### 1. **Factory Pattern**
```
AIServiceFactory
    ├── createOpenAIService()
    ├── createAnthropicService()
    └── createMockService()
```

### 2. **Singleton Pattern**
```
SmartNotify_AI::get_instance()
```

### 3. **Dependency Injection**
```
Container
    ├── register('ai.factory', ...)
    ├── register('content.generator', ...)
    └── get('service.name')
```

### 4. **Strategy Pattern (via Interfaces)**
```
AIServiceInterface
    ├── OpenAIService
    ├── AnthropicService
    └── MockAIService
```

---

## 📦 Dependencias Entre Componentes

```
smartnotify-ai.php
    │
    ├── Autoloader
    ├── Container
    │   ├── Config
    │   ├── Assets
    │   ├── Plugin
    │   │   ├── NewsPostType
    │   │   ├── NewsTaxonomies
    │   │   ├── AdminController
    │   │   │   ├── SettingsPage
    │   │   │   ├── NewsForm
    │   │   │   └── MetaBoxes
    │   │   ├── AjaxHandler
    │   │   │   ├── ContentGenerator
    │   │   │   │   └── AIServiceFactory
    │   │   │   │       ├── OpenAIService
    │   │   │   │       └── AnthropicService
    │   │   │   └── SentimentAnalyzer
    │   │   └── NewsShortcode
    │   └── ...
    └── ...
```

---

## 🗂️ Estructura de Archivos y Componentes

```
smartnotify-ai/
│
├── smartnotify-ai.php                 ← Main Plugin File
│
├── includes/
│   ├── class-autoloader.php           ← Autoloader
│   ├── class-activator.php            ← Activator
│   ├── class-deactivator.php          ← Deactivator
│   │
│   ├── Core/                          ← Core Layer
│   │   ├── class-container.php
│   │   ├── class-config.php
│   │   ├── class-assets.php
│   │   └── class-plugin.php
│   │
│   ├── Admin/                         ← Admin Layer
│   │   ├── class-admin-controller.php
│   │   ├── class-settings-page.php
│   │   ├── class-news-form.php
│   │   └── class-meta-boxes.php
│   │
│   ├── Frontend/                      ← Frontend Layer
│   │   └── class-news-shortcode.php
│   │
│   ├── PostTypes/                     ← Data Layer
│   │   └── class-news-post-type.php
│   │
│   ├── Taxonomies/                    ← Data Layer
│   │   └── class-news-taxonomies.php
│   │
│   ├── Ajax/                          ← AJAX Handler
│   │   └── class-ajax-handler.php
│   │
│   └── Services/                      ← Services Layer
│       ├── AI/
│       │   ├── interface-ai-service.php
│       │   ├── class-ai-service-factory.php
│       │   ├── class-open-ai-service.php
│       │   ├── class-anthropic-service.php
│       │   ├── class-mock-ai-service.php
│       │   ├── class-content-generator.php
│       │   └── class-sentiment-analyzer.php
│       │
│       └── Image/
│           ├── interface-image-service.php
│           ├── class-dalle-service.php
│           └── class-huggingface-service.php
│
└── assets/                            ← Frontend Assets
    ├── css/
    │   ├── admin.css
    │   └── frontend.css
    └── js/
        ├── admin.js
        └── frontend.js
```

---

## 🔗 Interfaces y Contratos

### AIServiceInterface
```php
interface AIServiceInterface {
    public function generate($prompt, array $options = []);
    public function generateTitle($content);
    public function generateSummary($content);
    public function generateTags($content);
    public function analyzeSentiment($content);
    public function isAvailable();
}
```

### ImageServiceInterface
```php
interface ImageServiceInterface {
    public function generate($prompt, array $options = []);
    public function isAvailable();
}
```

---

## 📊 Resumen de Componentes

| Capa | Componentes | Total |
|------|-------------|-------|
| **Initialization** | Main, Autoloader, Activator, Deactivator | 4 |
| **Core** | Container, Config, Assets, Plugin | 4 |
| **Data** | PostType, Taxonomies, MetaFields | 3 |
| **Admin** | Controller, Settings, Form, MetaBoxes | 4 |
| **Services (AI)** | Factory, OpenAI, Anthropic, Generator, Analyzer, Interface | 6 |
| **Services (Image)** | DALLE, HuggingFace, Interface | 3 |
| **Frontend** | Shortcode, Assets | 2 |
| **AJAX** | Handler | 1 |
| **TOTAL** | | **27 componentes** |

---

**Fecha:** Enero 2026  
**Versión:** 1.0.0  
**Proyecto:** SmartNotify AI
