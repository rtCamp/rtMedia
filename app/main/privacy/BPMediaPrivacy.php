<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaPrivacy
 *
 * @author saurabh
 */
class BPMediaPrivacy {

	var $settings = array( );
	var $messages = array( );

	/**
	 *
	 */
	function __construct() {
		add_action( 'bp_media_after_update_media', array( $this, 'save_privacy' ), 99, 2 );
		add_action( 'bp_media_no_object_after_add_media', array( $this, 'save_privacy' ), 99, 2 );
		add_action( 'wp_ajax_bp_media_privacy_install', 'BPMediaPrivacy::install' );
		add_action( 'wp_ajax_bp_media_privacy_redirect', array( $this, 'set_option_redirect' ) );
		add_action( 'bp_has_activities', array( $this, 'activity' ), 10, 2 );
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		$this->settings = $this->get_settings();
		add_action( 'bp_media_add_media_fields', array( $this, 'ui' ) );
	}

	public function set_option_redirect() {
		bp_update_option( 'bp_media_privacy_installed', true );
		do_action( 'bp_media_after_privacy_install' );
		echo true;
		die();
	}

	static function is_enabled() {
		if ( ! BPMediaPrivacy::is_installed() )
			return false;
		global $bp_media;
		$options = $bp_media->options;
		if ( ! array_key_exists( 'privacy_enabled', $options ) ) {
			return false;
		} else {
			if ( $options[ 'privacy_enabled' ] != true ) {
				return false;
			}
		}
		return true;
	}

	static function is_installed() {
		$settings = new BPMediaPrivacySettings();
		$total = $settings->get_total_count();
		if ( is_array( $total ) && ! empty( $total ) ) {
			$total = $total[ 0 ]->Total;
		} else {
			$total = 0;
		}
		$finished = $settings->get_completed_count();
		if ( is_array( $finished ) && ! empty( $finished ) ) {
			$finished = $finished[ 0 ]->Finished;
		} else {
			$finished = 0;
		}
		if ( $total === $finished )
			$installed = true;
		else
			$installed = false;

		bp_update_option( 'bp_media_privacy_installed', $installed );
		return $installed;
	}

	static function get_site_default() {
		global $bp_media;
		$site_privacy = false;
		$options = $bp_media->options;
		if ( array_key_exists( 'privacy_enabled', $options ) ) {
			if ( array_key_exists( 'default_privacy_level', $options ) ) {
				$site_privacy = $options[ 'default_privacy_level' ];
			}
		}
		return $site_privacy;
	}

	static function default_privacy() {
		global $bp_media;
		$options = $bp_media->options;
		$default_privacy = false;
		$default_privacy = BPMediaPrivacy::get_site_default();
		if ( array_key_exists( 'privacy_override_enabled', $options ) ) {
			$user_default_privacy = BPMediaPrivacy::get_user_default();
			if ( $user_default_privacy !== false ) {
				$default_privacy = $user_default_privacy;
			}
		}
		return $default_privacy;
	}

	static function get_settings() {
		return array(
			6 => array(
				'private',
				__( '<strong>Private</strong>, Visible only to myself', BP_MEDIA_TXT_DOMAIN )
			),
			4 => array(
				'friends',
				__( '<strong>Friends</strong>, Visible to my friends', BP_MEDIA_TXT_DOMAIN )
			),
			2 => array(
				'users',
				__( '<strong>Users</strong>, Visible to registered users', BP_MEDIA_TXT_DOMAIN )
			),
			0 => array(
				'public',
				__( '<strong>Public</strong>, Visible to the world', BP_MEDIA_TXT_DOMAIN )
			)
		);
	}

	function ui() {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		global $bp_media_current_entry;
		$privacy_level = get_post_meta( $bp_media_current_entry->get_id(), 'bp_media_privacy', TRUE );
		BPMediaPrivacy::ui_html( $privacy_level );
	}

