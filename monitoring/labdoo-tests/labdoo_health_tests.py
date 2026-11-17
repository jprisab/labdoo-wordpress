#!/usr/bin/env python3
"""Labdoo health tests - comprobaciones externas de solo lectura.

Tests:
  1. response_time  - tiempo de respuesta de las paginas principales (mediana de 3 muestras)
  2. tls_cert       - dias que le quedan al certificado
  3. chatbot        - el Labdoo AI Agent (flujo n8n) responde con sentido
  4. last_activities- el bloque "Last activities" tiene actividades de las ultimas 48 h

Uso:
  python3 labdoo_health_tests.py [--chatbot-via-ssh RUTA_CLAVE.pem] [--out DIR] [--only t1,t2]

Codigo de salida: 0 = todo OK, 1 = algun FAIL, 2 = solo WARN.
Solo usa la libreria estandar de Python 3.8+.
"""
import argparse, datetime as dt, html, json, os, re, shlex, socket, ssl, statistics
import subprocess, sys, time, urllib.request, urllib.error

HOST = "www.labdoo.org"
PAGES = {
    "root":      "https://www.labdoo.org/",
    "deu-en":    "https://www.labdoo.org/deu/en/",
    "deu-de":    "https://www.labdoo.org/deu/de/",
    "global-en": "https://www.labdoo.org/global/en/",
}
CHATBOT_URL = os.environ.get("LABDOO_CHATBOT_URL", "https://N8N_HOST.example:5678/webhook/N8N_WEBHOOK_ID_DEU/chat")
CHATBOT_QUESTION = "Hola, ¿qué es Labdoo?"
CHATBOT_EXPECT = re.compile(r"labdoo", re.I)

# umbrales (segundos / dias / minutos)
RT_WARN, RT_FAIL = 2.5, 5.0
BOT_WARN, BOT_FAIL, BOT_TIMEOUT = 20.0, 45.0, 60
CERT_WARN, CERT_FAIL = 21, 7
ACT_MAX_AGE_MIN = 48 * 60
ACT_MIN_ENTRIES = 4
ACT_SITE_SKEW_MIN = 30
UA = "Mozilla/5.0 (labdoo-health-tests)"

results = []
def rec(test, name, status, detail, **extra):
    results.append({"test": test, "name": name, "status": status, "detail": detail, **extra})
    print("[%-4s] %-16s %-12s %s" % (status, test, name, detail), flush=True)

def fetch(url, timeout=30):
    t0 = time.perf_counter()
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Encoding": "identity"})
    with urllib.request.urlopen(req, timeout=timeout) as r:
        ttfb = time.perf_counter() - t0
        body = r.read()
        total = time.perf_counter() - t0
        return r.status, r.geturl(), ttfb, total, body.decode("utf-8", "replace")

