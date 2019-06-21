<?php
/**
 * CloudFront cache invalidation
 *
 * @package Guillotine
 * @since 1.0.0
 */

/**
 * Add CloudFront Cache Invalidator to WP Admin Menu
 */
add_action( 'admin_menu', 'register_cloudfront_cache_buster' );

/**
 * Add a menu page for the CloudFront Cache Invalidator
 */
function register_cloudfront_cache_buster() {
	add_menu_page( 'Bust CloudFront Cache', 'CloudFront Cache', 'manage_options', 'cache_invalidator', 'cloudfront_cache_invalidator' );
}

/**
 * Add a javascript function to perform CloudFront Cache Invalidation, and adds some very basic markup to execute that function
 */
function cloudfront_cache_invalidator() {
	?>
	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		const cacheInvalidationEndpoint = '<?php echo get_option( 'guillotine_cache_invalidation_endpoint' ); ?>';
		const cacheInvalidationStatusEndpoint = '<?php echo get_option( 'guillotine_cache_invalidation_status_endpoint' ); ?>';
		let interval;

		// DOM Elements
		const cloudfrontButton = document.getElementById('cloudfront_cache_invalidator');
		const loadingText      = document.getElementById('loading_text');
		const completeText     = document.getElementById('complete_text');

		// Performs the cache invalidation
		const performInvalidation = () => {
			// Don't let them hit the button again
			cloudfrontButton.remove();

			// Notify user that this is in progress
			loadingText.style.display = 'block';

			// Fire off the request
			fetch(cacheInvalidationEndpoint)
				.then(response => response.json())
				.then(response => {
					if (!response.invalidationId) {
						alert('Something went wrong, please refresh and try again.');
						return false;
					}
					startPollingStatus(response.invalidationId);
				});
		};

		// Kicks off the periodic check of the invalidation status.
		const startPollingStatus = (invalidationId) => {
			interval = setInterval(() => {
				// Fire off an invalidation check
				checkInvalidation(invalidationId);

				// Let the user know that we're still loading
				loadingText.innerHTML += '.';
			}, 5000);
		}

		// Checks the invalidation status. Once it's complete, will notify the user.
		const checkInvalidation = (invalidationId) => {
			// If we have an invalidationId, go ahead and fire off the request
			var url = `${cacheInvalidationStatusEndpoint}/${invalidationId}`;
			fetch(url)
				.then(response => response.json())
				.then(response => {
					// Fail early
					if (response.status !== 'Completed') return false;

					// We're done, we can clear the interval
					clearInterval(interval);

					// Notify the user
					loadingText.style.display = 'none';
					completeText.style.display = 'block';
				});
		};

		// Add button action
		cloudfrontButton.addEventListener('click', performInvalidation);
	});
	</script>

	<div class="invalidation-wrapper">
		<h2 style="margin-top:20px;color:#FF0000;">WARNING: This will invalidate ALL of CloudFront's cache for your site</h2>
		<h4>This may take up to 10 minutes</h4>
		<button id="cloudfront_cache_invalidator">Invalidate CloudFront Cache</button>
		<span id="loading_text" style="display:none;">Invalidating, please wait...</span>
		<span id="complete_text" style="display:none;">Invalidation complete!</span>
	</div>
	<?php
}

const CACHE_INVALIDATION_URL = 'https://at38idql6j.execute-api.us-east-1.amazonaws.com/development/clearcache?path=';

/**
 * Invalidate CloudFront cache after saving a published post
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post The post object.
 * @param bool    $update Whether this hook is fired on an content update or not.
 * @return void
 */
function partial_cache_invalidation( $post_id, $post, $update ) {
	// IF USING GUTENBERG - Check server first, and ignore anything from wp-admin since this hook is hit multiple times from 2 origins
	// wp-admin hits this hook twice, and wp-json hits it once, which is why we're ignoring wp-admin here
	// if ( $_SERVER && $_SERVER['REQUEST_URI'] && strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false )
	// return;
	// Return if autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Return if ajax.
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	// Return if insufficient permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Return if it's a post revision.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	// Return if it's a post autosave.
	if ( wp_is_post_autosave( $post_id ) ) {
		return;
	}
	// Return if they're not updating.
	if ( ! $update ) {
		return;
	}
	// Return if not publishing.
	if ( 'publish' !== $post->post_status ) {
		return;
	}

	// Get the invalidation endpoint setting.
	$cache_invalidation_endpoint = get_option( 'guillotine_cache_invalidation_endpoint' );
	if ( ! $cache_invalidation_endpoint ) {
		return;
	}

	// Pull the post path that we want to invalidate.
	$invalidate_path = urlencode( get_permalink( $post ) );

	// Set up a WP_Http request to our cache invalidation url and fire it.
	$http     = new WP_Http();
	$response = $http->request(
		$cache_invalidation_endpoint . '?path=' . $invalidate_path
	);
}
// Add our save_post hook, specify a priority of 100, and 3 accepted arguments.
add_action( 'save_post', 'partial_cache_invalidation', 100, 3 );
