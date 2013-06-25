<?php

/**
 * Description of BuddyPress_Migration
 *
 * @author faishal
 */
class RTMediaMigration {

    public $bmp_table = "";

    function __construct() {
        global $wpdb;
        $this->bmp_table = $wpdb->prefix . "rt_rtm_media";
        add_action('admin_menu', array($this, 'menu'));
        add_action('wp_ajax_bp_media_rt_db_migration', array($this, "migrate_to_new_db"));
//        add_action('init', array(&$this, 'init_sessions'));
    }

    static function table_exists($table) {
        global $wpdb;

        if ($wpdb->query("SHOW TABLES LIKE '" . $table . "'") == 1) {
            return true;
        }

        return false;
    }

    function init_sessions() {
        if (!session_id()) {
            session_start();
        }
    }

    function menu() {
        add_submenu_page('rt-media-settings', __('Migration', 'buddypress-media'), __('Migration', 'buddypress-media'), 'manage_options', 'rt-media-migration', array($this, 'test'));
    }

    function get_total_count() {
        global $wpdb;
        if (function_exists("bp_core_get_table_prefix"))
            $bp_prefix = bp_core_get_table_prefix();
        else
            $bp_prefix = "";
        $sql_album_usercount = "select count(*) FROM $wpdb->usermeta where meta_key ='bp-media-default-album' ";

        $_SESSION["migration_user_album"] = $wpdb->get_var($sql_album_usercount);
        $count = intval($_SESSION["migration_user_album"]);

        if ($this->table_exists($bp_prefix . "bp_groups_groupmeta")) {
            $sql_album_groupcount = "select count(*) FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album'";
            $_SESSION["migration_group_album"] = $wpdb->get_var($sql_album_groupcount);
            $count += intval($_SESSION["migration_group_album"]);
        }
        if ($this->table_exists($bp_prefix . "bp_activity")) {
            //$sql_bpm_comment_count = "select count(*) from {$bp_prefix}bp_activity where component='activity' and type='activity_comment' and is_spam <> 1 and ;";
            $sql_bpm_comment_count = "SELECT
                                                count(*)
                                            FROM
                                                {$bp_prefix}bp_activity
                                            where
                                                type = 'activity_comment' and  is_spam <> 1
                                                    and item_id in (SELECT
                                                        id
                                                    FROM
                                                        wp_bp_activity
                                                    where
                                                        component = 'activity'
                                                            and type = 'activity_update' and is_spam <> 1
                                                            and item_id in (select post_id
                                                            from
                                                                {$wpdb->postmeta} a

                                                            where
                                                                a.post_id > 0
                                                                    and a.meta_key = 'bp-media-key'))";


            //echo  $sql_bpm_comment_count;

            $_SESSION["migration_activity"] = $wpdb->get_var($sql_bpm_comment_count);
            $count +=intval($_SESSION["migration_activity"]);
        }

        $sql = "select count(*)
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp_media_privacy'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.id)
                where
                    a.post_id > 0
                        and a.meta_key = 'bp-media-key'";


        $_SESSION["migration_media"] = $wpdb->get_var($sql);
        $count += intval($_SESSION["migration_media"]);
        //var_dump($_SESSION);
        return $count;
    }

    function get_last_imported() {
        $album = get_site_option("rt-media-global-albums");
        $album_id = $album[0];

        global $wpdb;
        $sql = "select media_id
                from {$this->bmp_table} where blog_id = %d and media_id < %d order by media_id desc";
        $row = $wpdb->get_row($wpdb->prepare($sql, get_current_blog_id(), $album_id));
        if ($row) {
            return $row->media_id;
        } else {
            return false;
        }
    }

