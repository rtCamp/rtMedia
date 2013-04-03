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
        global $wpdb;
        parent::__construct();
        $table = "{$wpdb->base_prefix}bp_album";
        if (BPMediaImporter::table_exists($table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1 && !$this->column_exists('import_status')) {
            $this->update_table();
        }
    }

    function update_table() {
        if ($this->column_exists('import_status'))
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

    function ui() {
        $this->progress = new rtProgress();
        $total = BPMediaAlbumimporter::get_total_count();
        $remaining_comments = $this->get_remaining_comments();
        $finished = BPMediaAlbumimporter::get_completed_count();
        $finished_comments = $this->get_finished_comments();
        $total_comments = (int) $finished_comments + (int) $remaining_comments;

        //(isset($total) && isset($finished) && is_array($total) && is_array($finished)){
        echo '<div id="bpmedia-bpalbumimporter">';
        if ($finished[0]->media != $total[0]->media) {
            if (!$total) {
                echo '<p><strong>' . __('You have nothing to import') . '</strong></p>';
            } elseif (BPMediaAlbumimporter::_active('bp-album/loader.php') != 1) {
                echo '<div id="setting-error-bp-album-importer" class="error settings-error below-h2">
<p><strong>' . __('This process is irreversible. Please take a backup of your database and files, before proceeding.', BP_MEDIA_TXT_DOMAIN) . '</strong></p></div>';
                echo '<div class="bp-album-users">';
                echo '<strong>';
                echo __('Users', BP_MEDIA_TXT_DOMAIN) . ': <span class="finished">' . $finished[0]->users . '</span> / <span class="total">' . $total[0]->users . '</span>';
                echo '</strong>';
                if ($total[0]->users != 0) {
                    $users_progress = $this->progress->progress($finished[0]->users, $total[0]->users);
                    $this->progress->progress_ui($users_progress);
                }
                echo '</div>';
                echo '<br />';
                echo '<div class="bp-album-media">';
                echo '<strong>';
                echo __('Media', BP_MEDIA_TXT_DOMAIN) . ': <span class="finished">' . $finished[0]->media . '</span> / <span class="total">' . $total[0]->media . '</span>';
                echo '</strong>';
                $progress = 100;
                if ($total[0]->media != 0) {
                    $todo = $total[0]->media - $finished[0]->media;
//                    $steps = ceil($todo / 20);
//                    $laststep = $todo % 20;
                    $steps = ceil($todo / 1);
                    $laststep = $todo % 1;
                    $progress = $this->progress->progress($finished[0]->media, $total[0]->media);
                    echo '<input type="hidden" value="' . $finished[0]->media . '" name="finished"/>';
                    echo '<input type="hidden" value="' . $total[0]->media . '" name="total"/>';
                    echo '<input type="hidden" value="' . $todo . '" name="todo"/>';
                    echo '<input type="hidden" value="' . $steps . '" name="steps"/>';
                    echo '<input type="hidden" value="' . $laststep . '" name="laststep"/>';
                    $this->progress->progress_ui($progress);
                }
                echo '</div>';
                echo "<br>";
                echo '<div class="bp-album-comments">';
                echo '<strong>';
                echo __('Comments', BP_MEDIA_TXT_DOMAIN) . ': <span class="finished">' . $finished_comments . '</span> / <span class="total">' . $total_comments . '</span>';
                echo '</strong>';
                if ($total_comments != 0) {
                    $comments_progress = $this->progress->progress($finished_comments, $total_comments);
                    $this->progress->progress_ui($comments_progress);
                }
                echo '</div>';
                echo '<br />';
                echo '<button id="bpmedia-bpalbumimport" class="button button-primary">';
                _e('Start', BP_MEDIA_TXT_DOMAIN);
                echo '</button>';
            } else {
                $install_link = wp_nonce_url(admin_url('plugins.php?action=deactivate&amp;plugin=' . urlencode($this->path)), 'deactivate-plugin_' . $this->path);
                echo '<p>' . sprintf(__('Please <a class="deactivate-bp-album" href="%s">deactivate</a> BP-Album first.', BP_MEDIA_TXT_DOMAIN), $install_link) . '</p>';
            }
        } else {
            echo '<p class="info">';
            _e('All media from BP Album has been imported. However, there are a lot of extra files and a database table eating up your resources. Would you like to delete them now?', BP_MEDIA_TXT_DOMAIN);
            echo '</p>';
            echo '<div id="setting-error-bp-album-importer" class="error settings-error below-h2"> 
<p><strong>' . __('This process is irreversible. Please take a backup of your database and files, before proceeding.', BP_MEDIA_TXT_DOMAIN) . '</strong></p></div>';
            echo '<button id="bpmedia-bpalbumimport-cleanup" class="button button-primary">';
            _e('Clean Up', BP_MEDIA_TXT_DOMAIN);
            echo '</button>';
        }
        echo '</div>';
    }

    function create_album($author_id, $album_name = 'Imported Media') {
        global $bp_media, $wpdb;

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
        $wpdb->update($wpdb->base_prefix . 'bp_activity', array('secondary_item_id' => -999), array('id' => get_post_meta($album_id, 'bp_media_child_activity', true)));

        return $album_id;
    }

    static function get_total_count() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        if (BPMediaAlbumimporter::table_exists($table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            return $wpdb->get_results("SELECT COUNT(distinct owner_id) as users, COUNT(id) as media FROM $table");
        }
        return 0;
    }
    
    static function get_total_users() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        if (BPMediaAlbumimporter::table_exists($table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            return $wpdb->get_var("SELECT COUNT( DISTINCT owner_id ) FROM $table");
        }
        return 0;
    }

    static function get_finished_users() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $users = $this->get_users();
        $count = 0;
        if (BPMediaImporter::table_exists($table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            foreach ($users as $user) {
                $user_status = $wpdb->get_var("SELECT COUNT( id ) FROM $table WHERE import_status=0 AND owner_id = $user->owner_id");
                if (!$user_status) {
                    $count++;
                }
            }
            return $count;
        }
        return 0;
    }

    function get_remaining_comments() {
        global $wpdb;
        $bp_album_table = $wpdb->base_prefix . 'bp_album';
        $activity_table = $wpdb->base_prefix . 'bp_activity';
        if ($this->table_exists($bp_album_table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            return $wpdb->get_var("SELECT SUM( b.count ) AS total
                                        FROM (
                                            SELECT (
                                                SELECT COUNT( a.id ) 
                                                FROM $activity_table a
                                                WHERE a.item_id = activity.id
                                                AND a.component =  'activity'
                                                AND a.type =  'activity_comment'
                                            ) AS count
                                            FROM $activity_table AS activity
                                            INNER JOIN $bp_album_table AS album ON ( album.id = activity.item_id ) 
                                            WHERE activity.component =  'album'
                                            AND activity.type =  'bp_album_picture'
                                            AND album.import_status =0
                                        )b");
        }
        return 0;
    }

    function get_finished_comments() {
        global $wpdb;
        $bp_album_table = $wpdb->base_prefix . 'bp_album';
        $activity_table = $wpdb->base_prefix . 'bp_activity';
        if ($this->table_exists($bp_album_table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            return $wpdb->get_var("SELECT COUNT( activity.id ) AS count
                                        FROM $activity_table AS activity
                                        INNER JOIN $bp_album_table AS album ON ( activity.item_id = album.import_status ) 
                                        WHERE activity.component =  'activity'
                                        AND activity.type =  'activity_comment'");
        }
        return 0;
    }

    static function get_completed_count() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        if (BPMediaAlbumimporter::table_exists($table) && BPMediaAlbumimporter::_active('bp-album/loader.php') != -1) {
            return $wpdb->get_results("SELECT COUNT(distinct owner_id) as users, COUNT(id) as media FROM $table WHERE import_status!=0");
        }
        return 0;
    }

    static function batch_import($count = 20) {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $bp_album_data = $wpdb->get_results("SELECT * FROM $table WHERE import_status = 0 ORDER BY owner_id LIMIT $count");
        return $bp_album_data;
    }

    static function bpmedia_ajax_import_callback() {

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
//        $count = isset($_GET['count']) ? $_GET['count'] : 20;
//        $offset = ($page > 1) ? (($page - 1) * 20 + $count) : 0;
        $count = isset($_GET['count']) ? $_GET['count'] : 1;
//        $offset = ($page > 1) ? (($page - 2) * 1 + $count) : 0;
        $bp_album_data = BPMediaAlbumimporter::batch_import($count);
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $comments = 0;
        foreach ($bp_album_data as &$bp_album_item) {

            if (get_site_option('bp_media_bp_album_importer_base_path') == '') {
                $base_path = pathinfo($bp_album_item->pic_org_path);
                update_site_option('bp_media_bp_album_importer_base_path', $base_path['dirname']);
            }
            $album_id = BPMediaAlbumimporter::create_album($bp_album_item->owner_id, 'Imported Media');
            $imported_media_id = BPMediaImporter::add_media(
                            $album_id, $bp_album_item->title, $bp_album_item->description, $bp_album_item->pic_org_path, $bp_album_item->privacy, $bp_album_item->owner_id, 'Imported Media'
            );
            $comments += (int) BPMediaAlbumimporter::update_recorded_time_and_comments($imported_media_id, $bp_album_item->id, "{$wpdb->base_prefix}bp_album");
            $wpdb->update($table, array('import_status' => $imported_media_id), array('id' => $bp_album_item->id), array('%d'), array('%d'));
        }
        
        $finished_users = BPMediaAlbumimporter::get_completed_count();
        
        echo json_encode(array('page' => $page, 'users' => $finished_users[0]->users, 'comments' => $comments));
        die();
    }

    static function cleanup_after_install() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'bp_album';
        $dir = get_site_option('bp_media_bp_album_importer_base_path');
        BPMediaImporter::cleanup($table, $dir);
        die();
    }

    static function update_recorded_time_and_comments($media, $bp_album_id, $table) {
        global $wpdb;
        if (function_exists('bp_activity_add')) {
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
            $activity_id = get_post_meta($media->get_id(), 'bp_media_child_activity', true);
            error_log('check=' . $activity_id);
            if ($activity_id) {
                $date_uploaded = $wpdb->get_var("SELECT date_uploaded from $table WHERE id = $bp_album_id");
                $old_activity_id = $wpdb->get_var("SELECT id from {$wpdb->base_prefix}bp_activity WHERE component = 'album' AND type = 'bp_album_picture' AND item_id = $bp_album_id");
                $comments = $wpdb->get_results("SELECT id,secondary_item_id from {$wpdb->base_prefix}bp_activity WHERE component = 'activity' AND type = 'activity_comment' AND item_id = $old_activity_id");
                error_log(var_export($comments, true));
                foreach ($comments as $comment) {
                    $update = array('item_id' => $activity_id);
                    if ($comment->secondary_item_id == $old_activity_id) {
                        $update['secondary_item_id'] = $activity_id;
                    }
                    $wpdb->update($wpdb->base_prefix . 'bp_activity', $update, array('id' => $comment->id));
                    BP_Activity_Activity::rebuild_activity_comment_tree($activity_id);
                }
                $wpdb->update($wpdb->base_prefix . 'bp_activity', array('date_recorded' => $date_uploaded), array('id' => $activity_id));
                return count($comments);
            }

            return 0;
        }
    }

    static function bp_album_deactivate() {
        deactivate_plugins('bp-album/loader.php');
        die(true);
    }

}

?>
