#!/bin/bash
# Copia de seguridad diaria de www.labdoo.org (base de datos + ficheros + config).
# Instalar en /usr/local/sbin/labdoo-wp-backup.sh y lanzar desde /etc/cron.d como root:
#   30 3 * * * root /usr/local/sbin/labdoo-wp-backup.sh >/var/log/labdoo-wp-backup.log 2>&1
# Credenciales de MySQL en /root/.my.cnf ([client] user=... password=...), chmod 600.
set -euo pipefail
D=/var/backups/labdoo-wp; TS=$(date +%F); KEEP_DAYS=7
mkdir -p "$D"; chmod 700 "$D"

mysqldump --single-transaction --routines --no-tablespaces \
  --default-character-set=utf8mb4 lbdwpdb | gzip > "$D/db_$TS.sql.gz"

tar -czf "$D/files_$TS.tar.gz" \
  --exclude=wp-content/cache --exclude=wp-content/duplicator-backups \
  /var/www/www.labdoo.org /home/wpsyncjson /etc/apache2 \
  /etc/ssl/certs/labdoo.org.crt /etc/ssl/certs/labdoo-intermediate-chain.crt \
  /etc/ssl/private/labdoo-cert.key /etc/php/8.3/fpm /etc/fail2ban /etc/cron.d \
  /etc/ssh/ssh_host_* /opt/labdoo-tests /var/spool/cron/crontabs

find "$D" -type f -mtime +$KEEP_DAYS -delete
# Copia fuera del servidor (elegir una):
# aws s3 sync "$D/" s3://<bucket>/labdoo-wp/
# rsync -a "$D/" backup@<nas>:/volume1/backups/labdoo-wp/
