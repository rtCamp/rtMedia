<?php
/**
 * Description of BPMediaGroupImage
 *
 * @author faishal
 */
if ( class_exists( 'BP_Group_Extension' ) ) :
class BPMediaGroupImage extends BPMediaGroupElementExtension {

    function __construct() {
        parent::__construct(BP_MEDIA_IMAGES_LABEL, BP_MEDIA_IMAGES_SLUG);
        bp_register_group_extension("BPMediaGroupImage");
    }

}
endif;
?>
