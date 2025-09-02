<?php
/**
 * Godam Integration for rtMedia
 *
 * This file integrates the Godam player with rtMedia and BuddyPress.
 *
 * Responsibilities:
 * - Enqueue Godam scripts and styles (including skins) on both normal pages and BuddyPress pages.
 * - Replace rtMedia video lists in BuddyPress activity streams with Godam players.
 * - Handle AJAX requests for activity comments to ensure Godam players load dynamically.
 * - Ensure compatibility with multisite setups using `get_site_option` for global settings.
 * - Maintain Magnific Popup compatibility when Godam is active or inactive.
 */

if ( defined( 'RTMEDIA_GODAM_ACTIVE' ) && RTMEDIA_GODAM_ACTIVE ) {

	/**
	 * Enqueue GoDAM scripts and styles (with skins).
	 */
	add_action( 'wp_enqueue_scripts', 'godam_enqueue_player_assets', 20 );
	add_action( 'bp_enqueue_scripts', 'godam_enqueue_player_assets', 20 ); // BuddyPress pages

	function godam_enqueue_player_assets() {
		// Base scripts and styles
		wp_enqueue_script( 'godam-player-frontend-script' );
		wp_enqueue_script( 'godam-player-analytics-script' );
		wp_enqueue_style( 'godam-player-frontend-style' );
		wp_enqueue_style( 'godam-player-style' );

		// Skin detection — multisite safe (uses site option).
		$godam_settings = get_option( 'rtgodam-settings', array() );
		$selected_skin  = $godam_settings['video_player']['player_skin'] ?? '';

		if ( 'Minimal' === $selected_skin ) {
			wp_enqueue_style( 'godam-player-minimal-skin' );
		} elseif ( 'Pills' === $selected_skin ) {
			wp_enqueue_style( 'godam-player-pills-skin' );
		} elseif ( 'Bubble' === $selected_skin ) {
			wp_enqueue_style( 'godam-player-bubble-skin' );
		} elseif ( 'Classic' === $selected_skin ) {
			wp_enqueue_style( 'godam-player-classic-skin' );
		}
	}

	/**
	 * Enqueue frontend scripts for Godam integration and AJAX refresh.
	 */
	add_action(
		'wp_enqueue_scripts',
		function () {

			// Enqueue integration script for rtMedia and Godam.
			wp_enqueue_script(
				'godam-rtmedia-integration',
				RTMEDIA_URL . 'app/assets/js/godam-integration.min.js',
				array( 'godam-player-frontend-script' ),
				null,
				true
			);

			// Enqueue the script responsible for AJAX-based comment refresh.
			wp_enqueue_script(
				'godam-ajax-refresh',
				RTMEDIA_URL . 'app/assets/js/godam-ajax-refresh.min.js',
				array(),
				null,
				true
			);

			// Pass AJAX URL and nonce to the script.
			wp_localize_script(
				'godam-ajax-refresh',
				'GodamAjax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'godam-ajax-nonce' ),
				)
			);
		}
	);

	/**
	 * Filter BuddyPress activity content to replace rtMedia video list
	 * with Godam player shortcodes.
	 */
	add_filter(
		'bp_get_activity_content_body',
		function ( $content ) {
			global $activities_template;

			// Bail early if activity object is not available.
			if ( empty( $activities_template->activity ) || ! is_object( $activities_template->activity ) ) {
				return $content;
			}

			$activity = $activities_template->activity;

			// Allow only certain activity types.
			$valid_types = array( 'rtmedia_update', 'activity_update', 'activity_comment' );
			if ( ! isset( $activity->type ) || ! in_array( $activity->type, $valid_types, true ) ) {
				return $content;
			}

			// Ensure RTMediaModel class exists.
			if ( ! class_exists( 'RTMediaModel' ) ) {
				return $content;
			}

			$model       = new RTMediaModel();
			$media_items = $model->get( array( 'activity_id' => $activity->id ) );

			if ( empty( $media_items ) || ! is_array( $media_items ) ) {
				return $content;
			}

			// Remove rtMedia default video <ul>.
			$clean_content = preg_replace(
				'#<ul[^>]*class="[^"]*rtmedia-list[^"]*rtm-activity-media-list[^"]*rtmedia-activity-media-length-[0-9]+[^"]*rtm-activity-video-list[^"]*"[^>]*>.*?</ul>#si',
				'',
				$activity->content
			);

			// Group media by type.
			$grouped_media = array();
			foreach ( $media_items as $media ) {
				$grouped_media[ $media->media_type ][] = $media;
			}

			$godam_videos = '';

			// Build Godam player shortcodes for videos.
			if ( ! empty( $grouped_media['video'] ) ) {
				foreach ( $grouped_media['video'] as $index => $video ) {
					$player_id     = 'godam-activity-' . esc_attr( $activity->id ) . '-' . $index;
					$godam_videos .= do_shortcode(
						'[godam_video id="' . esc_attr( $video->media_id ) .
						'" context="buddypress" player_id="' . esc_attr( $player_id ) . '"]'
					);
				}
			}

			// Process video media in activity comments.
			if ( ! empty( $activity->children ) && is_array( $activity->children ) ) {
				foreach ( $activity->children as $child ) {
					$child_media = $model->get( array( 'activity_id' => $child->id ) );

					if ( empty( $child_media ) ) {
						continue;
					}

					$child_videos = '';

					foreach ( $child_media as $index => $video ) {
						$player_id     = 'godam-comment-' . esc_attr( $child->id ) . '-' . $index;
						$child_videos .= do_shortcode(
							'[godam_video id="' . esc_attr( $video->media_id ) . '"]'
						);
					}

					if ( $child_videos ) {
						// Remove rtMedia <ul> from comment.
						$child->content = preg_replace(
							'#<ul[^>]*class="[^"]*rtmedia-list[^"]*rtm-activity-media-list[^"]*rtmedia-activity-media-length-[0-9]+[^"]*rtm-activity-video-list[^"]*"[^>]*>.*?</ul>#si',
							'',
							$child->content
						);

						// Append Godam video players.
						$child->content .= '<div class="godam-video-players-wrapper">' . $child_videos . '</div>';
					}
				}
			}

			// Final video output appended to cleaned content.
			if ( $godam_videos ) {
				$godam_videos = '<div class="godam-video-players-wrapper">' . $godam_videos . '</div>';
			}

			return wp_kses_post( $clean_content ) . $godam_videos;
		},
		10
	);

	/**
	 * Handle AJAX request for loading a single activity comment's HTML.
	 */
	add_action( 'wp_ajax_get_single_activity_comment_html', 'handle_get_single_activity_comment_html' );
	add_action( 'wp_ajax_nopriv_get_single_activity_comment_html', 'handle_get_single_activity_comment_html' );

	/**
	 * AJAX handler to fetch and return the HTML for a single activity comment.
	 *
	 * Validates the request, loads the activity comment by ID,
	 * renders its HTML using the BuddyPress template, and returns it in a JSON response.
	 *
	 * @return void Outputs JSON response with rendered HTML or error message.
	 */
	function handle_get_single_activity_comment_html() {
		check_ajax_referer( 'godam-ajax-nonce', 'nonce' );

		$activity_id = isset( $_POST['comment_id'] ) ? intval( $_POST['comment_id'] ) : 0;

		if ( ! $activity_id ) {
			wp_send_json_error( 'Invalid activity ID' );
		}

		$activity = new BP_Activity_Activity( $activity_id );
		if ( empty( $activity->id ) ) {
			wp_send_json_error( 'Activity comment not found' );
		}

		global $activities_template;

		// Backup original activity.
		$original_activity = $activities_template->activity ?? null;

		// Replace global for template rendering.
		$activities_template             = new stdClass();
		$activities_template->activity  = $activity;

		ob_start();
		bp_get_template_part( 'activity/entry' );
		$html = ob_get_clean();

		// Restore original.
		if ( $original_activity ) {
			$activities_template->activity = $original_activity;
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

}

/**
 * Enqueue the Magnific Popup script for rtMedia.
 *
 * This function ensures that the Magnific Popup script is loaded correctly on the frontend
 * so that popup functionality works seamlessly with all combinations of plugin states:
 * - When only rtMedia is active
 * - When both rtMedia and Godam plugins are active
 * - When Godam plugin is deactivated
 *
 * To achieve this, the script is deregistered first if already registered or enqueued,
 * preventing conflicts or duplicates.
 *
 * When Godam plugin is active, the script is loaded without dependencies to avoid
 * redundant or conflicting scripts. When Godam is not active, dependencies such as
 * jQuery and rt-mediaelement-wp are included to ensure proper functionality.
 *
 * Enqueuing here guarantees consistent script loading regardless of Godam’s activation status.
 */
function enqueue_rtmedia_magnific_popup_script() {
	$handle     = 'rtmedia-magnific-popup';
	$script_src = RTMEDIA_URL . 'app/assets/js/vendors/magnific-popup.js';
	$version    = RTMEDIA_VERSION;
	$in_footer  = true;

	// Deregister the script if already registered or enqueued to prevent conflicts.
	if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
		wp_deregister_script( $handle );
	}

	// Determine dependencies based on whether Godam integration is active.
	$dependencies = array();

	// If Godam plugin is NOT active, add dependencies for jQuery and mediaelement.
	if ( ! defined( 'RTMEDIA_GODAM_ACTIVE' ) || ! RTMEDIA_GODAM_ACTIVE ) {
		$dependencies = array( 'jquery', 'rt-mediaelement-wp' );
	}

	// Enqueue the Magnific Popup script with the appropriate dependencies.
	wp_enqueue_script( $handle, $script_src, $dependencies, $version, $in_footer );
}

add_action( 'wp_enqueue_scripts', 'enqueue_rtmedia_magnific_popup_script', 20 );
