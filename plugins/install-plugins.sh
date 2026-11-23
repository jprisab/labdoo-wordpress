#!/usr/bin/env bash
# Instala los plugins de www.labdoo.org desde wordpress.org con WP-CLI y los
# activa igual que en producción (red y sitio por sitio).
#
# Uso:  sudo -u www-data ./install-plugins.sh /var/www/www.labdoo.org
#
# Las versiones exactas en producción el 2026-11-17 están en versions.txt.
# Por defecto instala la última versión; para clavar una: VERSION_PIN=1 ./install-plugins.sh ...
set -euo pipefail
WP="wp --path=${1:?ruta de WordPress}"
HERE="$(cd "$(dirname "$0")" && pwd)"

ver() { grep "^$1|" "$HERE/versions.txt" | cut -d'|' -f2; }
inst() { if [ "${VERSION_PIN:-0}" = 1 ]; then $WP plugin install "$1" --version="$(ver "$1")" --force; else $WP plugin install "$1" --force; fi; }

# Activos en toda la red
NETWORK="polylang json-content-importer wp-super-cache autoptimize ml-slider megamenu
google-calendar-events seo-by-rank-math wpforms-lite happy-elementor-addons
redux-framework loco-translate ns-cloner-site-copier widget-importer-exporter"
# Activos solo en algunos sitios
SITE_6_9="elementor permalink-manager rife-elementor-extensions wordpress-importer"
# Instalado e inactivo a propósito: mu-plugins/labdoo-region-redirect.php usa su base de países.
KEEP_INACTIVE="country-redirect"
# Instalados pero inactivos en producción (no hace falta reinstalarlos):
#   ai-engine apollo13-framework-extensions duplicate-page duplicator
#   php-compatibility-checker wp-maintenance-mode

for p in $NETWORK $SITE_6_9 $KEEP_INACTIVE; do inst "$p"; done
for p in $NETWORK; do $WP plugin activate "$p" --network; done
for url in https://www.labdoo.org/deu/ https://www.labdoo.org/global/; do
  for p in $SITE_6_9; do $WP plugin activate "$p" --url="$url"; done
done
echo "Plugins instalados. Duplicator estaba activo en /deu/ y /global/; no se reactiva a propósito."
