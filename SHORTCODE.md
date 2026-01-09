# SmartNotify AI - Documentación del Shortcode

## Shortcode: `[smartnotify_news]`

Este shortcode te permite mostrar las noticias de SmartNotify AI en cualquier página o entrada de WordPress con un diseño moderno de cards.

## Uso Básico

```
[smartnotify_news]
```

Esto mostrará las últimas 6 noticias en un grid de 3 columnas.

## Parámetros Disponibles

### `limit`
Número de noticias a mostrar.
- **Por defecto:** 6
- **Ejemplo:** `[smartnotify_news limit="9"]`

### `columns`
Número de columnas en el grid.
- **Por defecto:** 3
- **Valores:** 1, 2, 3, 4
- **Ejemplo:** `[smartnotify_news columns="4"]`

### `category`
Filtrar por categorías (slugs separados por comas).
- **Por defecto:** Todas las categorías
- **Ejemplo:** `[smartnotify_news category="tecnologia,deportes"]`

### `sentiment`
Filtrar por sentimiento.
- **Por defecto:** Todos los sentimientos
- **Valores:** positive, neutral, negative
- **Ejemplo:** `[smartnotify_news sentiment="positive"]`

### `order`
Orden de las noticias.
- **Por defecto:** DESC
- **Valores:** DESC, ASC
- **Ejemplo:** `[smartnotify_news order="ASC"]`

### `orderby`
Ordenar por campo.
- **Por defecto:** date
- **Valores:** date, title, modified, rand
- **Ejemplo:** `[smartnotify_news orderby="title"]`

## Ejemplos de Uso

### Mostrar últimas 12 noticias en 4 columnas
```
[smartnotify_news limit="12" columns="4"]
```

### Mostrar solo noticias positivas de tecnología
```
[smartnotify_news category="tecnologia" sentiment="positive" limit="6"]
```

### Mostrar 3 noticias aleatorias en una columna
```
[smartnotify_news limit="3" columns="1" orderby="rand"]
```

### Mostrar noticias en orden alfabético
```
[smartnotify_news orderby="title" order="ASC"]
```

### Layout de 2 columnas para móviles
```
[smartnotify_news columns="2" limit="8"]
```

## Características del Diseño

- **Responsive:** Se adapta automáticamente a móviles, tablets y escritorio
- **Animaciones:** Efectos suaves al hacer hover y al cargar
- **Badges de sentimiento:** Colores diferentes según el sentimiento (positivo, neutral, negativo)
- **Categorías:** Muestra las categorías de cada noticia con estilo
- **Imágenes:** Muestra la imagen destacada con efecto zoom al hover
- **Dark mode:** Soporte automático para modo oscuro
- **Accesibilidad:** Diseñado con ARIA labels y navegación por teclado

## Cómo Usar en WordPress

### En una Página o Entrada
1. Ve a **Páginas > Agregar nueva** o edita una página existente
2. En el editor de bloques, agrega un bloque de **Shortcode**
3. Pega el shortcode: `[smartnotify_news]`
4. Personaliza con los parámetros que necesites
5. Publica o actualiza

### En un Widget
1. Ve a **Apariencia > Widgets**
2. Agrega un widget de **Shortcode** o **HTML personalizado**
3. Pega el shortcode con tus parámetros
4. Guarda

### En un Template PHP
Si estás editando directamente los archivos del tema:

```php
<?php echo do_shortcode('[smartnotify_news limit="6" columns="3"]'); ?>
```

## Personalización de Estilos

Los estilos del shortcode están en `assets/css/frontend.css`. Puedes personalizar:

- Colores de las cards
- Tamaño de las imágenes
- Espaciado del grid
- Colores de sentimiento
- Animaciones
- Y mucho más

## Notas

- Las noticias deben estar publicadas para aparecer en el frontend
- El shortcode solo muestra noticias del post type `smartnotify_news`
- Las imágenes se cargan con lazy loading para mejor rendimiento
- El diseño es completamente responsive y se adapta automáticamente
