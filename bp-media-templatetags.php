<?php
/**
 * Buddypress Media Template Class
 * This class is a test class..
 * @author rtCamp
 * @version 1.2.1-dev
 * @package Media_Component
 *
 */
class BP_User_Media_Template {
    var $current_picture = -1;
    var $picture_count;
    var $pictures;
    var $picture; //where the hell this initialized?

    var $in_the_loop;

    var $pag_page;
    var $pag_num;
    var $pag_links;
    var $total_picture_count;
    var $media_slug;
    /**
     * Defines the constructor for the BP_User_Media
     *
     * @global object $bp :the BP object
     * @param int $user_id_filter user id
     * @param int $page pagination
     * @param int $per_page page value
     * @param int $max max media to be displayed
     * @param string $media_type type of media request
     * @param string $view is single page
     *
     */
    function BP_User_Media_Template( $user_id_filter, $page, $per_page, $max, $media_type, $view = 'multiple',$group_id,$album_id ) { //kapil
        global $bp;
        if ( !$user_id )
            $user_id = $bp->loggedin_user->id;
        $this->pag_page = isset( $_GET['fpage'] ) ? intval( $_GET['fpage'] ) : $page;
        $this->pag_num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;

        if ( !$this->pictures = wp_cache_get( 'bp_pictures_for_user_' . $user_id, 'bp' ) ) {
            $this->pictures = bp_pictures_get_pictures_for_user( $user_id_filter, $media_type,$view,$group_id,$album_id);//kapil

            $this->media_slug = $this->pictures['media_slug'];
            wp_cache_set( 'bp_pictures_for_user_' . $user_id, $this->pictures, 'bp' );
//          wp_cache_set($key, $data, $flag = '', $expire = 0)
        }

        if ( !$max )
            $this->total_picture_count = (int)$this->pictures['count'];
        else
            $this->total_picture_count = (int)$max;

        $this->pictures = array_slice( (array)$this->pictures['pictures'], intval( ( $this->pag_page - 1 ) * $this->pag_num), intval( $this->pag_num ) );

        if ( $max ) {
            if ( $max >= count($this->pictures) )
                $this->picture_count = count($this->pictures);
            else
                $this->picture_count = (int)$max;
        } else {
            $this->picture_count = count($this->pictures);
        }


        $this->pag_links = paginate_links( array(
                'base' => add_query_arg( array('fpage'=> '%#%', 'num' => $this->pag_num) ),
                'format' => '',
                'total' => ceil(( $this->total_picture_count) / $this->pag_num),
                'current' => $this->pag_page,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'mid_size' => 1
        ));

    }
    /**
     * This function counts the picture for  the given reason <code> if ( $this->picture_count ) </code>
     * @return boolean
     *
     */
    function has_pictures() {
        if ( $this->picture_count )
            return true;

        return false;
    }

    function next_picture() {
        $this->current_picture++;
        $this->picture = $this->pictures[$this->current_picture];

        return $this->picture;
    }
    /**
     * <code>rewind_pictures()</code>
     *
     */
    function rewind_pictures() {
        $this->current_picture = -1;
        if ( $this->picture_count > 0 ) {
            $this->picture = $this->pictures[0];
        }
    }
    /**
     *This function returns true/false values for the users media.
     * @return boolean
     *
     */
    function user_pictures() {
        if ( $this->current_picture + 1 < $this->picture_count ) {
            return true;
        } elseif ( $this->current_picture + 1 == $this->picture_count ) {
            do_action('loop_end');
            // Do some cleaning up after the loop
            $this->rewind_pictures();
        }

        $this->in_the_loop = false;
        return false;
    }
    /**
     * this function returns the single media
     * @global object $picture
     * @global array $kaltura_validation_data
     *
     */
    function the_picture() {
        global $picture,$kaltura_validation_data;
        $this->in_the_loop = true;
        $picture = $this->next_picture();
        if ( 0 == $this->current_picture ) // loop has just started
            do_action('loop_start');
    }
}

/**
 * This functions returns all the media available.
 *
 * @global object $pictures_template It holds all the values for the media
 * @global object $bp It is buddypress global object. for more read @link http://codex.buddypress.org/developer-docs/the-bp-global/
 * @param array $args
 * @return array
 *
 */
