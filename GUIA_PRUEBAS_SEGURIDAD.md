# Guía de Pruebas de Seguridad - SmartNotify AI

## Introducción

Esta guía proporciona procedimientos detallados para realizar las 6 pruebas de seguridad (PS-01 a PS-06) documentadas en `DESCRIPCION_PROYECTO.md`. Cada prueba incluye pasos específicos y qué capturas de pantalla tomar.

---

## PS-01: Inyección SQL

### Objetivo
Verificar que el plugin usa WordPress Query API (WP_Query) y no consultas SQL directas, previniendo inyecciones SQL.

### Herramientas Necesarias
- Plugin "Query Monitor" (instalado desde WordPress.org)

### Pasos

1. **Instalar Query Monitor:**
   ```
   Plugins → Añadir nuevo → Buscar "Query Monitor" → Instalar → Activar
   ```

2. **Ver el frontend con el shortcode:**
   - Ve a una página con `[smartnotify_news]`
   - En la barra superior, clic en el icono de Query Monitor
   - **CAPTURA 1:** Panel de Query Monitor mostrando las consultas SQL

3. **Analizar las consultas:**
   - Busca consultas que contengan `smartnotify_news`
   - **CAPTURA 2:** Detalle de una consulta mostrando que NO hay concatenación directa de variables
   - Ejemplo de consulta esperada:
   ```sql
   SELECT SQL_CALC_FOUND_ROWS wp_posts.* 
   FROM wp_posts 
   WHERE 1=1 
   AND wp_posts.post_type = 'smartnotify_news'
   ```

4. **Verificar en el código:**
   - Abrir `includes/Frontend/class-news-shortcode.php`
   - Buscar el método `render()`
   - **CAPTURA 3:** Código mostrando uso de `WP_Query` con array de parámetros:
   ```php
   $args = array(
       'post_type'      => 'smartnotify_news',
       'posts_per_page' => $limit,
       ...
   );
   $query = new WP_Query($args);
   ```

5. **Buscar en todo el código:**
   - Buscar en todos los archivos `.php` las palabras: `$wpdb`, `prepare`, `query`
   - **CAPTURA 4:** Resultado de búsqueda mostrando que NO hay consultas directas con `$wpdb->query()` o SQL concatenado

### Resultado Esperado
✅ El plugin usa exclusivamente `WP_Query`, que internamente usa declaraciones preparadas (prepared statements).

---

## PS-02: Cross-Site Scripting (XSS)

### Objetivo
Verificar que todos los datos de salida están escapados correctamente usando funciones de WordPress.

### Pasos

1. **Crear noticia con código malicioso:**
   - Ve a `Noticias → Añadir nueva`
   - Título: `<script>alert('XSS')</script>Test`
   - Contenido: `Hola <img src=x onerror="alert('XSS')">`
   - Publicar

2. **Ver la noticia en el frontend:**
   - Ve a la página con `[smartnotify_news]`
   - **CAPTURA 1:** Los scripts NO se ejecutan, se muestran como texto

3. **Inspeccionar el HTML generado:**
   - Clic derecho → "Inspeccionar elemento"
   - **CAPTURA 2:** HTML mostrando que los caracteres `<>` fueron convertidos a entidades HTML:
   ```html
   <h3>&lt;script&gt;alert('XSS')&lt;/script&gt;Test</h3>
   ```

4. **Verificar funciones de escape en el código:**
   - Abrir `includes/Frontend/class-news-shortcode.php`
   - Buscar: `esc_html`, `esc_attr`, `esc_url`
   - **CAPTURA 3:** Código mostrando múltiples usos de funciones de escape:
   ```php
   echo '<h3>' . esc_html(get_the_title()) . '</h3>';
   echo '<a href="' . esc_url(get_permalink()) . '">';
   echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '">';
   ```

