<?php
/**
 * Customize links to guillotine frontend
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Customize the preview button in the WordPress admin to point to the guillotine client.
 *
 * @param string $link The WordPress preview link.
 * @param object $post The WordPress post object.
 * @return str The guillotine WordPress preview link.
 */
function guillotine_filter_preview_link( $link, $post = null ) {
	$frontend_url = get_option( 'guillotine_frontend_url' );

	// Get global post if a post was not passed in.
	if ( null === $post ) {
		$post = get_post();
	}

	// Generate jwt token.
	$scopes = array( 'preview', 'preview_' . $post->ID );
	$ttl    = 60; // Todo: pick the appropriate preview link TTL.
	$jwt    = guillotine_jwt_create_token( $scopes, $ttl );

	// Set query params.
	$params = array(
		'token' => $jwt,
	);

	// Build preview url.
	$filtered = $frontend_url
	. '/_preview'
	. '/' . $post->ID
	. '?' . build_query( $params );

	return $filtered;
}
add_filter( 'preview_post_link', 'guillotine_filter_preview_link', 99, 2 );

/**
 * Customize the post permalink to point to the guillotine client.
 *
 * @param string $url The WordPress post permalink.
 * @param object $post The WordPress post object.
 * @return string The static permalink.
 */
function guillotine_filter_permalink( $url, $post = null ) {
	$frontend_url = get_option( 'guillotine_frontend_url' );

	// Get global post if a post was not passed in.
	if ( null === $post ) {
		$post = get_post();
	}

	if ( 'publish' !== $post->post_status ) {
		$filtered = guillotine_filter_preview_link( $url );
		return $filtered;
	}

	return $frontend_url
	. '/post'
	. '/' . $post->ID
	. '/';
}
add_filter( 'post_link', 'guillotine_filter_permalink', 99, 2 );