function bp_has_media( $args = '' ) {
    global $pictures_template,$bp;
    if($bp->media->view == 'single')
        $view = 'single';
    else
        $view = 'multiple';

    $defaults = array(
            'user_id' => false,
            'per_page' => 8,
            'page' =>1,
            'max' => false,
            'scope'=> 'mediaall',
            'group_id'=> 0,
            'view' => $view,
            'album_id' => 0 //album_id = 0 >> default album //kapil
    );

    $r = wp_parse_args( $args, $defaults );

    extract( $r, EXTR_SKIP );
    
    //group_id initialized only when group template is loaded : kapil
    if ( $bp->groups->current_group->id ) {
        $group_id = $bp->groups->current_group->id;
    }

    $pictures_template = new BP_User_Media_Template( $user_id,$page, $per_page, $max,$scope, $view,$group_id,$album_id ); //kapil
    return $pictures_template->has_pictures();
}
/**
 * This function returns media.
 * @global object $pictures_template
 * @return object
 *
 */
function bp_pictures() {
    global $pictures_template;
    return $pictures_template->user_pictures();
}
/**
 * This function returns the <code>$pictures_template->the_picture();</code>
 * @global object $pictures_template
 * @return object
 *
 */
function bp_the_picture() {
    global $pictures_template;
    return $pictures_template->the_picture();
}
function is_media_exists($id) {
    global $wpdb,$bp;
    $qry = "SELECT id FROM {$bp->media->table_media_data} WHERE id='$id'";
    $result = $wpdb->get_col($qry);
//    die();
    if($result)
        return true;
    else
        return false;
}
/**
 * This function counts the pagination for the media available.
 * @global object $bp
 * @global object $pictures_template
 *
 */
function bp_pictures_pagination_count() {
    global $bp, $pictures_template;
    $media_slug = $pictures_template->media_slug;

    switch ($media_slug) {
        case 'mediaall':
            $media_slug = 'All Media';
            break;
        case 'photo':
            $media_slug = 'Photo';
            break;
        case 'audio':
            $media_slug = 'Audio';
            break;
        case 'video':
            $media_slug = 'Video';
            break;

    }

    $from_num = intval( ( $pictures_template->pag_page - 1 ) * $pictures_template->pag_num ) + 1;
    $to_num = ( $from_num + ( $pictures_template->pag_num - 1 ) > $pictures_template->total_picture_count ) ? $pictures_template->total_picture_count : $from_num + ( $pictures_template->pag_num - 1 ) ;

    echo sprintf( __( 'Viewing %s %d to %d (of %d %s)', 'buddypress' ), $media_slug, $from_num, $to_num, $pictures_template->total_picture_count,$media_slug ); ?> &nbsp;
<img id="ajax-loader-pictures" src="<?php echo $bp->core->image_base ?>/ajax-loader.gif" height="7" alt="<?php _e( "Loading", "buddypress" ) ?>" style="display: none;" /><?php
}
/**
 * This functions fetches and displayes thr pagination links.
 */
function bp_pictures_pagination_links() {
    echo bp_get_pictures_pagination_links();
}
/**
 *It applys filter to the current <code>bp_get_pictures_pagination_links</code>.
 * @global object $pictures_template
 * @return filter
 *
 */
function bp_get_pictures_pagination_links() {
    global $pictures_template;
    return apply_filters( 'bp_get_pictures_pagination_links', $pictures_template->pag_links );
}
/**
 * This function prints the value of title whatever is fethed.
 */
function bp_picture_title() {
    echo bp_get_picture_title();
}
/**
 * This function echo the current media rating individually
 */
function bp_media_rating() {
    echo bp_get_media_rating();
}
/**
 * Here is the actual logic for the rating functionality
 * @global object $pictures_template
 * @global object $single_pic_template It holds data for the single media
 */
function bp_get_media_rating() {
    global $pictures_template,$single_pic_template;
    $view = $pictures_template->picture->localview;
    $tot_rank = $pictures_template->picture->local_tot_rank;
    $ctr_rank = $pictures_template->picture->local_rating_ctr;
    $rank.'a '.$view.' b '. $tot_rank.'c '.$ctr_rank;

    $local_entry = $pictures_template->picture->id;
    if($tot_rank==0 && ctr_rank ==0) {
        get_actual_rating($r,$local_entry);
    }else {
        try {
            $rating = $tot_rank / $ctr_rank;
            $r =  ceil($rating);
            get_actual_rating($r,$local_entry);
        }
        catch(Exception $e) {
            echo 'Error !!!';
        }
    }
}
/**
 * This function retrives and sets the rating star.
 * @param int $r : The actual rank
 * @param int $local_entry : db local entryid
 */
