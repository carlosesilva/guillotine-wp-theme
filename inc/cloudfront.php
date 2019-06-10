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
	add_menu_page( 'Bust CloudFront Cache', 'CloudFront Cache', 'manage_options', 'cache_invalidator', 'cloudfront_cache_invalidator');
}

/**
 * Add a javascript function to perform CloudFront Cache Invalidation, and adds some very basic markup to execute that function
 */
function cloudfront_cache_invalidator() {
	?>
	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		// Some global vars
		const cacheInvalidationEndpoint = '<?php echo get_field('cache_invalidation_endpoint', 'option' ); ?>';
		const cacheInvalidationStatusEndpoint       = '<?php echo get_field('cache_invalidation_status_endpoint', 'option' ); ?>';
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
		<h2 style="margin-top:20px;color:#FF0000;">WARNING: This will invalidate ALL of CloudFront's cache for Allconnext</h2>
		<h4>This may take up to 10 minutes</h4>
		<button id="cloudfront_cache_invalidator">Invalidate CloudFront Cache</button>
		<span id="loading_text" style="display:none;">Invalidating, please wait...</span>
		<span id="complete_text" style="display:none;">Invalidation complete!</span>
	</div>
	<?php
}