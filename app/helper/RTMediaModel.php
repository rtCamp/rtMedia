<?php

/**
 * Description of BPMediaModel
 *
 * @author joshua
 */
class RTMediaModel extends RTDBModel {

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

    function get_media($columns, $offset, $per_page) {
        if (is_multisite()) {
            $results = $this->get($columns, $offset, $per_page, "blog_id");
        } else {
            $results = $this->get($columns, $offset, $per_page);
        }
        return $results;
    }
    function get_media_meta($media_id){
        $media_query_str = "";
        if (is_array($media_id)){
            $sep = "";
            foreach($media_id as $mid){
                $media_query_str .= $sep . $mid;
                $sep= ",";
            }
        }else{
            $media_query_str .= $media_id;
        }
        
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->posts} LEFT JOIN {$this->table_name} 
            ON {$wpdb->posts}.ID = {$this->table_name}.media_id 
            WHERE {$wpdb->posts}.ID in ({$media_query_str});";
        return $wpdb->get_results($sql,ARRAY_A);
    }

}

?>
