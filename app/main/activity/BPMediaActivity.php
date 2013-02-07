<?php
/**
 * Description of BPMediaActivity
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaActivity')) {

    class BPMediaActivity {

        public function __construct() {
            add_action('bp_after_activity_post_form', array($this, 'activity_uploader'));
        }

        public function activity_uploader() {
            ?>
            <div id="bp-media-upload-ui" class="hide-if-no-js drag-drop">
                <input id="bp-media-upload-browse-button" type="button" value="<?php _e('Insert Media', BP_MEDIA_TXT_DOMAIN); ?>" class="button" />
                <div id="bp-media-uploaded-files"></div>
            </div><?php
        }

    }

}
?>
