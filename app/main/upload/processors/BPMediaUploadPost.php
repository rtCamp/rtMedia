<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaUploadPost
 *
 * @author joshua
 */
class BPMediaUploadPost {

    var $post_array = false;

    function init($uploaded, $file_object) {
        $sanitized_file_object = $this->sanitize_type($file_object);
//        print_r($sanitized_file_object);
        $attachments = $this->generate_post_array($sanitized_file_object);
//        print_r($attachments);die;
        return $this->insert_attachment($attachments,$sanitized_file_object);
    }

    function generate_post_array($file_object) {
//        error_log(var_export($file_object,true));die;
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

    function sanitize_type($file_object) {
        foreach ($file_object as $key => $file) {
            switch ($file['type']) {
                case 'video/mp4' :
                case 'video/quicktime' :
                    $type = 'video';
                    include_once(trailingslashit(BP_MEDIA_PATH) . 'lib/getid3/getid3.php');
                    try {
                        $getID3 = new getID3;
                        $vid_info = $getID3->analyze($file['file']);
                    } catch (Exception $e) {
                        unlink($file['file']);
                        $activity_content = false;
                        throw new Exception(__('MP4 file you have uploaded is corrupt.', 'buddypress-media'));
                    }
                    if (is_array($vid_info)) {
                        if (!array_key_exists('error', $vid_info) && array_key_exists('fileformat', $vid_info) && array_key_exists('video', $vid_info) && array_key_exists('fourcc', $vid_info['video'])) {
                            if (!($vid_info['fileformat'] == 'mp4' && $vid_info['video']['fourcc'] == 'avc1')) {
                                unlink($file['file']);
                                $activity_content = false;
                                throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                            }
                        } else {
                            unlink($file['file']);
                            $activity_content = false;
                            throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                        }
                    } else {
                        unlink($file['file']);
                        $activity_content = false;
                        throw new Exception(__('The MP4 file you have uploaded is not a video file.', 'buddypress-media'));
                    }
                    break;
                case 'audio/mpeg' :
                    include_once(trailingslashit(BP_MEDIA_PATH) . 'lib/getid3/getid3.php');
                    try {
                        $getID3 = new getID3;
                        $file_info = $getID3->analyze($file['file']);
                    } catch (Exception $e) {
                        unlink($file['file']);
                        $activity_content = false;
                        throw new Exception(__('MP3 file you have uploaded is currupt.', 'buddypress-media'));
                    }
                    if (is_array($file_info)) {
                        if (!array_key_exists('error', $file_info) && array_key_exists('fileformat', $file_info) && array_key_exists('audio', $file_info) && array_key_exists('dataformat', $file_info['audio'])) {
                            if (!($file_info['fileformat'] == 'mp3' && $file_info['audio']['dataformat'] == 'mp3')) {
                                unlink($file['file']);
                                $activity_content = false;
                                throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                            }
                        } else {
                            unlink($file['file']);
                            $activity_content = false;
                            throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                        }
                    } else {
                        unlink($file['file']);
                        $activity_content = false;
                        throw new Exception(__('The MP3 file you have uploaded is not an audio file.', 'buddypress-media'));
                    }
                    $type = 'audio';
                    break;
                case 'image/gif' :
                case 'image/jpeg' :
                case 'image/png' :
                    $type = 'image';
                    break;
                default :
                    unlink($file['file']);
                    $activity_content = false;
                    throw new Exception(__('Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', 'buddypress-media'));
            }
            $sanitized_file_object[] = $file;
        }
        
        return $sanitized_file_object;
    }
    
    function insert_attachment($attachments,$file_object){
        foreach( $attachments as $key => $attachment) {
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
