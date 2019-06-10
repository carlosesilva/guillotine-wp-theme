<?php
/**
 * Customize links to headless frontend
 *
 * @package HeadlessWP
 * @since 1.0.0
 */

/**
 * Customize the preview button in the WordPress admin to point to the headless client.
 *
 * @param  str $link The WordPress preview link.
 * @return str The headless WordPress preview link.
 */
function headlesswp_filter_preview_link( $link, $post = null ) {
  $frontend_url = get_field('frontend_url', 'option');
  
  // Get global post if a post was not passed in.
  if ($post === null) {
    $post = get_post();
  }

  // Generate jwt token.
  $scopes = array( 'preview', 'preview_' . $post->ID );
  $ttl = 60; // Todo: pick the appropriate preview link TTL.
  $jwt = headlesswp_jwt_create_token( $scopes, $ttl );

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
add_filter( 'preview_post_link', 'headlesswp_filter_preview_link', 99, 2 );

/**
 * Customize the post permalink to point to the headless client.
 *
 * @param  str $url The WordPress post permalink.
 * @return str The static permalink.
 */
function headlesswp_filter_permalink( $url, $post = null ) {
  $frontend_url = get_field('frontend_url', 'option');

  // Get global post if a post was not passed in.  
  if ($post === null) {
    $post = get_post();
  }

  if ($post->post_status !== 'publish') {
    $filtered = headlesswp_filter_preview_link($url);
    return $filtered;
  }

  return $frontend_url
    . '/post'
    . '/' . $post->ID
    . '/';
}
add_filter( 'post_link', 'headlesswp_filter_permalink', 99, 2 );
