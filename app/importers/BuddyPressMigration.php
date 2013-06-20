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
        $this->bmp_table = $wpdb->prefix . "rt_rtm_media";
        add_action('admin_menu', array($this, 'menu'));
        add_action('wp_ajax_bp_media_rt_db_migration', array($this, "migrate_to_new_db"));
        add_action('admin_init', array($this, "migration_settings"));
        //exit;
        add_action('init', array(&$this,'init_sessions'));

    }
    
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }
    }


    function menu() {
        add_submenu_page('bp-media-settings', __('Migration', 'buddypress-media'), __('Migration', 'buddypress-media'), 'manage_options', 'bp-media-migration', array($this, 'test'));
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
        if(function_exists("bp_core_get_table_prefix")) 
            $bp_prefix = bp_core_get_table_prefix();
        $sql_album_usercount = "select count(*) FROM $wpdb->usermeta where meta_key ='bp-media-default-album' ";
        
        $_SESSION["migration_user_album"] = $wpdb->get_var($sql_album_usercount);
        $count = intval($_SESSION["migration_user_album"]);
        
        if (function_exists("bp_is_active")) {
            if (bp_is_active('groups')) {
                $sql_album_groupcount = "select count(*) FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album'";
                $_SESSION["migration_group_album"] = $wpdb->get_var($sql_album_groupcount);
                $count += intval($_SESSION["migration_group_album"]);
            }
            if (bp_is_active('activity')) {
                $sql_bpm_comment_count = "select count(*) from {$bp_prefix}bp_activity where component='activity' and type='activity_comment';";
                $_SESSION["migration_activity"] = $wpdb->get_var($sql_bpm_comment_count);
                $count +=intval($_SESSION["migration_activity"]);
            }
        }

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
                    
                    $_SESSION["migration_media"]= $wpdb->get_var($sql) ;
        $count += intval($_SESSION["migration_media"]);

        return $count;
        
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
        $media_count =  $wpdb->get_var($wpdb->prepare($sql, get_current_blog_id()));
        $state = intval(get_site_option("rt-media-migration","0"));
        if($state<=5){
            $album_count = intval($_SESSION["migration_user_album"]) + (isset($_SESSION["migration_group_album"]))? intval($_SESSION["migration_group_album"]): 0;
        }else{
            $album_count= 0;
        }
        
        return $media_count + $album_count;
        
    }
    function manage_album(){
        $album = get_site_option("rt-media-global-albums");
        
        $album_id = $album[0];
        
        $album_post_type="rt_media_album";
        
        global $wpdb;
        if(function_exists("bp_core_get_table_prefix")) 
            $bp_prefix = bp_core_get_table_prefix();

        $sql_group = "update $wpdb->posts set post_parent='{$album_id}' where post_parent in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";
        if (function_exists("bp_is_active")) {
            if (bp_is_active('groups')) {
                $sql_group .= " or post_parent in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
            }
        }
        $sql_delete = "delete from $wpdb->posts where post_type='bp_media_album' and (ID in (select meta_value FROM $wpdb->usermeta where meta_key ='bp-media-default-album') ";
        if (function_exists("bp_is_active")) {
            if (bp_is_active('groups')) {
                $sql_delete .= " or ID in (select meta_value FROM {$bp_prefix}bp_groups_groupmeta where meta_key ='bp_media_default_album')";
            }
        }
        $sql_delete .= ")";

        $sql = "update $wpdb->posts set post_type='{$album_post_type}' where post_type='bp_media_album'";
        
        if($wpdb->query($sql_group) !== false){
            if($wpdb->query($sql_delete) !== false ){
                if($wpdb->query($sql) !== false ){
                    update_site_option("rt-media-migration", "5");
                    return true;
                }
            }
        }
        return false;
    }
    function test() {
        $prog = new rtProgress();
        $done = $this->get_done_count();
        $total = $this->get_total_count();
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

            jQuery(document).on('click','#submit',function(e){
                e.preventDefault();
                var db_done = <?php echo $done; ?>;
                var db_total = <?php echo $total; ?>;
                db_start_migration(db_done,db_total);
            });
        </script>

        <input type="button" id="submit" value="start" class="button button-primary" />

        <?php
    }

    function migrate_to_new_db($lastid = 0, $limit = 5) {
        if(!isset($_SESSION["migration_media"]))
            $this->get_total_count ();
        
        $state= intval(get_site_option("rt-media-migration"));
        if($state< 5){
            if($this->manage_album()){
                echo json_encode(array("status" => true, "done" => $this->get_done_count(), "total" => $this->get_total_count()));
                die();
            }else{
                echo "error";
            }
        }
        
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