5. **Verificar en AJAX:**
   - Abrir `includes/Ajax/class-ajax-handler.php`
   - Buscar funciones de escape en las respuestas JSON
   - **CAPTURA 4:** Código mostrando sanitización de datos antes de enviarlos:
   ```php
   'title' => esc_html($post->post_title),
   'excerpt' => esc_html($post->post_excerpt),
   ```

### Resultado Esperado
✅ Todas las salidas usan `esc_html()`, `esc_attr()`, `esc_url()` según el contexto.

---

## PS-03: CSRF (Cross-Site Request Forgery)

### Objetivo
Verificar que todas las peticiones AJAX usan nonces de WordPress para prevenir CSRF.

### Pasos

1. **Inspeccionar una petición AJAX:**
   - Ve al panel de admin del plugin
   - F12 → Pestaña "Red/Network"
   - Filtrar por "XHR"
   - Realizar una acción (ej: Generar noticia)
   - **CAPTURA 1:** Petición AJAX mostrando el parámetro `nonce` en el payload

2. **Ver el valor del nonce:**
   - Clic en la petición AJAX
   - Pestaña "Carga útil/Payload"
   - **CAPTURA 2:** Valor del nonce (ej: `nonce: "a1b2c3d4e5"`)

3. **Intentar petición sin nonce (usando Postman o curl):**
   ```bash
   curl -X POST "http://tu-sitio.local/wp-admin/admin-ajax.php" \
   -d "action=smartnotify_generate_news" \
   -d "title=Test"
   ```
   - **CAPTURA 3:** Respuesta de error: `Nonce verification failed` o `Security check failed`

4. **Verificar en el código:**
   - Abrir `includes/Ajax/class-ajax-handler.php`
   - Buscar todos los métodos AJAX
   - **CAPTURA 4:** Código mostrando `check_ajax_referer()` en CADA endpoint:
   ```php
   public function generateNews() {
       check_ajax_referer('smartnotify_ajax_nonce', 'nonce');
       
       if (!current_user_can('edit_posts')) {
           wp_send_json_error('Insufficient permissions');
       }
       ...
   }
   ```

5. **Ver cómo se crea el nonce:**
   - Abrir `includes/Core/class-assets.php`
   - Buscar `wp_localize_script`
   - **CAPTURA 5:** Código mostrando creación del nonce:
   ```php
   wp_localize_script('smartnotify-admin', 'smartnotifyAjax', array(
       'ajaxurl' => admin_url('admin-ajax.php'),
       'nonce'   => wp_create_nonce('smartnotify_ajax_nonce')
   ));
   ```

### Resultado Esperado
✅ Todos los 18 endpoints AJAX verifican el nonce con `check_ajax_referer()`.

---

## PS-04: Acceso Directo a Archivos

### Objetivo
Verificar que los archivos PHP no pueden ejecutarse directamente fuera del contexto de WordPress.

### Pasos

1. **Intentar acceso directo desde el navegador:**
   - URL: `http://tu-sitio.local/wp-content/plugins/SmartNotify-AI/includes/Core/class-container.php`
   - **CAPTURA 1:** Página en blanco (sin contenido visible)

2. **Inspeccionar el código fuente de la página:**
   - Clic derecho → "Ver código fuente"
   - **CAPTURA 2:** La página está completamente vacía (el `exit` se ejecutó)

3. **Verificar protección en múltiples archivos:**
   - Abrir `includes/Core/class-container.php`
   - **CAPTURA 3:** Primera línea del archivo:
   ```php
   <?php
   if (!defined('ABSPATH')) {
       exit;
   }
   ```

4. **Buscar esta protección en todos los archivos:**
   - Buscar en `includes/**/*.php` la cadena: `ABSPATH`
   - **CAPTURA 4:** Lista de archivos mostrando que TODOS tienen esta protección

5. **Probar con otros archivos críticos:**
   - `includes/Ajax/class-ajax-handler.php`
   - `includes/Services/AI/class-anthropic-service.php`
   - **CAPTURA 5:** Ambos archivos también muestran página en blanco

