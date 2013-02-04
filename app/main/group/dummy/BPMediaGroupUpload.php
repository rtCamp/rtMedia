<?php
/**
 * Registers BPMediaGroupUpload class in groups in buddypress
 *
 * @package BuddyPressMedia
 * @subpackage Group
 *
 * @author Hrishikesh Vaipurkar <hrishikesh.vaipurkar@rtcamp.com>
 */
if ( class_exists( 'BP_Group_Extension' ) ) :
class BPMediaGroupUpload extends BPMediaGroupElementExtension {

    function __construct() {
        parent::__construct(BP_MEDIA_UPLOAD_LABEL, BP_MEDIA_UPLOAD_SLUG);
        bp_register_group_extension("BPMediaGroupUpload");
    }

}
endif;
?>
