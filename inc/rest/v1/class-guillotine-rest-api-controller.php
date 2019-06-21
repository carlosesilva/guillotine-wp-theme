<?php
/**
 * REST API Controller
 *
 * @package Guillotine
 * @since 1.0.0
 */

// Import route controllers.
require_once get_template_directory() . '/inc/rest/v1/class-guillotine-menus-controller.php';
require_once get_template_directory() . '/inc/rest/v1/class-guillotine-previews-controller.php';
require_once get_template_directory() . '/inc/rest/v1/class-guillotine-content-controller.php';

/**
 * Guillotine REST API Controller class
 */
class Guillotine_Rest_Api_Controller {
	/**
	 * Singleton Instance
	 *
	 * @var string
	 */
	private static $instance = null;

	/**
	 * REST API namespace
	 *
	 * @var string
	 */
	public $namespace = 'guillotine/v1';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->menus_controller    = new Guillotine_Menus_Controller( $this->namespace );
		$this->previews_controller = new Guillotine_Previews_Controller( $this->namespace );
		$this->content_controller  = new Guillotine_Content_Controller( $this->namespace );
	}

	/**
	 * Initialize singleton
	 *
	 * @return void
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new Guillotine_Rest_Api_Controller();
		}
	}
}
add_action( 'rest_api_init', 'Guillotine_Rest_Api_Controller::init' );