function get_actual_rating($r,$local_entry) {
    switch ($r) {
        case 0:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value ="1" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="2" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="3" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="4" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="5" class="star {split:2}"/>
                    </div>';
            break;


        case 1:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value ="1" class="star {split:2}" checked="checked"/>
                    <input name="star'.$local_entry.'" type="radio" value ="2" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="3" class="star {split:2}" />
                    <input name="star'.$local_entry.'" type="radio" value ="4" class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value ="5" class="star {split:2}"/>
                    </div>';
            break;

        case 2:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value = 1 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 2 class="star {split:2}" checked="checked"/>
                    <input name="star'.$local_entry.'" type="radio" value = 3 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 4 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 5 class="star {split:2}"/>
                    </div>';

            break;

        case 3:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value = 1 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 2 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 3 class="star {split:2}" checked="checked"/>
                    <input name="star'.$local_entry.'" type="radio" value = 4 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 5 class="star {split:2}"/>
                    </div>';
            break;
        case 4:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value = 1 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 2 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 3 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 4 class="star {split:2}" checked="checked"/>
                    <input name="star'.$local_entry.'" type="radio" value = 5 class="star {split:2}"/>
                    </div>';
            break;
        case 5:

            echo '<div class="jquerystar">
                    <input name="star'.$local_entry.'" type="radio" value = 1 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 2 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 3 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 4 class="star {split:2}"/>
                    <input name="star'.$local_entry.'" type="radio" value = 5 class="star {split:2}" checked="checked"/>
                    </div>';
            break;
        default:
            echo '<div class = "jquerystar"> Be 1st to rate it </div>';

    }

}
/**
 *It returns the single title from the Kaltura Server
 * @global object $pictures_template
 * @return var
 */
function bp_get_picture_title() {
    global $pictures_template;

    return apply_filters( 'bp_get_picture_title',substr($pictures_template->picture->name,0,20).'...');
}


function bp_picture_description() {
    echo bp_get_picture_description;
}
function bp_get_picture_description() {
    global $pictures_template;

    return apply_filters( 'bp_get_picture_description', $pictures_template->picture->description);
}

/**
 * This function prints the value for thumbnail url which is fetched from Kaltura Server
 * @global object $bp
 * @global object $pictures_template.
 *
 */
function bp_picture_small_link() {
    global $bp,$pictures_template;
    echo $pictures_template->picture->thumbnailUrl;

}

/**
 *  Functions for Single picture View
 */

/**
 * This function simply checks the function and echo whatever it recieves from the corrosponding function
 */

function bp_picture_view_link() {
    echo bp_get_picture_view_link();
}
/**
 *It returns the proper link for <a href> tag.
 * @global object $pictures_template.
 * @global object $bp.
 * @return <type>
 *
 */
function bp_get_picture_view_link() {
    global $pictures_template, $bp;//$pictures_template->picture->id shud point to the id and not entry_id
    $url = site_url() . '/' . BP_MEDIA_SLUG .'/' . $pictures_template->media_slug . '/' . $pictures_template->picture->db_id;
//    if($bp->current_component == BP_GROUPS_SLUG){
//        return apply_filters('bp_get_picture_view_link',$bp->displayed_user->domain . $bp->media->slug .'/'. $pictures_template->media_slug .'/'. $pictures_template->picture->db_id) ;
//    }else {
    return apply_filters('bp_get_picture_view_link',$url) ;
//    }

}
/**
 * Returns the single media path
 * @global object $bp
 * @global object $single_pic_template
 * @return returns the single media template path
 */
function bp_picture_view() {
    global $bp,$single_pic_template;

    return $single_pic_template->pic_mid_path;
}

/**
 *
 * It rates the media and displays on the single page
 * @global <type> $bp
 * @global <type> $single_pic_template
 * @global <type> $pictures_template
 */
function bp_media_user_rated() {
    global $bp,$single_pic_template,$pictures_template;
    $rating = $single_pic_template->tot_rating;
    $views =  $single_pic_template->rating_ctr;

    if ($rating == 0 && $views == 0) {
        get_actual_rating($r = 0,$local_entry);
    }
    else {
        try {
            $rating =  $rating/$views;
            $r = ceil($rating);
            get_actual_rating($r,$local_entry);
        }
        catch(Exception $e) {
            $e. " Oops Error";
        }
    }
}

/**
 *To get the media data for the single view
 * @global object $bp
 * @global object $single_pic_template
 * @return <type>
 */
