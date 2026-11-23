<?php
/**
 * Plugin Name: Labdoo region redirect
 * Description: Envía a /deu/ a quien entra por la raíz desde Alemania o Austria, y recuerda la región elegida en el menú «Location». Sustituye al plugin Country Redirect.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * Por qué existe
 * --------------
 * Country Redirect enviaba a /deu cualquier página del sitio principal (no solo la portada)
 * a los visitantes con IP alemana, sin recordar lo que hubieran elegido. Quien estaba en /deu
 * y elegía «Global» volvía rebotado a /deu, y por eso se mantenía /global, una copia a mano
 * del sitio principal sin esa regla. Además las páginas servidas desde WP Super Cache se
 * saltaban la redirección.
 *
 * Qué hace
 * --------
 * Solo actúa en https://www.labdoo.org/ (la raíz exacta, que ya es una redirección de
 * Polylang al idioma y nunca se cachea):
 *
 *   /?region=deu      guarda la cookie labdoo_region=deu y va a /deu/
 *   /?region=global   guarda la cookie labdoo_region=global y vuelve a / (Polylang elige idioma)
 *   /  con cookie     deu → /deu/;  global → no hace nada
 *   /  sin cookie     IP de DE o AT → /deu/; el resto → no hace nada
 *
 * Las páginas interiores nunca redirigen: un enlace de Google a /es/sobre-nosotros/ funciona
 * igual desde Alemania. No redirige a usuarios conectados ni a bots.
 *
 * Los enlaces del menú «Location» apuntan a /?region=deu y /?region=global (ver
 * docs/arquitectura.md).
 *
 * El país se obtiene con la base Sypex Geo (solo IPv4; el sitio no tiene IPv6) que trae el
 * plugin Country Redirect. El plugin queda desactivado pero instalado. Si falta la base, nadie es
 * redirigido.
 *
 * Pruebas: con LABDOO_REGION_TEST_KEY definida en wp-config.php, la cookie
 * labdoo_geo_test=<clave>:<PAÍS> fuerza el país (p. ej. curl -b 'labdoo_geo_test=xyz:DE').
 */

const LABDOO_REGION_COOKIE    = 'labdoo_region';
const LABDOO_REGION_COUNTRIES = array( 'DE', 'AT' );
const LABDOO_REGION_DEU_PATH  = '/deu/';

/**
 * Decide qué hacer con la petición. Sin efectos: devuelve null o
 * array( 'location' => ruta, 'cookie' => valor|null ).
 *
 * @param array    $server  $_SERVER
 * @param array    $get     $_GET
 * @param array    $cookie  $_COOKIE
 * @param callable $country fn( string $ip ): string  código ISO o ''
 */
function labdoo_region_decide( array $server, array $get, array $cookie, callable $country ) {
	$method = $server['REQUEST_METHOD'] ?? 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return null;
	}
	if ( '/' !== parse_url( $server['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) {
		return null;
	}

	// Elección explícita desde el menú.
	$choice = isset( $get['region'] ) && is_string( $get['region'] ) ? strtolower( $get['region'] ) : '';
	if ( 'deu' === $choice ) {
		return array( 'location' => LABDOO_REGION_DEU_PATH, 'cookie' => 'deu' );
	}
	if ( 'global' === $choice ) {
		return array( 'location' => '/', 'cookie' => 'global' );
	}

	// Elección guardada.
	$saved = $cookie[ LABDOO_REGION_COOKIE ] ?? '';
	if ( 'global' === $saved ) {
		return null;
	}
	if ( 'deu' === $saved ) {
		return array( 'location' => LABDOO_REGION_DEU_PATH, 'cookie' => null );
	}

	// Sin elección: por país, salvo usuarios conectados y bots.
	foreach ( array_keys( $cookie ) as $name ) {
		if ( 0 === strpos( (string) $name, 'wordpress_logged_in_' ) ) {
			return null;
		}
	}
	$agent = $server['HTTP_USER_AGENT'] ?? '';
	if ( '' === $agent || preg_match( '/bot|crawl|spider|slurp|archiver|mediapartners|facebookexternalhit|preview/i', $agent ) ) {
		return null;
	}
	if ( in_array( $country( $server['REMOTE_ADDR'] ?? '' ), LABDOO_REGION_COUNTRIES, true ) ) {
		return array( 'location' => LABDOO_REGION_DEU_PATH, 'cookie' => null );
	}
	return null;
}

/**
 * País de una IPv4 con Sypex Geo, o '' si no se sabe.
 */
function labdoo_region_country( $ip ) {
	if ( defined( 'LABDOO_REGION_TEST_KEY' ) && LABDOO_REGION_TEST_KEY && isset( $_COOKIE['labdoo_geo_test'] ) ) {
		$parts = explode( ':', (string) $_COOKIE['labdoo_geo_test'], 2 );
		if ( 2 === count( $parts ) && hash_equals( (string) LABDOO_REGION_TEST_KEY, $parts[0] ) ) {
			return strtoupper( substr( $parts[1], 0, 2 ) );
		}
	}

	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		return '';
	}
	$base = WP_CONTENT_DIR . '/plugins/country-redirect/DB/SxGeo/SxGeo';
	if ( ! is_readable( $base . '.php' ) || ! is_readable( $base . '.dat' ) ) {
		return '';
	}
	try {
		if ( ! class_exists( 'CountryRedirect\\SxGeo', false ) ) {
			require_once $base . '.php';
		}
		$geo = new CountryRedirect\SxGeo( $base . '.dat' );
		return (string) $geo->getCountry( $ip );
	} catch ( Throwable $e ) {
		return '';
	}
}

/*
 * Se ejecuta al cargar el mu-plugin, antes de que Polylang haga su propia redirección de la
 * raíz. Aquí aún no existen wp_redirect() ni is_user_logged_in() (son «pluggable»), por eso
 * se usan header() y la cookie de sesión.
 */
( static function () {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || 1 !== get_current_blog_id() ) {
		return;
	}

	$action = labdoo_region_decide( $_SERVER, $_GET, $_COOKIE, 'labdoo_region_country' );
	if ( null === $action ) {
		return;
	}

	if ( null !== $action['cookie'] ) {
		setcookie(
			LABDOO_REGION_COOKIE,
			$action['cookie'],
			array(
				'expires'  => time() + YEAR_IN_SECONDS,
				'path'     => '/',
				'secure'   => true,
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}
	header( 'Cache-Control: private, no-store' );
	header( 'X-Redirect-By: Labdoo region' );
	header( 'Location: ' . network_home_url( $action['location'] ), true, 302 );
	exit;
} )();
