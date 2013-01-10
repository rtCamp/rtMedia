<?php
/**
 * Description of BPMediaGroupImage
 *
 * @author faishal
 */
if ( class_exists( 'BP_Group_Extension' ) ) :
class BPMediaGroupVideo extends BPMediaGroupElementExtension {

    function __construct() {
        parent::__construct(BP_MEDIA_VIDEOS_LABEL, BP_MEDIA_VIDEOS_SLUG);
        bp_register_group_extension("BPMediaGroupVideo");
    }

}
endif;
?>
