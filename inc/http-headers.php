<?php
/**
 * HTTP Headers customizations
 *
 * @package Guillotine
 */

/**
 * Filters the http headers that are sent to the browser
 *
 * @param array $headers The array of http headers to be filtered.
 *
 * @return array The filterd array of http headers.
 */
function allconnect_filter_wp_headers( $headers ) {
	// Prevent clickjacking.
	$headers['X-Frame-Options'] = 'sameorigin';

	return $headers;
};
add_filter( 'wp_headers', 'allconnect_filter_wp_headers', 10, 1 );

/**
 * Filter list of allowed http origins
 *
 * @param array $urls The list of CORS allowed urls.
 * @return array The filtered array.
 */
function guillotine_allowed_origins( $urls ) {
	$frontend_url = get_option( 'guillotine_frontend_url' );
	$urls[]       = $frontend_url;
	return $urls;
}
add_filter( 'allowed_http_origins', 'guillotine_allowed_origins' );
