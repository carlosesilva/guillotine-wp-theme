<?php
/**
 * REST API Controller
 *
 * @package Guillotine
 * @since 1.0.0
 */

// Import route controllers.
require_once get_template_directory() . '/inc/rest/v1/menus.php';
require_once get_template_directory() . '/inc/rest/v1/preview.php';

class Guillotine_Rest_Api_Controller {
  /**
   * REST API namespace
   */
  public $namespace = "guillotine/v1";

  /**
   * Constructor
   */
  public function __construct() {
    $this->menus_controller = new Guillotine_Menus_Controller( $this->namespace );
    $this->previews_controller = new Guillotine_Previews_Controller( $this->namespace );
  }
}
$guillotine_rest_api_controller = new Guillotine_Rest_Api_Controller();
