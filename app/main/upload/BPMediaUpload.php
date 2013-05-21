<?php

/**
 * Description of BPMediaUpload
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMediaUpload {

    public function __construct() {
        add_action('init', array($this, 'endpoint'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }

    function endpoint() {
        add_rewrite_endpoint('upload', EP_ALL);
    }

    function template_redirect() {
        global $wp_query;
        if (!isset($wp_query->query_vars['upload']))
            return;

        if (isset($wp_query->query_vars['upload']) && !count($_POST)) {
            include get_404_template();
        } else {
             $model = new BPMediaUploadModel();
             $this->upload = $model->set_post_object();
        }

        exit;
    }
}

?>