    function get_done_count($flag = false) {
        global $wpdb;
        $sql = "select count(*)
                from {$this->bmp_table} where blog_id = %d and media_id in (select a.post_id
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp_media_privacy'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.id)
                where
                    a.post_id > 0
                        and a.meta_key = 'bp-media-key')";

        $media_count = $wpdb->get_var($wpdb->prepare($sql, get_current_blog_id()));
        if ($flag)
            return $media_count - 1;
        $state = intval(get_site_option("rt-media-migration", "0"));
        if ($state == 5) {
            $album_count = intval($_SESSION["migration_user_album"]);
            $album_count += (isset($_SESSION["migration_group_album"])) ? intval($_SESSION["migration_group_album"]) : 0;
        } else {
            $album_count = 0;
        }

        $comment_sql = $wpdb->get_var("select count(*) from $wpdb->comments where comment_post_ID in (select media_id from $this->bmp_table) and comment_agent=''");

        //echo $media_count . "--" . $album_count . "--" . $comment_sql;
        return $media_count + $album_count + $comment_sql;
    }

    function manage_album() {
        $album = get_site_option("rt-media-global-albums");

        $album_id = $album[0];

        $album_post_type = "rt_media_album";

        global $wpdb;
        if (function_exists("bp_core_get_table_prefix"))
            $bp_prefix = bp_core_get_table_prefix();
        else
            $bp_prefix = "";

        $sql_group = "update $wpdb->posts set post_parent='{$album_id}' where post_parent in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";
        if ($this->table_exists($bp_prefix . "bp_groups_groupmeta")) {
            $sql_group .= " or post_parent in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
        }

        $sql_delete = "delete from $wpdb->posts where post_type='bp_media_album' and (ID in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";
        if ($this->table_exists($bp_prefix . "bp_groups_groupmeta")) {
            $sql_delete .= " or ID in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
        }
        $sql_delete .= ")";

        $sql = "update $wpdb->posts set post_type='{$album_post_type}' where post_type='bp_media_album'";

        if ($wpdb->query($sql_group) !== false) {
            if ($wpdb->query($sql_delete) !== false) {
                if ($wpdb->query($sql) !== false) {
                    update_site_option("rt-media-migration", "5");
                    return true;
                }
            }
        }
        return false;
    }

    function test() {
        $prog = new rtProgress();
        $total = $this->get_total_count();
        $done = $this->get_done_count();
        ?>
        <div class="wrap">
            <h2>rtMedia Migration</h2>
            <h3><?php _e("It will migrate following things"); ?> </h3>
            User Albums : <?php echo $_SESSION["migration_user_album"]; ?><br />
            <?php if (isset($_SESSION["migration_group_album"])) { ?>
                Groups Albums : <?php echo $_SESSION["migration_group_album"]; ?><br />
            <?php } ?>
            Media : <?php echo $_SESSION["migration_media"]; ?><br />
            <?php if (isset($_SESSION["migration_activity"])) { ?>
                Comments : <?php echo $_SESSION["migration_activity"]; ?><br />
            <?php } ?>
            <hr />

            <?php
            echo '<span class="pending">' . $this->formatSeconds($total - $done) . '</span><br />';
            echo '<span class="finished">' . $done . '</span>/<span class="total">' . $total . '</span>';
            echo '<img src="images/loading.gif" alt="syncing" id="rtMediaSyncing" style="display:none" />';
            $temp = $prog->progress($done, $total);
            $prog->progress_ui($temp, true);
            ?>
            <script type="text/javascript">
                function db_start_migration(db_done, db_total) {
                    if (db_done < db_total) {
                        jQuery("#rtMediaSyncing").show();
                        jQuery.ajax({
                            url: rt_media_admin_ajax,
                            type: 'post',
                            data: {
                                "action": "bp_media_rt_db_migration",
                                "done": db_done
                            },
                            success: function (sdata) {
                                data = JSON.parse(sdata);
                                if (data.status) {
                                    done = parseInt(data.done);
                                    total = parseInt(data.total);
                                    var progw = Math.ceil((done / total) * 100);
                                    if (progw > 100) {
                                        progw = 100;
                                    };
                                    jQuery('#rtprogressbar>div').css('width', progw + '%');
                                    jQuery('span.finished').html(done);
                                    jQuery('span.total').html(total);
                                    jQuery('span.pending').html(data.pending);
                                    db_start_migration(done, total);
                                } else {
                                    alert("Migration Done");
                                    jQuery("#rtMediaSyncing").hide();
                                }
                            }
                        });
                    } else {
                        alert("Migration Done");
                        jQuery("#rtMediaSyncing").hide();
                    }
                }

                jQuery(document).on('click', '#submit', function (e) {
                    e.preventDefault();
                    var db_done = <?php echo $done; ?> ;
                    var db_total = <?php echo $total; ?> ;
                    db_start_migration(db_done, db_total);
                });
            </script>
            <hr />
            <input type="button" id="submit" value="start" class="button button-primary" />

        </div>
        <?php
    }

