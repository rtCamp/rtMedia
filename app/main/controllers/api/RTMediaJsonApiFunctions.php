<?php
/**
 * API function to handle API requests for data.
 *
 * @package rtMedia
 * @author Umesh Kumar<umeshsingla05@gmail.com>
 */

/**
 * Class to handle API requests for data.
 */
class RTMediaJsonApiFunctions {

	/**
	 * RTMediaJsonApiFunctions constructor.
	 */
	public function __construct() {}

	/**
	 * Generates a user token for user login
	 *
	 * @param int    $user_id User id to generate token.
	 * @param string $user_login User login to generate token.
	 *
	 * @return bool|string
	 */
	public function rtmedia_api_get_user_token( $user_id, $user_login ) {
		if ( empty( $user_id ) || empty( $user_login ) ) {
			return false;
		}
		$string = '08~' . $user_id . '~' . $user_login . '~kumar';

		return sha1( $string . current_time( 'timestamp' ) . wp_rand( 1, 9 ) );
	}

	/**
	 * User data from user id
	 *
	 * @param int    $user_id User id to get details.
	 * @param int    $width Width of avatar.
	 * @param int    $height Height of avatar.
	 * @param string $type Avatar type.
	 *
	 * @return array|bool
	 */
	public function rtmedia_api_user_data_from_id( $user_id, $width = 80, $height = 80, $type = 'thumb' ) {
		if ( empty( $user_id ) ) {
			return false;
		}
		$user_data         = array();
		$user_data['id']   = $user_id;
		$user_data['name'] = xprofile_get_field_data( 'Name', $user_id );

		$avatar_args         = array(
			'item_id' => $user_id,
			'width'   => $width,
			'height'  => $height,
			'html'    => false,
			'alt'     => '',
			'type'    => $type,
		);
		$user_data['avatar'] = bp_core_fetch_avatar( $avatar_args );

		return $user_data;
	}

	/**
	 * Media details from media id
	 *
	 * @param array $media Array consisting media id to get details.
	 *
	 * @return array|bool
	 */
	public function rtmedia_api_media_data_from_object( $media ) {
		if ( empty( $media ) ) {
			return false;
		}
		$media_data                  = array();
		$media_data['id']            = $media['id'];
		$media_data['src']           = rtmedia_image( 'rt_media_activity_image', $media['id'], false );
		$media_data['title']         = $media['media_title'];
		$media_data['comment_count'] = bp_activity_get_comment_count();

		return $media_data;
	}

