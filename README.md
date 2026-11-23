# labdoo-wordpress

Código propio, configuración y base de datos anonimizada de **www.labdoo.org**, el
WordPress público de Labdoo. Con este repositorio, una copia de RT-Theme 17 y los
medios (`wp-content/uploads`) se puede reconstruir el sitio desde cero.

Estado tomado del servidor de producción el **17-11-2026** (WordPress 7.1.x
multisite, PHP 8.3, MySQL 8.0, Apache 2.4, Ubuntu 24.04).

## Qué hay aquí

| Carpeta | Contenido |
|---|---|
| `docs/` | Arquitectura, integración con Drupal, instalación del tema y reconstrucción paso a paso |
| `theme/rttheme17/` | **Solo lo que Labdoo ha cambiado** del tema comercial: parches, fragmentos de código e iconos propios. El tema en sí no está (es de pago). |
| `mu-plugins/` | Plugins «must-use» propios (`labdoo-security.php`, `labdoo-chatbot-mobile.php`, `labdoo-seo-fixes.php`, `labdoo-region-redirect.php`) |
| `webroot/` | `.htaccess` (redirecciones a la plataforma, multisite, bloqueo de bots), `hub-lookup.php`, `robots.txt` |
| `config/` | `wp-config.sample.php` (constantes de multisite, secretos como `CHANGE_ME`) y configuración de WP Super Cache |
| `plugins/` | Versiones en producción e `install-plugins.sh` (WP-CLI) con la activación por sitio |
| `db/` | Volcado anonimizado de la base de datos `lbdwpdb` (ver `db/README.md`) |
| `server/` | Vhost de Apache, módulos activos, PHP-FPM/OPcache, fail2ban, cron |
| `monitoring/labdoo-tests/` | Tests de salud que corren en el servidor y avisan por correo |
| `scripts/backup.sh` | Copia de seguridad diaria recomendada |

## Qué NO hay (a propósito)

- **RT-Theme 17**: tema comercial de ThemeForest. Cómo conseguirlo e instalarlo: [docs/rt-theme.md](docs/rt-theme.md).
- **`wp-content/uploads/`** (~240 MB de fotos y documentos). Sale del backup del servidor.
- **Código de los plugins**: se instalan desde wordpress.org (`plugins/install-plugins.sh`).
- **Secretos**: contraseñas, claves y sales de `wp-config.php`, certificados TLS, claves SSH,
  claves de API, la contraseña de aplicación del correo de los tests, el host y los IDs de los
  webhooks de n8n del chatbot. Donde hacían falta aparecen como `CHANGE_ME`,
  `N8N_HOST.example`, `N8N_WEBHOOK_ID_DEU` o direcciones `@example.org`.
- **Datos personales**: usuarios, emails, IPs, logs y conversaciones de IA (ver `db/README.md`).

## Reconstruir el sitio

Resumen; el detalle está en [docs/reconstruccion.md](docs/reconstruccion.md).

1. Servidor Ubuntu 24.04 con Apache, PHP 8.3-FPM y MySQL 8 (`server/`).
2. WordPress core + `config/wp-config.sample.php` → `wp-config.php` con secretos nuevos.
3. Base de datos: `db/lbdwpdb-anon.sql.gz` (o un backup real si lo hay).
4. Tema: RT-Theme 17 v2.9.9 + `theme/rttheme17/apply-patches.sh`.
5. Plugins: `plugins/install-plugins.sh`; mu-plugins y `webroot/`.
6. Medios: restaurar `wp-content/uploads/` desde el backup.
7. Puente con Drupal: usuario `wpsyncjson` y su clave ([docs/integracion-drupal.md](docs/integracion-drupal.md)).
8. Comprobaciones y cambio de DNS.

## Documentación

- [docs/arquitectura.md](docs/arquitectura.md): cómo está montado el sitio, sus tres subsitios, idiomas y plugins.
- [docs/integracion-drupal.md](docs/integracion-drupal.md): `data.json`, `tags_data.json`, enlaces y redirecciones con platform.labdoo.org.
- [docs/rt-theme.md](docs/rt-theme.md): instalación del tema comercial y de los cambios de Labdoo.
- [docs/reconstruccion.md](docs/reconstruccion.md): procedimiento completo desde cero.
- [db/README.md](db/README.md): qué contiene y qué se ha quitado del volcado.