    function migrate_to_new_db($lastid = 0, $limit = 1) {

        if (!isset($_SESSION["migration_media"]))
            $this->get_total_count();

        $state = intval(get_site_option("rt-media-migration"));
        if ($state < 5) {
            if ($this->manage_album()) {
                $done = $this->get_done_count();
                $total = $this->get_total_count();

                $pending = $total - $done;
                if ($pending < 0)
                    $pending = 0;

                $pending_time = $this->formatSeconds($pending);

                echo json_encode(array("status" => true, "done" => $done, "total" => $this->get_total_count(), "pending" => $pending_time));
                die();
            }
        }

        if (intval($_SESSION["migration_media"]) >= $this->get_done_count(true)) {

            if (!$lastid) {
                $lastid = $this->get_last_imported();
                if (!$lastid)
                    $lastid = 0;
            }
            global $wpdb;
            $sql = "select
                    a.post_id as 'post_id',
                    a.meta_value as 'privacy',
                    b.meta_value as 'context_id',
                    c.meta_value as 'activity_id',
                    p.post_type,
                    p.post_mime_type,
                    p.post_author as 'media_author'
                from
                    {$wpdb->postmeta} a
                        left join
                    {$wpdb->postmeta} b ON ((a.post_id = b.post_id)
                        and (b.meta_key = 'bp-media-key'))
                        left join
                    {$wpdb->postmeta} c ON (a.post_id = c.post_id)
                        and (c.meta_key = 'bp_media_child_activity')
                        left join
                    {$wpdb->posts} p ON (a.post_id = p.id)
                where
                    a.post_id > %d
                        and a.meta_key = 'bp_media_privacy'
                order by a.post_id
                limit %d";


            $results = $wpdb->get_results($wpdb->prepare($sql, $lastid, $limit));

            if (function_exists("bp_core_get_table_prefix"))
                $bp_prefix = bp_core_get_table_prefix();
            else
                $bp_prefix = "";
            if ($results) {
                $blog_id = get_current_blog_id();
                foreach ($results as $result) {

                    $media_id = $result->post_id;

                    if (intval($result->context_id) > 0) {
                        $media_context = "profile";
                        $prefix = "users/" . abs(intval($result->context_id));
                    } else {
                        $media_context = "group";
                        $prefix = "groups/" . abs(intval($result->context_id));
                    }



                    if ($result->post_type != "attachment") {
                        $media_type = "album";
                    } else {
                        $mime_type = strtolower($result->post_mime_type);
                        if (strpos($mime_type, "image") == 0) {
                            $media_type = "image";
                        } else if (strpos($mime_type, "audio") == 0) {
                            $media_type = "audio";
                        } else if (strpos($mime_type, "video") == 0) {
                            $media_type = "video";
                        } else {
                            $media_type = "other";
                        }
                    }

                    if ($media_type != 'album')
                        $this->import_media($media_id, $prefix);
//                        $this->import_media($media_id, $prefix, $result->activity_id);

                    if ($this->table_exists($bp_prefix . "bp_activity") && class_exists("BP_Activity_Activity")) {
                        $bp_activity = new BP_Activity_Activity();
                        $activity_sql = $wpdb->prepare("SELECT * FROM {$bp_prefix}bp_activity where component='activity' and  type='activity_update' and item_id =%d order by id", $media_id);
                        $all_activity = $wpdb->get_results($activity_sql);
                        remove_all_actions("wp_insert_comment");
                        foreach ($all_activity as $activity) {
                            $comments = $bp_activity->get_activity_comments($activity->id, $activity->mptt_left, $activity->mptt_right);
                            $exclude = get_post_meta($media_id, "rt_media_imported_activity", true);
                            if (!is_array($exclude)) {
                                $exclude = array();
                            }
                            if ($comments)
                                $this->insert_comment($media_id, $comments, $exclude);
                        }
                    }

                    $wpdb->insert(
                            $this->bmp_table, array(
                        'blog_id' => $blog_id,
                        'media_id' => $media_id,
                        'media_type' => $media_type,
                        "context" => $media_context,
                        "context_id" => abs(intval($result->context_id)),
                        "activity_id" => $result->activity_id,
                        "privacy" => intval($result->privacy) * 10,
                        "media_author" => $result->media_author,
                            ), array('%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d')
                    );
                    update_option("rtMedia-media-migration-last-id", $media_id);
                }
            }
        } else {
            echo json_encode(array("status" => false, "done" => $done, "total" => $this->get_total_count()));
            die();
        }
        $done = $this->get_done_count();
        $total = $this->get_total_count();

        $pending = $total - $done;
        if ($pending < 0)
            $pending = 0;

        $pending_time = $this->formatSeconds($pending);

        echo json_encode(array("status" => true, "done" => $done, "total" => $this->get_total_count(), "pending" => $pending_time));
        die();
    }

