<?php
/**
 * Plugin Name: Labdoo SEO fixes
 * Description: Corrige la URL canónica de las portadas de idioma, que Rank Math rompe en su integración con Polylang.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * Rank Math 1.0.282, includes/3rdparty/polylang/class-polylang.php, fix_home_canonical()
 * (filtro rank_math/frontend/canonical, prioridad 10) añade la ruta del idioma a la
 * canónica de la portada aunque ya la lleve: /es/ declara /es/es/, /deu/de/ declara
 * /deu/deu/de/ y /global/en/ declara /global/global/en/. En /deu/ y /global/ el valor
 * previo tampoco vale (es /deu/ o /global/, que redirigen), así que en vez de deshacer
 * el añadido se pone la portada real del idioma, la misma URL que Polylang usa en sus
 * hreflang.
 *
 * Solo se toca la portada sin paginar. Si una actualización de Rank Math corrige el fallo,
 * este filtro devuelve la misma URL y se puede borrar.
 */
add_filter(
	'rank_math/frontend/canonical',
	static function ( $canonical ) {
		if ( ! is_front_page() || is_paged() || ! function_exists( 'pll_home_url' ) || ! function_exists( 'pll_current_language' ) ) {
			return $canonical;
		}

		$lang = pll_current_language();
		if ( ! $lang ) {
			return $canonical;
		}

		$home = pll_home_url( $lang );

		return $home ? $home : $canonical;
	},
	11
);
