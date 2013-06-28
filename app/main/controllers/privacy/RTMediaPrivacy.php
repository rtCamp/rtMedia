<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaPrivacy
 *
 * @author saurabh
 */
class RTMediaPrivacy {


	/**
	 *
	 * @var object default application wide privacy levels
	 */
	public $default_privacy;

	function __construct() {
		add_action('rt_media_after_file_upload_ui',array($this,'select_privacy_ui'));
	}

	function select_privacy_ui($attr) {
		if(!isset($attr['privacy'])) {
			$form = new rtForm();
			$attributes = array(
				'name' => 'privacy',
				'id' => 'privacy'
			);
			global $rt_media;
			foreach ($rt_media->privacy_settings['levels'] as $key => $value) {
				$attributes['rtForm_options'][] = array(
					$value => $key,
					'selected' => ($key=='0') ? 1 : 0
				);
			}
			echo $form->get_select($attributes);
		}
	}

	public function system_default(){
		return 0;
	}

	public function site_default(){
		global $rtmedia;

		return rt_media_get_site_option('privacy_settings');
	}

	public function user_default(){
		return;

	}

	public function get_default(){
		$default_privacy = $this->user_default();

		if($default_privacy===false){
			$default_privacy = $this->site_default();
		}

		if(!$default_privacy ===false){
			$default_privacy = $this->system_default();
		}
	}


	static function is_enabled() {
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

	static function get_privacy( $id ) {
		return get_post_meta( $id, 'bp_media_privacy', TRUE );
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
		$settings = array(
			6 => array(
				'private',
				__( '<strong>Private</strong>, Visible only to myself', 'buddypress-media' )
			),
			4 => array(
				'friends',
				__( '<strong>Friends</strong>, Visible to my friends', 'buddypress-media' )
			),
			2 => array(
				'users',
				__( '<strong>Users</strong>, Visible to registered users', 'buddypress-media' )
			),
			0 => array(
				'public',
				__( '<strong>Public</strong>, Visible to the world', 'buddypress-media' )
			)
		);
		if ( ! bp_is_active( 'friends' ) ) {
			unset( $settings[ 4 ] );
		}
		return $settings;
	}

	function ui() {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		global $bp_media_current_entry;
		$privacy_level = BPMediaPrivacy::get_privacy( $bp_media_current_entry->get_id() );
		BPMediaPrivacy::ui_html( $privacy_level );
	}

	static function ui_html( $privacy_level ) {
		?>
		<div id="bp-media-upload-privacy-wrap">
			<label for="bp-media-upload-set-privacy"><?php _e( 'Set default privacy levels for your media', 'buddypress-media' ); ?></label>
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

	function save_privacy( $level, $object_id, $type ) {

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

	function save_privacy_by_object( $object ) {
		$level = isset( $_POST[ 'bp_media_privacy' ] ) ? $_POST[ 'bp_media_privacy' ] : false;

		if ( ! is_object( $object ) ) {
			return false;
		}
		if ( $level == false ) {
			if ( $object->get_type() != 'album' ) {
				$album_id = $object->get_album_id();
				$level = BPMediaPrivacy::get_privacy( $album_id );
			}
		}

		$default_level = BPMediaPrivacy::default_privacy();
		if ( $level == false ) {
			$level = $default_level;
		}

		$media_id = $object->get_id();
		$type = $object->get_type();
		return $this->save_privacy( $level, $media_id, $type );
	}

	function save( $level = 0, $object_id = false ) {
		if ( $object_id == false )
			return false;
		if ( ! $level )
			$level = 0;
		if ( ! array_key_exists( $level, BPMediaPrivacy::get_settings() ) )
			$level = 0;

		$level = apply_filters( 'bp_media_save_privacy', $level );

		return update_post_meta( $object_id, 'bp_media_privacy', $level );
	}

	function activity( $a, $activities ) {
		global $bp_media;

		foreach ( $activities->activities as $key => $activity ) {
			//if ( $activity != null && in_array( $activity->type, $bp_media->activity_types ) ) {
				$has_access = BPMediaPrivacy::has_access( $activity->item_id );
				if ( ! $has_access ) {

					unset( $activities->activities[ $key ] );
					$activities->activity_count = $activities->activity_count - 1;
					$activities->total_activity_count = $activities->total_activity_count - 1;
					$activities->pag_num = $activities->pag_num - 1;
				}
			//}
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
		$privacy = BPMediaPrivacy::get_privacy( $object_id );
		$parent = get_post_field( 'post_parent', $object_id, 'raw' );
		$parent_privacy = BPMediaPrivacy::get_privacy( $parent );

		if ( $privacy === false ) {
			if ( $parent_privacy !== false ) {
				$privacy = $parent_privacy;
			} else {
				$privacy = BPMediaPrivacy::default_privacy();
			}
		}
		return $privacy;
	}

	static function get_messages( $media_type, $username ) {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		return array(
			6 => sprintf( __( 'This %s is private', 'buddypress-media' ), $media_type ),
			4 => sprintf( __( 'This %1s is visible only to %2s&rsquo;s friends', 'buddypress-media' ), $media_type, $username ),
			2 => sprintf( __( 'This %s is visible to logged in users, only', 'buddypress-media' ), $media_type ),
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