    function import_media($id, $prefix) {
        $delete = false;
        $attached_file = get_attached_file($id);
        $attached_file_option = get_post_meta($id, '_wp_attached_file', true);
        $basename = wp_basename($attached_file);
        $file_folder_path = trailingslashit(str_replace($basename, '', $attached_file));


        $siteurl = get_option('siteurl');
        $upload_path = trim(get_option('upload_path'));

        if (empty($upload_path) || 'wp-content/uploads' == $upload_path) {
            $dir = WP_CONTENT_DIR . '/uploads';
        } elseif (0 !== strpos($upload_path, ABSPATH)) {
            // $dir is absolute, $upload_path is (maybe) relative to ABSPATH
            $dir = path_join(ABSPATH, $upload_path);
        } else {
            $dir = $upload_path;
        }

        if (!$url = get_option('upload_url_path')) {
            if (empty($upload_path) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ))
                $url = WP_CONTENT_URL . '/uploads';
            else
                $url = trailingslashit($siteurl) . $upload_path;
        }

        // Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
        // We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
        if (defined('UPLOADS') && !( is_multisite() && get_site_option('ms_files_rewriting') )) {
            $dir = ABSPATH . UPLOADS;
            $url = trailingslashit($siteurl) . UPLOADS;
        }

        // If multisite (and if not the main site in a post-MU network)
        if (is_multisite() && !( is_main_site() && defined('MULTISITE') )) {

            if (!get_site_option('ms_files_rewriting')) {
                // If ms-files rewriting is disabled (networks created post-3.5), it is fairly straightforward:
                // Append sites/%d if we're not on the main site (for post-MU networks). (The extra directory
                // prevents a four-digit ID from conflicting with a year-based directory for the main site.
                // But if a MU-era network has disabled ms-files rewriting manually, they don't need the extra
                // directory, as they never had wp-content/uploads for the main site.)

                if (defined('MULTISITE'))
                    $ms_dir = '/sites/' . get_current_blog_id();
                else
                    $ms_dir = '/' . get_current_blog_id();

                $dir .= $ms_dir;
                $url .= $ms_dir;
            } elseif (defined('UPLOADS') && !ms_is_switched()) {
                // Handle the old-form ms-files.php rewriting if the network still has that enabled.
                // When ms-files rewriting is enabled, then we only listen to UPLOADS when:
                //   1) we are not on the main site in a post-MU network,
                //      as wp-content/uploads is used there, and
                //   2) we are not switched, as ms_upload_constants() hardcodes
                //      these constants to reflect the original blog ID.
                //
			// Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
                // (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
                // as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
                // rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)

                if (defined('BLOGUPLOADDIR'))
                    $dir = untrailingslashit(BLOGUPLOADDIR);
                else
                    $dir = ABSPATH . UPLOADS;
                $url = trailingslashit($siteurl) . 'files';
            }
        }

