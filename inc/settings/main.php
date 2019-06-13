<?php
/**
 * Settings page
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Register settings page
 *
 * @return void
 */
function guillotine_register_settings_page() {
  add_menu_page( 'Guillotine Settings', 'Guillotine', 'manage_options', 'guillotine_settings', 'guillotine_render_settings_page');
}
add_action( 'admin_menu', 'guillotine_register_settings_page' );

/**
 * Render settings page
 *
 * @return void
 */
function guillotine_render_settings_page() {
  if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Enqueue static assets.
	wp_enqueue_script( 'guillotine-settings', get_theme_file_uri( '/dist/settings.js' ), array(), GUILLOTINE_VERSION, true );

	// Render html.
	?>
<h1>Guillotine Settings</h1>
<div id="guillotine-settings-root"></div>
	<?php
	// Pass rest api info to javascript.
	wp_localize_script( 'guillotine-settings', 'WP_API_Settings', array(
		'endpoint' => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' )
) );
}

/**
 * Register settings
 *
 * @return void
 */
function guillotine_register_settings() {
	register_setting(
		'guillotine',
		'guillotine_frontend_url',
		array(
			'type'              => 'string',
			'description'       => 'The url to the headless frontend.',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'http://localhost:3000',
		)
	);
}
add_action( 'admin_init',    'guillotine_register_settings' );
add_action( 'rest_api_init', 'guillotine_register_settings' );