function get_media_data() {
    global $bp,$single_pic_template;
    $media_type =$bp->current_action;
    if($single_pic_template->picture_id == -9999)
        echo 'This file has been deleted from server due to some reasons ....';
    else {
        switch($single_pic_template->media_type) {
            case '1':
                return bp_rt_media_video_library();
                break;
            case '2':
                return '<img src='. bp_picture_view().' />
                        <input id="current-media-id" type = "hidden" value = "'.$bp->action_variables[0].'"/>
                        <input id="current-user-id" type = "hidden" value = "'.$bp->loggedin_user->id.'"/>
                        ';
                break;
            case '5':
                return bp_rt_media_video_library();
                break;
        }
    }
}
/**
 *This functions embeds the media player using swf object from the Kaltura server
 *
 * @global object $bp
 * @global object $kaltura_validation_data
 * @global object $single_pic_template
 *
 */
function bp_rt_media_video_library() {
    global $bp,$kaltura_validation_data,$single_pic_template;
    $partner_id = $kaltura_validation_data['partner_id'];
    $kaltura_url = $kaltura_validation_data['config']->serviceUrl;
    /*
     * Get the userwise media content.
    */

    ?>
<input id="current-media-id" type = "hidden" value = "<?php echo $bp->action_variables[0]?>"/>
<input id="current-user-id" type = "hidden" value = "<?php echo $bp->loggedin_user->id?>"/>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<div id="asdf1">
    <?php echo rt_get_object_markup();?>
</div>

    <?php

}

/**
 * To call in editor
 */

function rt_get_object_markup() {
    global $bp,$kaltura_validation_data,$single_pic_template,$picture;
    $partner_id = $kaltura_validation_data['partner_id'];
    $kaltura_url = $kaltura_validation_data['config']->serviceUrl;
    ob_start();
    ?>

<object name="kaltura_player" id="kaltura_player" type="application/x-shockwave-flash"
        allowScriptAccess="always" allowNetworking="all"
        allowFullScreen="true" height="335" width="400"
        data="<?php echo  $kaltura_url;?>/index.php/kwidget/cache_st/1274050232/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $picture->id?>">
    <param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" />
    <param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" />
    <param name="movie" value="<?php echo  $kaltura_url;?>/index.php/kwidget/cache_st/1274050232/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $picture->id?>"/>
    <param name="flashVars" value=""/></object>



<?php
    return ob_get_clean();
}

/**
 * returns the current single media title
 */
function bp_single_picture_title() {
    echo bp_get_single_picture_title();
}
/**
 * To get the media title
 * @global object $bp
 * @global object $single_pic_template
 * @global object $pictures_template
 * @return Media Title
 */
function bp_get_single_picture_title() {
    global $bp,$single_pic_template,$pictures_template;

    return apply_filters( 'bp_get_single_picture_title', $single_pic_template->title);
}
/**
 *It returns the single media id.
 * @return id
 */
function bp_single_media_id() {
    return bp_get_single_media_id();
}
/**
 *
 * @global object $bp
 * @global object $single_pic_template
 * @global object $pictures_template
 * @return dataset through filters.
 */
function bp_get_single_media_id() {
    global $bp,$single_pic_template,$pictures_template;
    return apply_filters( 'bp_get_single_media_id', $single_pic_template->id);
}

/**
 * this function displayes the description from the kaltura server
 */
function bp_single_picture_description() {
    echo bp_get_single_picture_description();
}

/**
 *
 * @global object $single_pic_template
 * @return description
 */
function bp_get_single_picture_description() {
    global $single_pic_template;
    if($single_pic_template->description == '')
        $single_pic_template->description = 'You can add Description here...';

    return apply_filters( 'bp_get_single_picture_description', $single_pic_template->description);
}
/**
 *
 * @global object $bp
 * @global object $single_pic_template
 * @return boolean : true/false
 */
function bp_single_pic_exist() {
    global $bp,$single_pic_template;

    if (!$bp->action_variables[0])
        return flase;

    $pic_id=$bp->action_variables[0];
    $single_pic_template=new BP_Media_Picture($pic_id);
    if (!$single_pic_template->id)
        return false;

    return true;
}

/**
 * Echo's the delete links in a herf tag:
 */
function bp_single_pic_delete_link() {
    echo bp_get_single_pic_delete_link();
}
/**
 * Return  the link path with the a tag server copy
 * @global <type> $single_pic_template
 * @global <type> $bp
 * @return <type>
 */
