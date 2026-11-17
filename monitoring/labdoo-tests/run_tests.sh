#!/bin/bash
# Labdoo health tests - wrapper para cron. Avisa por correo (Gmail via msmtp) solo al cambiar el estado
# (OK->WARN/FAIL, cambio de gravedad, recuperacion), recuerda cada 12 h mientras siga mal
# y envia un resumen diario a las 08:00 (hora de Munich), que sirve de "latido":
# si un dia no llega, algo va mal con el servidor o el cron.
cd /opt/labdoo-tests || exit 1
. ./config.env
exec 9>/tmp/labdoo-tests.lock; flock -n 9 || exit 0

notify() {  # titulo prioridad etiquetas cuerpo -> correo via msmtp (Gmail)
  local PRIO=""; [ "${2:-3}" -ge 4 ] && PRIO=$'X-Priority: 1\nImportance: high\n'
  printf 'From: Labdoo tests <%s>\nTo: %s\nSubject: %s\nMIME-Version: 1.0\n%sContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\n\n%s\n\n-- \nEnviado por /opt/labdoo-tests/run_tests.sh en %s (%s UTC)\n' \
    "$MAIL_FROM" "$MAIL_TO" "$1" "$PRIO" "$4" "$(hostname)" "$(date -u '+%F %H:%M')" \
    | msmtp -C /opt/labdoo-tests/msmtprc -t > /dev/null 2> state/last_notify_err
  echo $? > state/last_notify_rc
}

if [ "$1" = "--test-alert" ]; then
  notify "Labdoo: prueba de alerta" 3 "test_tube" "Si lees esto, las alertas de los tests de Labdoo te llegan por correo."
  exit 0
fi

OUT=$(python3 -B labdoo_health_tests.py 2>&1); RC=$?
echo "$OUT" > results/last.txt
find results -name 'labdoo_tests_*.json' -mtime +14 -delete 2>/dev/null

PREV=$(cat state/rc 2>/dev/null || echo 0); echo $RC > state/rc
BAD=$(echo "$OUT" | grep -E '^\[(FAIL|WARN)')
SUMMARY=$(echo "$OUT" | grep '^Resumen')

if [ $RC -ne 0 ]; then
  STALE=0; [ -f state/alerted ] && [ -z "$(find state/alerted -mmin -720)" ] && STALE=1
  if [ "$PREV" != "$RC" ] || [ ! -f state/alerted ] || [ $STALE -eq 1 ]; then
    if [ $RC -eq 1 ]; then notify "Labdoo: FALLO en los tests" 4 "rotating_light" "$BAD"$'\n'"$SUMMARY"
    else notify "Labdoo: aviso en los tests" 3 "warning" "$BAD"$'\n'"$SUMMARY"; fi
    touch state/alerted
  fi
else
  if [ "$PREV" != "0" ]; then
    notify "Labdoo: recuperado" 3 "white_check_mark" "Todos los tests vuelven a pasar."$'\n'"$SUMMARY"
  fi
  rm -f state/alerted
fi

TODAY=$(TZ=Europe/Berlin date +%F); HOUR=$(TZ=Europe/Berlin date +%H)
if [ "$HOUR" = "08" ] && [ "$(cat state/daily 2>/dev/null)" != "$TODAY" ]; then
  echo "$TODAY" > state/daily
  FAST=$(echo "$OUT" | grep -E 'response_time' | sed -E 's/.*(mediana [0-9.]+s).*/\1/' | tr '\n' ' ')
  notify "Labdoo: resumen diario" 1 "bar_chart" "$SUMMARY"$'\n'"Tiempos: $FAST"
fi
exit 0