        $basedir = trailingslashit($dir);
        $baseurl = trailingslashit($url);

        $new_file_folder_path = trailingslashit(str_replace($basedir, $basedir . "rtMedia/$prefix/", $file_folder_path));

        $year_month = untrailingslashit(str_replace($basedir, '', $file_folder_path));


        $metadata = wp_get_attachment_metadata($id);
        $backup_metadata = get_post_meta($id, '_wp_attachment_backup_sizes', true);
        $instagram_thumbs = get_post_meta($id, '_instagram_thumbs', true);
        $instagram_full_images = get_post_meta($id, '_instagram_full_images', true);
        $instagram_metadata = get_post_meta($id, '_instagram_metadata', true);

        if (wp_mkdir_p($basedir . "rtMedia/$prefix/" . $year_month)) {
            if (copy($attached_file, str_replace($basedir, $basedir . "rtMedia/$prefix/", $attached_file))) {
                $delete = true;

                if (isset($metadata['sizes'])) {
                    foreach ($metadata['sizes'] as $size) {
                        if (!copy($file_folder_path . $size['file'], $new_file_folder_path . $size['file'])) {
                            $delete = false;
                        } else {
                            $delete_sizes[] = $file_folder_path . $size['file'];
                        }
                    }
                }
                if ($backup_metadata) {
                    foreach ($backup_metadata as $backup_images) {
                        if (!copy($file_folder_path . $backup_images['file'], $new_file_folder_path . $backup_images['file'])) {
                            $delete = false;
                        } else {
                            $delete_sizes[] = $file_folder_path . $backup_images['file'];
                        }
                    }
                }

                if ($instagram_thumbs) {
                    foreach ($instagram_thumbs as $key => $insta_thumb) {
                        if (!copy(str_replace($baseurl, $basedir, $insta_thumb), str_replace($baseurl, $basedir . "rtMedia/$prefix/", $insta_thumb))) {
                            $delete = false;
                        } else {
                            $delete_sizes[] = str_replace($baseurl, $basedir, $insta_thumb);
                            $instagram_thumbs_new[$key] = str_replace($baseurl, $baseurl . "rtMedia/$prefix/", $insta_thumb);
                        }
                    }
                }

                if ($instagram_full_images) {
                    foreach ($instagram_full_images as $key => $insta_full_image) {
                        if (!copy($insta_full_image, str_replace($basedir, $basedir . "rtMedia/$prefix/", $insta_full_image))) {
                            $delete = false;
                        } else {
                            $delete_sizes[] = $insta_full_image;
                            $instagram_full_images_new[$key] = str_replace($basedir, $basedir . "rtMedia/$prefix", $insta_full_image);
                        }
                    }
                }

                if ($instagram_metadata) {
                    $instagram_metadata_new = $instagram_metadata;
                    foreach ($instagram_metadata as $wp_size => $insta_metadata) {
                        if (isset($insta_metadata['file'])) {
                            if (!copy($basedir . $insta_metadata['file'], $basedir . "rtMedia/$prefix/" . $insta_metadata['file'])) {
                                $delete = false;
                            } else {
                                $delete_sizes[] = $basedir . $insta_metadata['file'];
                                $instagram_metadata_new[$wp_size]['file'] = "rtMedia/$prefix/" . $insta_metadata['file'];
                                if (isset($insta_metadata['sizes'])) {
                                    foreach ($insta_metadata['sizes'] as $key => $insta_size) {
                                        if (!copy($file_folder_path . $insta_size['file'], $new_file_folder_path . $insta_size['file'])) {
                                            $delete = false;
                                        } else {
                                            $delete_sizes[] = $file_folder_path . $insta_size['file'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($delete) {
                    if (file_exists($attached_file))
                        unlink($attached_file);

                    if (isset($delete_sizes)) {
                        foreach ($delete_sizes as $delete_size) {
                            if (file_exists($delete_size))
                                unlink($delete_size);
                        }
                    }
                    update_post_meta($id, '_wp_attached_file', "rtMedia/$prefix/" . $attached_file_option);
                    if (isset($metadata['file'])) {
                        $metadata['file'] = "rtMedia/$prefix/" . $metadata['file'];
                        wp_update_attachment_metadata($id, $metadata);
                    }
                    if ($instagram_thumbs) {
                        update_post_meta($id, '_instagram_thumbs', $instagram_thumbs_new);
                    }
                    if ($instagram_full_images) {
                        update_post_meta($id, '_instagram_full_images', $instagram_full_images_new);
                    }
                    if ($instagram_metadata) {
                        update_post_meta($id, '_instagram_metadata', $instagram_metadata_new);
                    }


                    $attachment = array();
                    $attachment['ID'] = $id;
                    $attachment['guid'] = str_replace($baseurl, $baseurl . "rtMedia/$prefix/", get_post_field('guid', $id));

                    wp_update_post($attachment);
                }
            }
        }
    }

    function formatSeconds($secondsLeft) {

        $minuteInSeconds = 60;
        $hourInSeconds = $minuteInSeconds * 60;
        $dayInSeconds = $hourInSeconds * 24;

        $days = floor($secondsLeft / $dayInSeconds);
        $secondsLeft = $secondsLeft % $dayInSeconds;

        $hours = floor($secondsLeft / $hourInSeconds);
        $secondsLeft = $secondsLeft % $hourInSeconds;

        $minutes = floor($secondsLeft / $minuteInSeconds);

        $seconds = $secondsLeft % $minuteInSeconds;

        $timeComponents = array();

        if ($days > 0) {
            $timeComponents[] = $days . " day" . ($days > 1 ? "s" : "");
        }

        if ($hours > 0) {
            $timeComponents[] = $hours . " hour" . ($hours > 1 ? "s" : "");
        }

        if ($minutes > 0) {
            $timeComponents[] = $minutes . " minute" . ($minutes > 1 ? "s" : "");
        }

        if ($seconds > 0) {
            $timeComponents[] = $seconds . " second" . ($seconds > 1 ? "s" : "");
        }
        if (count($timeComponents) > 0) {
            $formattedTimeRemaining = implode(", ", $timeComponents);
            $formattedTimeRemaining = trim($formattedTimeRemaining);
        } else {
            $formattedTimeRemaining = "No time remaining.";
        }

        return $formattedTimeRemaining;
    }

    function insert_comment($media_id, $data, $exclude, $parent_commnet_id = 0) {
        foreach ($data as $cmnt) {
            $comment_id = 0;
            if (!key_exists(strval($cmnt->id), $exclude)) {
                $commentdata = array(
                    "comment_date" => $cmnt->date_recorded,
                    "comment_parent" => $parent_commnet_id,
                    "user_id" => $cmnt->user_id,
                    "comment_content" => $cmnt->content,
                    "comment_author_email" => $cmnt->user_email,
                    'comment_post_ID' => $media_id,
                    'comment_author' => $cmnt->display_name,
                    'comment_author_url' => '',
                    'comment_author_IP' => '');
                $comment_id = wp_insert_comment($commentdata);
                $exclude[strval($cmnt->id)] = $comment_id;
            } else {
                $comment_id = $exclude[strval($cmnt->id)];
            }

            update_post_meta($media_id, "rt_media_imported_activity", $exclude);

            if (is_array($cmnt->children)) {
                $this->insert_comment($media_id, $cmnt->children, $exclude, $comment_id);
            }
        }
    }

}
?>