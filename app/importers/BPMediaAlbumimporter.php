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
        if ($this->active != -1 && !$this->column_exists('import_status')) {
            $this->update_table();
        }
    }

    function update_table() {
		if($this->column_exists('import_status'))
			return;
        global $wpdb;
        return $wpdb->query(
                        "ALTER TABLE {$wpdb->base_prefix}bp_album ADD COLUMN
					import_status TINYINT (1) NOT NULL DEFAULT 0"
        );
    }

    function column_exists($column) {
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

        $total = $this->get_total_count();
        $finished = $this->get_completed_count();


        //(isset($total) && isset($finished) && is_array($total) && is_array($finished)){
        echo '<div id="bpmedia-bpalbumimporter">';
		if($finished!=$total){
        if ( !$total ) {
            echo '<p><strong>' . __('You have nothing to import') . '</strong></p>';
        } elseif ($this->active != 1) {
			echo '<p class="warning">';
			_e('This process is irreversible. Please take a backup of your database and files, before proceeding.' , BP_MEDIA_TXT_DOMAIN);
			echo '</p>';
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
        }}else{
			echo '<p class="info">';
			_e('All media from BP Album has been imported. However, there are a lot of extra files and a database table eating up your resources. Would you like to delete them now?' , BP_MEDIA_TXT_DOMAIN);
			echo '</p>';
			echo '<p class="warning">';
			_e('This process is irreversible. Please take a backup of your database and files, before proceeding' , BP_MEDIA_TXT_DOMAIN);
			echo '</p>';
			echo '<button id="bpmedia-bpalbumimport-cleanup" class="button button-primary">';
            _e('Clean Up', BP_MEDIA_TXT_DOMAIN);
            echo '</button>';
		}
        echo '</div>';
    }

    function create_album( $author_id,$album_name = 'BP Album') {
        global $bp_media,$wpdb;

        if (array_key_exists('bp_album_import_name', $bp_media->options)) {
            if ($bp_media->options['bp_album_import_name'] != '') {
                $album_name = $bp_media->options['bp_album_import_name'];
            }
        }

        $query = "SELECT ID from $wpdb->posts WHERE post_type='bp_media_album' AND post_status = 'publish' AND post_author = $author_id AND post_title LIKE '{$album_name}'";
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
            return $wpdb->query("SELECT * FROM $table");
        }
        return 0;
    }

	function get_completed_count() {
		global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        if ($this->table_exists($table) && $this->active != -1) {
            return $wpdb->query("SELECT * FROM $table WHERE import_status=1");
        }
        return 0;
	}

    static function batch_import($offset = 0,$count = 20) {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $bp_album_data = $wpdb->get_results("SELECT * FROM $table WHERE import_status=0  LIMIT $count OFFSET $offset");
        return $bp_album_data;
    }

    static function bpmedia_ajax_import_callback() {

        $page = isset($_GET['page'])?$_GET['page']:1;
		$count = isset($_GET['count'])?$_GET['count']:20;
		$offset = ($page>1)?(($page-1)*20 + $count):0;

        $bp_album_data = BPMediaAlbumimporter::batch_import($offset,$count);
		global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';

        foreach ($bp_album_data as &$bp_album_item) {

			if(get_site_option('bp_media_bp_album_importer_base_path')==''){
				$base_path = path_info($bp_album_item->pic_org_path);
				update_site_option('bp_media_bp_album_importer_base_path', $base_path['dirname']);
			}
            $album_id = BPMediaAlbumimporter::create_album($bp_album_item->owner_id,'BP Album');
            BPMediaImporter::add_media(
                    $album_id, $bp_album_item->title, $bp_album_item->description, $bp_album_item->pic_org_path, $bp_album_item->privacy, $bp_album_item->owner_id
            );
            $wpdb->update($table,array('import_status' => 1),array('id' => $bp_album_item->id), array('%d'),array('%d'));
        }

		echo $page;
		die();
    }

	static function cleanup_after_install(){
		global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
		$dir = get_site_option('bp_media_bp_album_importer_base_path');
		BPMediaImporter::cleanup($table,$dir);
		die();
	}

}

?>