function bp_get_single_pic_delete_link() {
    global $single_pic_template, $bp;

    $pic_id = $single_pic_template->id;
    if ( isMediaOwner($pic_id) ) {

        if ($bp->current_component == $bp->media->slug) {

            return apply_filters( 'bp_get_single_pic_delete_link',
                    '<a id ="yes" class="del" href="'
                    . wp_nonce_url( $bp->root_domain.'/'
                    . $bp->media->slug . '/'.$bp->current_action.'/delete-picture-server/'
                    . $single_pic_template->id, 'bp_single_pic_delete_link_server' ) . '">'
                    . Yes . '</a> ' );
        }
    }
}
/*
 * Echo deleting link local copy
*/
function bp_single_pic_delete_link_local() {
    echo bp_get_single_pic_delete_link_local_copy();
}
/**
 *Deleting link local copy actual implementation
 * @global <type> $single_pic_template
 * @global <type> $bp
 * @return <type>
 */
function bp_get_single_pic_delete_link_local_copy() {
    global $single_pic_template, $bp;
    $pic_id = $single_pic_template->id;
    if ( isMediaOwner($pic_id) ) {
        if ($bp->current_component == $bp->media->slug) {
            return apply_filters( 'bp_get_single_pic_delete_link_local',
                    '<a id ="no" class="del" href="' . wp_nonce_url( $bp->root_domain.'/' . $bp->media->slug . '/'.$bp->current_action.'/delete-picture/'
                    . $single_pic_template->id, 'bp_single_pic_delete_link' ) . '">'
                    . No . '</a> ' );
        }
    }
}

/**
 *Check for the pic owner
 * @global object $bp
 * @global object $single_pic_template
 * @return true/false
 */
function bp_single_pic_check_owner() {
    global $bp,$single_pic_template;
    if ($bp->displayed_user->id == $single_pic_template->user_id)
        return true;

    return false;
}

function bp_single_pic_id() {
    global $single_pic_template;

    return $single_pic_template->id;
}

function bp_get_picture_permalink( $picture_id = false ) {
    global $bp;

    return apply_filters( 'bp_get_picture_permalink', $bp->displayed_user->domain . $bp->media->slug . '/picture/' . $picture_id );
}



/*
 * Wire Functions
*/

function bp_pic_wire_get_post_list( $item_id = null, $title = null, $empty_message = null, $can_post = true, $show_email_notify = false ) {
    global $bp_item_id, $bp_wire_header, $bp_wire_msg, $bp_wire_can_post, $bp_wire_show_email_notify;

    if ( !$item_id )
        return false;

    if ( !$message )
        $empty_message = __("There are currently no wire posts.", 'buddypress');

    if ( !$title )
        $title = __('Wire', 'buddypress');

    /* Pass them as globals, using the same name doesn't work. */
    $bp_item_id = $item_id;
    $bp_wire_header = $title;
    $bp_wire_msg = $empty_message;
    $bp_wire_can_post = $can_post;
    $bp_wire_show_email_notify = $show_email_notify;

    load_template( TEMPLATEPATH . '/media/post-list.php' );
}

function bp_media_wire_get_post_form() {
    global $wire_posts_template;

    if ( is_user_logged_in() && $wire_posts_template->can_post )
        load_template( TEMPLATEPATH . '/media/post-form.php' );
}

function bp_media_wire_get_action() {
    echo bp_get_picture_wire_get_action();
}
function bp_get_picture_wire_get_action() {
    global $bp;

    if ( empty( $bp->current_item ) )
        $uri = $bp->current_action;
    else
        $uri = $bp->current_item;

    if ( $bp->current_component == $bp->wire->slug || $bp->current_component == $bp->profile->slug ) {
        return apply_filters( 'bp_get_picture_wire_get_action', $bp->displayed_user->domain . $bp->wire->slug . '/post/' );
    } else if ($bp->current_component == $bp->media->slug) {
        global $single_pic_template;
        return apply_filters( 'bp_get_picture_wire_get_action', $bp->displayed_user->domain . $bp->media->slug . '/picture/post-wire/' );
    } else {
        return apply_filters( 'bp_get_picture_wire_get_action', site_url() . '/' . $bp->{$bp->current_component}->slug . '/' . $uri . '/' . $bp->wire->slug . '/post/' );
    }
}


