#!/usr/bin/env bash
# Aplica las modificaciones de Labdoo sobre una copia de RT-Theme 17 (v2.9.9).
#
# Uso:  ./apply-patches.sh /ruta/a/wp-content/themes/rttheme17
#
# El tema comercial NO está en este repositorio (ver ../../docs/rt-theme.md).
# Los parches están hechos contra el tema tal como estaba instalado en marzo de
# 2021. Si alguno no aplica sobre una copia recién descargada de ThemeForest,
# el script lo dice y se termina a mano con los ficheros de snippets/.
set -uo pipefail

T="${1:?Indica la carpeta del tema: ./apply-patches.sh /var/www/.../themes/rttheme17}"
HERE="$(cd "$(dirname "$0")" && pwd)"
[ -f "$T/style.css" ] && grep -q "RT-Theme 17" "$T/style.css" || { echo "No parece RT-Theme 17: $T"; exit 1; }

fail=0
for p in "$HERE"/patches/*.patch; do
  if patch -d "$T" -p1 --forward --dry-run --silent < "$p" >/dev/null 2>&1; then
    patch -d "$T" -p1 --forward --silent < "$p" && echo "OK      $(basename "$p")"
  else
    echo "NO APLICA  $(basename "$p")  -> revisar a mano (ver docs/rt-theme.md)"; fail=1
  fi
done

cp -v "$HERE"/images/assets/social_media/*.png "$T/images/assets/social_media/"

# Datos que sube la plataforma Drupal (ver docs/integracion-drupal.md)
for f in data.json tags_data.json; do
  if [ -e "/home/wpsyncjson/$f" ]; then ln -sfn "/home/wpsyncjson/$f" "$T/$f" && echo "enlace  $f"
  else echo "AVISO   /home/wpsyncjson/$f no existe todavía: crea el usuario wpsyncjson y vuelve a lanzar el script"; fi
done

[ $fail = 0 ] && echo "Tema listo." || { echo "Hay parches pendientes."; exit 2; }
