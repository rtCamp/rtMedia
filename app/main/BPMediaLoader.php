<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * BuddyPress Media Loader
 *
 * Hook into BuddyPress, so we can load BuddyPress Media.
 * Called by BuddyPressMedia on intialisation.
 *
 * @package BuddyPressMedia
 * @subpackage Main
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 */
class BPMediaLoader {

	/**
	 * Initialises BuddyPress Media's functionality. Hooks into BuddyPress
	 *
	 * Hooks into bp_loaded to load itself
	 * Hooks into bp_setup_nav to add tabs to the profile and group navigation
	 * Hooks into after_setup_theme to add its thumbnail sizes
	 *
	 * @uses bp_loaded
	 * @uses bp_setup_nav
	 * @uses after_setup_theme
	 * @global object $bp_media
	 */
	public function __construct() {
		global $bp_media;
		//$options = $bp_media->options;

		add_action( 'bp_loaded', array( $this, 'load_component' ) );
		add_action( 'bp_setup_nav', array( $this, 'custom_nav' ), 999 );
		//if ( array_key_exists( 'enable_on_profile', $options ) ) {
		//	if ( $options[ 'enable_on_profile' ] ) {
				// This is where the add actions should move
				// after some refactoring,
				// so it is loaded on profiles, only when the admin specifies
		//	}
		//}

		add_action( 'after_setup_theme', array( $this, 'thumbnail' ) );
	}

	/**
	 * Load the BPMedia Component as an component of BuddyPress
	 * Add it to the BuddyPress global object
	 *
	 * @global object $bp BuddyPress object
	 */

	/**
	 *
	 * @global object $bp
	 */
	public function load_component() {
		global $bp;
		$bp->{BP_MEDIA_SLUG} = new BPMediaComponent();


	}

	/**
	 * Navigation Loader
	 *
	 * Loads BuddyPress Media's navigation
	 *
	 * @global object $bp BuddyPress object
	 */

	public function custom_nav() {
		global $bp;
		foreach ( $bp->bp_nav as $key => $nav_item ) {
			switch ( $nav_item[ 'slug' ] ) {
				case BP_MEDIA_IMAGES_SLUG:
				case BP_MEDIA_VIDEOS_SLUG:
				case BP_MEDIA_AUDIO_SLUG:
				case BP_MEDIA_ALBUMS_SLUG:
					$bp->bp_options_nav[ BP_MEDIA_SLUG ][ ] = array(
						'name' => $nav_item[ 'name' ],
						'link' => (
							isset( $bp->displayed_user->domain ) ?
							$bp->displayed_user->domain
							: (
									isset( $bp->loggedin_user->domain ) ?
									$bp->loggedin_user->domain
									: ''
								)
						)
						. $nav_item[ 'slug' ]
						. '/',
						'slug' => $nav_item[ 'slug' ],
						'css_id' => $nav_item[ 'css_id' ],
						'position' => $nav_item[ 'position' ],
						'screen_function' => $nav_item[ 'screen_function' ],
						'user_has_access' => true,
						'parent_url' => trailingslashit(
								bp_displayed_user_domain()
								)
					);
					unset( $bp->bp_nav[ $key ] );
					break;
				case BP_MEDIA_UPLOAD_SLUG:
					$bp->bp_options_nav[ BP_MEDIA_SLUG ][ ] = array(
						'name' => $nav_item[ 'name' ],
						'link' => (
							isset( $bp->displayed_user->domain ) ?
								$bp->displayed_user->domain
								: (
										isset( $bp->loggedin_user->domain ) ?
										$bp->loggedin_user->domain
										: ''
									)
							)
						. $nav_item[ 'slug' ]
						. '/',
						'slug' => $nav_item[ 'slug' ],
						'css_id' => $nav_item[ 'css_id' ],
						'position' => $nav_item[ 'position' ],
						'screen_function' => $nav_item[ 'screen_function' ],
						'user_has_access' => bp_is_my_profile(),
						'parent_url' => trailingslashit(
								bp_displayed_user_domain()
								)
					);
					unset( $bp->bp_nav[ $key ] );
			}
			switch ( $bp->current_component ) {
				case BP_MEDIA_IMAGES_SLUG:
				case BP_MEDIA_VIDEOS_SLUG:
				case BP_MEDIA_AUDIO_SLUG:
				case BP_MEDIA_ALBUMS_SLUG:
				case BP_MEDIA_UPLOAD_SLUG:
					$count = count( $bp->action_variables );
					for ( $i = $count; $i > 0; $i --  ) {
						$bp->action_variables[ $i ]
								= $bp->action_variables[ $i - 1 ];
					}
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = $bp->current_component;
					$bp->current_component = BP_MEDIA_SLUG;
			}
		}
	}

	/**
	 * Add image sizes required by the plugin to existing WordPress sizes.
	 * This can be filtered
	 *
	 * @global object $bp_media
	 */
	public function thumbnail() {
		global $bp_media;

		$default_sizes = $bp_media->media_sizes();

		add_image_size(
				'bp_media_activity_image',
				$default_sizes[ 'activity_image' ][ 'width' ],
				$default_sizes[ 'activity_image' ][ 'height' ],
				true
				);
		add_image_size(
				'bp_media_single_image',
				$default_sizes[ 'single_image' ][ 'width' ],
				$default_sizes[ 'single_image' ][ 'height' ],
				true
				);
	}

}

?>