	/**
	 * Function to Validate token
	 *
	 * @param string $token Token to get validated.
	 *
	 * @return array|bool
	 */
	public function rtmedia_api_validate_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		if ( class_exists( 'RTMediaApiLogin' ) ) {
			$rtmediaapilogin = new RTMediaApiLogin();
			$columns         = array(
				'token' => $token,
			);
			$token_data      = $rtmediaapilogin->get( $columns );
			if ( empty( $token_data ) || 'FALSE' === $token_data[0]->status ) {
				return false;
			}

			return $token_data;
		} else {
			return false;
		}
	}

	/**
	 * Get user id from token
	 *
	 * @param string $token Token to get user.
	 *
	 * @return bool
	 */
	public function rtmedia_api_get_user_id_from_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}
		$token_data = $this->rtmedia_api_validate_token( $token );

		return $token_data[0]->user_id;
	}

	/**
	 * Token processing for all data fetch/post requests
	 */
	public function rtmedia_api_verfiy_token() {
		$rtmjsonapi = new RTMediaJsonApi();
		$token      = sanitize_text_field( filter_input( INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

		if ( empty( $token ) ) {
			wp_send_json( $rtmjsonapi->rtmedia_api_response_object( 'FALSE', $rtmjsonapi->ec_token_missing, $rtmjsonapi->msg_token_missing ) );
		}

		// Validate token.
		$token_valid = $this->rtmedia_api_validate_token( $token );

		if ( ! $token_valid ) {
			wp_send_json( $rtmjsonapi->rtmedia_api_response_object( 'FALSE', $rtmjsonapi->ec_token_invalid, $rtmjsonapi->msg_token_invalid ) );
		}
	}

	/**
	 * Function to send response when activity and media id is missing.
	 */
	public function rtmedia_api_media_activity_id_missing() {
		$rtmjsonapi  = new RTMediaJsonApi();
		$activity_id = filter_input( INPUT_POST, 'activity_id', FILTER_SANITIZE_NUMBER_INT );
		$media_id    = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $activity_id ) && empty( $media_id ) ) {
			wp_send_json( $rtmjsonapi->rtmedia_api_response_object( 'FALSE', $rtmjsonapi->ec_media_activity_id_missing, $rtmjsonapi->msg_media_activity_id_missing ) );
		}
	}

	/**
	 * Function to get activity from media.
	 *
	 * @param int $media_id Media id to get activity.
	 *
	 * @return bool
	 */
	public function rtmedia_api_activityid_from_mediaid( $media_id ) {
		$rtmjsonapi = new RTMediaJsonApi();

		if ( empty( $media_id ) ) {
			return false;
		}

		$media_model = new RTMediaModel();
		$result      = $media_model->get( array( 'id' => $media_id ) );

		if ( empty( $result ) ) {
			wp_send_json( $rtmjsonapi->rtmedia_api_response_object( 'FALSE', $rtmjsonapi->ec_invalid_media_id, $rtmjsonapi->msg_invalid_media_id ) );
		}

		return $result[0]->activity_id;
	}

	/**
	 * Get followers of the user.
	 *
	 * @param int $user_id User ID to get followers of user.
	 *
	 * @return bool
	 */
	public function rtmedia_api_followers( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$followers = bp_follow_get_followers( array( 'user_id' => $user_id ) );

		return $followers;
	}

	/**
	 * Get users which provided user follows.
	 *
	 * @param int $user_id User ID to get following users.
	 *
	 * @return bool
	 */
	public function rtmedia_api_following( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}
		$followers = bp_follow_get_following( array( 'user_id' => $user_id ) );

		return $followers;
	}

	/**
	 * Accepts a rtmedia media object and returns a array of media details
	 *
	 * @param array $media_list Media array to get details.
	 *
	 * @return array|bool
	 */
	public function rtmedia_api_media_details( $media_list ) {
		global $rtmediajsonapi;

		if ( empty( $media_list ) ) {
			return false;
		}

		$result = array();

		if ( is_array( $media_list ) ) {
			foreach ( $media_list as $media ) {
				// Media likes.
				$rtmediainteraction = new RTMediaInteractionModel();
				$action             = 'like';
				$results            = $rtmediainteraction->get_row( $rtmediajsonapi->user_id, $media['id'], $action );
				$row                = ! empty( $results ) ? $results[0] : '';
				$current_user       = ( ! empty( $row ) && 1 === $row->value ) ? 'TRUE' : 'FALSE';

				$result[] = array(
					'id'           => $media['id'],
					'title'        => $media['media_title'],
					'src'          => rtmedia_image( 'rt_media_activity_image', $media['id'], false ),
					'likes'        => $media['likes'],
					'current_user' => $current_user,

				);
			}
		}

		return $result;
	}

	/**
	 * Fetches Activity for rtmedia updates, if user id for activity is provided fetches the user specific rtmedia updates
	 *
	 * @global type $activities_template
	 *
	 * @param bool $activity_user_id User id to get activity.
	 * @param bool $activity_id Activity ID to get updates.
	 * @param int  $per_page Per page count for activity update feed.
	 *
	 * @return array
	 */
	public function rtmedia_api_get_feed( $activity_user_id = false, $activity_id = false, $per_page = 10 ) {
		global $activities_template, $rtmediajsonapi;

		$activity_feed = array();
		$page          = filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE );
		$i             = 0;
		$args          = array(
			'user_id'  => $activity_user_id,
			'action'   => '', /* or rtmedia_update for fetching only rtmedia updates */
			'page'     => ! empty( $page ) ? $page : 1,
			'per_page' => $per_page,
			'in'       => $activity_id,
		);

		if ( bp_has_activities( $args ) ) :

			$activity_feed['total_activity_count'] = $activities_template->total_activity_count;
			$activity_feed['total']                = ceil( (int) $activities_template->total_activity_count / (int) $activities_template->pag_num );
			$activity_feed['current']              = $activities_template->pag_page;

			while ( bp_activities() ) :
				bp_the_activity();

				// Activity basic details.
				$activity_feed[ $i ]['id']                  = $activities_template->activity->id;
				$activity_feed[ $i ]['activity_type']       = $activities_template->activity->type;
				$activity_feed[ $i ]['activity_time']       = bp_get_activity_date_recorded();
				$activity_feed[ $i ]['activity_time_human'] = wp_strip_all_tags( bp_insert_activity_meta( '' ) );
				$activity_feed[ $i ]['activity_content']    = $activities_template->activity->content;

				// activity User.
				if ( ! $activity_user_id ) {
					// Activity User data.
					$activity_feed[ $i ]['user'] = $this->rtmedia_api_user_data_from_id( bp_get_activity_user_id() );
				}

				// Media Details.
				if ( class_exists( 'RTMediaModel' ) ) {
					$model = new RTMediaModel();
					$media = $model->get_by_activity_id( $activities_template->activity->id );

					if ( isset( $media['result'] ) && count( $media['result'] ) > 0 ) {
						// Create media array.
						$media = $this->rtmedia_api_media_details( $media['result'] );
					} else {
						$media = false;
					}
				}

				if ( $activity_id ) {
					// Activity Comment Count.
					$id                              = $media[0]['id'];
					$activity_feed[ $i ]['comments'] = $this->rtmedia_api_get_media_comments( $id );
				}
				// Activity Image.
				$activity_feed[ $i ]['media'] = $media;
				$i++;
			endwhile;
		endif;

		return $activity_feed;
	}

	/**
	 * Function to get media comments.
	 *
	 * @param int $media_id Media id to get comments.
	 *
	 * @return array
	 */
	public function rtmedia_api_get_media_comments( $media_id ) {
		global $wpdb;
		$rtmjsonapi = new RTMediaJsonApi();
		$id         = rtmedia_media_id( $media_id );

		if ( empty( $id ) ) {
			wp_send_json( $rtmjsonapi->rtmedia_api_response_object( 'FALSE', $rtmjsonapi->ec_invalid_media_id, $rtmjsonapi->msg_invalid_media_id ) );
		}

		$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = %d limit 100", $id ), ARRAY_A );

		$media_comments = array();
		if ( ! empty( $comments ) ) {

			foreach ( $comments as $comment ) {
				$media_comments['comments'][] = array(
					'comment_ID'      => $comment['comment_ID'],
					'comment_content' => $comment['comment_content'],
					'user_id'         => $comment['user_id'],
				);

				if ( ! array_key_exists( $comment['user_id'], $media_comments['user'] ) ) {

					$user_data = $this->rtmedia_api_user_data_from_id( $comment['user_id'] );

					$media_comments['user'][ $comment['user_id'] ] = array(
						'name'   => $user_data['name'],
						'avatar' => $user_data['avatar'],
					);
				}
			}
		}

		return $media_comments;
	}

	/**
	 * Get users who liked particular media.
	 *
	 * @param int $media_id Media id to get users who liked it.
	 *
	 * @return array
	 */
	public function rtmedia_api_media_liked_by_user( $media_id ) {
		$rtmedia_interaction_model = new RTMediaInteractionModel();
		$media_like_cols           = array(
			'media_id' => $media_id,
			'action'   => 'like',
			'value'    => 1,
		);

		$liked_by = $rtmedia_interaction_model->get( $media_like_cols, false, false, 'action_date' );

		return $liked_by;
	}

	/**
	 * Function to get details of media in album.
	 *
	 * @param int $album_id Album id to get details.
	 *
	 * @return array|bool
	 */
	public function rtmedia_api_album_media( $album_id ) {
		if ( empty( $album_id ) ) {
			return false;
		}

		$rtmediamodel = new RTMediaModel();
		$args         = array(
			'album_id' => $album_id,
		);
		$media_list   = $rtmediamodel->get( $args );
		$media_data   = array();

		if ( ! empty( $media_list ) && is_array( $media_list ) ) {

			foreach ( $media_list as $media ) {
				$media_data[] = array(
					'id'           => $media->id,
					'media_title'  => $media->media_title,
					'media_url'    => get_rtmedia_permalink( $media->media_id ),
					'media_author' => $media->media_author,
					'cover'        => rtmedia_image( 'rt_media_thumbnail', $media->media_id, false ),
				);
			}
		}

		return $media_data;
	}

	/**
	 * Function to return user id from json API object.
	 *
	 * @return mixed
	 */
	public function rtmedia_api_set_user_id() {
		global $rtmediajsonapi;

		return $rtmediajsonapi->user_id;
	}
}
