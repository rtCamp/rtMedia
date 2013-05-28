<?php

/**
 * Description of BuddyPress_Migration
 *
 * @author faishal
 */
class BuddyPressMigration {

    public $bmp_table = "";

    function __construct() {
        global $wpdb;
        $this->bmp_table = $wpdb->prefix . "rt_bpm_media";
        add_action(bp_core_admin_hook(), array($this, 'menu'));
        add_action('wp_ajax_bp_media_rt_db_migration', array($this, "migrate_to_new_db"));
        add_action('admin_init', array($this, "migration_settings"));
    }

    function menu() {
        add_submenu_page('bp-media-settings', __('Migration', 'buddypress-media'), __('Migration', 'buddypress-media'), 'manage_options', 'bp-media-migration', array($this, 'migration_page'));
    }

    public function migration_settings() {
        add_settings_section('bpm-migration', __('Migration', 'buddypress-media'), array($this, 'test'), 'bp-media-migration');
    }

    /**
     * Render the BuddyPress Media Migration page
     */
    public function migration_page() {
        global $bp_media_admin;
        $bp_media_admin->render_page('bp-media-migration', 'bp-media-migration','Start Migration');
    }

    function get_total_count() {
        global $wpdb;
        $sql = "select count(*)
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
                    a.post_id > 0
                        and a.meta_key = 'bp_media_privacy'";
        return $wpdb->get_var($sql);
    }

    function get_last_imported() {
        global $wpdb;
        $sql = "select media_id
                from {$this->bmp_table} where blog_id = %d order by media_id desc limit 1 ";
        return $wpdb->get_var($wpdb->prepare($sql, get_current_blog_id()));
    }

    function get_done_count() {
        global $wpdb;
        $sql = "select count(*)
                from {$this->bmp_table} where blog_id = %d and media_id in (select a.post_id
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
                    a.post_id > 0
                        and a.meta_key = 'bp_media_privacy')";
        return $wpdb->get_var($wpdb->prepare($sql, get_current_blog_id()));
    }

    function test() {
        $prog = new rtProgress();
        $done = $this->get_done_count() ? $this->get_done_count() : 0;
        $total = $this->get_total_count() ? $this->get_total_count() : 0;
        echo '<span class="finished">'.$done.'</span>/<span class="total">'.$total.'</span>';
        $temp = $prog->progress($done, $total);
        $prog->progress_ui($temp, true);
        ?>
        <script>
                            
            function db_start_migration(db_done,db_total) {
                if (db_done < db_total) {
                    jQuery.ajax({
                        url: bp_media_admin_ajax,
                        type: 'post',
                        data: {"action": "bp_media_rt_db_migration", "done": db_done},
                        success: function(sdata) {
                            data = JSON.parse(sdata);
                            if (data.status) {
                                done = parseInt(data.done);
                                total = parseInt(data.total);
                                var progw = Math.ceil((done/total) *100);
                                if(progw>100){
                                    progw=100;
                                };
                                jQuery('#bp-media-settings-boxes #rtprogressbar>div').css('width',progw+'%');
                                jQuery('#bp-media-settings-boxes span.finished').html(done);
                                jQuery('#bp-media-settings-boxes span.total').html(total);
                                db_start_migration(done,total);
                            }

                        }
                    });
                }
            }
                    
            jQuery('.bp-media-migration').on('click','#submit',function(e){
                e.preventDefault();
                var db_done = <?php echo $done; ?>;
                var db_total = <?php echo $total; ?>;
                db_start_migration(db_done,db_total);
            });
        </script>



        <?php
    }

    function migrate_to_new_db($lastid = 0, $limit = 5) {
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
                    p.post_mime_type
                    
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
        if ($results) {
            $blog_id = get_current_blog_id();
            foreach ($results as $result) {
                $media_id = $result->post_id;

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

                if (intval($result->context_id) > 0) {
                    $media_context = "profile";
                } else {
                    $media_context = "group";
                }
//                echo $media_context ."-". abs(intval($result->context_id)) . "<br />";

                $wpdb->insert(
                        $this->bmp_table, array(
                    'blog_id' => $blog_id,
                    'media_id' => $media_id,
                    'media_type' => $media_type,
                    "context" => $media_context,
                    "context_id" => abs(intval($result->context_id)),
                    "activity_id" => $result->activity_id,
                    "privacy" => $result->privacy,
                        ), array('%d', '%d', '%s', '%s', '%d', '%d', '%d')
                );
            }
        }
        echo json_encode(array("status" => true, "done" => $this->get_done_count(), "total" => $this->get_total_count()));
        die();
    }

}
?>
