<?php
/**
 * Description of BPMediaUpgrade
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaUpgrade')) {

    class BPMediaUpgrade {

        public function __construct() {
            if (is_admin()) {
                if (version_compare(BP_MEDIA_DB_VERSION, get_site_option('bp_media_db_version', '1.0'), '>')) {
                    add_action('admin_notices', array($this, 'upgrade_db'));
                }
                add_action('wp_loaded', array($this, 'upgrade'));
            }
        }

        /**
         * Displays admin notice to upgrade BuddyPress Media Database
         * 
         * @global string BP_MEDIA_TXT_DOMAIN
         */

        /**
         * 
         * @global type $bp_media
         */
        public function upgrade_db() {
            global $bp_media;
            ?>
            <div class="error"><p><?php
            printf(__('Please click upgrade to upgrade the database of BuddyPress Media <a class="button" id="refresh_media_count" href ="%s" class="button" title="It will migrate your BuddyPress Media\'s earlier database to new database.">Upgrade</a>', BP_MEDIA_TXT_DOMAIN), bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings', 'bp_media_upgrade_db' => 1, 'wp_nonce' => wp_create_nonce('bp_media_upgrade_db')), 'admin.php')))
            ?>
                </p></div>
            <?php
        }

        /**
         * Upgrade Script
         */
        public function upgrade() {
            if (isset($_GET['bp_media_upgrade_db']) && empty($_REQUEST['settings-updated'])) {
                check_admin_referer('bp_media_upgrade_db', 'wp_nonce');
                $current_version = get_site_option('bp_media_db_version', '1.0');
                if ($current_version == '2.0')
                    $this->upgrade_2_0_to_2_1();
                else
                    $this->upgrade_1_0_to_2_1();
                remove_action('admin_notices', 'upgrade_db');
            }
        }

        /**
         * Upgrade from BuddyPress Media 1.0 to 2.1
         * @global wpdb $wpdb
         * @global string BP_MEDIA_TXT_DOMAIN
         */

        /**
         * 
         * @global wpdb $wpdb
         * @global type $bp_media
         */
        public function upgrade_1_0_to_2_1() {
            global $wpdb, $bp_media;
            $post_wall = __('Wall Posts', BP_MEDIA_TXT_DOMAIN);
            remove_filter('bp_activity_get_user_join_filter', 'BPMediaFilters::activity_query_filter', 10);
            /* @var $wpdb wpdb */
            $wall_posts_album_ids = array();
            do {
                $media_files = new WP_Query(array(
                            'post_type' => 'bp_media',
                            'posts_per_page' => 10
                        ));
                $media_files = isset($media_files->posts) ? $media_files->posts : null;
                if (is_array($media_files) && count($media_files)) {
                    foreach ($media_files as $media_file) {
                        $attachment_id = get_post_meta($media_file->ID, 'bp_media_child_attachment', true);
                        $child_activity = get_post_meta($media_file->ID, 'bp_media_child_activity', true);
                        update_post_meta($attachment_id, 'bp_media_child_activity', $child_activity);
                        $attachment = get_post($attachment_id, ARRAY_A);
                        if (isset($wall_posts_album_ids[$media_file->post_author])) {
                            $wall_posts_id = $wall_posts_album_ids[$media_file->post_author];
                        } else {
                            $wall_posts_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = $post_wall AND post_author = '" . $media_file->post_author . "' AND post_type='bp_media_album'");
                            if ($wall_posts_id == null) {
                                $album = new BPMediaAlbum();
                                $album->add_album($post_wall, $media_file->post_author);
                                $wall_posts_id = $album->get_id();
                            }
                            if (!$wall_posts_id) {
                                continue; //This condition should never be encountered
                            }
                            $wall_posts_album_ids[$media_file->post_author] = $wall_posts_id;
                        }
                        $attachment['post_parent'] = $wall_posts_id;
                        wp_update_post($attachment);
                        update_post_meta($attachment_id, 'bp-media-key', $media_file->post_author);
                        $activity = bp_activity_get(array('in' => intval($child_activity)));
                        if (isset($activity['activities'][0]->id))
                            $activity = $activity['activities'][0];
                        try {
                            $bp_media = new BPMediaHostWordpress($attachment_id);
                        } catch (exception $e) {
                            continue;
                        }
                        $args = array(
                            'content' => $bp_media->get_media_activity_content(),
                            'id' => $child_activity,
                            'type' => 'media_upload',
                            'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($media_file->post_author), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>')),
                            'primary_link' => $bp_media->get_url(),
                            'item_id' => $attachment_id,
                            'recorded_time' => $activity->date_recorded,
                            'user_id' => $bp_media->get_author()
                        );
                        $act_id = BPMediaFunction::record_activity($args);
                        bp_activity_delete_meta($child_activity, 'bp_media_parent_post');
                        wp_delete_post($media_file->ID);
                    }
                } else {
                    break;
                }
            } while (1);
            update_site_option('bp_media_db_version', BP_MEDIA_DB_VERSION);
            add_action('admin_notices', 'BPMediaUpgradeScript::database_updated_notice');
            wp_cache_flush();
        }

        /**
         * Upgrade from BuddyPress Media 2.0 to 2.1
         * 
         * @global string BP_MEDIA_TXT_DOMAIN
         */

        /**
         * 
         * @global type $bp_media
         */
        public function upgrade_2_0_to_2_1() {
            global $bp_media;
            $page = 0;
            while ($media_entries = BPMediaUpgradeScript::return_query_posts(array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'meta_key' => 'bp-media-key',
                'meta_value' => 0,
                'meta_compare' => '>',
                'paged' => ++$page,
                'postsperpage' => 10
            ))) {
                foreach ($media_entries as $media) {
                    try {
                        $bp_media = new BPMediaHostWordpress($media->ID);
                    } catch (exception $e) {
                        continue;
                    }
                    $child_activity = get_post_meta($media->ID, 'bp_media_child_activity', true);
                    if ($child_activity) {
                        $activity = bp_activity_get(array('in' => intval($child_activity)));
                        if (isset($activity['activities'][0]->id))
                            $activity = $activity['activities'][0];
                        else
                            continue;
                        $args = array(
                            'content' => $bp_media->get_media_activity_content(),
                            'id' => $child_activity,
                            'type' => 'media_upload',
                            'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($bp_media->get_author()), '<a href="' . $bp_media->get_url() . '">' . $bp_media->get_media_activity_type() . '</a>')),
                            'primary_link' => $bp_media->get_url(),
                            'item_id' => $activity->item_id,
                            'recorded_time' => $activity->date_recorded,
                            'user_id' => $bp_media->get_author()
                        );
                        BPMediaFunction::record_activity($args);
                    }
                }
            }
            update_site_option('bp_media_db_version', BP_MEDIA_DB_VERSION);
            add_action('admin_notices', 'BPMediaUpgradeScript::database_updated_notice');
            wp_cache_flush();
        }

    }

}
?>