### Resultado Esperado
✅ El 100% de los archivos PHP del plugin contienen `if (!defined('ABSPATH')) { exit; }`.

---

## PS-05: Privilege Escalation

### Objetivo
Verificar que los usuarios sin permisos no pueden acceder a funciones de administración.

### Pasos

1. **Crear usuario de prueba:**
   - Ve a `Usuarios → Añadir nuevo`
   - Nombre de usuario: `test-subscriber`
   - Correo: `test@example.com`
   - Rol: **Suscriptor**
   - **CAPTURA 1:** Formulario de creación de usuario mostrando rol "Suscriptor"

2. **Cerrar sesión e iniciar como suscriptor:**
   - Logout del admin
   - Login con `test-subscriber`
   - **CAPTURA 2:** Dashboard del usuario suscriptor (sin menú de plugins)

3. **Intentar acceder directamente a la URL del plugin:**
   - URL: `http://tu-sitio.local/wp-admin/edit.php?post_type=smartnotify_news`
   - **CAPTURA 3:** Mensaje de error: "Sorry, you are not allowed to edit posts."

4. **Intentar una petición AJAX como suscriptor:**
   - F12 → Consola
   - Ejecutar:
   ```javascript
   fetch(ajaxurl, {
       method: 'POST',
       body: new URLSearchParams({
           action: 'smartnotify_generate_news',
           nonce: '...'
       })
   }).then(r => r.json()).then(console.log);
   ```
   - **CAPTURA 4:** Respuesta: `{"success":false,"data":"Insufficient permissions"}`

5. **Verificar checks de permisos en el código:**
   - Abrir `includes/Ajax/class-ajax-handler.php`
   - **CAPTURA 5:** Código mostrando verificaciones en cada método:
   ```php
   if (!current_user_can('edit_posts')) {
       wp_send_json_error('Insufficient permissions');
   }
   ```

6. **Ver permisos del Custom Post Type:**
   - Abrir `includes/PostTypes/class-news-post-type.php`
   - **CAPTURA 6:** Configuración del CPT:
   ```php
   'capability_type' => 'post',
   ```
   - Esto significa que usa los mismos permisos que posts normales (requiere rol Editor o superior)

### Resultado Esperado
✅ Todos los endpoints verifican permisos con `current_user_can()`. Se encontraron:
- 8 verificaciones de `edit_posts`
- 2 verificaciones de `manage_options`
- 1 verificación de `upload_files`
- 1 verificación de `edit_post` en meta boxes

---

## PS-06: Exposición de Claves API

### Objetivo
Verificar que las claves API están almacenadas de forma segura en la base de datos y nunca se exponen en el frontend.

### Pasos

1. **Ver código fuente del frontend:**
   - Ve a una página con `[smartnotify_news]`
   - Clic derecho → "Ver código fuente de la página"
   - Buscar (Ctrl+F):
     - `anthropic`
     - `openai`
     - `api_key`
     - `sk-`
   - **CAPTURA 1:** Búsqueda mostrando "0 resultados" para estas palabras

2. **Inspeccionar archivos JavaScript:**
   - F12 → Pestaña "Fuentes/Sources"
   - Abrir `frontend.js`
   - **CAPTURA 2:** Código JavaScript sin ninguna mención de claves API

3. **Inspeccionar peticiones AJAX:**
   - F12 → Pestaña "Red/Network"
   - Filtrar por "XHR"
   - Generar una noticia desde el admin
   - Clic en la petición → Pestaña "Payload"
   - **CAPTURA 3:** Payload mostrando solo `action`, `nonce`, `title`, etc. (sin claves API)

4. **Verificar campos ocultos en configuración:**
   - Ve a `Configuración → SmartNotify AI`
   - **CAPTURA 4:** Campos de API Key con `type="password"` (ocultos con asteriscos)

5. **Ver cómo se almacenan las claves:**
   - Inspeccionar elemento en el campo de API Key
   - **CAPTURA 5:** HTML mostrando:
   ```html
   <input type="password" name="smartnotify_anthropic_api_key" value="***">
   ```

