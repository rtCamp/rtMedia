<?php

/**
 * Class extends the add_media functionality of BPMediaHostWordpress to send
 * the file to the rtCamp Transcoding server instead of making it as attachment.
 */
class BPMediaEncodingTranscoder extends BPMediaHostWordpress {


    public function insert_media($name, $description, $album_id = 0, $group = 0, $is_multiple = false, $is_activity = false, $files = false, $author_id = false, $album_name = false) {
        do_action('bp_media_before_add_media');
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        include_once(ABSPATH . 'wp-admin/includes/image.php');
        global $bp_media_admin;

        if (!$author_id)
            $author_id = get_current_user_id();

        $post_id = $this->check_and_create_album($album_id, $group, $author_id, $album_name);
        if (!$files) {
            $files = $parent_fallback_files = $_FILES['bp_media_file'];
            if ( in_array($_FILES['bp_media_file']['type'],array('audio/mp3','video/mp4') )){
                return parent::insert_media($name, $description, $album_id, $group, $is_multiple, $is_activity, $parent_fallback_files, $author_id, $album_name);
            }
            $file = wp_handle_upload($files);
        } else {
            $parent_fallback_files = $files;
            if ( in_array($files['type'],array('audio/mp3','video/mp4') )){
                return parent::insert_media($name, $description, $album_id, $group, $is_multiple, $is_activity, $parent_fallback_files, $author_id, $album_name);
            }
            $file = wp_handle_sideload($files, array('test_form' => false));
        }
        
        $parent_fallback_files['tmp_name'] = $file['file'];

        if (isset($file['error']) || $file === null) {
            throw new Exception(__('Error Uploading File', 'buddypress-media'));
        }

        $type = $file['type'];
        if (!preg_match('/video|audio/i', $type, $result)) {
            throw new Exception(__('Upload file type not supported', BP_MEDIA_FFMPEG_TXT_DOMAIN));
        }
        
        $attachment = array();
        $url = $file['url'];
        $file = $file['file'];
        $title = $name;
        $content = $description;
        
        $api_key = bp_get_option('bp-media-encoding-api-key');

        $query_args = array('url' => urlencode($url),
            'callbackurl' => urlencode(home_url()),
            'force' => 0,
            'size' => filesize($file),
            'formats' => ($result[0] == 'video')?'mp4':'mp3');
        $encoding_url = 'http://api.rtcamp.com/job/new/';

        $upload_url = add_query_arg($query_args, $encoding_url . $api_key);
        $upload_page = wp_remote_get($upload_url,array('timeout'=>20));
        if (!is_wp_error($upload_page) && (!isset($upload_page['headers']['status']) || (isset($upload_page['headers']['status']) && ($upload_page['headers']['status'] == 200)))) {
            $upload_info = json_decode($upload_page['body']);
            if (isset($upload_info->status) && $upload_info->status && isset($upload_info->job_id)&&$upload_info->job_id) {
                $job_id = $upload_info->job_id;
            } else {
                $bp_media_admin->bp_media_encoding->update_usage($bp_media_admin->bp_media_encoding->api_key);
                $bp_media_admin->bp_media_encoding->usage_quota_over();
                remove_filter('bp_media_plupload_files_filter', array($bp_media_admin->bp_media_encoding, 'allowed_types'));
                return parent::insert_media($name, $description, $album_id, $group, $is_multiple, $is_activity, $parent_fallback_files, $author_id, $album_name);
            }
        }
        
        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_title' => $title,
            'post_content' => $content,
            'post_parent' => $post_id,
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file, $post_id);
        if (!is_wp_error($attachment_id)) {
            update_post_meta($attachment_id, 'bp-media-encoding-job-id', $job_id);
            add_filter('intermediate_image_sizes', array($this, 'bp_media_image_sizes'), 99);
            wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
        } else {
            unlink($file);
            throw new Exception(__('Error creating attachment for the media file, please try again', 'buddypress-media'));
        }
        $this->id = $attachment_id;
        $this->name = $name;
        $this->description = $description;
        $this->type = $result[0];
        $this->owner = $author_id;
        $this->album_id = $post_id;
        $this->group_id = $group;
        $this->set_permalinks();
        if ($group == 0) {
            update_post_meta($attachment_id, 'bp-media-key', $author_id);
        } else {
            update_post_meta($attachment_id, 'bp-media-key', (-$group));
        }
        
        $bp_media_admin->bp_media_encoding->update_usage($bp_media_admin->bp_media_encoding->api_key);
//        update_post_meta( $attachment_id, 'bp_media_privacy', 6 );
//        $this->pre_update_count();
        do_action('bp_media_after_add_media', $this, $is_multiple, $is_activity, $group);
        return $attachment_id;
    }

    function pre_update_count() {
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