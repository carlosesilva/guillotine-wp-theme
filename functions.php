<?php
/**
 * Guillotine functions and definitions
 *
 * @package Guillotine
 * @since 1.0.0
 */

// Composer autoload.
require __DIR__ . '/vendor/autoload.php';

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
