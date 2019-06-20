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
	 * Keeps track of the post type being currently requested
	 *
	 * @var string
	 */
	public $post_type = '';

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
					'callback' => array( $this, 'get_item' ),
				),
			)
		);
	}

	/**
	 * Get single post from specified path
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		/**
		 * TODO:
		 *  - Sanitize the "path" URL parameter so it can only be a valid URL
		 * I will rely on PR feedback from our B.E. team on how to best do these things.
		 */

		// Get the path from query parameter.
		$path = $request->get_param( 'path' );
		if ( ! $path ) {
			return new WP_Error( 'path_not_included', __( 'Path parameter not included.' ), array( 'status' => 400 ) );
		}

		// Figure out the post id from the path.
		$id = url_to_postid( $path );

		// Get post object from the translated id.
		$post = get_post( $id );
		if ( ! $post ) {
			return new WP_Error( 'post_not_found', __( 'Post not found.' ), array( 'status' => 404 ) );
		}

		// Save the current post type to a class variable so that we can access it in get_item_schema().
		$this->post_type = $post->post_type;

		// Prepare item for rest api response.
		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepares a single post output for response.
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$data = array();

		$data['author'] = (int) $post->post_author;

		$data['id'] = $post->ID;

		$data['parent'] = (int) $post->post_parent;

		$data['title'] = array(
			'raw'      => $post->post_title,
			'rendered' => get_the_title( $post->ID ),
		);

		$data['content'] = array(
			'raw'      => $post->post_content,
			/** This filter is documented in wp-includes/post-template.php */
			'rendered' => apply_filters( 'the_content', $post->post_content ),
		);

		$data = $this->add_additional_fields_to_object( $data, $request );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_{$post->post_type}", $response, $post, $request );
	}

	/**
	 * Retrieves the post's schema, conforming to JSON Schema.
	 *
	 * @since 4.7.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => $this->post_type,
			'type'    => 'object',
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
