<?php
/**
 * Description of BPMediaActivity
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! class_exists( 'BPMediaActivity' ) ) {

	class BPMediaActivity {

		var $default_album_id;
		var $attachment_id = 0;
		var $content = '';
		var $media_count = 1;

		public function __construct() {
			global $bp_media;
			$options = $bp_media->options;
			if ( isset( $options[ 'activity_upload' ] ) && $options[ 'activity_upload' ] != 0 ) {
				add_action( 'bp_activity_post_form_options', array( $this, 'activity_uploader' ) );
				add_action( 'bp_groups_posted_update', array( $this, 'override_media_album_id' ), '', 4 );
				add_filter( 'bp_activity_new_update_content', array( $this, 'override_update' ) );
				add_filter( 'bp_activity_latest_update_content', array( $this, 'override_update' ) );
				add_filter( 'bp_get_member_latest_update', array( $this, 'latest_update' ) );
				add_filter( 'bp_get_activity_latest_update', array( $this, 'latest_update' ) );
				add_action( 'wp_ajax_bp_media_get_latest_activity', array( $this, 'latest_update' ) );
				add_filter( 'groups_activity_new_update_content', array( $this, 'override_update' ) );

				add_filter( 'bp_activity_allowed_tags', 'BPMediaFunction::override_allowed_tags', 1 );
				add_action( 'init', array( $this, 'scripts' ) );
				//remove_filter( 'bp_get_activity_content_body',          'wptexturize' );
				remove_filter( 'bp_get_activity_content', 'wptexturize' );
			}
		}

		public function scripts() {
			wp_enqueue_script( 'json2' );
		}

		public function activity_uploader() {
			?>
			<input type ="hidden" id="bp-media-update-text" />
			<input type ="hidden" id="bp-media-update-json" />
			<input type ="hidden" id="bp-media-latest-update" />
			<div id="bp-media-activity-upload-ui" class="hide-if-no-js drag-drop">
				<input id="bp-media-activity-upload-browse-button" type="button" value="<?php _e( 'Attach Media', BP_MEDIA_TXT_DOMAIN ); ?>" class="button" />
				<div id="bp-media-activity-uploaded-files"></div>
			</div>
			<?php
		}

		public function decode( $content ) {
			$content = stripslashes( $content );
			$activity_json = json_decode( $content, true );
			return $activity_json;
		}

		public function get_media( $content ) {
			$activity_json = $this->decode( $content );
			$activity_media = json_decode( $activity_json[ 'media' ], true );
			return $activity_media;
		}

		public function get_text( $content ) {
			$activity_json = $this->decode( $content );
			$activity_text = rawurldecode($activity_json[ 'update_txt' ]);
			return $activity_text;
		}

		public function latest_update( $content ) {
			global $bp;
			if ( isset( $_GET[ 'content' ] ) ) {
				$update_id = $_GET[ 'id' ];
				$content = $_GET[ 'content' ];
			} else {
				if ( bp_displayed_user_id() )
					$user_id = bp_displayed_user_id();
				else
					$user_id = bp_get_member_user_id();
				if ( ! $update = bp_get_user_meta( $user_id, 'bp_latest_update', true ) )
					return $content;
				$update_id = $update[ 'id' ];
				$content = $update[ 'content' ];
			}
			//$activity_id = $update[''];
			$activity_media = $this->get_media( $content );
			$newcontent = $this->get_text( $content );
			if ( isset( $activity_media ) ) {
				if ( ! is_array( $activity_media ) ) {
					$activity_media[ ] = $activity_media;
				}
				$media_id = $activity_media[ count( $activity_media ) - 1 ];
				try {
					$media = new BPMediaHostWordpress( $media_id );
					$newcontent .= '<a href="' . $media->get_url() . '">
					<img src="' . $media->get_media_thumbnail() . '"/>
						</a>';
				} catch ( Exception $e ) {
					echo $e->getMessage();
				}
			}

			$newcontent .= ' <a href="' . bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/p/' . $update_id . '/"> ' . __( 'View', 'buddypress' ) . '</a>';
			if ( isset( $_GET[ 'content' ] ) ) {
				echo $newcontent;
				die;
			} else {
				return $newcontent;
			}
		}

		public function override_update( $content ) {
			$this->content = $content;
			$activity_media = $this->get_media( $content );
			$newcontent = '<p>' . $this->get_text( $content ) . '</p>';
			if ( isset( $activity_media ) ) {
				if ( ! is_array( $activity_media ) ) {
					$activity_media[ ] = $activity_media;
				}

				if ( count( $activity_media ) > 1 ) {
					$newcontent .= '<ul class="bp-media-list-media">';
				}

				foreach ( $activity_media as $media_id ) {
					try {
						$media = new BPMediaHostWordpress( $media_id );
						if ( count( $activity_media ) > 1 ) {
							$newcontent .= $media->get_album_activity_content();
						} else {
							add_filter( 'bp_media_single_activity_title', create_function( '', 'return;' ) );
							$newcontent .= $media->get_media_activity_content();
						}
					} catch ( Exception $e ) {
						echo $e->getMessage();
					}
				}
				if ( count( $activity_media ) > 1 ) {
					$newcontent .= '</ul>';
				}
			}
			return $newcontent;
		}

		public function override_media_album_id( $content, $user_id, $group_id, $activity_id ) {
			global $bp;
			$activity_media = $this->get_media( $content );
			if ( isset( $activity_media ) ) {
				if ( ! is_array( $activity_media ) ) {
					$activity_media[ ] = $activity_media;
				}

				foreach ( $activity_media as $media_id ) {
					update_post_meta( $media_id, 'bp-media-key', -$group_id );
					$attachment = get_post( $media_id );
					$attachment->post_parent = groups_get_groupmeta( $group_id, 'bp_media_default_album' );
					wp_update_post( $attachment );
					$activity_id = groups_record_activity( array(
						'id' => $activity_id,
						'user_id' => $user_id,
						'action' => sprintf( __( '%1$s posted an update in the group %2$s', 'buddypress' ), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' ),
						'content' => $this->override_update( $this->content ),
						'type' => 'activity_update',
						'item_id' => $group_id
							) );
				}
			}
		}

	}

}
?>
