<?php
/**
 * Preview route
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Guillotine Previews Controller class
 */
class Guillotine_Previews_Controller extends WP_REST_Posts_Controller {

	/**
	 * REST API route base
	 *
	 * @var string
	 */
	public $base = 'previews';

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
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get preview
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$post_id = (int) $request->get_param( 'id' );
		if ( $post_id <= 0 ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}

		// Get original post object.
		$posts = get_posts(
			array(
				'numberposts' => 1,
				'post_status' => 'any',
				'post_type'   => 'any',
				'include'     => array( $post_id ),
			)
		);

		if ( count( $posts ) < 1 ) {
			return new WP_Error( 'rest_post_not_found', __( 'Post not found' ), array( 'status' => '404' ) );
		}
		$post = $posts[0];

		// Save the current post type to a class variable so that we can access it in get_item_schema().
		$this->post_type = $post->post_type;
		$this->meta      = new WP_REST_Post_Meta_Fields( $this->post_type );

		// Depending on the post status, get the post appropriate preview object.
		if ( 'publish' === $post->post_status ) {
			$preview = wp_get_post_autosave( $post->ID );
		} else {
			$revisions = wp_get_post_revisions(
				$post->ID,
				array(
					'numberposts' => 1,
				)
			);
			$preview      = count( $revisions ) > 0 ? array_shift( $revisions ) : null;
		}

		if ( !$preview ) {
			return new WP_Error( 'rest_post_no_preview', __( 'No preview was found' ), array( 'status' => '404' ) );
		}

		$data     = $this->prepare_preview_item_for_response( $post, $preview, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		$post_id = (int) $request->get_param( 'id' );
		if ( $post_id <= 0 ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}
		$jwt = $request->get_param( 'token' );
		if ( ! $jwt ) {
			return new WP_Error( 'rest_jwt_unauthorized', __( 'Missing token query parameter.' ), array( 'status' => 401 ) );
		}

		$scopes         = array( 'preview', 'preview_' . $post_id );
		$is_valid_token = guillotine_jwt_validate_token( $jwt, $scopes );

		if ( is_wp_error( $is_valid_token ) ) {
			return new WP_Error(
				'rest_jwt_unauthorized',
				$is_valid_token->get_error_message(),
				array(
					'status' => '401',
				)
			);
		}

		return true;
	}

	/**
	 * Prepares a single post output for response.
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_Post         $preview Preview post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_preview_item_for_response( $post, $preview, $request ) {
		$post_data = $this->prepare_item_for_response( $post, $request );
		$preview_data = $this->prepare_item_for_response( $preview, $request );

		$post_data->data["title"] = $preview_data->data["title"];
		$post_data->data["content"] = $preview_data->data["content"];
		$post_data->data["excerpt"] = $preview_data->data["excerpt"];

		return rest_ensure_response( $post_data );
	}
}
