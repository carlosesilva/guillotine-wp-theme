<?php
/**
 * REST API Controller
 *
 * @package HeadlessWP
 * @since 1.0.0
 */

// Import route controllers.
require_once get_template_directory() . '/inc/rest/v1/menus.php';
require_once get_template_directory() . '/inc/rest/v1/preview.php';

class HeadlessWP_Rest_Api_Controller {
  /**
   * REST API namespace
   */
  public $namespace = "headlesswp/v1";

  /**
   * Constructor
   */
  public function __construct() {
    $this->menus_controller = new HeadlessWP_Menus_Controller( $this->namespace );
    $this->previews_controller = new HeadlessWP_Previews_Controller( $this->namespace );
  }
}
$headlesswp_rest_api_controller = new HeadlessWP_Rest_Api_Controller();