function bp_media_wire_delete_link() {
    echo bp_get_picture_wire_delete_link();
}
function bp_get_picture_wire_delete_link() {
    global $wire_posts_template, $bp;

    if ( empty( $bp->current_item ) )
        $uri = $bp->current_action;
    else
        $uri = $bp->current_item;

    if ( ( $wire_posts_template->wire_post->user_id == $bp->loggedin_user->id ) || $bp->is_item_admin || is_site_admin() ) {
        if ( $bp->wire->slug == $bp->current_component || $bp->profile->slug == $bp->current_component ) {
            return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( $bp->displayed_user->domain . $bp->wire->slug . '/delete/' . $wire_posts_template->wire_post->id, 'bp_media_wire_delete_link' ) . '">[' . __('Delete', 'buddypress') . ']</a>' );
        } else if ($bp->current_component == $bp->media->slug) {
            global $single_pic_template;
            return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( $bp->displayed_user->domain . $bp->media->slug . '/picture/delete-wire/' . $wire_posts_template->wire_post->id, 'bp_media_wire_delete_link' ) . '">[' . __('Delete', 'buddypress') . ']</a>' );
        } else {
            return apply_filters( 'bp_get_picture_wire_delete_link', '<a href="' . wp_nonce_url( site_url( $bp->{$bp->current_component}->slug . '/' . $uri . '/wire/delete/' . $wire_posts_template->wire_post->id ), 'bp_media_wire_delete_link' ) . '">[' . __('Delete', 'buddypress') . ']</a>' );
        }
    }
}

/**
 *
 * This function checks that IS current user the Media owner ?
 *
 * @global object $bp
 * @global object $wpdb
 * @param int $picture_id
 * @return boolean true/false
 *
 */
function isMediaOwner($picture_id) {
    global $bp,$wpdb;
    $user_id = $wpdb->get_var("SELECT user_id from {$bp->media->table_media_data} WHERE ID = {$picture_id} ");
    if($user_id == $bp->loggedin_user->id)
        return true;
    else
        return false;
}

function rt_who_owns_this_media($media_id) {
    global $bp,$wpdb;
    $user_id = $wpdb->get_var("SELECT user_id from {$bp->media->table_media_data} WHERE ID = {$media_id} ");
    return $user_id;
}

/*
 * Function for Next and Previous Picture
*/
function next_picture_link($format='%link &raquo;', $link='%title') {
    adjacent_picture_link($format, $link, true);
}
/**
 * This function gets the next and previous media.
 * @param string $format
 * @param string $link
 */
function previous_picture_link($format='&laquo; %link', $link='%title') {
    adjacent_picture_link($format, $link, false);
}
/**
 *It fetches the next /previous media link.
 * @param <type> $format
 * @param <type> $link
 * @param <type> $previous
 * @return <type>
 *
 */
function adjacent_picture_link($format, $link, $previous = true) {
    $picture = get_adjacent_picture($previous);
    if ( !$picture->id )
        return;
    $title = $picture->title;

    if ( empty($picture->title) )
        $title = $previous ? __('Previous') : __('Next');
    $title = $title;
    $string = '<a href="'.$picture->id.'">';
    $link = str_replace('%title', $title, $link);
    $link = $string . $link . '</a>';
    $format = str_replace('%link', $link, $format);
    $adjacent = $previous ? 'previous' : 'next';
    echo apply_filters( "picture_link", $format, $link );
}//function ends

/**
 *Sets the media rating
 * @global <type> $wpdb
 * @global <type> $bp
 * @param <type> $rating
 * @param <type> $id
 */
function set_media_rating($rating,$id) {
    global $wpdb, $bp;

    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $wpdb->query("UPDATE {$bp->media->table_media_data} SET rating = rating + {$rating}, views = views + 1  WHERE ID = {$id} ");
}

function get_media_rating() {
    global $wpdb, $bp;

    $id = $bp->action_variables[0];

    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $view = $wpdb->get_var("SELECT views from {$bp->media->table_media_data} WHERE ID = {$id} ");

    echo "Views : " .'<span class="view">'. $view.'</span>';
}

//bp_media_title_action

//new functions for bp 1.2 : Kapil
function bp_media_locate_template( $template_names, $load = false ) {
//    var_dump($template_names);
    if ( !is_array( $template_names ) )
        return '';
    $located = '';
    foreach($template_names as $template_name) {

        // split template name at the slashes
        $paths = explode( '/', $template_name );


        /**
         * This explode and implode is added to support media with groups component.
         * When $template_name = groups/single/media-list.php, putting 'media' at 0th postion of $template_name,
         * makes to get the proper path of media-list.php template.
         */
        if($paths[0]!= 'media') {
            //insert 'media' to the 0th position of the current array
            array_unshift($paths,'media');
        }
        $template_name = implode('/', $paths);
        // only filter templates names that match our unique starting path
        if ( !empty( $paths[0] ) && 'media' == $paths[0] ) {
            $style_path = STYLESHEETPATH . '/' . $template_name;
            $plugin_path = BP_MEDIA_PLUGIN_DIR . "/themes/{$template_name}";

            if ( file_exists( $style_path )) {
                $located = $style_path;
                break;
            } else if ( file_exists( $plugin_path ) ) {
                $located = $plugin_path;
                break;
            }
        }
    }
    if ($load && '' != $located)
        load_template($located);
    return $located;
}

