<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaBPAlbumImporter
 *
 * @author saurabh
 */
class BPMediaAlbumimporter extends BPMediaImporter {

    function __construct() {
        parent::__construct();
        $this->path = 'bp-album/loader.php';
        $this->active = $this->_active($this->path);
        if ($this->active != -1 && !$this->is_column_exists('import_status')) {
            $this->update_table();
        }
    }

    function update_table() {
        global $wpdb;
        return $wpdb->query(
                        "ALTER TABLE {$wpdb->base_prefix}bp_album ADD COLUMN
					import_status TINYINT (1) NOT NULL DEFAULT 0"
        );
    }

    function is_column_exists($column) {
        global $wpdb;
        return $wpdb->query(
                        "SHOW COLUMNS FROM {$wpdb->base_prefix}bp_album LIKE '$column'"
        );
    }

    function tab($tabs, $tab) {
        $idle_class = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        $tabs[] = array(
            'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-bp-album-importer'), 'admin.php')),
            'title' => __('Import BP-Album', BP_MEDIA_TXT_DOMAIN),
            'name' => __('Import BP-Album', BP_MEDIA_TXT_DOMAIN),
            'class' => ($tab == 'bp-media-bp-album-importer') ? $active_class : $idle_class
        );
        return $tabs;
    }

    function ui() {
        $this->progress = new rtProgress();
        $total = $this->get_total_count();
        $finished = $this->batch_import();
        $finished = count($finished);

        //(isset($total) && isset($finished) && is_array($total) && is_array($finished)){
        echo '<div id="bpmedia-bpalbumimporter">';
        if ( !$total ) {
            echo '<p><strong>' . __('You have nothing to import') . '</strong></p>';
        } elseif ($this->active != 1) {
            echo '<strong>';
            echo '<span class="finished">' . $finished . '</span> / <span class="total">' . $total . '</span>';
            echo '</strong>';
            $progress = 100;
            if ($total != 0) {
                $todo = $total - $finished;
                $steps = ceil($todo / 20);
                $laststep = $todo % 20;
                $progress = $this->progress->progress($finished, $total);
                echo '<input type="hidden" value="' . $finished . '" name="finished"/>';
                echo '<input type="hidden" value="' . $total . '" name="total"/>';
                echo '<input type="hidden" value="' . $todo . '" name="todo"/>';
                echo '<input type="hidden" value="' . $steps . '" name="steps"/>';
                echo '<input type="hidden" value="' . $laststep . '" name="laststep"/>';
                $this->progress->progress_ui($progress);
                echo "<br>";
            }
            echo '<button id="bpmedia-bpalbumimport" class="button button-primary">';
            _e('Start', BP_MEDIA_TXT_DOMAIN);
            echo '</button>';
        } else {
            $install_link = wp_nonce_url(admin_url('plugins.php?action=deactivate&amp;plugin=' . urlencode($this->path)), 'deactivate-plugin_' . $this->path);
            echo '<p><strong>' . sprintf(__('Please <a href="%s">deactivate</a> BP-Album first', BP_MEDIA_TXT_DOMAIN), $install_link) . '</strong></p>';
        }
        echo '</div>';
    }

    function create_album($album_name = 'BP Album', $author_id) {
        global $bp_media,$wpdb;

        if (array_key_exists('bp_album_import_name', $bp_media->options)) {
            if ($bp_media->options['bp_album_import_name'] != '') {
                $album_name = $bp_media->options['bp_album_import_name'];
            }
        }
        
        $query = "SELECT ID from $wpdb->posts WHERE post_type='bp_media_album' AND post_status = 'publish' AND post_author = $author_id AND post_title LIKE '$album_name'";
        $result = $wpdb->get_results($query);
        if (count($result) < 1) {
            $album = new BPMediaAlbum();
            $album->add_album($album_name, $author_id);
            $album_id = $album->get_id();
        } else {
            $album_id = $result[0]->ID;
        }
        return $album_id;
    }

    function get_total_count() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        if ($this->table_exists($table) && $this->active != -1) {
            return $wpdb->query("SELECT * FROM {$table}");
        }
        return 0;
    }

    static function batch_import($offset = 0) {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $bp_album_data = $wpdb->get_results(
                "SELECT * FROM {$table} WHERE import_status='0'
					LIMIT 10 OFFSET {$offset}"
        );
        return $bp_album_data;
    }

    static function bpmedia_ajax_import_callback() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $offset = 0; //$_GET['offset'];

        $bp_album_data = BPMediaAlbumimporter::batch_import($offset);

        foreach ($bp_album_data as &$bp_album_item) {
            $album_id = BPMediaAlbumimporter::create_album('BP Album', $bp_album_item->owner_id);
            BPMediaImporter::add_media(
                    $album_id, $bp_album_item->title, $bp_album_item->description, $bp_album_item->pic_org_path, $bp_album_item->privacy, $bp_album_item->owner_id
            );
            $wpdb->update($table,array('import_status' => 1),array('id' => $bp_album_item));
        }
    }

}

?>