	static function ui_html( $privacy_level ) {
		?>
		<div id="bp-media-upload-privacy-wrap">
			<label for="bp-media-upload-set-privacy"><?php _e( 'Set default privacy levels for your media', BP_MEDIA_TXT_DOMAIN ); ?></label>
			<ul id="bp-media-upload-set-privacy">
				<?php
				$settings = BPMediaPrivacy::get_settings();
				foreach ( $settings as $level => &$msg ) {
					?>
					<li>
						<input type="radio" name="bp_media_privacy" class="set-privacy-radio" id="bp-media-privacy-<?php echo $msg[ 0 ]; ?>" value="<?php echo $level; ?>" <?php checked( $level, $privacy_level, TRUE ); ?> >
						<label class="album-set-privacy-label" for="bp-media-privacy-<?php echo $msg[ 0 ]; ?>"><?php echo $msg[ 1 ]; ?></label>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}

	function save_privacy( $object_id, $type ) {

		$level = isset( $_POST[ 'bp_media_privacy' ] ) ? $_POST[ 'bp_media_privacy' ] : false;
		if ( ! $level ) {
			$level = BPMediaPrivacy::default_privacy();
		}
		if(!$level){
			$level = '0';
		}
		$this->save( $level, $object_id );
		if ( $type == 'album' ) {
			$args = array(
				'post_type' => 'attachment',
				'post_parent' => $object_id,
				'post_status' => 'any',
				'posts_per_page' => -1
			);

			$child_query = new WP_Query( $args );
			$children = $child_query->posts;
			foreach ( $children as $child ) {
				$this->save( $level, $child->ID );
			}
		}
	}

	function save( $level = 0, $object_id = false ) {

		if ( ! array_key_exists( $level, $this->get_settings() ) )
			return false;


		return $this->save_by_object( $level, $object_id );
	}

	private function save_by_object( $level = 0, $object_id = false ) {
		if ( $object_id == false )
			return false;

		$level = apply_filters( 'bp_media_save_privacy', $level );

		return update_post_meta( $object_id, 'bp_media_privacy', $level );
	}

	function activity( $a, $activities ) {
		global $bp_media;

		foreach ( $activities->activities as $key => $activity ) {
			if ( $activity != null && in_array( $activity->type, $bp_media->activity_types ) ) {
				$has_access = BPMediaPrivacy::has_access( $activity->item_id );
				if ( ! $has_access ) {

					unset( $activities->activities[ $key ] );
					$activities->activity_count = $activities->activity_count - 1;
					$activities->total_activity_count = $activities->total_activity_count - 1;
					$activities->pag_num = $activities->pag_num - 1;
				}
			}
		}
		$activities_new = array_values( $activities->activities );
		$activities->activities = $activities_new;

		return $activities;
	}

	static function save_user_default( $level = 0, $user_id = false ) {
		if ( $user_id == false ) {
			global $bp;
			$user_id = $bp->loggedin_user->id;
		}
		return update_user_meta( $user_id, 'bp_media_privacy', $level );
	}

	static function get_user_default( $user_id = false ) {
		if ( $user_id == false ) {
			global $bp;
			$user_id = $bp->loggedin_user->id;
		}
		$user_privacy = get_user_meta( $user_id, 'bp_media_privacy', true );
		if ( $user_privacy === false ) {

		}
		return $user_privacy;
	}

	static function required_access( $object_id = false ) {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		if ( $object_id == false )
			return;
		$privacy = get_post_meta( $object_id, 'bp_media_privacy', true );
		$parent = get_post_field( 'post_parent', $object_id, 'raw' );
		$parent_privacy = get_post_meta( $parent, 'bp_media_privacy', true );

		if ( $privacy === false ) {
			if ( $parent_privacy !== false ) {
				$privacy = $parent_privacy;
			} else {
				$privacy = BPMediaPrivacy::default_privacy();
			}
		}
		return $privacy;
	}

	static function current_access() {
		global $bp;
		$current_privacy = 0;

		if ( is_user_logged_in() ) {
			$current_privacy = 2;
			if ( bp_is_my_profile() ) {
				$current_privacy = 6;
			}
			if ( isset( $bp->displayed_user->id ) )
				if ( ! (bp_is_my_profile()) ) {
					if ( bp_is_active('groups') &&  class_exists( 'BP_Group_Extension' ) ) {
						if ( bp_get_current_group_id() == 0 ) {
							$is_friend = friends_check_friendship_status( $bp->loggedin_user->id, $bp->displayed_user->id );
							if ( $is_friend == 'is_friend' ) {
								$current_privacy = 4;
							}
						}
					}
				}
		}

		return $current_privacy;
	}

	static function has_access( $object_id = false ) {
		$access = false;
		$current_access = BPMediaPrivacy::current_access();
		$required_access = BPMediaPrivacy::required_access( $object_id );
		if ( $current_access >= $required_access )
			$access = true;
		return $access;
	}

	static function get_messages( $media_type, $username ) {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		return array(
			6 => sprintf( __( 'This %s is private', BP_MEDIA_TXT_DOMAIN ), $media_type ),
			4 => sprintf( __( 'This %1s is visible only to %2s&rsquo;s friends', BP_MEDIA_TXT_DOMAIN ), $media_type, $username ),
			2 => sprintf( __( 'This %s is visible to logged in users, only', BP_MEDIA_TXT_DOMAIN ), $media_type ),
		);
	}

	static function install() {
		$page = $_POST[ 'page' ];
		($page) ? $page : 1;
		$args = array(
			'post_type' => array(
				'attachment',
				'bp_media_album'
			),
			'post_status' => 'any',
			'posts_per_page' => $_POST[ 'count' ],
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'bp-media-key',
					'compare' => 'EXISTS'
				),
				array(
					'key' => 'bp_media_privacy',
					'compare' => 'NOT EXISTS'
				),
			),
		);
		$all_media = new WP_Query( $args );
		foreach ( $all_media->posts as $media ) {
			update_post_meta( $media->ID, 'bp_media_privacy', 0 );
		}
		die( $page );
	}

}
?>
