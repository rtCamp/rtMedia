<?php

/**
 * Description of BPMediaUploadFile
 *
 * @author joshua
 */
class BPMediaUploadFile {
    
    private $default_allowed_types = 'audio|video|image';
    
    public function __construct($uploaded) {
        
        $_POST['action'] = 'wp_handle_upload'; //to pass the form submission test
        
        if ( !isset($_FILES['bp_media_file']) || !is_array($_FILES['bp_media_file']) )
            return;
        
        if ($type = $this->is_allowed_type($_FILES['bp_media_file'])){
            
        } else {
            return;
        }
        
        $class_name = apply_filters('bp_media_transcoder', 'BPMediaHostWordpress', $type);
        $bp_media_entry = new $class_name();
        try {
            $title = pathinfo($_FILES['bp_media_file']['name'], PATHINFO_FILENAME);
            $album_id = 204;
            $is_multiple = false;
            $is_activity = false;
            $description = 'asd';
            $group_id = 0;
            $entry = $bp_media_entry->add_media($title, $description, $album_id, $group_id, $is_multiple, $is_activity);
            if (!isset($bp->{BP_MEDIA_SLUG}->messages['updated'][0]))
                $bp->{BP_MEDIA_SLUG}->messages['updated'][0] = __('Upload Successful', 'buddypress-media');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    function is_allowed_type($file_array){
        $allowed_types = apply_filters('bp_media_allowed_types',$this->default_allowed_types);
        if (!preg_match('/'.$allowed_types.'/i', $file_array['type'], $result) || !isset($result[0])) {
//            $bp->{BP_MEDIA_SLUG}->messages['error'][] = __('File uploaded is not supported');
            return false;
        }
        return $result[0];
    }
}

?>
