<?php

/**
 * Description of BPMediaUploadPost
 *
 * @author joshua
 */
class BPMediaUploadPost {

    var $post_array = false;

    function init($uploaded, $file_object) {
        
        $attachments = $this->generate_post_array($uploaded,$file_object);
        
        return $this->insert_attachment($attachments, $file_object);
        
    }

    function generate_post_array($uploaded,$file_object) {
        foreach ($file_object as $file) {
            $attachments[] = array(
                'post_mime_type' => $file['type'],
                'guid' => $file['url'],
                'post_title' => $uploaded['title'] ? $uploaded['title'] : $file['name'],
                'post_content' => $uploaded['description'] ? $uploaded['description'] : '',
                'post_parent' => $uploaded['album_id'] ? $uploaded['album_id'] : 0,
            );
        }
        return $attachments;
    }

    function insert_attachment($attachments, $file_object) {
        foreach ($attachments as $key => $attachment) {
            $attachment_id = wp_insert_attachment($attachment, $file_object[$key]['file'], $attachment['post_parent']);
            if (!is_wp_error($attachment_id)) {
                add_filter('intermediate_image_sizes', array($this, 'bp_media_image_sizes'), 99);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_object[$key]['file']));
            } else {
                unlink($file_object[$key]['file']);
                throw new Exception(__('Error creating attachment for the media file, please try again', 'buddypress-media'));
            }
            $updated_attachment_ids[] = $attachment;
        }
        return $updated_attachment_ids;
    }

    function bp_media_image_sizes($sizes) {
        return array('bp_media_thumbnail', 'bp_media_activity_image', 'bp_media_single_image');
    }

}

?>
