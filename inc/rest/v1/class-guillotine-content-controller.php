<?php
/**
 * Content route
 * - For getting the post content for any given URL
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Guillotine Content Controller class.
 */
class Guillotine_Content_Controller extends WP_REST_Controller {

	/**
	 * REST API route base
	 *
	 * @var string
	 */
	public $base = 'content';

	/**
	 * Constructor
	 *
	 * @param string $namespace The rest api route namespace.
	 */
	public function __construct( $namespace ) {
		$this->namespace = $namespace;
		$this->register_routes();
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_post' ),
				),
			)
		);
	}

	/**
	 * Get menu
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_post( $request ) {

		/**
		 * TODO:
		 *  - make necessary add_action/filter calls so plugins can supplement page data in this response
		 *  - Sanitize the "path" URL parameter so it can only be a valid URL
		 * I will rely on PR feedback from our B.E. team on how to best do these things.
		 */

		$path = $request->get_param( 'path' );
		if ( ! $path ) {
			return new WP_Error( 'path_not_included', __( 'Path parameter not included.' ), array( 'status' => 400 ) );
		}

		$id   = url_to_postid( $path );
		$post = get_post( $id );

		if ( ! $post ) {
			return new WP_Error( 'post_not_found', __( 'Post not found.' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $post );
	}
}