6. **Verificar en el código backend:**
   - Abrir `includes/Services/AI/class-ai-service-factory.php`
   - **CAPTURA 6:** Código mostrando que la clave se obtiene del Config:
   ```php
   private function createService() {
       $api_key = $this->config->get('ai.api_key');
       
       switch ($provider) {
           case 'openai':
               return new OpenAIService($api_key, $this->config);
           case 'anthropic':
               return new AnthropicService($api_key, $this->config);
       }
   }
   ```

7. **Ver dónde se almacenan las claves:**
   - Abrir `includes/Core/class-config.php`
   - **CAPTURA 7:** Código mostrando uso de `get_option()` para leer de la base de datos:
   ```php
   public function get($key, $default = null) {
       return get_option($this->getOptionKey($key), $default);
   }
   ```

8. **Verificar variables privadas:**
   - Abrir `includes/Services/AI/class-anthropic-service.php`
   - **CAPTURA 8:** Propiedad privada:
   ```php
   private $api_key;
   ```
   - Esto previene el acceso externo a la clave

### Resultado Esperado
✅ Las claves API:
- Se almacenan en la base de datos usando `update_option()`
- Se recuperan solo en el backend con `get_option()`
- Se pasan a servicios mediante constructores con parámetros privados
- NUNCA se exponen en HTML, JavaScript o respuestas AJAX
- Se muestran ocultas en la UI de configuración

---

## Checklist Final

Después de realizar todas las pruebas, verifica:

- [ ] **PS-01:** Query Monitor muestra solo consultas preparadas
- [ ] **PS-02:** Scripts maliciosos no se ejecutan, se muestran como texto
- [ ] **PS-03:** Peticiones sin nonce son rechazadas
- [ ] **PS-04:** Acceso directo a archivos PHP devuelve página en blanco
- [ ] **PS-05:** Usuarios sin permisos reciben mensaje de error
- [ ] **PS-06:** Claves API no aparecen en el código fuente del frontend

---

## Capturas Recomendadas para el Documento

Para cada prueba, incluye en tu `DESCRIPCION_PROYECTO.md`:

1. **Captura del código:** Muestra la función de seguridad implementada
2. **Captura de la prueba:** Muestra el resultado de intentar vulnerar la seguridad
3. **Captura del resultado:** Muestra el mensaje de error o la protección funcionando

Ejemplo de estructura para el documento:

```markdown
### PS-01: Inyección SQL

**Implementación:**
![Código WP_Query](capturas/ps01-codigo.png)

**Prueba:**
![Query Monitor](capturas/ps01-query-monitor.png)

**Resultado:**
✅ El plugin usa exclusivamente WP_Query con prepared statements.
```

---

## Notas Adicionales

- Todas estas pruebas deben realizarse en un **entorno de desarrollo local**.
- NO realizar estas pruebas en un sitio de producción.
- Guardar todas las capturas en una carpeta `capturas/` para referencia.
- Si encuentras alguna vulnerabilidad, documéntala y corrígela antes de continuar.

---

## Conclusión

Este plugin implementa múltiples capas de seguridad siguiendo las mejores prácticas de WordPress:

1. ✅ **SQL Injection:** Uso de WP_Query (prepared statements)
2. ✅ **XSS:** Escape de salidas con `esc_html()`, `esc_attr()`, `esc_url()`
3. ✅ **CSRF:** Verificación de nonces en todos los endpoints AJAX
4. ✅ **Direct File Access:** Protección `ABSPATH` en todos los archivos
5. ✅ **Privilege Escalation:** Verificación de permisos con `current_user_can()`
6. ✅ **API Key Exposure:** Almacenamiento seguro en BD, nunca expuestas en frontend

---

**Fecha de elaboración:** 2025
**Plugin:** SmartNotify AI v1.0
**Framework:** WordPress 6.0+
