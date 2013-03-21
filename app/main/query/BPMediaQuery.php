<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Handles all queries for Media
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 */
class BPMediaQuery {

	function init( $type = false, $album_id = false, $count = false, $page = false, $docount = false ) {
		$args = $this->prepare_args( $type, $album_id, $page, $docount, $count );
		return $this->return_result( $args, $docount );
	}

	function privacy_query() {
		$privacy = BPMediaPrivacy::current_access();
		return $meta_query = array(
			'key' => 'bp_media_privacy',
			'value' => $privacy,
			'compare' => '<=',
			'type' => 'NUMERIC'
		);
	}

	function group_query( $group = false ) {
		global $bp;
		$group_id = null;

		if ( $group == false ) {
			if ( isset( $bp->displayed_user->id ) ) {
				$group_id = $bp->displayed_user->id;
			}
		} else {
			if ( ! class_exists( 'BPMediaGroupsExtension' ) )
				return array( );
			$group_id = -bp_get_current_group_id();
		}
		return $meta_query = array(
			'key' => 'bp-media-key',
			'value' => $group_id,
		);
	}

	function prepare_meta_query() {
		if ( bp_is_active('groups') && class_exists( 'BP_Group_Extension' ) ) {
			$group = bp_is_current_component( 'groups' );
			$meta_query[ ] = $this->group_query( $group );
		}
		if ( ! bp_is_groups_component() ) {
			$meta_query[ ] = $this->privacy_query();
		}
		return $meta_query;
	}

	function prepare_args( $type = false, $album_id = false, $page = false, $docount = false, $count = false ) {

		global $bp, $bp_media;

		$enabled = $bp_media->enabled();

		unset( $enabled[ 'upload' ] );

		if ( $type != false ) {
			if ( ! array_key_exists( $type, $enabled ) )
				return;
		}


		$post_type = $this->prepare_post_type( $type );

		$mime_type = $this->prepare_mime_type( $type );
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'any',
			'post_mime_type' => $mime_type,
			'meta_query' => $this->prepare_meta_query(),
			'posts_per_page' => -1
		);
		if ( $album_id ) {
			$args[ 'post_parent' ] = $album_id;
		}
		if ( ! bp_is_groups_component() ) {
			if ( isset( $bp->displayed_user->id ) ) {
				$args[ 'author' ] = $bp->displayed_user->id;
			}
		}

		if ( $docount == false ) {
			if ( $count == false ) {
				$count = $bp_media->default_count();
			}
			$is_album=false;
			if($type=='album'){
				$is_album = true;
			}
			$limit_offset = $this->get_limit_offset( $count, $this->prepare_pagination( $page ),$is_album );
			$args[ 'posts_per_page' ] = $limit_offset[ 0 ];
			$args[ 'offset' ] = $limit_offset[ 1 ];
		}

		return $args;
	}

	function get_limit_offset( $limit, $page,$album=false ) {
		global $bp;
		$my_profile = false;
		if ( bp_is_active('groups') && class_exists( 'BP_Group_Extension' ) ) {
				if ( bp_get_current_group_id() == 0 ) {
					$my_profile = true;
				}
			if ( groups_is_user_member( $bp->loggedin_user->id, bp_get_current_group_id() ) ) {
				$my_profile = true;
			}
		}else{
			if ( bp_is_my_profile() ) {
				$my_profile = true;
			}
		}
                $offset = $limit * ($page - 1);
		return array( $limit, $offset );
	}

	function prepare_post_type( $type ) {
		$post_type = 'attachment';
		if ( $type == 'album' ) {
			$post_type = 'bp_media_album';
		}
		return $post_type;
	}

	function prepare_mime_type( $type ) {
		global $bp_media;
		$enabled = $bp_media->enabled();
		if ( $type == '' ) {
			unset( $enabled[ 'album' ] );
			unset( $enabled[ 'upload' ] );
			foreach ( $enabled as $type => $active ) {
				if ( $active ) {
					$mime_type[ ] = $type;
				}
			}
		} elseif ( $type == 'album' ) {
			$mime_type = '';
		} else {
			$mime_type = $type;
		}
		return $mime_type;
	}

	function prepare_pagination( $page ) {
		global $bp;
		if ( isset( $bp->action_variables ) && is_array( $bp->action_variables ) && isset( $bp->action_variables[ 0 ] ) && $bp->action_variables[ 0 ] == 'page' && isset( $bp->action_variables[ 1 ] ) && is_numeric( $bp->action_variables[ 1 ] ) ) {
			$paged = $bp->action_variables[ 1 ];
		} else {
			$paged = ($page) ? $page : 1;
		}
		return $paged;
	}

	function return_result( $args, $count ) {
		if ( $count == false ) {
			return $args;
		} else {
			return $this->get_count( $args );
		}
	}

	function query( $args ) {
		$query = new WP_Query( $args );
		return $query;
	}

	function get_count( $args ) {
		$query = $this->query( $args );
		return $query->found_posts();
	}

}

?>
