<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add BuddyPress Media as a component of BuddyPress
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 */
class BPMediaComponent extends BP_Component {

	/**
	 * Hold the messages that are generated during initialization process
	 * and will be shown on the screen functions
	 *
	 * @var array
	 */
	var $messages = array(
		'error' => array( ),
		'info' => array( ),
		'updated' => array( )
	);

	/**
	 * Initialise the component with appropriate parameters.
	 * Add hook for plugins, themes, extensions to hook on to
	 * Activates the component
	 * Registers necessary post types
	 *
	 * @global object $bp The global BuddyPress object
	 */
	function __construct() {
		global $bp;
		parent::start( BP_MEDIA_SLUG, BP_MEDIA_LABEL, BP_MEDIA_PATH );
		do_action( 'bp_media_init' );
		$bp->active_components[ $this->id ] = '1';
		add_action( 'init', array( &$this, 'register_post_types' ), 10 );
	}

	/**
	 * Initialise the global variables of the BuddyPress Media
	 * and its parent class.
	 * Add necessary slugs and search functionality
	 *
	 * @global object $bp The global BuddyPress object
	 */
	function setup_globals() {
		global $bp;
		$globals = array(
			'slug' => BP_MEDIA_SLUG,
			'root_slug' => isset(
					$bp->pages->{$this->id}->slug ) ?
					$bp->pages->{$this->id}->slug : BP_MEDIA_SLUG,
			// 'has_directory'         => true, // Set to false if not required
			'search_string' => __( 'Search Media...', BP_MEDIA_TXT_DOMAIN ),
		);
		parent::setup_globals( $globals );
	}

	/**
	 * Sets up BuddyPress Media navigation and tabs on profile
	 *
	 * @global object $bp The global BuddyPress object
	 */
	function setup_nav() {
		global $bp, $bp_media;

		$enabled = $bp_media->enabled();
		$default_tab = $bp_media->default_tab();
		$defaults_tab = $bp_media->defaults_tab();

		/* Upload Screen */


		/* Media Screens */

		foreach ( $enabled as $tab => $active ) {
			if ( $active == true ) {
				$tabs = $tab;
				if ( $tabs != 'audio' && $tabs != 'upload' ) {
					$tabs .= 's';
				}
				if ( $tab == 'upload' ) {
					${'bp_media_' . $tab} = new BPMediaUploadScreen(
									$tab,
									constant( 'BP_MEDIA_' . strtoupper( $tabs ) . '_SLUG' )
					);
				} elseif ( $tab == 'album' ) {
					$bp_media_album = new BPMediaAlbumScreen(
									$tab,
									constant( 'BP_MEDIA_' . strtoupper( $tabs ) . '_SLUG' )
					);
				} else {
					${'bp_media_' . $tab} = new BPMediaScreen(
									$tab,
									constant( 'BP_MEDIA_' . strtoupper( $tabs ) . '_SLUG' )
					);
				}
			}
		}

			if(array_key_exists('privacy_override_enabled',$bp_media->options))
					if(checked($bp_media->options['privacy_override_enabled'],true,false))
				$bp_media_privacy_screen = new BPMediaPrivacyScreen();

		/* Switch between different screens depending on context */
		switch ( $bp->current_component ) {
			case BP_MEDIA_IMAGES_SLUG:
				if ( $enabled[ 'image' ] && is_numeric( $bp->current_action ) ) {
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = BP_MEDIA_IMAGES_VIEW_SLUG;
				}
				break;
			case BP_MEDIA_AUDIO_SLUG:
				if ( $enabled[ 'audio' ] && is_numeric( $bp->current_action ) ) {
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = BP_MEDIA_AUDIO_VIEW_SLUG;
				}
				break;
			case BP_MEDIA_VIDEOS_SLUG:
				if ( $enabled[ 'video' ] && is_numeric( $bp->current_action ) ) {
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = BP_MEDIA_VIDEOS_VIEW_SLUG;
				}
				break;
			case BP_MEDIA_ALBUMS_SLUG:
				if ( is_numeric( $bp->current_action ) ) {
					$bp->action_variables[ 0 ] = $bp->current_action;
					$bp->current_action = BP_MEDIA_ALBUMS_VIEW_SLUG;
				}
				break;
		}

		/* Create the main navigation on profile */
		$main_nav = array(
			'name' => __( BP_MEDIA_LABEL, BP_MEDIA_TXT_DOMAIN ),
			'slug' => BP_MEDIA_SLUG,
			'position' => 80,
			'screen_function' => array( ${'bp_media_' . $default_tab}, 'screen' ),
			'default_subnav_slug' => constant( 'BP_MEDIA_' . strtoupper( $defaults_tab ) . '_SLUG' )
		);

		/* Create  an empty sub navigation */
		$sub_nav[ ] = array( );

		/* Set up navigation */
		parent::setup_nav( $main_nav, $sub_nav );
		/* Set up individual screens for each nav/sub nav */
		foreach ( $enabled as $type => $status ) {
			if($status){
			$screen_array[ ] = array(
				'type' => $type,
				'status' => $status,
				'subscreens' => true
			);
			}
		}
		$screen_array = apply_filters( 'bpmedia_media_screens', $screen_array );
		foreach ( $screen_array as $screen ) {
			$type = $screen[ 'type' ];
			$status = $screen[ 'status' ];
			$subscreens = $screen[ 'subscreens' ];
			if ( $type != 'upload' ) {
				$types = $type;
				if ( $type != 'audio' ) {
					$types .='s';
				}
				bp_core_new_nav_item( array(
					'name' => __( constant( 'BP_MEDIA_' . strtoupper( $types ) . '_LABEL' ), BP_MEDIA_TXT_DOMAIN ),
					'slug' => constant( 'BP_MEDIA_' . strtoupper( $types ) . '_SLUG' ),
					'screen_function' => array( ${'bp_media_' . $type}, 'screen' ),
				) );
				if ( $subscreens == true ) {
					$subscreens_array = array(
						'view',
						'edit',
						'delete',
						'page'
					);
					$subscreens_array = apply_filters( 'bpmedia_media_type_subscreens', $subscreens_array );

					foreach ( $subscreens_array as $subscreen ) {
						$screen_to_load = 'screen';
						if ( $subscreen == 'edit' ) {
							$screen_to_load = $subscreen . '_' . $screen_to_load;
						}
						$slug = 'BP_MEDIA_';
						if ( $subscreen != 'delete' ) {
							$slug .= strtoupper( $types ) . '_';
						}
						if ( $subscreen != 'page' ) {
							$slug .= strtoupper( $subscreen ) . '_';
						}
						$slug .= 'SLUG';

						bp_core_new_subnav_item( array(
							'name' => ucfirst( $subscreen ),
							/* Display name for the nav item(It won't be shown anywhere) */
							'slug' => constant( $slug ),
							/* URL slug for the nav item */
							'parent_slug' => constant( 'BP_MEDIA_' . strtoupper( $types ) . '_SLUG' ),
							/* URL slug of the parent nav item */
							'parent_url' => trailingslashit( bp_loggedin_user_domain()
									. constant( 'BP_MEDIA_' . strtoupper( $types ) . '_SLUG' ) ),
							/* URL of the parent item */
							'position' => 90,
							/* Index of where this nav item should be positioned */
							'screen_function' => array( ${'bp_media_' . $type}, $screen_to_load ),
								/* The name of the function to run when clicked */
						) );
					}
				}
			}
		}


		bp_core_new_nav_item( array(
			'name' => __( BP_MEDIA_UPLOAD_LABEL, BP_MEDIA_TXT_DOMAIN ),
			'slug' => BP_MEDIA_UPLOAD_SLUG,
			'screen_function' => array( $bp_media_upload, 'upload_screen' ),
			'user_has_access' => bp_is_my_profile()
		) );
		if(array_key_exists('privacy_override_enabled',$bp_media->options))
					if(checked($bp_media->options['privacy_override_enabled'],true,false))
				bp_core_new_subnav_item( array(
							'name' => __( BP_MEDIA_USER_SETTINGS_LABEL, BP_MEDIA_TXT_DOMAIN ),
							/* Display name for the nav item(It won't be shown anywhere) */
							'slug' => BP_MEDIA_USER_SETTINGS_SLUG,
							/* URL slug for the nav item */
							'parent_slug' => BP_MEDIA_SLUG,
							/* URL slug of the parent nav item */
							'parent_url' => trailingslashit( bp_loggedin_user_domain()
									. BP_MEDIA_SLUG),
							/* URL of the parent item */
							'position' => 900,
							/* Index of where this nav item should be positioned */
							'screen_function' => array( $bp_media_privacy_screen, 'ui' ),
								/* The name of the function to run when clicked */
								'user_has_access' => bp_is_my_profile()
						) );
	}

