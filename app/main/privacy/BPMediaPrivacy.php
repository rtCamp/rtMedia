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
	var $enabled = false;

	/**
	 *
	 */
	function __construct() {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		$this->settings = $this->get_settings();
		add_action( 'bp_media_add_media_fields', array( $this, 'ui' ) );
		add_action( 'bp_media_after_update_media', array( $this, 'save_privacy' ) );
	}

	static function is_enabled() {
		if(BPMediaPrivacy::install_status()==false)return false;
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

	static function is_installed(){
		global $bp_media;
		$options = $bp_media->options;
		$option_exists = true;
		$option_exists=BPMediaPrivacy::install_status();

		if ( ! array_key_exists( 'privacy_installed', $options ) ) {
			$option_exists = false;
		} else {
			if ( $options[ 'privacy_installed' ] != true ) {
				$option_exists = false;
			}
		}
		return $option_exists;
	}

	static function install_status(){
		$settings = new BPMediaPrivacySettings();
		$total = $settings->get_total_count();
		$total = $total[0]->Total;
		$finished = $settings->get_completed_count();
		$finished = $finished[0]->Finished;
		if($total===$finished){
			global $bp_media;
			$options = $bp_media->options;
			$options['privacy_installed']=true;
		}else{
			$options['privacy_installed']=false;
		}

		update_option('bp_media_options',$options);
		return $options['privacy_installed'];
	}

	static function default_privacy() {
		global $bp_media;
		$options = $bp_media->options;
		$default_privacy = 0;
		if ( ! array_key_exists( 'privacy_enabled', $options ) ) {

			if ( array_key_exists( 'privacy_override_enabled', $options ) ) {
				if ( array_key_exists( 'default_privacy_level', $options ) )
					if ( $options[ 'default_privacy_level' ] > 0 )
						$default_privacy = $options[ 'default_privacy_level' ];
			}
		}
		return $default_privacy;
	}

	static function get_settings() {
		return array(
			6 => array(
				'private',
				__( 'Private, Visible only to myself', BP_MEDIA_TXT_DOMAIN )
			),
			4 => array(
				'friends',
				__( 'Visible to my friends', BP_MEDIA_TXT_DOMAIN )
			),
			2 => array(
				'users',
				__( 'Visible to all registered members', BP_MEDIA_TXT_DOMAIN )
			),
			0 => array(
				'public',
				__( 'Visible to everyone', BP_MEDIA_TXT_DOMAIN )
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
		<?php
	}

	function save_privacy( $object_id ) {

		$level = $_POST[ 'bp_media_privacy' ];
		$this->save( $level, $object_id );
	}

	function save( $level = 0, $object_id = false ) {

		if ( ! array_key_exists( $level, $this->settings ) )
			return false;

		return $this->save_by_object( $level, $object_id );
	}

	private function save_by_object( $level = 0, $object_id = false ) {
		if ( $object_id == false )
			return false;

		$level = apply_filters( 'bp_media_save_privacy', $level );

		return update_post_meta( $object_id, 'bp_media_privacy', $level );
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
		return get_user_meta( $user_id, 'bp_media_privacy', true );
	}

	static function required_access( $object_id = false ) {
		if ( BPMediaPrivacy::is_enabled() == false )
			return;
		if ( $object_id == false )
			return;
		$privacy = get_post_meta( $object_id, 'bp_media_privacy', true );

		if ( $privacy == false ) {
			$privacy = BPMediaPrivacy::get_user_default();
			if ( $privacy == false ) {
				global $bp_media;
				$options = $bp_media->options;
				if ( array_key_exists( 'default_privacy_level', $options ) ) {
					$privacy = $options[ 'default_privacy_level' ];
				} else {
					$privacy == 0;
				}
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

			if ( ! (bp_is_my_profile()) && bp_get_current_group_id() == 0 ) {
				$is_friend = friends_check_friendship_status( $bp->loggedin_user->id, $bp->displayed_user->id );
				if ( $is_friend != 'is_friend' ) {
					$current_privacy = 4;
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
		$page = $_POST['page'];
		($page) ? $page : 1;
		$args = array(
			'post_type' => array(
				'attachment',
				'bp_media_album'
			),
			'post_status' => 'any',
			'posts_per_page' => 20,
			'paged' => $page,
			'meta_key'=>'bp-media-key'
		);
		$all_media = new WP_Query( $args );
		foreach ( $all_media->posts as $media ) {
			update_post_meta( $media->ID, 'bp_media_privacy', 0 );
		}
		wp_die($page);
	}

}
?>