# ---------------------------------------------------------------- 1 tiempos
def test_response_time():
    for name, url in PAGES.items():
        totals, ttfbs, status, size, err = [], [], None, 0, None
        for _ in range(3):
            try:
                status, _, ttfb, total, body = fetch(url)
                totals.append(total); ttfbs.append(ttfb); size = len(body)
            except Exception as e:
                err = str(e); break
            time.sleep(1)
        if err or not totals:
            rec("response_time", name, "FAIL", "error: %s" % err); continue
        med = statistics.median(totals)
        detail = "HTTP %s, mediana %.2fs (ttfb %.2fs, max %.2fs), %d KB" % (
            status, med, statistics.median(ttfbs), max(totals), size // 1024)
        if status != 200 or size < 50_000:
            rec("response_time", name, "FAIL", detail + " - respuesta anomala", median_s=med)
        elif med > RT_FAIL:
            rec("response_time", name, "FAIL", detail + " - lento (>%.0fs)" % RT_FAIL, median_s=med)
        elif med > RT_WARN:
            rec("response_time", name, "WARN", detail + " - algo lento (>%.1fs)" % RT_WARN, median_s=med)
        else:
            rec("response_time", name, "OK", detail, median_s=med)

# ---------------------------------------------------------------- 2 certificado
def test_tls_cert():
    try:
        ctx = ssl.create_default_context()
        with socket.create_connection((HOST, 443), timeout=15) as s:
            with ctx.wrap_socket(s, server_hostname=HOST) as ss:
                cert = ss.getpeercert()
        exp = dt.datetime.strptime(cert["notAfter"], "%b %d %H:%M:%S %Y %Z")
        days = (exp - dt.datetime.now(dt.timezone.utc).replace(tzinfo=None)).days
        detail = "caduca %s (%d dias)" % (exp.date(), days)
        st = "FAIL" if days < CERT_FAIL else "WARN" if days < CERT_WARN else "OK"
        rec("tls_cert", HOST, st, detail, days_left=days)
    except Exception as e:
        rec("tls_cert", HOST, "FAIL", "no se pudo validar el certificado: %s" % e)

# ---------------------------------------------------------------- 3 chatbot
def _bot_request(via_key):
    payload = json.dumps({"action": "sendMessage",
                          "sessionId": "healthcheck-%d" % int(time.time()),
                          "chatInput": CHATBOT_QUESTION}, ensure_ascii=False)
    t0 = time.perf_counter()
    if via_key:   # se ejecuta curl en el servidor de AWS: punto de vista externo a la red de casa
        remote = ("curl -s -m %d -w '\\n%%{http_code}' -H 'Content-Type: application/json' -d %s %s"
                  % (BOT_TIMEOUT, shlex.quote(payload), shlex.quote(CHATBOT_URL)))
        p = subprocess.run(["ssh", "-i", via_key, "-o", "StrictHostKeyChecking=accept-new",
                            "-o", "ConnectTimeout=20", "ubuntu@" + HOST, remote],
                           capture_output=True, text=True, timeout=BOT_TIMEOUT + 40)
        out = p.stdout.rstrip("\n")
        body, _, code = out.rpartition("\n")
        if p.returncode != 0 and not code.isdigit():
            raise RuntimeError("ssh/curl fallo: %s" % (p.stderr.strip()[:200] or p.returncode))
        return int(code), body, time.perf_counter() - t0
    req = urllib.request.Request(CHATBOT_URL, data=payload.encode(), method="POST",
                                 headers={"Content-Type": "application/json", "User-Agent": UA})
    try:
        with urllib.request.urlopen(req, timeout=BOT_TIMEOUT) as r:
            return r.status, r.read().decode("utf-8", "replace"), time.perf_counter() - t0
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace"), time.perf_counter() - t0

def test_chatbot(via_key):
    via = "via AWS" if via_key else "directo"
    try:
        code, body, secs = _bot_request(via_key)
    except Exception as e:
        rec("chatbot", "respuesta", "FAIL", "sin respuesta (%s): %s" % (via, e)); return
    if code != 200:
        rec("chatbot", "respuesta", "FAIL", "HTTP %s en %.1fs (%s)" % (code, secs, via), http=code); return
    try:
        out = (json.loads(body).get("output") or "").strip()
    except Exception:
        rec("chatbot", "respuesta", "FAIL", "respuesta no es JSON valido (%s): %r" % (via, body[:120])); return
    detail = "HTTP 200 en %.1fs (%s), %d caracteres" % (secs, via, len(out))
    if len(out) < 60 or not CHATBOT_EXPECT.search(out):
        rec("chatbot", "respuesta", "FAIL", detail + " - contenido inesperado: %r" % out[:120], secs=secs)
    elif secs > BOT_FAIL:
        rec("chatbot", "respuesta", "FAIL", detail + " - demasiado lento", secs=secs)
    elif secs > BOT_WARN:
        rec("chatbot", "respuesta", "WARN", detail + " - lento", secs=secs)
    else:
        rec("chatbot", "respuesta", "OK", detail + ": " + out[:80].replace("\n", " ") + "...", secs=secs)

# ---------------------------------------------------------------- 4 Last activities
UNITS = [("sec", 1/60), ("seg", 1/60), ("min", 1), ("hour", 60), ("stund", 60), ("hora", 60),
         ("day", 1440), ("tag", 1440), ("dia", 1440), ("día", 1440), ("week", 10080), ("woche", 10080),
         ("month", 43200), ("monat", 43200), ("mes", 43200)]
def age_minutes(text):
    t, total, found = text.lower(), 0.0, False
    for num, unit in re.findall(r"(\d+)\s*([a-zäöüíé]+)", t):
        for key, mult in UNITS:
            if unit.startswith(key):
                total += int(num) * mult; found = True; break
    return total if found else None

ENTRY = re.compile(r'<div class="column2">\s*<b[^>]*>(.*?)</b>\s*<h6[^>]*>(.*?)</h6>\s*'
                   r'<a href="([^"]+)"[^>]*>(.*?)</a>', re.S)
def test_last_activities():
    freshest = {}
    for name, url in PAGES.items():
        if name == "root":      # redirige a /deu/de/, ya cubierto
            continue
        try:
            _, _, _, _, page = fetch(url)
        except Exception as e:
            rec("last_activities", name, "FAIL", "no se pudo cargar la pagina: %s" % e); continue
        entries = []
        for age_txt, place, link, text in ENTRY.findall(page):
            entries.append({"age_txt": html.unescape(re.sub(r"<[^>]+>", "", age_txt)).strip(),
                            "place": html.unescape(re.sub(r"<[^>]+>", "", place)).strip(),
                            "link": link, "text": html.unescape(re.sub(r"<[^>]+>", "", text)).strip()})
        if not entries:
            rec("last_activities", name, "FAIL", "el bloque no existe o esta vacio"); continue
        for e in entries:
            e["age_min"] = age_minutes(e["age_txt"])
        problems, warns = [], []
        bad_age = [e for e in entries if e["age_min"] is None]
        if bad_age: problems.append("%d entradas sin tiempo legible (%r)" % (len(bad_age), bad_age[0]["age_txt"]))
        ages = [e["age_min"] for e in entries if e["age_min"] is not None]
        if ages:
            freshest[name] = min(ages)
            if min(ages) > ACT_MAX_AGE_MIN:
                problems.append("la actividad mas reciente tiene %.0f h (>48h)" % (min(ages) / 60))
            old = [a for a in ages if a > ACT_MAX_AGE_MIN]
            if old and min(ages) <= ACT_MAX_AGE_MIN:
                warns.append("%d entradas con mas de 48h" % len(old))
            if ages != sorted(ages):
                warns.append("no estan ordenadas de mas reciente a mas antigua")
        if len(entries) < ACT_MIN_ENTRIES:
            warns.append("solo %d entradas (esperadas >= %d)" % (len(entries), ACT_MIN_ENTRIES))
        if any(not e["place"] or not e["link"].startswith("https://platform.labdoo.org/") or not e["text"] for e in entries):
            problems.append("entradas con lugar, enlace o texto vacio/incorrecto")
        detail = "%d entradas, la mas reciente hace %s, la mas antigua hace %s" % (
            len(entries), entries[0]["age_txt"], entries[-1]["age_txt"])
        st = "FAIL" if problems else "WARN" if warns else "OK"
        rec("last_activities", name, st, "; ".join([detail] + problems + warns), n=len(entries))
    if len(freshest) > 1:
        skew = max(freshest.values()) - min(freshest.values())
        st = "WARN" if skew > ACT_SITE_SKEW_MIN else "OK"
        rec("last_activities", "coherencia", st,
            "diferencia entre sites en la actividad mas reciente: %.0f min (%s)" % (
                skew, ", ".join("%s=%.0f" % kv for kv in freshest.items())))

# ---------------------------------------------------------------- main
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--chatbot-via-ssh", metavar="PEM", help="probar el chatbot desde el servidor de AWS")
    ap.add_argument("--out", default=os.path.join(os.path.dirname(os.path.abspath(__file__)), "results"))
    ap.add_argument("--only", default="response_time,tls_cert,chatbot,last_activities")
    a = ap.parse_args()
    only = set(a.only.split(","))
    started = dt.datetime.now(dt.timezone.utc)
    print("Labdoo health tests - %s UTC\n" % started.strftime("%Y-%m-%d %H:%M"))
    if "response_time" in only: test_response_time()
    if "tls_cert" in only: test_tls_cert()
    if "chatbot" in only: test_chatbot(a.chatbot_via_ssh)
    if "last_activities" in only: test_last_activities()
    n = {s: sum(r["status"] == s for r in results) for s in ("OK", "WARN", "FAIL")}
    print("\nResumen: %d OK, %d WARN, %d FAIL" % (n["OK"], n["WARN"], n["FAIL"]))
    try:
        os.makedirs(a.out, exist_ok=True)
        doc = {"started_utc": started.isoformat(), "summary": n, "results": results}
        for fn in ("labdoo_tests_%s.json" % started.strftime("%Y%m%d_%H%M%S"), "latest.json"):
            with open(os.path.join(a.out, fn), "w", encoding="utf-8") as f:
                json.dump(doc, f, ensure_ascii=False, indent=2)
    except OSError as e:
        print("(no se pudo guardar el JSON: %s)" % e)
    sys.exit(1 if n["FAIL"] else 2 if n["WARN"] else 0)

if __name__ == "__main__":
    main()
