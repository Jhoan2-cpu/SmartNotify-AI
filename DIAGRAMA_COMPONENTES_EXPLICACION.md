# Diagrama de Componentes UML - SmartNotify AI

## 📋 Índice
1. [Introducción](#introducción)
2. [Vista General del Diagrama](#vista-general-del-diagrama)
3. [Componentes del Sistema](#componentes-del-sistema)
4. [Interfaces del Sistema](#interfaces-del-sistema)
5. [Relaciones y Dependencias](#relaciones-y-dependencias)
6. [Componentes Externos](#componentes-externos)
7. [Flujo de Comunicación](#flujo-de-comunicación)
8. [Conclusiones](#conclusiones)

---

## 1. Introducción

### ¿Qué es un Diagrama de Componentes?

El **Diagrama de Componentes UML** es una representación visual de alto nivel que muestra la estructura modular de un sistema de software. En este diagrama se representan:

- **Componentes**: Módulos o subsistemas del software
- **Interfaces**: Puntos de comunicación entre componentes
- **Dependencias**: Relaciones de uso entre componentes
- **Sistemas externos**: Recursos externos que utiliza el sistema

### Propósito del Diagrama

Este diagrama de componentes para **SmartNotify AI** tiene como objetivo:

1. Mostrar la **arquitectura modular** del plugin
2. Identificar las **responsabilidades** de cada componente
3. Visualizar las **dependencias** entre módulos
4. Documentar las **interfaces** de comunicación
5. Facilitar el **mantenimiento** y **evolución** del sistema

---

## 2. Vista General del Diagrama

### Estructura Jerárquica

El sistema SmartNotify AI está organizado en una **estructura de contenedores anidados**:

```
WordPress Core (CMS)
    └── SmartNotify AI Plugin
            ├── Core
            ├── Admin
            ├── Frontend
            ├── AI Services
            ├── Data Layer
            └── AJAX Handler
```

### Elementos del Diagrama

El diagrama contiene los siguientes elementos principales:

| Elemento | Cantidad | Descripción |
|----------|----------|-------------|
| **Subsistema contenedor** | 1 | WordPress Core |
| **Componente principal** | 1 | SmartNotify AI Plugin |
| **Componentes internos** | 6 | Módulos funcionales del plugin |
| **Interfaces provided** | 6 | Servicios expuestos por componentes |
| **Interfaces required** | 6 | Servicios requeridos por componentes |
| **Componentes externos** | 2 | Anthropic API, WordPress DB |
| **Dependencias** | 8 | Relaciones de uso entre componentes |

---

## 3. Componentes del Sistema

### 3.1 WordPress Core `<<CMS>>`

**Tipo**: Subsistema contenedor  
**Estereotipo**: `<<CMS>>`

**Descripción:**
Representa el sistema de gestión de contenidos WordPress que actúa como plataforma base para el plugin. Proporciona toda la infraestructura necesaria para que el plugin funcione.

**Responsabilidades:**
- Proporcionar el framework y APIs de WordPress
- Gestionar el ciclo de vida del plugin
- Proveer hooks y filtros para extensibilidad
- Administrar la base de datos y configuración

---

### 3.2 SmartNotify AI Plugin `<<component>>`

**Tipo**: Componente principal  
**Descripción:**
Contenedor principal que encapsula todos los módulos funcionales del plugin. Representa la aplicación completa de SmartNotify AI.

**Responsabilidades:**
- Inicializar y coordinar todos los componentes internos
- Gestionar el ciclo de vida del plugin
- Proporcionar la fachada principal del sistema

---

### 3.3 Core `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**: 
- `includes/Core/class-container.php`
- `includes/Core/class-config.php`
- `includes/Core/class-plugin.php`
- `includes/Core/class-assets.php`

**Descripción:**
Componente fundamental que proporciona servicios básicos de infraestructura para todo el plugin.

**Responsabilidades:**
1. **Dependency Injection Container**: Gestiona la creación e inyección de dependencias
2. **Configuración Global**: Administra opciones y configuración del plugin
3. **Inicialización**: Coordina el arranque de todos los componentes
4. **Gestión de Assets**: Controla la carga de CSS y JavaScript

**Interfaz Provista:**
- `ICore`: Servicios de configuración, contenedor DI, y utilidades comunes

**Por qué es importante:**
Es el **corazón arquitectónico** del sistema. Implementa el patrón Dependency Injection, lo que permite desacoplar componentes y facilitar el testing y mantenimiento.

---

### 3.4 Admin `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**:
- `includes/Admin/class-admin-controller.php`
- `includes/Admin/class-settings-page.php`
- `includes/Admin/class-news-form.php`
- `includes/Admin/class-meta-boxes.php`

**Descripción:**
Componente que gestiona toda la interfaz administrativa del plugin en el panel de WordPress.

**Responsabilidades:**
1. **Panel de Configuración**: Interfaz para configurar APIs de IA
2. **Formularios de Noticias**: Interfaz para crear/editar noticias
3. **Meta Boxes**: Cajas personalizadas con funciones de IA
4. **Filtros y Listados**: Gestión de la tabla de noticias

**Interfaz Provista:**
- `IAdmin`: Servicios de gestión administrativa

**Interfaces Requeridas:**
- `ICore`: Para acceder a configuración y contenedor DI
- `IAjaxHandler`: Para realizar operaciones asíncronas
- `IDataAccess`: Para manipular datos de noticias

**Por qué es importante:**
Es la **cara visible** del plugin para los administradores. Proporciona todas las herramientas para gestionar noticias con IA.

---

### 3.5 Frontend `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**:
- `includes/Frontend/class-news-shortcode.php`
- `assets/css/frontend.css`
- `assets/js/frontend.js`

**Descripción:**
Componente responsable de la visualización pública de las noticias en el sitio web.

**Responsabilidades:**
1. **Shortcode**: Implementa `[smartnotify_news]` para mostrar noticias
2. **Renderizado**: Genera HTML para visualización pública
3. **Estilos Frontend**: Proporciona CSS responsivo
4. **Interacciones**: Maneja JavaScript para funcionalidades públicas

**Interfaz Provista:**
- `IFrontend`: Servicios de visualización pública

**Interfaces Requeridas:**
- `ICore`: Para acceder a configuración
- `IDataAccess`: Para consultar noticias publicadas

**Por qué es importante:**
Es la **cara pública** del plugin. Permite a los visitantes del sitio ver las noticias generadas con IA.

---

### 3.6 AI Services `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**:
- `includes/Services/AI/class-ai-service-factory.php`
- `includes/Services/AI/class-anthropic-service.php`
- `includes/Services/AI/class-content-generator.php`
- `includes/Services/AI/class-sentiment-analyzer.php`
- `includes/Services/AI/interface-ai-service.php`

**Descripción:**
Componente que encapsula toda la lógica de integración con servicios de Inteligencia Artificial.

**Responsabilidades:**
1. **Factory de Servicios**: Crea instancias de proveedores de IA
2. **Integración con Anthropic**: Comunicación con Claude API
3. **Generación de Contenido**: Títulos, resúmenes, etiquetas
4. **Análisis de Sentimiento**: Clasificación positivo/neutral/negativo
5. **Abstracción de IA**: Interfaz unificada para diferentes proveedores

**Interfaz Provista:**
- `IAIService`: Servicios de inteligencia artificial

**Interfaces Requeridas:**
- `ICore`: Para acceder a API keys y configuración

**Patrón de Diseño:**
Implementa el **Factory Pattern** para crear servicios de IA intercambiables.

**Por qué es importante:**
Es el **cerebro del sistema**. Toda la magia de generación automática de contenido ocurre aquí.

---

### 3.7 Data Layer `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**:
- `includes/PostTypes/class-news-post-type.php`
- `includes/Taxonomies/class-news-taxonomies.php`
- `includes/Admin/class-meta-boxes.php` (para meta fields)

**Descripción:**
Componente que gestiona toda la persistencia y estructura de datos del plugin.

**Responsabilidades:**
1. **Custom Post Type**: Define y registra el tipo de contenido `smartnotify_news`
2. **Taxonomías**: Gestiona categorías y etiquetas de noticias
3. **Meta Fields**: Administra campos personalizados (sentimiento, flags IA)
4. **Abstracción de Datos**: Proporciona capa de acceso a datos

**Interfaz Provista:**
- `IDataAccess`: Servicios de acceso a datos

**Interfaces Requeridas:**
- `ICore`: Para inicialización y configuración

**Por qué es importante:**
Es el **almacén de datos** del sistema. Define cómo se estructuran y guardan las noticias.

---

### 3.8 AJAX Handler `<<component>>`

**Ubicación**: Interno al plugin  
**Archivos principales**:
- `includes/Ajax/class-ajax-handler.php`

**Descripción:**
Componente que gestiona todas las peticiones asíncronas entre el frontend administrativo y el backend.

**Responsabilidades:**
1. **Endpoints AJAX**: Define y maneja todas las acciones AJAX
   - `smartnotify_generate_title`
   - `smartnotify_generate_summary`
   - `smartnotify_generate_tags`
   - `smartnotify_analyze_sentiment`
   - `smartnotify_save_news`
2. **Validación**: Verifica nonces y permisos
3. **Orquestación**: Coordina llamadas entre Admin y Services

**Interfaz Provista:**
- `IAjaxHandler`: Servicios de comunicación asíncrona

**Interfaces Requeridas:**
- `IAIService`: Para invocar servicios de IA
- `IDataAccess`: Para guardar/consultar datos

**Por qué es importante:**
Es el **puente de comunicación** entre la interfaz y los servicios. Permite interactividad sin recargar páginas.

---

## 4. Interfaces del Sistema

### ¿Qué son las interfaces en este contexto?

Las interfaces en un diagrama de componentes UML representan **contratos de comunicación** entre componentes. Definen qué servicios ofrece un componente (provided) y qué servicios necesita de otros (required).

### Notación UML:

- **Círculo completo (○)**: Interfaz Provided (servicios que el componente ofrece)
- **Semicírculo (⊃)**: Interfaz Required (servicios que el componente necesita)

---

### 4.1 ICore (Provided por Core)

**Servicios que ofrece:**
```php
interface ICore {
    public function getContainer();      // Acceso al DI Container
    public function getConfig($key);     // Obtener configuración
    public function setConfig($key, $value); // Establecer configuración
    public function getAssets();         // Gestor de assets
}
```

**Consumido por:**
- Admin (requiere configuración y DI)
- Frontend (requiere configuración)
- AI Services (requiere API keys)
- Data Layer (requiere inicialización)

---

### 4.2 IAdmin (Provided por Admin)

**Servicios que ofrece:**
```php
interface IAdmin {
    public function init();              // Inicializar admin
    public function registerMenuPages(); // Registrar páginas de menú
    public function renderSettingsPage(); // Renderizar configuración
    public function addMetaBoxes();      // Agregar meta boxes
}
```

**Consumido por:**
- Core (para inicializar el panel administrativo)

---

### 4.3 IFrontend (Provided por Frontend)

**Servicios que ofrece:**
```php
interface IFrontend {
    public function registerShortcode(); // Registrar shortcode
    public function enqueueAssets();     // Cargar CSS/JS frontend
    public function render($atts);       // Renderizar noticias
}
```

**Consumido por:**
- Core (para registrar funcionalidades públicas)

---

### 4.4 IAIService (Provided por AI Services)

**Servicios que ofrece:**
```php
interface IAIService {
    public function generateTitle($content);   // Generar título
    public function generateSummary($content); // Generar resumen
    public function generateTags($content);    // Generar etiquetas
    public function analyzeSentiment($content); // Analizar sentimiento
    public function isAvailable();             // Verificar disponibilidad
}
```

**Consumido por:**
- AJAX Handler (para procesar peticiones de IA)

---

### 4.5 IDataAccess (Provided por Data Layer)

**Servicios que ofrece:**
```php
interface IDataAccess {
    public function getNews($args);          // Obtener noticias
    public function saveNews($data);         // Guardar noticia
    public function updateMetaField($id, $key, $value); // Actualizar meta
    public function getCategories();         // Obtener categorías
    public function getTags();               // Obtener etiquetas
}
```

**Consumido por:**
- Admin (para gestionar noticias)
- Frontend (para mostrar noticias)
- AJAX Handler (para guardar datos)

---

### 4.6 IAjaxHandler (Provided por AJAX Handler)

**Servicios que ofrece:**
```php
interface IAjaxHandler {
    public function init();                  // Registrar endpoints
    public function handleGenerateTitle();   // Manejar generación de título
    public function handleGenerateSummary(); // Manejar generación de resumen
    public function handleGenerateTags();    // Manejar generación de tags
    public function handleAnalyzeSentiment(); // Manejar análisis
}
```

**Consumido por:**
- Admin (para realizar operaciones asíncronas)

---

## 5. Relaciones y Dependencias

### Tipos de Relaciones

En el diagrama se representan dos tipos principales de relaciones:

1. **Dependency (Dependencia)**: Línea discontinua con flecha (----→)
   - Indica que un componente **usa** otro
   - Es una relación de **acoplamiento débil**

2. **Interface Connection (Conexión de Interfaz)**: Círculo conectado a semicírculo
   - Indica que un componente **consume** la interfaz de otro
   - Es la forma **estándar** de comunicación entre componentes

---

### Matriz de Dependencias

| Componente | Depende de | Interfaz Utilizada |
|------------|-----------|-------------------|
| **Admin** | Core | ICore |
| **Admin** | AJAX Handler | IAjaxHandler |
| **Admin** | Data Layer | IDataAccess |
| **Frontend** | Core | ICore |
| **Frontend** | Data Layer | IDataAccess |
| **AI Services** | Core | ICore |
| **AI Services** | Anthropic API | - (REST API) |
| **AJAX Handler** | AI Services | IAIService |
| **AJAX Handler** | Data Layer | IDataAccess |
| **Data Layer** | Core | ICore |
| **Data Layer** | WordPress DB | - (wpdb) |

---

### Flujo de Dependencias (Diagrama Simplificado)

```
             Core (ICore)
                 ↓
        ┌────────┼────────┐
        ↓        ↓        ↓
     Admin   Frontend   Data Layer → WordPress DB
        ↓                    ↑
   AJAX Handler ────────────┘
        ↓
   AI Services → Anthropic API
```

---

### Análisis de Acoplamiento

#### ✅ **Bajo Acoplamiento (Bueno)**

- Los componentes se comunican **solo a través de interfaces**
- No hay dependencias circulares
- Cada componente tiene responsabilidades bien definidas

#### 🎯 **Puntos Clave:**

1. **Core es el único dependiente directo**: Todos los componentes dependen de Core, pero no entre ellos directamente
2. **AJAX Handler como Mediador**: Actúa como intermediario entre Admin y Services
3. **Data Layer centralizado**: Un único punto de acceso a datos

---

## 6. Componentes Externos

### 6.1 Anthropic API `<<external>>`

**Tipo**: Sistema externo  
**Estereotipo**: `<<external>>`

**Descripción:**
API REST proporcionada por Anthropic que ofrece acceso a los modelos de lenguaje Claude.

**Servicios utilizados:**
- **Claude 3.5 Sonnet**: Modelo principal para generación
- **Claude 3 Opus**: Modelo alternativo de alta capacidad
- **Claude 3 Sonnet**: Modelo equilibrado

**Operaciones:**
```http
POST https://api.anthropic.com/v1/messages
Content-Type: application/json
x-api-key: [API_KEY]

{
  "model": "claude-3-5-sonnet-20241022",
  "messages": [{"role": "user", "content": "..."}],
  "max_tokens": 1024
}
```

**Relación con el sistema:**
- Consumida por: **AI Services**
- Tipo de conexión: HTTP REST API
- Autenticación: API Key

**Por qué es externo:**
Es un servicio de terceros completamente independiente del plugin. El sistema no tiene control sobre su implementación.

---

### 6.2 WordPress DB `<<database>>`

**Tipo**: Base de datos  
**Estereotipo**: `<<database>>`

**Descripción:**
Base de datos MySQL/MariaDB de WordPress que almacena todos los datos del plugin.

**Tablas utilizadas:**

1. **wp_posts**
   - Almacena las noticias (`post_type = 'smartnotify_news'`)
   - Campos: `ID`, `post_title`, `post_content`, `post_excerpt`, `post_status`, etc.

2. **wp_postmeta**
   - Almacena metadatos de las noticias
   - Campos personalizados:
     - `_smartnotify_sentiment` (positive/neutral/negative)
     - `_smartnotify_ai_generated_title` (boolean)
     - `_smartnotify_ai_generated_summary` (boolean)
     - `_smartnotify_ai_generated_tags` (boolean)

3. **wp_terms**
   - Almacena términos de taxonomías (categorías y etiquetas)

4. **wp_term_taxonomy**
   - Define el tipo de taxonomía
   - `taxonomy = 'smartnotify_category'` o `'smartnotify_tag'`

5. **wp_term_relationships**
   - Relaciona posts con términos de taxonomías

6. **wp_options**
   - Almacena configuración del plugin:
     - `smartnotify_ai_provider` (anthropic)
     - `smartnotify_ai_api_key` (API key encriptada)
     - `smartnotify_ai_model` (claude-3-5-sonnet-20241022)
     - Opciones de funciones automáticas

**Relación con el sistema:**
- Consumida por: **Data Layer**
- Tipo de conexión: wpdb (WordPress Database Class)
- Acceso: A través de funciones de WordPress

**Por qué es externo:**
Aunque es parte de WordPress, se considera externo al plugin porque es un recurso compartido gestionado por el CMS.

---

## 7. Flujo de Comunicación

### 7.1 Flujo de Inicialización del Plugin

```
1. WordPress Core carga el plugin
        ↓
2. Core Component se inicializa
        ↓
3. Core registra servicios en el Container (DI)
        ↓
4. Core inicializa Data Layer
        ↓
5. Data Layer registra Custom Post Type y Taxonomías en WordPress DB
        ↓
6. Core inicializa Admin Component (si es admin)
        ↓
7. Admin registra menús, meta boxes y hooks
        ↓
8. Core inicializa Frontend Component (si es público)
        ↓
9. Frontend registra shortcode
        ↓
10. Core inicializa AJAX Handler
        ↓
11. AJAX Handler registra endpoints
```

---

### 7.2 Flujo de Generación de Título con IA

```
Usuario hace clic en "Generar Título"
        ↓
Admin Component captura el evento (JavaScript)
        ↓
Admin envía petición AJAX a WordPress
        ↓
AJAX Handler recibe la petición
        │
        ├─→ Valida nonce y permisos
        │
        ├─→ Solicita servicio a AI Services (vía IAIService)
        │       ↓
        │   AI Services obtiene configuración de Core (API key)
        │       ↓
        │   AI Services llama a Anthropic API (HTTP POST)
        │       ↓
        │   Anthropic API procesa y devuelve respuesta
        │       ↓
        │   AI Services formatea la respuesta
        │
        └─→ AJAX Handler devuelve JSON al Admin
                ↓
Admin Component actualiza la interfaz con el título generado
```

---

### 7.3 Flujo de Guardado de Noticia

```
Usuario hace clic en "Publicar"
        ↓
Admin Component captura el envío del formulario
        ↓
Admin prepara datos de la noticia
        ↓
Admin envía petición AJAX
        ↓
AJAX Handler recibe la petición
        │
        ├─→ Valida datos y permisos
        │
        ├─→ Solicita guardado a Data Layer (vía IDataAccess)
        │       ↓
        │   Data Layer usa WordPress API
        │       ↓
        │   WordPress guarda en WordPress DB
        │       │
        │       ├─→ INSERT en wp_posts
        │       ├─→ INSERT en wp_postmeta
        │       └─→ INSERT en wp_term_relationships
        │
        └─→ AJAX Handler devuelve confirmación
                ↓
Admin Component muestra mensaje de éxito
```

---

### 7.4 Flujo de Visualización Frontend (Shortcode)

```
Usuario visita página con shortcode [smartnotify_news]
        ↓
WordPress detecta el shortcode
        ↓
Frontend Component procesa el shortcode
        │
        ├─→ Obtiene configuración de Core
        │
        ├─→ Consulta noticias a Data Layer (vía IDataAccess)
        │       ↓
        │   Data Layer consulta WordPress DB
        │       ↓
        │   WordPress DB devuelve noticias
        │
        ├─→ Frontend renderiza HTML
        │
        └─→ Frontend carga CSS/JS
                ↓
Usuario ve las noticias en el sitio
```

---

## 8. Conclusiones

### Fortalezas de la Arquitectura

#### ✅ **1. Separación de Responsabilidades**
Cada componente tiene una **responsabilidad única y bien definida**, siguiendo el principio SOLID de Single Responsibility.

#### ✅ **2. Bajo Acoplamiento**
Los componentes se comunican **solo a través de interfaces**, lo que reduce dependencias y facilita cambios.

#### ✅ **3. Alta Cohesión**
Dentro de cada componente, las clases están **fuertemente relacionadas** y trabajan juntas hacia un objetivo común.

#### ✅ **4. Extensibilidad**
Gracias al uso de interfaces y el patrón Factory, es **fácil agregar nuevos proveedores de IA** sin modificar código existente.

#### ✅ **5. Testabilidad**
El uso de Dependency Injection facilita la **creación de mocks** y unit tests.

#### ✅ **6. Mantenibilidad**
La estructura modular permite **modificar un componente** sin afectar a los demás.

---

### Patrones de Diseño Aplicados

1. **Dependency Injection (DI)**
   - Implementado en: Core Component (Container)
   - Beneficio: Desacoplamiento y testabilidad

2. **Factory Pattern**
   - Implementado en: AI Services (AIServiceFactory)
   - Beneficio: Creación flexible de servicios de IA

3. **Strategy Pattern** (via Interfaces)
   - Implementado en: IAIService interface
   - Beneficio: Intercambiabilidad de algoritmos de IA

4. **Facade Pattern**
   - Implementado en: AJAX Handler
   - Beneficio: Simplifica comunicación entre capas

5. **Repository Pattern**
   - Implementado en: Data Layer
   - Beneficio: Abstracción del acceso a datos

---

### Escalabilidad del Sistema

#### 🚀 **Agregar nuevo proveedor de IA (ej: OpenAI)**
```
1. Crear clase OpenAIService implements IAIService
2. Modificar AIServiceFactory para incluir OpenAI
3. Agregar opción en Settings Page
```
**Impacto:** Mínimo. No requiere modificar otros componentes.

#### 🚀 **Agregar nueva funcionalidad de IA**
```
1. Agregar método a IAIService interface
2. Implementar en AnthropicService
3. Agregar endpoint en AJAX Handler
4. Agregar botón en Admin
```
**Impacto:** Controlado. Cambios localizados en componentes específicos.

#### 🚀 **Migrar a otro CMS**
```
1. Reimplementar Data Layer para el nuevo CMS
2. Mantener la misma interfaz IDataAccess
```
**Impacto:** Moderado. Solo afecta Data Layer, el resto permanece igual.

---

### Puntos de Mejora Futura

#### 🔄 **1. Implementar Cache**
Agregar un componente de cache entre AJAX Handler y AI Services para reducir llamadas a la API externa.

#### 🔄 **2. Queue de Procesamiento**
Para generaciones masivas, implementar una cola de trabajos (job queue) para procesar noticias en background.

#### 🔄 **3. Logging Centralizado**
Agregar un componente de logging para rastrear errores y operaciones.

#### 🔄 **4. API REST Pública**
Exponer una API REST para que terceros puedan interactuar con el plugin.

---

### Métricas de Calidad Arquitectónica

| Métrica | Valor | Evaluación |
|---------|-------|------------|
| **Número de componentes** | 6 | ✅ Óptimo (ni muy pocos ni muchos) |
| **Profundidad de dependencias** | 2-3 niveles | ✅ Adecuada |
| **Acoplamiento (Coupling)** | Bajo | ✅ Solo via interfaces |
| **Cohesión (Cohesion)** | Alta | ✅ Responsabilidades claras |
| **Componentes externos** | 2 | ✅ Mínimo necesario |
| **Interfaces definidas** | 6 | ✅ Una por componente |
| **Dependencias circulares** | 0 | ✅ Ninguna |

---

### Resumen Ejecutivo

El **Diagrama de Componentes de SmartNotify AI** muestra una arquitectura **modular, escalable y mantenible**. El sistema está dividido en **6 componentes principales** que se comunican a través de **interfaces bien definidas**, siguiendo los principios de diseño SOLID.

**Puntos Destacados:**

1. ✅ **Arquitectura limpia** con separación clara de responsabilidades
2. ✅ **Bajo acoplamiento** entre componentes
3. ✅ **Alta extensibilidad** para agregar nuevas funcionalidades
4. ✅ **Fácil mantenimiento** gracias a la modularidad
5. ✅ **Preparado para escalar** con mínimos cambios

El diseño facilita la **colaboración en equipo**, permite **unit testing efectivo** y prepara el sistema para **futuras evoluciones** sin requerir refactorizaciones mayores.

---

**Documento creado:** Enero 2026  
**Versión del Plugin:** 1.0.0  
**Proyecto:** SmartNotify AI  
**Tipo de Diagrama:** UML Component Diagram
