<?php

/**
 * Description of BPMediaModel
 *
 * @author joshua
 */
class BPMediaModel extends rtDBModel {
    
    function __construct() {
        parent::__construct('bpm_media');
    }
    
    function __call($name, $arguments) {
        $result = parent::__call($name, $arguments);
        if (!$result['result']) {
            $result['result'] = $this->populate_results_fallback($name, $arguments);
        }
        return $result;
    }

    function populate_results_fallback($name, $arguments) {
        $result['result'] = false;
        if ('get_by_media_id' == $name && isset($arguments[0]) && $arguments[0]) {

            $result['result'][0]->media_id = $arguments[0];

            $post_type = get_post_field('post_type', $arguments[0]);
            if ('attachment' == $post_type) {
                $post_mime_type = explode('/', get_post_field('post_mime_type', $arguments[0]));
                $result['result'][0]->media_type = $post_mime_type[0];
            } elseif ('bp_media_album' == $post_type) {
                $result['result'][0]->media_type = 'bp_media_album';
            } else {
                $result['result'][0]->media_type = false;
            }

            $result['result'][0]->context_id = intval(get_post_meta($arguments[0], 'bp-media-key', true));
            if ($result['result'][0]->context_id > 0)
                $result['result'][0]->context = 'profile';
            else
                $result['result'][0]->context = 'group';

            $result['result'][0]->activity_id = get_post_meta($arguments[0], 'bp_media_child_activity', true);

            $result['result'][0]->privacy = get_post_meta($arguments[0], 'bp_media_privacy', true);
        }
        return $return['result'];
    }

}

?>