function bp_media_filter_template( $located_template, $template_names ) {
    // template already located, skip

    if ( !empty( $located_template ) )
        return $located_template;
    // only filter for our component
    if ( $bp->current_component == $bp->media->slug ) {
        return bp_media_locate_template( $template_names );
    }
    return '';
}
add_filter( 'bp_located_template', 'bp_media_filter_template', 10, 2 );

function bp_media_load_template( $template ) {

    bp_core_load_template( $template );
}
function bp_is_user_media() {
    global $bp;

    if ( BP_MEDIA_SLUG == $bp->current_component )
        return true;

    return false;
}

function bp_media_enqueue_url($file) {
    // split template name at the slashes

    $stylesheet_path = get_stylesheet_directory_uri();
    $suffix = explode($stylesheet_path,$file);

    $suffix_str=$suffix[1];
//        echo $suffix_str;
    $file_path_to_check = BP_MEDIA_PLUGIN_DIR . '/theme'.$suffix_str;
    $file_url_to_return = BP_MEDIA_PLUGIN_URL . '/theme'.$suffix_str;

    if ( file_exists($file)) {
        return $file;
    }elseif ( file_exists($file_path_to_check)) {
        return $file_url_to_return;
    }
}
add_filter( 'bp_media_enqueue_url', 'bp_media_enqueue_url' );
/**
 *This function returns the total number of times media viewed.
 *
 * @global object $wpdb
 * @global object $bp
 */
function get_media_views() {
    global $wpdb, $bp;
    $id = $bp->action_variables[0];
    $bp->media->table_media_data = $wpdb->base_prefix . 'bp_media_data';
    $view = $wpdb->get_var("SELECT views from {$bp->media->table_media_data} WHERE ID = {$id} ");
    echo "Views : " .'<span class="view">'. ++$view .'</span>';
}

function bp_get_photo_count() {
    global $wpdb,$bp;
    $u_id = $bp->loggedin_user->id;
    if($u_id == 0)
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 2 ");
    else
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 2 ");
    return $pic_count;
}

function bp_get_audio_count() {
    global $wpdb,$bp;
    $u_id = $bp->loggedin_user->id;
    if ($u_id == 0)
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 5 ");
    else
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 5 ");
    return $pic_count;
}

function bp_get_video_count() {
    global $wpdb,$bp;
    $u_id = $bp->loggedin_user->id;
    if($u_id == 0)
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 1 ");
    else
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} WHERE media_type = 1 ");
    return $pic_count;
}
function bp_get_media_count() {
    global $wpdb,$bp;
    $u_id = $bp->loggedin_user->id;

    if($u_id == 0)
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data}");
    else
        $pic_count = $wpdb->get_var("SELECT count(id) from {$bp->media->table_media_data} ");
    return $pic_count;
}
/**
 *
 *
 *
 * This <code>is_kaltura_configured() {
 if(get_site_option('bp_rt_kaltura_secret') && get_site_option(bp_rt_kaltura_admin_secret)){
 return true;
 }
 else
 return false;</code>
 * checks that the Kaltura Settings is properly installed or not, If so then it returns the true value.
 */
function is_kaltura_configured() {
    if(get_site_option('bp_rt_kaltura_secret') && get_site_option(bp_rt_kaltura_admin_secret)) {
        return true;
    }
    else
        return false;

}
/**
 * This function prints the value of Media Id
 */
function bp_media_id() {
    echo bp_get_media_id();
}
function bp_get_media_id( $media = false ) {
    global $pictures_template;
    if ( !$media )
        $media =& $pictures_template->pictures[0]->db_id;

    return apply_filters( 'bp_get_media_id', $pictures_template->pictures[0]->db_id );
}

/**
 * This function prints the value of acticity feed link
 */
function bp_media_activity_feed_link() {
    echo bp_get_media_activity_feed_link();
}
/**
 *This function apply a filter to the activity feed link
 * @global <type> $bp
 * @return the activity feed
 */
function bp_get_media_activity_feed_link() {
    global $bp;
    //return apply_filters( 'bp_get_media_activity_feed_link', bp_get_media_permalink( $bp->links->current_link ) . '/feed/' );
    return apply_filters( 'bp_get_media_activity_feed_link',  '/feed/' );
}
/**
 * This function returns the permalink for the current media.
 * @global <type> $bp
 * @global <type> $pictures_template
 * @global <type> $single_pic_template
 * @return <type>
 */
