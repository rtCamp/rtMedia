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

	var $settings = array(
		6	=>'private',
		4	=>'friends',
		2	=>'users',
		0	=>'public'
	);
        var $messages;
	var $enabled = false;

	/**
	 *
	 */
	function __construct() {
            $this->messages = array(
                8 => __('Privacy Options', BP_MEDIA_TXT_DOMAIN),
                6 => __('Visible to private members', BP_MEDIA_TXT_DOMAIN),
                4 => __('Visible to friends', BP_MEDIA_TXT_DOMAIN),
                2 => __('Visible to logged in users', BP_MEDIA_TXT_DOMAIN),
                0 => __( 'Visible to all', BP_MEDIA_TXT_DOMAIN)
            );
            add_action('bp_media_add_media_fields', array( $this, 'ui' ));
            add_action('bp_media_after_update_media', array( $this, 'save_privacy' ), 10, 2);
	}

	function ui($media_type='album') {
            global $bp_media_current_entry;
            if ( $media_type == 'album' ) {
                $privacy_level = get_post_meta($bp_media_current_entry->get_id(),'bp_media_privacy', TRUE);
                ?>
                <label for="bp-media-upload-set-privacy"><?php echo $this->messages[8]; ?></label>
                <ul id="bp-media-upload-set-privacy">
                    <li>
                        <input type="radio" name="bp-media-privacy" class="album-set-privacy-radio" id="bp-media-privacy-private" value="6" <?php checked('6', $privacy_level, TRUE); ?> >
                        <label class="album-set-privacy-label" for="bp-media-privacy-private"><?php echo $this->messages[6]; ?></label>
                    </li>
                    <li>
                        <input type="radio" name="bp-media-privacy" class="album-set-privacy-radio" id="bp-media-privacy-friends" value="4" <?php checked('6', $privacy_level, TRUE); ?> >
                        <label class="album-set-privacy-label" for="bp-media-privacy-friends"><?php echo $this->messages[4]; ?></label>
                    </li>
                    <li>
                        <input type="radio" name="bp-media-privacy" class="album-set-privacy-radio" id="bp-media-privacy-logged-in-users" value="2" <?php checked('6', $privacy_level, TRUE); ?> >
                        <label class="album-set-privacy-label" for="bp-media-privacy-logged-in-users"><?php echo $this->messages[2]; ?></label>
                    </li>
                    <li>
                        <input type="radio" name="bp-media-privacy" class="album-set-privacy-radio" id="bp-media-privacy-public" value="0" <?php checked('6', $privacy_level, TRUE); ?> >
                        <label class="album-set-privacy-label" for="bp-media-privacy-public"><?php echo $this->messages[0]; ?></label>
                    </li>
                </ul>
            <?php }
	}

        function save_privacy($object_id, $media_type){
            if ($media_type != 'album')
                return FALSE;

            $level = esc_html($_POST['bp_media_privacy']);
            $this->save( $level, $object_id );
        }

	function save( $level = 0, $object_id = false ) {

		if(!array_key_exists($level,$this->settings))
			return false;

		return $this->save_by_object( $level, $object_id );
	}

	private function save_by_object(  $level = 0, $object_id = false ) {
		if($object_id==false)
			return false;

		$level = apply_filters('bp_media_save_privacy', $level);

		return update_post_meta($object_id,'bp_media_privacy',$level);

	}
	function check($object_id=false, $object_type='media'){
		if($object_id==false)
			return;
		switch ($object_type){
			case 'media':
				$settings = get_post_meta($object_id,'bp_media_privacy');
				break;
			case 'profile':
				get_user_meta($object_id,'bp_media_privacy',$settings);
				return update_user_meta($object_id,'bp_media_privacy',$settings);
				break;
			case 'activity':
				break;
			case 'group':
				break;

		}


	}

	function get_friends(){

	}

        function set_messages($messages){
            $this->messages = $messages;
        }

        function get_messages(){
            return $this->messages;
        }

}

?>
