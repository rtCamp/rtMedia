<?php
/**
 * Description of BPMediaGroupImage
 *
 * @author faishal
 */
if ( class_exists( 'BP_Group_Extension' ) ) :
class BPMediaGroupMusic extends BPMediaGroupElementExtension {

    function __construct() {
        parent::__construct(BP_MEDIA_AUDIO_LABEL, BP_MEDIA_AUDIO_SLUG);
        bp_register_group_extension("BPMediaGroupMusic");
    }

}
endif;
?>