function bp_get_media_permalink() {
    global $bp,$pictures_template,$single_pic_template;
    return apply_filters('bp_get_media_permalink', $bp->root_domain .'/'.$bp->current_component.'/'.$bp->current_action.'/'.$bp->action_variables[0]);
}

/**
 * this echo the media avatar
 */
function bp_media_creation_time() {
    global $pictures_template;
    $format = "F j, Y";
    echo date( $format , $pictures_template->pictures[0]->createdAt  );
}
function bp_media_user_avatar_thumb() {
    echo bp_get_media_user_avatar_thumb();
}
function bp_get_media_user_avatar_thumb( $media = false ) {
    return bp_get_media_user_avatar( 'type=thumb', $link );
}

function bp_get_media_user_avatar( $args = '', $link = false ) {
    global $bp, $pictures_template;


    if ( !$media )
        $media =& $pictures_template->media;

    $defaults = array(
            'type' => 'full',
            'width' => false,
            'height' => false,
            'class' => 'avatar',
            'id' => false,
            'alt' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    return apply_filters( 'bp_get_media_user_avatar', bp_core_fetch_avatar( array( 'item_id' => rt_who_owns_this_media($bp->action_variables[0]), 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height ) ) );
}

function bp_media_displayed_user_username() {
    echo bp_media_get_displayed_user_username();
}
function bp_media_get_displayed_user_username() {
    global $bp,$wpdb;
    $media_id = rt_who_owns_this_media($bp->action_variables[0]);
//    $user_id = $wpdb->get_var("SELECT user_id from {$bp->media->table_media_data} WHERE ID = {$media_id} ");
    return apply_filters( 'bp_media_get_displayed_user_username', bp_core_get_username( $media_id ) );
}

function bp_media_displayed_user_fullname() {
    echo bp_media_get_displayed_user_fullname();
}
function bp_media_get_displayed_user_fullname() {
    global $bp;
    //  var_dump($bp);
    return apply_filters( 'bp_media_displayed_user_fullname', $bp->displayed_user->fullname );
}


/****
 * Media group extension template tags
 */

function bp_media_group_media_tabs( $group = false ) {
    global $bp, $groups_template;

    if ( !$group )
        $group = ( $groups_template->group ) ? $groups_template->group : $bp->groups->current_group;

    $current_tab = $bp->action_variables[0];
//    var_dump($current_tab);
    ?>
<li<?php if ( 'mediaall' == $current_tab || '' == $current_tab) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->groups->slug ?>/<?php echo $group->slug ?>/<?php echo $bp->media->slug ?>/mediaall"><?php printf( __('Media', 'buddypress-media')) ?></a></li>
<li<?php if ( 'video' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->groups->slug ?>/<?php echo $group->slug ?>/<?php echo $bp->media->slug ?>/video"><?php printf( __('Video', 'buddypress-media')) ?></a></li>
<li<?php if ( 'photo' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->groups->slug ?>/<?php echo $group->slug ?>/<?php echo $bp->media->slug ?>/photo"><?php printf( __('Photo', 'buddypress-media')) ?></a></li>
<li<?php if ( 'audio' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->groups->slug ?>/<?php echo $group->slug ?>/<?php echo $bp->media->slug ?>/audio"><?php printf( __('Audio', 'buddypress-media')) ?></a></li>
<li<?php if ( 'upload' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->groups->slug ?>/<?php echo $group->slug ?>/<?php echo $bp->media->slug ?>/upload"><?php printf( __('Upload', 'buddypress-media')) ?></a></li>

    <?php
}

/**
 * Returns the type of visibility of media
 */
function rt_get_media_visibility() {
    global $bp,$pictures_template;
//    var_dump($pictures_template);
    $entry_id = $pictures_template->pictures[0]->id;
//    var_dump($pictures_template);
    switch($pictures_template->pictures[0]->visibility) {
        case 'private':
            $visibility = 'Private';
            break;
        case 'public':
            $visibility = 'Public';
            break;
    }
//    echo $entry_id;
//    echo rt_get_media_type($entry_id);
    return $visibility . rt_get_media_type($entry_id);
//    return $visibility;

}
function rt_get_media_type($entry_id) {
    global $pictures_template;
    $type = '';
    foreach ($pictures_template->pictures[0] as $key => $value) { //kapil
        if($key == 'mediaType') {
            switch ($value) {
                case '1':
                    $type = ' Video';
                    break;
                case '2':
                    $type = ' Photo';
                    break;
                case '5':
                    $type = ' Audio';
                    break;
            }
        }
    }
    return $type;
}

?>