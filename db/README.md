# lbdwpdb-anon.sql.gz

Volcado de la base de datos `lbdwpdb` de www.labdoo.org del **17-11-2026**, anonimizado.
~12 MB sin comprimir. Es suficiente para levantar los tres sitios con todo su contenido;
para producción es mejor un backup real si existe.

## Qué incluye

Solo las tablas de WordPress de la red y de los sitios 1, 6 y 9:

- `wp_blogs`, `wp_blogmeta`, `wp_site`, `wp_sitemeta`
- por sitio: `posts`, `postmeta`, `options`, `terms`, `term_taxonomy`,
  `term_relationships`, `termmeta`, `comments`, `commentmeta`, `links`
- `wp_users`, `wp_usermeta` (ficticios, ver abajo); `wp_signups` y `wp_registration_log` vacías

Todo el contenido publicado (páginas, menús, sliders, traducciones de Polylang, opciones del
tema y de los plugins) está tal cual.

## Qué se ha quitado o cambiado

| Qué | Tratamiento |
|---|---|
| Usuarios | Sustituidos por `admin<ID>` / `admin<ID>@example.org`, sin contraseña válida. Se mantienen los IDs (las páginas siguen teniendo autor) y los roles; los 4 siguen siendo superadministradores. |
| Metadatos de usuario | Solo roles, nivel, sitio principal e idioma. Sin tokens de sesión ni datos de perfil. |
| Emails personales | Cambiados por `anon…@example.org` de la misma longitud (para no romper datos serializados). Se mantienen los de rol, como `contact@labdoo.org`. |
| Email de administración | `admin@example.org` en la red y en cada sitio. |
| Opciones con secretos | Fuera todas las que contienen key, token, secret, licen(se), api, pass(word), auth, smtp, account, oauth, jwt…, y las de AI Engine, AIPKit, Freemius, Duplicator y NS Cloner. |
| Claves de Google | Sustituidas por `AIzaXXXX…`. |
| Transients y caché | Fuera. |
| Revisiones, changesets y caché de oEmbed | Fuera (se regeneran). |
| Tablas de plugins | Fuera: logs y colas (Action Scheduler, WPForms, Rank Math 404 con IPs, NS Cloner), restos de plugins retirados (Revolution Slider, Events Manager, WooCommerce, TranslatePress, FileBird), Duplicator, AI Engine y AIPKit. Los plugins activos las vuelven a crear al activarse. |
| Sitios 7 y 8 | Fuera: son tablas huérfanas de subsitios borrados. |

No había comentarios ni conversaciones de IA guardadas.

## Restaurar

```bash
sudo mysql -e "CREATE DATABASE lbdwpdb DEFAULT CHARACTER SET utf8mb4;
  CREATE USER 'lbdwp'@'localhost' IDENTIFIED BY '<contraseña>';
  GRANT ALL ON lbdwpdb.* TO 'lbdwp'@'localhost';"
gunzip -c db/lbdwpdb-anon.sql.gz | sudo mysql lbdwpdb
```

Después, para poder entrar en wp-admin, dar a un usuario un email y una contraseña reales
(WP-CLI, desde la raíz de WordPress):

```bash
wp user update admin2 --user_email=tu@email --user_pass='<contraseña>' --skip-email
```

O crear uno nuevo y hacerlo superadministrador:

```bash
wp user create tunombre tu@email --role=administrator --user_pass='<contraseña>'
wp super-admin add tunombre
```

Si el dominio no es `www.labdoo.org` (copia de pruebas), hay que cambiarlo en toda la red; ver
`docs/reconstruccion.md`.
