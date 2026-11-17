# RT-Theme 17: instalación y cambios de Labdoo

## El tema

Los tres sitios usan **RT-Theme 17**, versión **2.9.9** (carpeta `rttheme17`, sin tema hijo).
Es un tema comercial de ThemeForest (autor: RT-Themes) y su licencia no permite
redistribuirlo, por eso este repositorio solo contiene lo que Labdoo ha modificado.

Cómo conseguir una copia, por orden de preferencia:

1. **El backup del servidor**: `wp-content/themes/rttheme17/` ya incluye los cambios de Labdoo;
   en ese caso no hace falta aplicar nada de este repositorio.
2. **La cuenta de ThemeForest con la que se compró** (Downloads → RT-Theme 17 → *All files* o
   *Installable WordPress file only*). Comprobar que la versión es la 2.9.9:
   `grep Version rttheme17/style.css`.
3. Si ThemeForest ya no ofrece la 2.9.9, una versión posterior de la rama 17 puede valer, pero
   es probable que los parches no apliquen y haya que usar los fragmentos (ver más abajo).

Instalación:

```bash
sudo unzip rttheme17.zip -d /var/www/www.labdoo.org/wp-content/themes/
sudo chown -R www-data:www-data /var/www/www.labdoo.org/wp-content/themes/rttheme17
sudo ./theme/rttheme17/apply-patches.sh /var/www/www.labdoo.org/wp-content/themes/rttheme17
sudo systemctl reload php8.3-fpm      # OPcache no revisa cambios en disco
```

El tema queda activo en los tres sitios porque la base de datos ya lo tiene configurado
(`template` y `stylesheet` = `rttheme17`). Sus opciones (logo, redes sociales, colores) también
están en la base de datos, en las opciones `rttheme_*` de cada sitio.

## Qué cambia Labdoo

Los parches de `patches/` son diferencias (`diff -u`) contra el tema tal como estaba
instalado en marzo de 2021, y se ha comprobado que reproducen exactamente los ficheros de
producción del 17-11-2026.

| Fichero | Cambio | Por qué |
|---|---|---|
| `header.php` | `<title>` por `wp_title()` en todas las páginas, para que Rank Math ponga el título también en las portadas (antes: el nombre del sitio); `preconnect` a Google Fonts; Google Analytics 4 (`G-69FD52Z9VZ`); barra **Global Progress** con las cifras de `data.json` en es/en/de/ca/fr/it; logo fijo con enlace al dashboard de edoovillages según idioma. | Integración con la plataforma Drupal y SEO. Es el cambio principal. La descripción y las keywords fijas se quitaron en 2026-11: duplicaban la descripción de Rank Math en todas las páginas. |
| `functions.php` | Verificación de Mastodon (`rel="me"`) en `/deu/`; **widget del chatbot** (botón fijo e iframe a `/labdoo-agent/.../chat`, un chat distinto para `/deu/`). | Chatbot de n8n. |
| `content_generator.php` | `new stdClass` antes de asignar propiedades. | Compatibilidad con PHP 8 (antes era un error fatal). |
| `page_content.php` | Título de página en `<h1>` en vez de `<h2>`. | SEO. |
| `css/style.css` | Clases `a.numbers` y `a.text-orange` al final. | Estilo de la barra de cifras. |
| `rt-framework/classes/loading.php` | Añade Instagram, Mastodon, WhatsApp y Signal a la lista de redes sociales. | Iconos nuevos. |
| `rt-framework/functions/rt_shortcodes.php` | Texto `alt` en los iconos de redes. | Accesibilidad. |
| `images/assets/social_media/*.png` | Iconos propios (8). | Se copian tal cual. |
| `data.json`, `tags_data.json` | Enlaces a `/home/wpsyncjson/`. | Los sube Drupal; ver `integracion-drupal.md`. |

## Si un parche no aplica

Pasará sobre todo con `header.php`, porque ya estaba modificado en 2021 y una copia limpia
de ThemeForest es distinta. En `snippets/` está el código de Labdoo separado del del tema:

| Fragmento | Dónde va en `header.php` |
|---|---|
| `header-head.php` | Los dos `preconnect` dentro de `<head>`, antes de `<?php wp_head(); ?>`. El bloque de Google Analytics justo después de `<body <?php body_class(); ?>>`. |
| `header-stats.php` | Dentro del bloque de la cabecera donde el tema pinta las banderas de idioma (`<div id="wpml_flags">`), en lugar de `languages_list()`. Es la cadena `if/elseif` por idioma. Para un idioma nuevo hay que copiar uno de los bloques. |
| `header-logo.php` | Donde el tema asigna `$logo_url`, antes del `<!-- logo -->`. |

Para el resto, los parches son tan pequeños que se pueden aplicar leyéndolos.

Después de editar, comprobar en `/en/`, `/deu/de/` y `/global/es/` que aparece la barra
«Global Progress» con números (no `{dootronics}`), el botón del chat abajo a la izquierda y los
iconos de redes sociales.
