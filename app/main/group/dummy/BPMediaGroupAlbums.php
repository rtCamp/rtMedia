<?php

/**
 * Description of BPMediaGroupImage
 *
 * @author faishal
 */
if (class_exists('BP_Group_Extension')) :

    class BPMediaGroupAlbums extends BPMediaGroupElementExtension {

        function __construct() {
            parent::__construct(BP_MEDIA_ALBUMS_LABEL, BP_MEDIA_ALBUMS_SLUG);
            bp_register_group_extension("BPMediaGroupAlbums");
        }

    }

    endif;
?>
