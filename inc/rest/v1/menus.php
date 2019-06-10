<?php
/**
 * Menus route
 *
 * @package HeadlessWP
 * @since 1.0.0
 */

class HeadlessWP_Menus_Controller extends WP_REST_Controller {

  /**
   * REST API route base
   */
  public $base = "menus";

  /**
   * Constructor
   */
  public function __construct( $namespace ) {
    $this->namespace = $namespace;
    $this->register_routes();
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    register_rest_route( $this->namespace, '/' . $this->base . '/(?P<name>.*?)', array(
      array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => array( $this, 'get_item' ),
      ),
    ) );
  }

  /**
   * Get menu
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item( $request ) {
    $menu_name = $request->get_param( 'name' );

    $menu = wp_get_nav_menu_items( $menu_name );
    if (!$menu) return new WP_Error( 'rest_menu_not_found', __( 'Menu not found.' ), array( 'status' => 404 ) );

    $data = $this->prepare_item_for_response( $menu, $request );
    return rest_ensure_response( $data );
  }

  /**
   * Used to convert the menu "object"s in WP into arrays,
   * so we can loop through them.
   */
  public function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;

    if(is_array($obj)) {
      $new = array();
      foreach($obj as $key => $val) {
        $new[$key] = $this->object_to_array($val);
      }
    } else $new = $obj;

    return $new;
  }

  /**
   * Checks if a given object has "menu_item_parent" property
   */
  public function api_array_item_has_parent($item) {
    return (!empty($item['menu_item_parent']));
  }

  /**
   * Given a "child" object and an array of objects,
   * return the index of the array, where the "menu_item_parent"
   * of the child matches the "ID" of an object in the array.
   */
  public function api_get_parent_link_index($child, $menu) {
    foreach($menu as $index => $value) {
      if ($value['ID'] == $child['menu_item_parent']) return $index;
    }
    return null;
  }

  /**
   * Loop through an array of objects ($data), and reformat that array,
   * such that "child" objects appear in a "children" proprty of their parents
   */
  public function prepare_item_for_response($item, $request) {
    $arr = $this->object_to_array($item);

    $menu = [];

    // loop through every item of the array,
    // if you find a child, remove it from the array,
    // find it's parent, and add it in the "children" propery
    // of that parent
    foreach($arr as $key => &$item) {
      // If item has no parent, push it to our menu, and go on to next one
      if (!$this->api_array_item_has_parent($item)) {
        $menu[] = $item;
        continue;
      }

      // Find parent index, add this $item to that index's children
      $matchedItemIndex = $this->api_get_parent_link_index($item, $menu);

      // If parent wasn't found at top level, this means this item
      // is > 2 lvls deep. We don't allow that. Don't add the item from our list.
      if ($matchedItemIndex === null) {
        continue;
      }

      // We have a "child" item, and we found it's "parent",
      // so add it to the parent's "children" property
      $menu[$matchedItemIndex]['children'][] = $item;
    }

    return $menu;
  }
}
