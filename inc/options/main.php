<?php
/**
 * Options page
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Register options page
 *
 * @return void
 */
function guillotine_register_options_page() {
  add_menu_page( 'Guillotine Options', 'Guillotine', 'manage_options', 'guillotine_options', 'guillotine_render_options_page');
}
add_action( 'admin_menu', 'guillotine_register_options_page' );

/**
 * Render options page
 *
 * @return void
 */
function guillotine_render_options_page() {
  if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Enqueue static assets.
	wp_enqueue_script( 'guillotine-options', get_theme_file_uri( '/dist/options.js' ), array(), GUILLOTINE_VERSION, true );

	// Render html.
	echo '<div id="guillotine-options-root"></div>';
}