	/**
	 * Register Custom Post Types required by BuddyPress Media
	 */
	function register_post_types() {

		/* Set up Album labels */
		$album_labels = array(
			'name' => __( 'Albums', BP_MEDIA_TXT_DOMAIN ),
			'singular_name' => __( 'Album', BP_MEDIA_TXT_DOMAIN ),
			'add_new' => __( 'Create', BP_MEDIA_TXT_DOMAIN ),
			'add_new_item' => __( 'Create Album', BP_MEDIA_TXT_DOMAIN ),
			'edit_item' => __( 'Edit Album', BP_MEDIA_TXT_DOMAIN ),
			'new_item' => __( 'New Album', BP_MEDIA_TXT_DOMAIN ),
			'all_items' => __( 'All Albums', BP_MEDIA_TXT_DOMAIN ),
			'view_item' => __( 'View Album', BP_MEDIA_TXT_DOMAIN ),
			'search_items' => __( 'Search Albums', BP_MEDIA_TXT_DOMAIN ),
			'not_found' => __( 'No album found', BP_MEDIA_TXT_DOMAIN ),
			'not_found_in_trash' => __( 'No album found in Trash', BP_MEDIA_TXT_DOMAIN ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Albums', BP_MEDIA_TXT_DOMAIN )
		);

		/* Set up Album post type arguments */
		$album_args = array(
			'labels' => $album_labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(
				'title',
				'author',
				'thumbnail',
				'excerpt',
				'comments'
			)
		);

		/* register Album post type */
		register_post_type( 'bp_media_album', $album_args );


		/* Set up labels for Media post type */
		$labels = array(
			'name' => __( 'Media', BP_MEDIA_TXT_DOMAIN ),
			'singular' => __( 'Media', BP_MEDIA_TXT_DOMAIN ),
			'add_new' => __( 'Add New Media', BP_MEDIA_TXT_DOMAIN )
		);

		/* Set up the arguments for Media post type */
		$args = array(
			'label' => __( 'Media', BP_MEDIA_TXT_DOMAIN ),
			'labels' => $labels,
			'description' => __(
					'BuddyPress Media\'s Media Files', BP_MEDIA_TXT_DOMAIN
			),
			'public' => true,
			'show_ui' => false,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields'
			)
		);

		/* Register Media post type */
		register_post_type( 'bp_media', $args );

		/* Register parent's post types */
		parent::register_post_types();
	}

}

?>
