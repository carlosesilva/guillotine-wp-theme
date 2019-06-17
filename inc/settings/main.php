<?php
/**
 * Settings page
 *
 * @package Guillotine
 * @since 1.0.0
 */

class Guillotine_Settings {
	private $settings = array(
		"Headless" => array(
			'guillotine_frontend_url' => array(
				'name'              => 'Frontend URL',
				'order'             => '10',
				'type'              => 'text',
				'description'       => 'The url to the headless frontend.',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => 'http://localhost:3000',
			),
			'guillotine_jwt_secret' => array(
				'name'              => 'JWT Secret',
				'order'             => '20',
				'type'              => 'password',
				'description'       => 'The secret used to encrypt the JWT tokens.',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			),
		),
		"Examples" => array(
			'guillotine_textarea' => array(
				'name'              => 'Example Textarea',
				'order'             => '10',
				'type'              => 'textarea',
				'description'       => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			),
			'guillotine_email' => array(
				'name'              => 'Example Email',
				'order'             => '30',
				'type'              => 'email',
				'description'       => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			),
			'guillotine_url' => array(
				'name'              => 'Example URL',
				'order'             => '20',
				'type'              => 'url',
				'description'       => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			),
		),
	);

	/**
   * Constructor
   */
  public function __construct() {
		add_action( 'admin_menu', array( $this, 'guillotine_register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'guillotine_register_settings' ) );
		add_action( 'rest_api_init', array( $this, 'guillotine_register_settings' ) );
	}

	/**
	 * Register settings page
	 *
	 * @return void
	 */
	function guillotine_register_settings_page() {
		add_menu_page( 'Guillotine Settings', 'Guillotine', 'manage_options', 'guillotine_settings', array( $this, 'guillotine_render_settings_page' ) );
	}

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
		wp_localize_script( 'guillotine-settings', 'WPAPI_Config', array(
			'endpoint' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );
		wp_localize_script( 'guillotine-settings', 'Guillotine_Settings_Schema', $this->settings );
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	function guillotine_register_settings() {
		foreach ($this->settings as $section => $settings) {
			foreach ($settings as $setting => $args) {
				register_setting(
					'guillotine',
					$setting,
					array(
						'type'              => $this->normalize_setting_type_for_settings_api( $args['type'] ),
						'description'       => $args['description'],
						'sanitize_callback' => $args['sanitize_callback'],
						'show_in_rest'      => $args['show_in_rest'],
						'default'           => $args['default'],
					)
				);
			}
		}
	}

	function normalize_setting_type_for_settings_api( $type ) {
		return array(
			'text' => 'string',
			'textarea' => 'string',
			'password' => 'string',
			'email' => 'string',
			'url' => 'string',
		)[ $type ];
	}
}
$guillotine_settings = new Guillotine_Settings();