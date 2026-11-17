# Arquitectura de www.labdoo.org

## Stack

| Pieza | Producción (17-11-2026) | Configuración en este repo |
|---|---|---|
| Sistema | Ubuntu 24.04 LTS en AWS EC2 (1 vCPU, 1 GB RAM + 2 GB swap) | — |
| Web | Apache 2.4 (MPM event, HTTP/2) | `server/apache/` |
| PHP | 8.3 con php-fpm; OPcache con `validate_timestamps=0` | `server/php/` |
| Base de datos | MySQL 8.0 local, base `lbdwpdb`, prefijo `wp_` | `db/` |
| CMS | WordPress 7.1 multisite por subdirectorios | `config/wp-config.sample.php` |
| TLS | Certificado propio en Apache; DNS en Cloudflare sin proxy (nube gris) | — |
| Seguridad | fail2ban, bloqueos en vhost y `.htaccess`, mu-plugin `labdoo-security` | `server/fail2ban/`, `webroot/.htaccess`, `mu-plugins/` |

Con `validate_timestamps=0`, **después de editar cualquier PHP hay que hacer
`sudo systemctl reload php8.3-fpm`** o el cambio no se ve.

## Los tres sitios

| ID | URL | Idiomas (Polylang) | Para qué |
|---|---|---|---|
| 1 | `/` | en, de, es, ca, fr, it | Sitio internacional. `/` → `/en/` |
| 6 | `/deu/` | de, en | Labdoo.org e.V. (Alemania): Impressum, donantes, socios, premios, FAQ |
| 9 | `/global/` | en, de, es, ca | Copia casi idéntica del sitio 1, de la que se clonó en 2021 |

Cada sitio tiene sus tablas (`wp_`, `wp_6_`, `wp_9_`) y sus medios
(`uploads/`, `uploads/sites/6/`, `uploads/sites/9/`). Los tres usan RT-Theme 17 y la
estructura de enlaces `/blog/%year%/%monthnum%/%day%/%postname%/`, aunque casi todo
son páginas.

### Páginas

Por idioma se repite: **Home, Proyectos, Sobre nosotros, Colaborar, Eventos**. Las páginas de
cada idioma son páginas distintas enlazadas como traducciones por Polylang.

- **Home**: presentación, vídeo de YouTube, carrusel MetaSlider, columna «Latest activities»
  (de `tags_data.json`).
- **Proyectos**: galería de escuelas, cada una enlazada a su ficha en la plataforma.
- **Colaborar**: formas de participar, con enlaces a los formularios de la plataforma.
- **Eventos**: calendario de Google (Simple Calendar).
- Además: términos y protección de datos, Creative Commons (sitios 1 y 9); en `/deu/`,
  Labdoo.org e.V., Projektpartner, Spender, Auszeichnungen, Impressum, FAQ, Labdoo in
  Deutschland, cada una en alemán e inglés.

Casi todo está hecho con el editor de bloques; solo la Home inglesa de `/deu/` usa Elementor.
Hay un menú principal por idioma asignado por Polylang a `rt-theme-main-navigation`.

## Idiomas y redirecciones

1. **Polylang** decide el idioma por la URL, con detección del navegador.
2. **Country Redirect** (en toda la red) tiene una sola regla: visitantes con IP de Alemania →
   `/deu`. No se aplica a páginas servidas desde la caché.
3. **`.htaccess`**: redirecciones a la plataforma (ver `integracion-drupal.md`), cabeceras
   `Expires`, reglas estándar de multisite y bloqueo de bots por User-Agent.

## Plugins

Ver `plugins/versions.txt` e `install-plugins.sh`. Los importantes:

- **Polylang**: idiomas.
- **JSON Content Importer**: pinta los JSON de Drupal (integración).
- **WP Super Cache**: caché de página completa en modo PHP, caducidad 300 s, más un cron
  (`server/cron/labdoo-supercache-expire`) que borra cada minuto lo que tenga más de 5 min.
- **Autoptimize**, **MetaSlider**, **Max Mega Menu**, **Simple Calendar**, **Rank Math SEO**,
  **Country Redirect**.
- En `/deu/` y `/global/`: **Elementor** y complementos, **Permalink Manager**.

## Código propio fuera del tema

- `mu-plugins/labdoo-security.php`: oculta `/wp-json/wp/v2/users` a visitantes anónimos.
- `mu-plugins/labdoo-chatbot-mobile.php`: CSS para la ventana del chatbot de AI Engine en
  móvil (AI Engine está ahora inactivo).
- `mu-plugins/labdoo-seo-fixes.php`: corrige la URL canónica de las portadas de idioma, que
  Rank Math 1.0.282 rompe en su integración con Polylang (`/es/` declaraba `/es/es/`).
- `webroot/hub-lookup.php`: buscador de hubs por ciudad para el chatbot.

## Chatbot

El widget (en `functions.php` del tema) abre un iframe a `/labdoo-agent/<id>/chat`. Apache
reenvía esa ruta a un n8n externo (`N8N_HOST.example` en `server/apache/www.labdoo.org.conf`;
el host real no está en el repositorio) y reescribe las URLs del HTML con `mod_substitute`.
Hay dos chats: uno internacional (`labdoo-chat`) y otro alemán (`N8N_WEBHOOK_ID_DEU`). Si n8n no
responde, solo falla el chat. Las conversaciones se guardan en n8n, no en WordPress.

## Monitorización

`monitoring/labdoo-tests/` corre en el servidor desde el crontab de `ubuntu` (ver
`server/cron/crontab-ubuntu.txt`): tiempos de respuesta, certificado, chatbot y «Latest
activities». Avisa por correo con msmtp; la contraseña va en `/opt/labdoo-tests/.gmail_app_pw`,
que no está en el repositorio.
