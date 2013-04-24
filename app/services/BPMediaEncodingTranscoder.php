<?php

/**
 * Class extends the add_media functionality of BPMediaHostWordpress to send
 * the file to the rtCamp Transcoding server instead of making it as attachment.
 */
class BPMediaEncodingTranscoder extends BPMediaHostWordpress {
    
    public $encoding_url = 'http://api.rtcamp.info/job/new/';
    
    public function insert_media($name, $description, $album_id = 0, $group = 0, $is_multiple = false, $is_activity = false, $files = false, $author_id = false, $album_name = false) {
        do_action('bp_media_before_add_media');

        include_once(ABSPATH . 'wp-admin/includes/file.php');
        include_once(ABSPATH . 'wp-admin/includes/image.php');

        if (!$author_id)
            $author_id = get_current_user_id();

        $post_id = $this->check_and_create_album($album_id, $group, $author_id, $album_name);

        if (!$files) {
            $files = $_FILES['bp_media_file'];
            $file = wp_handle_upload($files);
        } else {
            $file = wp_handle_sideload($files, array('test_form' => false));
        }

        if (isset($file['error']) || $file === null) {
            throw new Exception(__('Error Uploading File', 'buddypress-media'));
        }
        

        $type = $file['type'];
        if (in_array($type, array('image/gif', 'image/jpeg', 'image/png'))) {
            if (function_exists('read_exif_data')) {
                $file = $this->exif($file);
            }
        }

        $attachment = array();
        $url = $file['url'];
        $file = $file['file'];
        $title = $name;
        $content = $description;
        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_title' => $title,
            'post_content' => $content,
            'post_parent' => $post_id,
        );
        
        switch ($type) {
            case 'video/mp4' :
            case 'video/quicktime' :
                $type = 'video';
                include_once(trailingslashit(BP_MEDIA_PATH) . 'lib/getid3/getid3.php');
                try {
                    $getID3 = new getID3;
                    $vid_info = $getID3->analyze($file);
                } catch (Exception $e) {
                    unlink($file);
                    $activity_content = false;
                    throw new Exception(__('MP4 file you have uploaded is corrupt.', 'buddypress-media'));
                }
                if (is_array($vid_info)) {
                    if (!array_key_exists('error', $vid_info) && array_key_exists('fileformat', $vid_info) && array_key_exists('video', $vid_info) && array_key_exists('fourcc', $vid_info['video'])) {
                        if (!($vid_info['fileformat'] == 'mp4' && $vid_info['video']['fourcc'] == 'avc1')) {
                            unlink($file);
                            $activity_content = false;
                            throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                        }
                    } else {
                        unlink($file);
                        $activity_content = false;
                        throw new Exception(__('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                    }
                } else {
                    unlink($file);
                    $activity_content = false;
                    throw new Exception(__('The MP4 file you have uploaded is not a video file.', 'buddypress-media'));
                }
                break;
            case 'audio/mpeg' :
                include_once(trailingslashit(BP_MEDIA_PATH) . 'lib/getid3/getid3.php');
                try {
                    $getID3 = new getID3;
                    $file_info = $getID3->analyze($file);
                } catch (Exception $e) {
                    unlink($file);
                    $activity_content = false;
                    throw new Exception(__('MP3 file you have uploaded is currupt.', 'buddypress-media'));
                }
                if (is_array($file_info)) {
                    if (!array_key_exists('error', $file_info) && array_key_exists('fileformat', $file_info) && array_key_exists('audio', $file_info) && array_key_exists('dataformat', $file_info['audio'])) {
                        if (!($file_info['fileformat'] == 'mp3' && $file_info['audio']['dataformat'] == 'mp3')) {
                            unlink($file);
                            $activity_content = false;
                            throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                        }
                    } else {
                        unlink($file);
                        $activity_content = false;
                        throw new Exception(__('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                    }
                } else {
                    unlink($file);
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
                unlink($file);
                $activity_content = false;
                throw new Exception(__('Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', 'buddypress-media'));
        }
        
        $attachment_id = wp_insert_attachment($attachment, $file, $post_id);
        if (!is_wp_error($attachment_id)) {
            add_filter('intermediate_image_sizes', array($this, 'bp_media_image_sizes'), 99);
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
        } else {
            unlink($file);
            throw new Exception(__('Error creating attachment for the media file, please try again', 'buddypress-media'));
        }
        $this->id = $attachment_id;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->owner = $author_id;
        $this->album_id = $post_id;
        $this->group_id = $group;
        $this->set_permalinks();
        if ($group == 0) {
            update_post_meta($attachment_id, 'bp-media-key', $author_id);
        } else {
            update_post_meta($attachment_id, 'bp-media-key', (-$group));
        }
        
        $api_key = bp_get_option('bp-media-encoding-api-key');
        
        $query_args = array('url' => urlencode($url),
            'callbackurl' =>urlencode(home_url()),
            'force' => 0,
            'size' => filesize($file),
            'formats' => 'mp4');
        $upload_url = add_query_arg($query_args,$this->encoding_url.$api_key);
        error_log($upload_url);
        $upload_page = wp_remote_get($upload_url,array('timeout'=>20));
        if ( !is_wp_error($upload_page) ) {
            $upload_info = json_decode($upload_page['body']);
            if ( $upload_info->status ) {
                update_post_meta($attachment_id, 'bp-media-encoding-job-id', $upload_info->job_id);
            } else {
                throw new Exception($upload_info->message);
            }
        }
        
//        update_post_meta( $attachment_id, 'bp_media_privacy', 6 );
//        $this->pre_update_count();
        do_action('bp_media_after_add_media', $this, $is_multiple, $is_activity, $group);

        return $attachment_id;

    }
    
    function pre_update_count(){
        global $bp;
        $user_id = $bp->loggedin_user->id;
        global $wpdb;
        $formatted = array();
        $query =
                "SELECT
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums
	FROM
		$wpdb->posts p inner join $wpdb->postmeta  pm on pm.post_id = p.id INNER JOIN $wpdb->postmeta pmp
		on pmp.post_id = p.id  WHERE
		p.post_author = $user_id AND
		pm.meta_key = 'bp-media-key' AND
		pm.meta_value > 0 AND
		pmp.meta_key = 'bp_media_privacy' AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY pmp.meta_value";
        $result = $wpdb->get_results($query);
        if (!is_array($result))
            return false;
        foreach ($result as $level => $obj) {
            $formatted[$level * 2] = array(
                'image' => $obj->Images,
                'video' => $obj->Videos,
                'audio' => $obj->Audio,
                'album' => $obj->Albums,
            );
        }
        bp_update_user_meta($user_id, 'bp_media_count', $formatted);
    }

}

?>