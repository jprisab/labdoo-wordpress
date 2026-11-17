# Integración con la plataforma Drupal

www.labdoo.org (WordPress) es el escaparate. Todo lo operativo (portátiles, escuelas o
«edoovillages», hubs, viajes) está en **platform.labdoo.org**, un Drupal 7 en otro servidor.
No comparten base de datos, usuarios ni API. La unión es de un solo sentido
(Drupal → WordPress) y por ficheros:

```
platform.labdoo.org                               www.labdoo.org
──────────────────                                ───────────────
cron (cada 5 min)
  drush → labdoo_macro_stats_to_json()
  generate_upload_json_to_wp.sh
     data.json, tags_data.json  ── scp ──▶  wpsyncjson@www.labdoo.org:/home/wpsyncjson/
                                                   │ (enlaces)
                                            wp-content/themes/rttheme17/{data,tags_data}.json
                                                   │ HTTPS a sí mismo
                                            JSON Content Importer (shortcode)
                                              ├─ header.php: barra «Global Progress»
                                              └─ Home de cada idioma: «Latest activities»
```

Si Drupal se cae, WordPress sigue funcionando con las últimas cifras recibidas.

## data.json: cifras globales

Lo genera `labdoo_macro_stats_to_json()` (módulo `labdoo_lib` de la plataforma). Formato:

```json
{ "_coment": "...",
  "level1": { "dootronics": "67045", "escuelas": "3285", "estudiantes": "1078776",
              "hubs": "659", "paises": "156", "co2saved": "1070376" } }
```

`header.php` elige por `get_locale()` (es, en, de, ca, fr, it) una plantilla con las etiquetas
traducidas y llama a:

```
[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json" basenode="level1"]
  ... {dootronics} ... {escuelas} ... {estudiantes} ... {hubs} ... {paises} ... {co2saved} ...
[/jsoncontentimporter]
```

Cada cifra enlaza a `platform.labdoo.org/content/{dootronics,edoovillages,hubs}-dashboard`.

## tags_data.json: últimas actividades

Mismo mecanismo. Lleva los últimos eventos de portátiles:

```json
{ "level1": { "start": "Last taged Computers", "level2": [
  {"city":"…","country":"…","time":"10 hours 31 min ago",
   "msg":"Laptop 000066723 was delivered to an edoovillage",
   "url":"https://platform.labdoo.org/node/…",
   "image":"/wp-content/uploads/events_icons/laptop-delivered.png.jpg"} ] } }
```

Se muestra en la Home de cada idioma con un bloque HTML (también existe como bloque
reutilizable «json»):

```
[jsoncontentimporter url=".../rttheme17/tags_data.json" basenode="level1"] {subloop-array:level2:-1}
  <img src="{level2.image}"> {level2.time} {level2.city}, {level2.country}
  <a href="{level2.url}">{level2.msg}</a>
{/subloop-array:level2}[/jsoncontentimporter]
```

Los iconos (`uploads/events_icons/`) son de WordPress: si faltan, salen imágenes rotas.

## Montar el puente en un servidor nuevo

```bash
sudo adduser --disabled-password --gecos "" wpsyncjson
sudo install -d -m 700 -o wpsyncjson -g wpsyncjson /home/wpsyncjson/.ssh
# clave pública del servidor Drupal (la del usuario que ejecuta el cron dentro del chroot):
sudo tee /home/wpsyncjson/.ssh/authorized_keys <<< 'ssh-rsa AAAA... '
sudo chown wpsyncjson: /home/wpsyncjson/.ssh/authorized_keys && sudo chmod 600 $_
sudo chmod 755 /home/wpsyncjson
sudo ./theme/rttheme17/apply-patches.sh /var/www/www.labdoo.org/wp-content/themes/rttheme17   # crea los enlaces
```

Puntos que fallan sin avisar:

- **Usa enlaces simbólicos.** En producción eran enlaces duros, que solo sobreviven mientras
  Drupal sobrescriba con `scp`. Si se cambia a `rsync` o se restaura el tema desde una copia,
  el tema se queda con un fichero congelado.
- **Claves de host SSH.** Si el servidor nuevo tiene otras, el `scp` de Drupal se niega a
  conectar («REMOTE HOST IDENTIFICATION HAS CHANGED»). O se restauran `/etc/ssh/ssh_host_*`
  del backup o se borra la entrada antigua del `known_hosts` en el lado Drupal.
- **El plugin lee por HTTPS, no del disco.** El servidor tiene que poder conectarse a
  `https://www.labdoo.org` (a sí mismo). Una copia de pruebas con otro dominio seguirá leyendo
  el JSON de producción, porque la URL está escrita a mano en `header.php` y en las Home.
- **Un idioma nuevo** necesita su bloque en `header.php`; si no, la barra no aparece.

Comprobación: `ls -l --time-style=+%T /home/wpsyncjson/data.json` debe cambiar de hora cada
pocos minutos.

## Enlaces y redirecciones

- Las páginas tienen cientos de enlaces fijos a la plataforma: fichas de escuelas
  (`/edoovillage?e=<nid>`), formularios de contacto por idioma (`/<lang>/content/contact-labdoo`;
  los formularios de WordPress no se usan), dashboards, hubs (`/hub?h=<nid>`), `/user/register`,
  `/actions`, guías para donantes en alemán.
- `webroot/.htaccess` redirige con 301 a la plataforma cualquier URL antigua `/content/…` o
  `/<lang>/content/…` (de cuando www.labdoo.org era el Drupal), además de algunos hubs y
  `/de/LabdooD`.
- No hay inicio de sesión común: las cuentas de WordPress y de la plataforma son distintas.

## Listado de hubs para el chatbot

`webroot/hub-lookup.php?city=<ciudad>` busca en `wp-content/uploads/hubs_clean.txt` (líneas
«Ciudad, País → URL del hub»). Ese fichero es una exportación manual de los hubs de la
plataforma: no se actualiza solo. Está en `uploads/`, así que viene con el backup de medios.
