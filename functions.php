<?php
/**
 * Guillotine functions and definitions
 *
 * @package Guillotine
 * @since 1.0.0
 */

define('GUILLOTINE_VERSION', '0.0.1');

// Composer autoload.
if (file_exists(__DIR__ . '/vendor')) {
  require __DIR__ . '/vendor/autoload.php';
} else {
  require ABSPATH . '/vendor/autoload.php';
}

// Options page.
require_once get_template_directory() . '/inc/options/main.php';

// ACF Options page.
require_once get_template_directory() . '/inc/options-page.php';

// JWT auth functions.
require_once get_template_directory() . '/inc/jwt.php';

// HTTP headers customizations.
require get_template_directory() . '/inc/http-headers.php';

// Preview customizations.
require_once get_template_directory() . '/inc/links.php';

// CloudFront cache invalidation
require_once get_template_directory() . '/inc/cloudfront.php';

// Custom REST API.
require_once get_template_directory() . '/inc/rest/v1/index.php';
