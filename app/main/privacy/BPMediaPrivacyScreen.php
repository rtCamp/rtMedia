<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaPrivacyScreen
 *
 * @author saurabh
 * ****
 * ****
 * Need to add required privacy level vs current privacy level
 * Right now there's only required privacy level
 */
class BPMediaPrivacyScreen {

	/**
	 *
	 */
	function __construct() {
		$this->template = new BPMediaTemplate();
	}

	function ui(){
		global $bp;
        add_action('bp_template_content', array($this, 'screen_content'));
		add_action('bp_template_title', array($this, 'screen_title'));
        $this->template->loader();
	}

	function screen_content(){
		if ( isset( $_POST['submit'] ) ) {

		// Nonce check
		check_admin_referer('bpmedia_user_privacy_settings');
		$new_privacy_default = $_POST['bp_media_privacy'];
		BPMediaPrivacy::save_user_default($new_privacy_default);

		?>
<div id="message" class="updated"><p><?php _e('Default privacy level for your media has been updated','buddypress-media'); ?></p></div>
<?php
		}
		$privacy_level = BPMediaPrivacy::get_user_default();
		if(!$privacy_level)$privacy_level = 0;

		echo '<form id="bp-media-user-privacy" method="post">';
		BPMediaPrivacy::ui_html($privacy_level);
		wp_nonce_field( 'bpmedia_user_privacy_settings' );
		echo'<div class="submit">'.
						'<input type="submit" name="submit" value="';
		_e( 'Save Changes', 'buddypress' );
		echo '" id="submit" class="auto" /></div>';
		echo '</form>';
	}

	function screen_title(){
		_e( 'Privacy Settings', 'buddypress-media' );
	}

}

?>
