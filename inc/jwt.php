<?php
/**
 * JWT auth functions
 *
 * @package HeadlessWP
 * @since 1.0.0
 */

use \Firebase\JWT\JWT;

// Define JWT constants.
define( "HEADLESSWP_JWT_KEY", get_field('jwt_secret_key', 'option' ) );
define( "HEADLESSWP_JWT_ALGORITHM", "HS256" );

/**
 * Create JWT token
 *
 * @param array $scopes The scopes to be added to this token.
 * @param int $ttl The token's time-to-live in seconds.
 * @return string The newly created jwt token.
 */
function headlesswp_jwt_create_token( $scopes, $ttl ) {
  $issuedAt = time();
  $expirationTime = $issuedAt + $ttl;
  $payload = array(
    "iss" => get_home_url(),
    "aud" => get_field('frontend_url', 'option'),
    "iat" => time(),
    "exp" => time() + $ttl,
    "scopes" => $scopes,
  );

  $jwt = JWT::encode( $payload, HEADLESSWP_JWT_KEY );

  return $jwt;
}

/**
 * Check if token is valid.
 *
 * @param string $jwt The JWT token to be validated.
 * @param array $scopes The scopes the token is expected to have.
 * @return bool|WP_ERROR Return true if token is valid or return WP_ERROR if token is invalid.
 */
function headlesswp_jwt_validate_token( $jwt, $scopes ) {
  try {
    $payload = headlesswp_jwt_decode_token( $jwt );
    if ( is_wp_error($payload) ) {
      return $payload;
    }
    return headlesswp_jwt_check_scopes( $payload, $scopes );
  } catch (Exception $e) {
    return new WP_ERROR('Invalid token', $e->getMessage());
  }
}

/**
 * Decode the JWT token.
 *
 * @param string $jwt The JWT token to be decoded.
 * @return object|WP_ERROR Return the JWT payload object on success or WP_ERROR.
 */
function headlesswp_jwt_decode_token( $jwt ) {
  try {
    $payload = JWT::decode( $jwt, HEADLESSWP_JWT_KEY, array( HEADLESSWP_JWT_ALGORITHM ) );
    return $payload;
  } catch (Exception $e) {
    return new WP_ERROR('Invalid token', $e->getMessage());
  }
}

/**
 * Make sure token has the proper scopes.
 *
 * @param object $payload The decoded JWT payload object.
 * @param array $scopes The scopes the token is expected to have.
 * @return void
 */
function headlesswp_jwt_check_scopes( $payload, $scopes ) {
  if ( count( $scopes ) !== count( array_intersect( $scopes, $payload->scopes ) ) ) {
    throw new Exception( "Token does not have the proper scopes." );
  }
  return true;
}
