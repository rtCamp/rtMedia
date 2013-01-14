<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of BPMediaLoader
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 */
class BPMediaLoader {

	public function __construct() {
		add_action( 'bp_loaded', array( $this, 'load_component' ) );
		add_action( 'bp_setup_nav', array( $this, 'custom_nav' ), 999 );
		add_action( 'after_setup_theme', array( $this, 'thumbnail' ) );
	}

	public function load_component() {
		global $bp;
		$bp->{BP_MEDIA_SLUG} = new BPMediaComponent();
	}

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
						'link' => (isset( $bp->displayed_user->domain ) ? $bp->displayed_user->domain : (isset( $bp->loggedin_user->domain ) ? $bp->loggedin_user->domain : '')) . $nav_item[ 'slug' ] . '/',
						'slug' => $nav_item[ 'slug' ],
						'css_id' => $nav_item[ 'css_id' ],
						'position' => $nav_item[ 'position' ],
						'screen_function' => $nav_item[ 'screen_function' ],
						'user_has_access' => true,
						'parent_url' => trailingslashit( bp_displayed_user_domain() )
					);
					unset( $bp->bp_nav[ $key ] );
			}
			switch ( $bp->current_component ) {
				case BP_MEDIA_IMAGES_SLUG:
				case BP_MEDIA_VIDEOS_SLUG:
				case BP_MEDIA_AUDIO_SLUG:
				case BP_MEDIA_ALBUMS_SLUG:
					$count = count( $bp->action_variables );
					for ( $i = $count; $i > 0; $i --  ) {
						$bp->action_variables[ $i ] = $bp->action_variables[ $i - 1 ];
					}
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = $bp->current_component;
					$bp->current_component = BP_MEDIA_SLUG;
			}
		}
	}

	public function thumbnail() {
		global $bp_media;

		$default_sizes = $bp_media->media_sizes();

		add_image_size( 'bp_media_activity_image', $default_sizes[ 'activity_image' ][ 'width' ], $default_sizes[ 'activity_image' ][ 'height' ], true );
		add_image_size( 'bp_media_single_image', $default_sizes[ 'single_image' ][ 'width' ], $default_sizes[ 'single_image' ][ 'height' ], true );
	}

}

?>
