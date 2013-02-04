<?php
/**
 * Description of BPMediaWidget
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaAdminWidget')) {

    class BPMediaAdminWidget {

        public function __construct($id = NULL, $title = NULL, $content = NULL) {
            global $bp_media;
            if ($id) {
                ?>
                <div class="postbox" id="<?php echo $id; ?>"><?php if ($title) { ?>
                        <h3 class="hndle"><span><?php echo $title; ?></span></h3><?php }
                ?>
                    <div class="inside"><?php echo $content; ?></div>
                </div><?php
            } else {
                trigger_error(__('Argument missing. id is required.', BP_MEDIA_TXT_DOMAIN));
            }
        }

    }

}
?>
