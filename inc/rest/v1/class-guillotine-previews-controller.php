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
class Guillotine_Previews_Controller extends WP_REST_Controller {

	/**
	 * REST API route base
	 *
	 * @var string
	 */
	public $base = 'previews';

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
				'include'     => array( $post_id ),
			)
		);

		if ( count( $posts ) < 1 ) {
			return new WP_Error( 'rest_post_not_found', __( 'Post not found' ), array( 'status' => '404' ) );
		}
		$post = $posts[0];

		// Depending on the post status, get the post appropriate preview object.
		if ( 'publish' === $post->post_status ) {
			$item = wp_get_post_autosave( $post->ID );
		} else {
			$revisions = wp_get_post_revisions(
				$post->ID,
				array(
					'numberposts' => 1,
				)
			);
			$item      = count( $revisions ) > 0 ? array_shift( $revisions ) : null;
		}

		if ( ! isset( $item ) ) {
			return new WP_Error( 'rest_post_no_preview', __( 'No preview was found' ), array( 'status' => '404' ) );
		}
		$data = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $data );
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
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $date_gmt GMT publication time.
	 * @param string|null $date     Optional. Local publication time. Default null.
	 * @return string|null ISO8601/RFC3339 formatted datetime, otherwise null.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Checks the post excerpt and prepare it for single post output.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $excerpt The post excerpt.
	 * @param WP_Post $post    Post revision object.
	 * @return string Prepared excerpt or empty string.
	 */
	protected function prepare_excerpt_response( $excerpt, $post ) {

		/** This filter is documented in wp-includes/post-template.php */
		$excerpt = apply_filters( 'the_excerpt', $excerpt, $post );

		if ( empty( $excerpt ) ) {
			return '';
		}

		return $excerpt;
	}

	/**
	 * Prepares the revision for the REST response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $post    Post revision object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$data = array();

		$data['author'] = (int) $post->post_author;

		$data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );

		$data['date_gmt'] = $this->prepare_date_response( $post->post_date_gmt );

		$data['id'] = $post->ID;

		$data['modified'] = $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified );

		$data['modified_gmt'] = $this->prepare_date_response( $post->post_modified_gmt );

		$data['parent'] = (int) $post->post_parent;

		$data['slug'] = $post->post_name;

		$data['guid'] = array(
			/** This filter is documented in wp-includes/post-template.php */
			'rendered' => apply_filters( 'get_the_guid', $post->guid, $post->ID ),
			'raw'      => $post->guid,
		);

		$data['title'] = array(
			'raw'      => $post->post_title,
			'rendered' => get_the_title( $post->ID ),
		);

		$data['content'] = array(
			'raw'      => $post->post_content,
			/** This filter is documented in wp-includes/post-template.php */
			'rendered' => apply_filters( 'the_content', $post->post_content ),
		);

		$data['excerpt'] = array(
			'raw'      => $post->post_excerpt,
			'rendered' => $this->prepare_excerpt_response( $post->post_excerpt, $post ),
		);

		$response = rest_ensure_response( $data );

		return $response;
	}
}
