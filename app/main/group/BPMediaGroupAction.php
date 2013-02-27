<?php

/**
 * Description of BPMediaGroupLoader
 *
 * @author faishal
 */
class BPMediaGroupAction {
static function bp_media_groups_set_query() {
        global $bp, $bp_media, $bp_media_query, $bp_media_posts_per_page;
		$enabled = $bp_media->enabled();
		$default_tab = $bp_media->default_tab();
		$defaults_tab= $default_tab;
		if($default_tab!='audio') $defaults_tab.='s';

        if (isset($bp->current_action) && $bp->current_action == BP_MEDIA_SLUG) {
            $current_tab = constant('BP_MEDIA_'.strtoupper($defaults_tab).'_SLUG');
            if (isset($bp->action_variables[0])) {
                $current_tab = $bp->action_variables[0];
            }
            if ($current_tab) {
                switch ($current_tab) {
                    case BP_MEDIA_IMAGES_SLUG:
                        $type = 'image';
                        break;
                    case BP_MEDIA_AUDIO_SLUG:
                        $type = 'audio';
                        break;
                    case BP_MEDIA_VIDEOS_SLUG:
                        $type = 'video';
                        break;
                    default :
                        $type = null;
                }
                if (bp_action_variable(1) == 'page' && is_numeric(bp_action_variable(2))) {
                    $paged = bp_action_variable(2);
                } else {
                    $paged = 1;
                }
                if ($type) {
                    $args = array(
                        'post_type' => 'attachment',
                        'post_status' => 'any',
                        'post_mime_type' => $type,
                        'meta_key' => 'bp-media-key',
                        'meta_value' => -bp_get_current_group_id(),
                        'meta_compare' => '=',
                        'paged' => $paged,
                        'posts_per_page' => $bp_media_posts_per_page
                    );
                    $bp_media_query = new WP_Query($args);
                }
            }
        }
    }
	static function filter_entries(){
		global $bp_media;
		$enabled = $bp_media->enabled();
		if(isset($enabled['upload'])) unset($enabled['upload']);
		if(isset($enabled['album'])) unset($enabled['album']);
		foreach($enabled as $type=>$active){
			if($active==true){
				$filters[] = $type;
			}

		}

		if(count($filters)==1) $filters = $filters[0];
		return $filters;
	}

    /**
     * Called on bp_init by screen functions
     * Initializes the albums query for groups
     *
     * @uses global $bp, $bp_media_albums_query
     *
     * @since BuddyPress Media 2.2
     */

    /**
     *
     * @global type $bp
     * @global WP_Query $bp_media_albums_query
     */
    static function bp_media_groups_albums_set_query() {
        global $bp, $bp_media_albums_query;
        if (isset($bp->action_variables) && isset($bp->action_variables[1]) && $bp->action_variables[1] == 'page' && isset($bp->action_variables[2]) && is_numeric($bp->action_variables[2])) {
            $paged = $bp->action_variables[2];
        } else {
            $paged = 1;
        }

        if (isset($bp->action_variables[0]) && $bp->action_variables[0] == BP_MEDIA_ALBUMS_SLUG) {
            $args = array(
                'post_type' => 'bp_media_album',
                'paged' => $paged,
                'meta_key' => 'bp-media-key',
                'meta_value' => -bp_get_current_group_id(),
                'meta_compare' => '='
            );
            $bp_media_albums_query = new WP_Query($args);
        }
    }

    /**
     *
     * @param BPMediaHostWordpress $media
     * @param type $hidden
     * @return boolean
     */
    static function bp_media_groups_activity_create_after_add_media($media, $hidden = false) {
        if (function_exists('bp_activity_add')) {
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
            $args = array(
                'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', BP_MEDIA_TXT_DOMAIN), bp_core_get_userlink($media->get_author()), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>')),
                'content' => $media->get_media_activity_content(),
                'primary_link' => $media->get_url(),
                'item_id' => $media->get_id(),
                'type' => 'media_upload',
                'user_id' => $media->get_author()
            );
            $hidden = apply_filters('bp_media_force_hide_activity', $hidden);
            if ($hidden) {
                $args['secondary_item_id'] = -999;
                //do_action('bp_media_album_updated',$media->get_album_id());
            }
            $activity_id = BPMediaFunction::record_activity($args);
            add_post_meta($media->get_id(), 'bp_media_child_activity', $activity_id);
        }
    }

    //add_action('bp_media_groups_after_add_media','bp_media_groups_activity_create_after_add_media',10,2);
    /**
     *
     * @global type $bp
     */

    /**
     *
     * @global type $bp
     */
    static function bp_media_groups_redirection_handler() {
        global $bp;
        echo '<pre>';
        var_dump($bp);
        echo '</pre>';
        die();
    }

    //add_action('bp_media_init','bp_media_groups_redirection_handler');
    /**
     *
     * @return boolean
     */
    static function bp_media_groups_force_hide_activity() {
        return true;
    }

}
