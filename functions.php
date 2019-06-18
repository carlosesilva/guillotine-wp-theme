<?php
/**
 * Guillotine functions and definitions
 *
 * @package Guillotine
 * @since 1.0.0
 */

define( 'GUILLOTINE_VERSION', '0.0.1' );

// Composer autoload.
if ( file_exists( __DIR__ . '/vendor' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	require_once ABSPATH . '/vendor/autoload.php';
}

// Register theme options.
require_once get_template_directory() . '/inc/settings/class-guillotine-settings.php';
// TODO: Check that certain vital settings have a value before enabling some of the other functionality.

// JWT auth functions.
require_once get_template_directory() . '/inc/jwt.php';

// HTTP headers customizations.
require_once get_template_directory() . '/inc/http-headers.php';

// Preview customizations.
require_once get_template_directory() . '/inc/links.php';

// CloudFront cache invalidation.
require_once get_template_directory() . '/inc/cloudfront.php';

// Custom REST API.
require_once get_template_directory() . '/inc/rest/v1/class-guillotine-rest-api-controller.php';
