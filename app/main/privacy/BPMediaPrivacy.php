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
	function __construct( $media_type ) {
		$this->messages = $this->get_messages( $media_type );
		$this->settings = $this->get_settings(  );
		add_action( 'bp_media_add_media_fields', array( $this, 'ui' ) );
		add_action( 'bp_media_after_update_media', array( $this, 'save_privacy' ), 10, 2 );
	}

	function get_settings() {
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
		global $bp_media_current_entry;
		$privacy_level = get_post_meta( $bp_media_current_entry->get_id(), 'bp_media_privacy', TRUE );
		?>
		<label for="bp-media-upload-set-privacy"><?php _e( 'Privacy Settings', BP_MEDIA_TXT_DOMAIN ); ?></label>
		<ul id="bp-media-upload-set-privacy">
			<?php
			foreach ( $this->settings as $level => &$msg ) {
				?>
				<li>
					<input type="radio" name="bp-media-privacy" class="set-privacy-radio" id="bp-media-privacy-friends" value="<?php echo $level; ?>" <?php checked( $level, $privacy_level, TRUE ); ?> >
					<label class="album-set-privacy-label" for="bp-media-privacy-<?php echo $msg[0]; ?>"><?php echo $msg[1]; ?></label>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}

	function save_privacy( $object_id, $media_type ) {

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

	function check( $object_id = false, $object_type = 'media' ) {
		if ( $object_id == false )
			return;
		switch ( $object_type ) {
			case 'media':
				$settings = get_post_meta( $object_id, 'bp_media_privacy' );
				break;
			case 'profile':
				get_user_meta( $object_id, 'bp_media_privacy', $settings );
				return update_user_meta( $object_id, 'bp_media_privacy', $settings );
				break;
			case 'activity':
				break;
			case 'group':
				break;
		}
	}

	function get_messages( $media_type ) {
		global $bp;
		$username = $bp->displayed_user->fullname;
		return array(
			6 => sprintf( __( 'This %s is private', BP_MEDIA_TXT_DOMAIN ), $media_type ),
			4 => sprintf( __( 'This %1s is visible only to %2s&rsquo;s friends', BP_MEDIA_TXT_DOMAIN ), $media_type, $username ),
			2 => sprintf( __( 'This %1s is visible to logged in users, only', BP_MEDIA_TXT_DOMAIN ), $media_type ),
		);
	}

}
?>
