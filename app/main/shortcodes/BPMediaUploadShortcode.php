<?php

/**
 * Description of BPMediaUploadShortcode
 *
 * @author joshua
 */
class BPMediaUploadShortcode {

    var $add_sc_script = false;

    public function __construct() {
        add_shortcode('bp-media-uploader', array($this, 'render'));
        add_action('init', array($this, 'register_script'));
        add_action('wp_footer', array($this, 'print_script'));
    }

    function render() {
        $this->add_sc_script = true;
        $view = new BPMediaUploadView();
        return $view->render('upload/uploader.php');
    }

    function register_script() {
        wp_register_script('bpm-plupload', BP_MEDIA_URL . 'app/assets/js/bpm-plupload.js', array('plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4', 'plupload-handlers'), '1.0', true);
    }

    function print_script() {
        if (!$this->add_sc_script)
            return;
        $params = array(
            'url' => 'upload',
            'runtimes' => 'gears,html5,flash,silverlight,browserplus',
            'browse_button' => 'browse-button',
            'container' => 'bpm-file_upload-ui',
            'drop_element' => 'drag-drop-area',
            'filters' => apply_filters('bp_media_plupload_files_filter', array(array('title' => "Media Files", 'extensions' => "mp4,jpg,png,jpeg,gif,mp3"))),
            'max_file_size' => min(array(ini_get('upload_max_filesize'), ini_get('post_max_size'))),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            'file_data_name' => 'bp_media_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters('bp_media_multipart_params_filter', array('action' => 'wp_handle_upload'))
        );

        foreach ((array) $params as $key => $value) {
            if (!is_scalar($value))
                continue;

            $params[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
        }

        echo "<script type='text/javascript'>\n"; // CDATA and type='text/javascript' is not needed for HTML 5
        echo "/* <![CDATA[ */\n";
        echo "var bpm_plupload_params = " . json_encode($params) . ";\n";
        echo "/* ]]> */\n";
        echo "</script>\n";

        wp_print_scripts('bpm-plupload');
    }

}

?>
