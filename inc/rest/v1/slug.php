<?php

class Guillotine_Preview_Route extends WP_REST_Controller {
	// /**
	// * Constructor
	// */
	// public function __construct() {
	// $this->register_routes();
	// }

	$types = get_post_types( array( 'show_in_rest' => true ) );

}
