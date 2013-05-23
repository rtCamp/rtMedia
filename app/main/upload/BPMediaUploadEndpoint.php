<?php

/**
 * Description of BPMediaUploadEndpoint
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMediaUploadEndpoint {

    public function __construct() {
        add_action('init', array($this, 'endpoint'));
        add_action('template_redirect', array($this, 'template_redirect'));
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
            
            $model = new BPMediaUploadModel();
            $this->upload = $model->set_post_object();
            
            $upload = new BPMediaUpload($this->upload);
            wp_safe_redirect(wp_get_referer());
        }

        exit;
    }

}

?>
