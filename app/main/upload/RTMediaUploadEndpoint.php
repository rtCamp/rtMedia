<?php

/**
 * Description of BPMediaUploadEndpoint
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadEndpoint {

    public function __construct() {
        add_action('init', array($this, 'endpoint'));
        add_action('template_redirect', array($this, 'template_redirect'));
        new RTMediaDelete(); // should be placed somewhere else ( just does the trick here )
    }

    function endpoint() {
        add_rewrite_endpoint(BP_MEDIA_UPLOAD_SLUG, EP_ALL);
    }

    function template_redirect() {
        global $wp_query, $bp;
        if (!isset($wp_query->query_vars['upload']))
            return;
        if (isset($wp_query->query_vars['upload']) && !count($_POST)) {
            include get_404_template();
        } else {
            $nonce = $_REQUEST['bp_media_upload_nonce'];
            $mode = $_REQUEST['mode'];
            if (wp_verify_nonce($nonce, 'bp_media_' . $mode)) {
                $model = new RTMediaUploadModel();
                $this->upload = $model->set_post_object();

                $upload = new RTMediaUpload($this->upload);
            }
            wp_safe_redirect(wp_get_referer());
        }

        exit;
    }

}

?>
