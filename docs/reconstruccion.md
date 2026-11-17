# Reconstruir www.labdoo.org desde cero

Necesitas:

- este repositorio;
- una copia de **RT-Theme 17 v2.9.9** (`docs/rt-theme.md`);
- **`wp-content/uploads/`** del backup del servidor;
- la base de datos: un backup real o, si no, `db/lbdwpdb-anon.sql.gz`;
- el certificado TLS (o Let's Encrypt / Cloudflare Origin CA), la clave pública SSH del
  servidor Drupal y, si se quieren los avisos, una contraseña de aplicación de correo.

> **Atajo**: si hay un backup completo del servidor (`scripts/backup.sh` o un snapshot de AWS),
> restaurarlo es más rápido y ya trae el tema, los plugins y los medios. Esta guía es para
> cuando solo hay piezas.

## 1. Servidor

Ubuntu 24.04. Mejor 2 GB de RAM que 1. Puertos: 22 solo para administradores; 80 y 443.

```bash
sudo apt update && sudo apt install -y apache2 mysql-server fail2ban unzip patch \
  php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml \
  php8.3-zip php8.3-intl php8.3-imagick php8.3-bcmath
sudo a2dismod mpm_prefork php8.3 2>/dev/null; sudo a2enmod mpm_event
sudo a2enmod proxy_fcgi setenvif ssl http2 headers rewrite expires \
  proxy proxy_http substitute filter access_compat
sudo a2enconf php8.3-fpm
# WP-CLI
curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
sudo install -m 755 wp-cli.phar /usr/local/bin/wp
```

`server/apache/mods-enabled.txt` tiene la lista completa de módulos de producción.

## 2. Base de datos

Ver `db/README.md`. Con el volcado anonimizado:

```bash
sudo mysql -e "CREATE DATABASE lbdwpdb DEFAULT CHARACTER SET utf8mb4;
  CREATE USER 'lbdwp'@'localhost' IDENTIFIED BY '<contraseña>';
  GRANT ALL ON lbdwpdb.* TO 'lbdwp'@'localhost';"
gunzip -c db/lbdwpdb-anon.sql.gz | sudo mysql lbdwpdb
sudo mysql lbdwpdb -e "SELECT blog_id, path FROM wp_blogs"    # 1 /, 6 /deu/, 9 /global/
```

## 3. WordPress core y configuración

```bash
W=/var/www/www.labdoo.org
sudo mkdir -p $W && sudo chown www-data: $W
sudo -u www-data wp core download --path=$W --locale=en_US
sudo cp config/wp-config.sample.php $W/wp-config.php
```

En `$W/wp-config.php`:

- `DB_USER` y `DB_PASSWORD`: los del paso 2.
- Las 8 claves y sales: generarlas en https://api.wordpress.org/secret-key/1.1/salt/.
- Borrar la línea `DUPLICATOR_AUTH_KEY` (o generar una).
- No tocar `MULTISITE`, `SUBDOMAIN_INSTALL=false`, `DOMAIN_CURRENT_SITE`, `PATH_CURRENT_SITE`,
  `SITE_ID_CURRENT_SITE`, `BLOG_ID_CURRENT_SITE`, `WP_CACHE`, `WPCACHEHOME`.

```bash
sudo cp webroot/.htaccess webroot/hub-lookup.php webroot/robots.txt $W/
sudo mkdir -p $W/wp-content/mu-plugins && sudo cp mu-plugins/*.php $W/wp-content/mu-plugins/
```

## 4. Medios

```bash
sudo tar -xzf uploads.tar.gz -C $W/wp-content/      # o rsync desde el backup
```

Incluye `uploads/events_icons/` (iconos de «Latest activities») y `uploads/hubs_clean.txt`
(lo usa `hub-lookup.php`).

## 5. Tema

```bash
sudo unzip rttheme17.zip -d $W/wp-content/themes/
sudo ./theme/rttheme17/apply-patches.sh $W/wp-content/themes/rttheme17
```

Si algún parche no aplica, ver `docs/rt-theme.md`.

## 6. Plugins

```bash
sudo chown -R www-data: $W
sudo -u www-data ./plugins/install-plugins.sh $W
sudo cp config/wp-cache-config.php $W/wp-content/
sudo sed -i "s/\$cache_page_secret = 'CHANGE_ME'/\$cache_page_secret = '$(openssl rand -hex 16)'/" $W/wp-content/wp-cache-config.php
sudo cp server/cron/labdoo-supercache-expire /etc/cron.d/
```

WP Super Cache crea `wp-content/advanced-cache.php` al activarse. Si no aparece: wp-admin →
Red → Ajustes → WP Super Cache → «Caching On».

## 7. Apache, TLS, PHP y fail2ban

```bash
sudo cp server/apache/www.labdoo.org.conf /etc/apache2/sites-available/
sudo a2ensite www.labdoo.org && sudo a2dissite 000-default
```

En el vhost:

- Rutas del certificado: `SSLCertificateFile`, `SSLCertificateChainFile`, `SSLCertificateKeyFile`
  (clave con `chmod 600`, propiedad de root).
- Chatbot: sustituir `N8N_HOST.example` por el host real de n8n y `N8N_WEBHOOK_ID_DEU` por el
  ID del webhook alemán. Lo mismo en `wp-content/themes/rttheme17/functions.php`.
- `ServerAdmin`: poner el email real.

```bash
sudo cp server/php/www.conf /etc/php/8.3/fpm/pool.d/
sudo cp server/php/10-opcache.ini /etc/php/8.3/fpm/conf.d/   # revisar antes de copiar
# server/php/php.ini.changes.diff: cambios sobre php.ini-production, aplicarlos a mano
sudo cp server/fail2ban/jail.local /etc/fail2ban/
sudo cp server/fail2ban/jail.d/*.conf /etc/fail2ban/jail.d/
sudo cp server/fail2ban/filter.d/* /etc/fail2ban/filter.d/
sudo apache2ctl configtest && sudo systemctl restart apache2 php8.3-fpm fail2ban
```

`.htaccess` usa `ErrorDocument 403 /403_forbidden_custom_labdoo.html`, que no existe en
producción: crear esa página estática o quitar la línea.

## 8. Puente con Drupal

Ver `docs/integracion-drupal.md`: usuario `wpsyncjson`, su `authorized_keys`, enlaces de
`data.json` y `tags_data.json` (los crea `apply-patches.sh` si ya existe el usuario) y claves de
host SSH.

## 9. Usuarios

Con el volcado anonimizado no hay ninguna contraseña válida:

```bash
sudo -u www-data wp --path=$W user update admin2 --user_email=tu@email --user_pass='<contraseña>' --skip-email
```

## 10. Monitorización (opcional)

```bash
sudo mkdir -p /opt/labdoo-tests && sudo cp monitoring/labdoo-tests/* /opt/labdoo-tests/
```

Poner los emails reales en `config.env` y `msmtprc`, el host de n8n en
`labdoo_health_tests.py`, la contraseña de aplicación en `/opt/labdoo-tests/.gmail_app_pw`
(`chmod 600`), `sudo apt install msmtp`, y la línea de `server/cron/crontab-ubuntu.txt` en el
crontab de `ubuntu`. Probar con `/opt/labdoo-tests/run_tests.sh --test-alert`.

## 11. Probar antes de cambiar el DNS

Multisite solo responde con su dominio, así que hay que forzar la resolución:

```bash
IP=<IP nueva>
for u in / /en/ /deu/de/ /global/en/ /wp-content/themes/rttheme17/data.json \
         /hub-lookup.php?city=Madrid /content/hubs-dashboard /wp-admin/; do
  curl -sk --resolve www.labdoo.org:443:$IP -o /dev/null \
       -w "%{http_code} %{redirect_url}  $u\n" "https://www.labdoo.org$u"; done
```

Esperado: `/` → 302 a `/en/`; `/en/`, `/deu/de/`, `/global/en/` → 200; `/content/…` → 301 a la
plataforma; `/wp-admin/` → 302 al login.

Para que la barra de cifras lea el JSON de la máquina nueva y no el de producción, añadir en su
`/etc/hosts`: `127.0.0.1 www.labdoo.org`.

## 12. Cambiar el DNS

En Cloudflare, apuntar `www` y el dominio raíz a la IP nueva (en gris, sin proxy, como en
producción). Después comprobar:

- `/home/wpsyncjson/data.json` cambia de hora cada pocos minutos;
- la barra «Global Progress» muestra números en `/en/` y `/deu/de/`;
- el chat abre;
- se puede entrar en `/wp-admin/network/`.

Y programar `scripts/backup.sh`.

## Copia de pruebas con otro dominio

El dominio está en `wp_blogs`, `wp_site`, `wp_sitemeta`, en `siteurl` y `home` de cada sitio y
dentro de datos serializados. No usar `sed` sobre el SQL; usar WP-CLI:

```bash
wp search-replace 'https://www.labdoo.org' 'https://staging.example.org' --network --all-tables --precise --skip-columns=guid
wp search-replace 'www.labdoo.org' 'staging.example.org' wp_blogs wp_site --network
```

Y cambiar `DOMAIN_CURRENT_SITE`. Las URLs de `data.json`/`tags_data.json` en `header.php` y en
las Home siguen apuntando a producción salvo que se cambien también.
