<?php

/**
 * Description of RTMediaTemplate
 *
 * Template to display rtMedia Gallery.
 * A stand alone template that renders the gallery/uploader on the page.
 *
 * @author saurabh
 */
class RTMediaTemplate {

    public $media_args;

    function __construct () {
        global $rtmedia_query;
        if ( $rtmedia_query ) {
            add_action ( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action ( 'wp_enqueue_scripts', array( $this, 'enqueue_image_editor_scripts' ) );
        }
    }

    /**
     * Enqueues required scripts on the page
     */
    function enqueue_scripts () {
        wp_enqueue_script ( 'rtmedia-backbone' );
        $is_album = is_rtmedia_album () ? true : false;
        $is_edit_allowed = is_rtmedia_edit_allowed () ? true : false;
        wp_localize_script ( 'rtmedia-backbone', 'is_album', array( $is_album ) );
        wp_localize_script ( 'rtmedia-backbone', 'is_edit_allowed', array( $is_edit_allowed ) );
    }

    function enqueue_image_editor_scripts () {
        $suffix = defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script ( 'wp-ajax-response' );
        wp_enqueue_script ( 'rtmedia-image-edit', admin_url ( "js/image-edit$suffix.js" ), array( 'jquery', 'json2', 'imgareaselect' ), false, 1 );
        wp_enqueue_style ( 'rtmedia-image-edit', RTMEDIA_URL . 'app/assets/css/image-edit.css' );
        wp_enqueue_style ( 'rtmedia-image-area-select', includes_url ( '/js/imgareaselect/imgareaselect.css' ) );
    }

    /**
     * redirects to the template according to the page request
     * Pass on the shortcode attributes to the template so that the shortcode can berendered accordingly.
     *
     * Also handles the json request coming from the AJAX calls for the media
     *
     * @global type $rtmedia_query
     * @global type $rtmedia_interaction
     * @param type $template
     * @param type $shortcode_attr
     * @return type
     */
    function set_template ( $template = false, $shortcode_attr = false ) {

        global $rtmedia_query, $rtmedia_interaction, $rtmedia_media;

        do_action ( 'rtmedia_pre_template' );

        //print_r($rtmedia_query);

        if ( isset ( $rtmedia_query->action_query->action ) ) {
            //echo $rtmedia_query->action_query->action;
            do_action ( 'rtmedia_pre_action_' . $rtmedia_query->action_query->action );
        } else {
            do_action ( 'rtmedia_pre_action_default' );
        }

        $this->check_return_json ();

        $this->check_return_upload ();

        if ( in_array ( $rtmedia_interaction->context->type, array( "profile", "group" ) ) ) {


            $this->check_return_edit ();

            $this->check_return_delete ();

            $this->check_return_merge ();

            $this->check_return_comments ();

            return $this->get_default_template ();
        } else if ( ! $shortcode_attr ) {
            return $this->get_default_template ();
        } else if ( $shortcode_attr[ 'name' ] == 'gallery' ) {
            $valid = $this->sanitize_gallery_attributes ( $shortcode_attr[ 'attr' ] );
            if ( $valid ) {
                if ( is_array ( $shortcode_attr[ 'attr' ] ) ) {
                    $this->update_global_query ( $shortcode_attr[ 'attr' ] );
                }
                global $rtaccount;
                if ( ! isset ( $rtaccount ) ) {
                    $rtaccount = 0;
                }
                //add_action("rtmedia_before_media_gallery",array(&$this,"")) ;
                $this->add_hidden_fields_in_gallery ();
                $gallery_template = apply_filters("rtmedia-before-template",$template,$shortcode_attr);
                include $this->locate_template ( $gallery_template );
            } else {
                echo __ ( 'Invalid attribute passed for rtmedia_gallery shortcode.', 'rtmedia' );
                return false;
            }
        }
    }

    function add_hidden_fields_in_gallery () {
        global $rtmedia_query;
        $return_str = "<input name='rtmedia_shortcode' value='true' type='hidden' />";
        if ( $rtmedia_query->original_query && is_array ( $rtmedia_query->original_query ) ) {
            foreach ( $rtmedia_query->original_query as $key => $val ) {
                $return_str.= '<input name="' . $key . '" value="' . $val . '" type="hidden" />';
            }
        }
        echo $return_str;
    }

    function check_return_json () {
        global $rtmedia_query;
        if ( $rtmedia_query->format == 'json' ) {
            $this->json_output ();
        } else {
            return;
        }
    }

    function check_return_upload () {
        global $rtmedia_query;
        if ( $rtmedia_query->action_query->action != 'upload' )
            return;
        $upload = new RTMediaUploadEndpoint();
        $upload->template_redirect ();
    }

    function json_output () {
        global $rtmedia_query;
        $media_array = array( );
        if ( $rtmedia_query->media ) {
            foreach ( $rtmedia_query->media as $key => $media ) {
                $media_array[ $key ] = $media;
                $media_array[ $key ]->guid = rtmedia_image('rt_media_thumbnail', $media->id ,false);
                $media_array[ $key ]->rt_permalink = get_rtmedia_permalink ( $media->id );
            }
        }
        $return_array[ 'data' ] = $media_array;
        $return_array[ 'prev' ] = rtmedia_page () - 1;
        $return_array[ 'next' ] = (rtmedia_offset () + rtmedia_per_page_media () < rtmedia_count ()) ? (rtmedia_page () + 1) : -1;
        echo json_encode ( $return_array );
        die;
    }

    function check_return_edit () {
        global $rtmedia_query;
        if ( $rtmedia_query->action_query->action == 'edit' && count ( $_POST ) )
            $this->save_edit ();
        return $this->get_default_template ();
    }

    function save_edit () {
        if ( is_rtmedia_single () ) {
            $this->save_single_edit ();
        } elseif ( is_rtmedia_album () ) {
            $this->save_album_edit ();
        }
    }

    function save_single_edit () {
        global $rtmedia_query;
        $nonce = $_POST[ 'rtmedia_media_nonce' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_' . $rtmedia_query->action_query->id ) ) {
            do_action ( 'rtmedia_before_update_media', $rtmedia_query->action_query->id );
            $data = rtmedia_sanitize_object ( $_POST, array( 'media_title', 'description', 'privacy' ) );
            $media = new RTMediaMedia();
            $state = $media->update ( $rtmedia_query->action_query->id, $data, $rtmedia_query->media[ 0 ]->media_id );
            $rtmedia_query->query ( false );
            do_action ( 'rtmedia_after_update_media', $rtmedia_query->action_query->id, $state );
            if ( $state !== false ) {
                add_action ( "rtmedia_before_template_load", array( &$this, "media_update_success_messege" ) );
            } else {
                add_action ( "rtmedia_before_template_load", array( &$this, "media_update_success_error" ) );
            }
        } else {
            echo __ ( "Ooops !!! Invalid access. No nonce was found !!", "rtmedia" );
        }
    }

    function media_update_success_messege () {
        $message = apply_filters ( "rtmedia_update_media_message", "Media updated Sucessfully", false );
        $html = "<div class='rtmedia-success media-edit-messge'><p>" . __ ( $message, "rtmedia" ) . "</p></div>";
        echo apply_filters ( "rtmedia_update_media_message_html", $html, $message, false );
    }

    function media_update_success_error () {
        $message = apply_filters ( "rtmedia_update_media_message", "Error in updating Media", true );
        $html = "<div class='rtmedia-error  media-edit-messge'><p>" . __ ( $message, "rtmedia" ) . "</p></div>";
        echo apply_filters ( "rtmedia_update_media_message_html", $html, $message, true );
    }

    function save_album_edit () {
        global $rtmedia_query;
        $nonce = $_REQUEST[ 'rtmedia_media_nonce' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_' . $rtmedia_query->media_query[ 'album_id' ] ) ) {
            $media = new RTMediaMedia();
            $model = new RTMediaModel();
            if ( isset ( $_POST[ 'submit' ] ) ) {
                $data = $_POST;
                unset ( $data[ 'rtmedia_media_nonce' ] );
                unset ( $data[ '_wp_http_referer' ] );
                unset ( $data[ 'submit' ] );
                $album = $model->get_media ( array( 'id' => $rtmedia_query->media_query[ 'album_id' ] ), false, false );
                $media->update ( $album[ 0 ]->id, $data, $album[ 0 ]->media_id );
            } elseif ( isset ( $_POST[ 'move-selected' ] ) ) {
//                            print_r($_POST);die;
                $album_move = $_POST[ 'album' ];
                $selected_ids = NULL;

                if ( isset ( $_POST[ 'selected' ] ) ) {
                    $selected_ids = $_POST[ 'selected' ];
                    unset ( $_POST[ 'selected' ] );
                }
                if ( ! empty ( $selected_ids ) && is_array ( $selected_ids ) ) {
                    $album_move_details = $model->get_media ( array( 'id' => $album_move ), false, false );
                    foreach ( $selected_ids as $media_id ) {
                        $media_details = $model->get_media ( array( 'id' => $media_id ), false, false );
                        $post_array[ 'ID' ] = $media_details[ 0 ]->media_id;
                        $post_array[ 'post_parent' ] = $album_move_details[ 0 ]->media_id;
                        wp_update_post ( $post_array );
                        $media->update ( $media_details[ 0 ]->id, array( 'album_id' => $album_move_details[ 0 ]->id ), $media_details[ 0 ]->media_id );
                    }
                }
            }
            wp_safe_redirect ( get_rtmedia_permalink ( $rtmedia_query->media_query[ 'album_id' ] ) . 'edit/' );
        } else {
            echo __ ( "Ooops !!! Invalid access. No nonce was found !!", "rtmedia" );
        }
    }

    function check_return_delete () {

        global $rtmedia_query;
        if ( $rtmedia_query->action_query->action != 'delete' )
            return;
        if ( ! count ( $_POST ) )
            return;

        if ( isset ( $rtmedia_query->action_query->default ) && $rtmedia_query->action_query->default == 'delete' ) {
            $this->bulk_delete ();
        } else {
            if ( is_rtmedia_single () ) {
                $this->single_delete ();
            } elseif ( is_rtmedia_album () ) {

                $this->album_delete ();
            }
        }
    }

    function bulk_delete () {
        $nonce = $_POST[ 'rtmedia_bulk_delete_nonce' ];

        $media = new RTMediaMedia();
        if ( wp_verify_nonce ( $nonce, 'rtmedia_bulk_delete_nonce' ) && isset ( $_POST[ 'selected' ] ) ) {
            $ids = $_POST[ 'selected' ];
            foreach ( $ids as $id ) {
                $media->delete ( $id );
            }
        }
        wp_safe_redirect ( $_POST[ '_wp_http_referer' ] );
    }

    function single_delete () {
        global $rtmedia_query;
        $nonce = $_REQUEST[ 'rtmedia_media_nonce' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_' . $rtmedia_query->media[ 0 ]->id ) ) {

//                        do_action('rtmedia_before_delete_media',$rtmedia_query->media[ 0 ]->id);

            $id = $_POST;
            unset ( $id[ 'rtmedia_media_nonce' ] );
            unset ( $id[ '_wp_http_referer' ] );
            $media = new RTMediaMedia();
            $media->delete ( $rtmedia_query->media[ 0 ]->id );

            $post = get_post ( $rtmedia_query->media[ 0 ] );

            $parent_link = '';
            if ( function_exists ( 'bp_core_get_user_domain' ) ) {
                $parent_link = bp_core_get_user_domain ( $post->media_author );
            } else {
                $parent_link = get_author_posts_url ( $post->media_author );
            }

//                        do_action('rtmedia_after_delete_media',$rtmedia_query->media[ 0 ]->id);
            $redirect_url = $_SERVER[ "HTTP_REFERER" ];


            if ( strpos ( $_SERVER[ "HTTP_REFERER" ], "/" . $rtmedia_query->media[ 0 ]->id ) > 0 ) {
                if ( isset ( $rtmedia_query->media[ 0 ]->album_id ) && intval ( $rtmedia_query->media[ 0 ]->album_id ) > 0 ) {
                    $redirect_url = trailingslashit ( $parent_link ) . "media/" . $rtmedia_query->media[ 0 ]->album_id;
                } else {
                    $redirect_url = trailingslashit ( $parent_link ) . "media/";
                }
            }

            wp_redirect ( $redirect_url );
        } else {
            echo __ ( "Ooops !!! Invalid access. No nonce was found !!", "rtmedia" );
        }
    }

    function album_delete () {
        global $rtmedia_query;
        $nonce = $_REQUEST[ 'rtmedia_delete_album_nonce' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_delete_album_' . $rtmedia_query->media_query[ 'album_id' ] ) ) {
            $media = new RTMediaMedia();
            $model = new RTMediaModel();
            $album_contents = $model->get ( array( 'album_id' => $rtmedia_query->media_query[ 'album_id' ] ), false, false );
            foreach ( $album_contents as $album_media ) {
                $media->delete ( $album_media->id );
            }
            $media->delete ( $rtmedia_query->media_query[ 'album_id' ] );
        }
        wp_safe_redirect ( get_rtmedia_user_link ( get_current_user_id () ) . 'media/album/' );
        exit;
    }

    function check_return_merge () {
        global $rtmedia_query;
        if ( $rtmedia_query->action_query->action != 'merge' )
            return;
        $nonce = $_REQUEST[ 'rtmedia_merge_album_nonce' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_merge_album_' . $rtmedia_query->media_query[ 'album_id' ] ) ) {
            $media = new RTMediaMedia();
            $model = new RTMediaModel();
            $album_contents = $model->get ( array( 'album_id' => $rtmedia_query->media_query[ 'album_id' ] ), false, false );
//                    print_r($album_contents); die;
            $album_move_details = $model->get_media ( array( 'id' => $_POST[ 'album' ] ), false, false );
            foreach ( $album_contents as $album_media ) {

                $post_array[ 'ID' ] = $album_media->media_id;
                $post_array[ 'post_parent' ] = $album_move_details[ 0 ]->media_id;
                wp_update_post ( $post_array );
                $media->update ( $album_media->id, array( 'album_id' => $album_move_details[ 0 ]->id ), $album_media->media_id );
            }
            $media->delete ( $rtmedia_query->media_query[ 'album_id' ] );
        }
        wp_safe_redirect ( get_rtmedia_user_link ( get_current_user_id () ) . 'media/album/' );
        exit;
    }

    function check_return_comments () {
        global $rtmedia_query;

        if ( $rtmedia_query->action_query->action != 'comment' )
            return;
        if ( isset ( $rtmedia_query->action_query->id ) && count ( $_POST ) ) {
            /**
             * /media/comments [POST]
             * Post a comment to the album by post id
             */
            $nonce = $_REQUEST[ 'rtmedia_comment_nonce' ];
            if ( wp_verify_nonce ( $nonce, 'rtmedia_comment_nonce' ) ) {
                if ( empty ( $_POST[ 'comment_content' ] ) ) {
                    return false;
                }
                $comment = new RTMediaComment();
                $attr = $_POST;
                $mediaModel = new RTMediaModel();
                $result = $mediaModel->get ( array( 'id' => $rtmedia_query->action_query->id ) );

                if ( ! isset ( $attr[ 'comment_post_ID' ] ) )
                    $attr[ 'comment_post_ID' ] = $result[ 0 ]->media_id;
                $id = $comment->add ( $attr );


                if ( $result[ 0 ]->activity_id != NULL ) {
                    global $rtmedia_buddypress_activity;
                    remove_action ( "bp_activity_comment_posted", array( $rtmedia_buddypress_activity, "comment_sync" ), 10, 2 );
                    if ( function_exists ( 'bp_activity_new_comment' ) ) {
                        bp_activity_new_comment ( array( 'content' => $_POST[ 'comment_content' ], 'activity_id' => $result[ 0 ]->activity_id ) );
                    }
                }
                if ( isset ( $_POST[ "rtajax" ] ) ) {
                    global $wpdb;
                    $comments = $wpdb->get_row ( $wpdb->prepare ( "SELECT * FROM $wpdb->comments WHERE comment_ID = %d", $id ), ARRAY_A );
                    echo rmedia_single_comment ( $comments );
                    exit;
                }
            } else {
                echo "Ooops !!! Invalid access. No nonce was found !!";
            }
        }
    }

    /**
     * Helper method to fetch allowed media types from each section
     *
     * @param type $allowed_type
     * @return type
     */
    function get_allowed_type_name ( $allowed_type ) {
        return $allowed_type[ 'name' ];
    }

    /**
     * Validates all the attributes for gallery shortcode
     *
     * @global type $rtmedia
     * @param string $attr
     * @return type
     */
    function sanitize_gallery_attributes ( &$attr ) {
        global $rtmedia;

        $flag = true;

        if ( isset ( $attr[ 'media_type' ] ) ) {
            $allowed_type_names = array_map ( array( $this, 'get_allowed_type_name' ), $rtmedia->allowed_types );

            if ( strtolower ( $attr[ 'media_type' ] ) == 'all' ) {
                $flag = $flag && true;
                unset ( $attr[ 'media_type' ] );
            } else if(strtolower ( $attr[ 'media_type' ] ) == 'album' ){
                $flag = $flag && true;
            }
            else
                $flag = $flag && in_array ( $attr[ 'media_type' ], $allowed_type_names );
        }

        if ( isset ( $attr[ 'order_by' ] ) ) {

            $allowed_columns = array( 'date', 'views', 'downloads', 'ratings', 'likes', 'dislikes' );
            $allowed_columns = apply_filters ( 'filter_allowed_sorting_columns', $allowed_columns );

            $flag = $flag && in_array ( $attr[ 'order_by' ], $allowed_columns );

            if ( strtolower ( $attr[ 'order_by' ] ) == 'date' )
                $attr[ 'order_by' ] = 'media_id';
        }

        if ( isset ( $attr[ 'order' ] ) ) {
            $flag = $flag && strtolower ( $attr[ 'order' ] ) == 'asc' || strtolower ( $attr[ 'order' ] ) == 'desc';
        }

        return $flag;
    }

    function update_global_query ( $attr ) {
        global $rtmedia_query;
        $rtmedia_query->query ( $attr );
    }

    /**
     * filter to change the template path independent of the plugin
     *
     * @return type
     */
    function get_default_template () {

        return apply_filters ( 'rtmedia_media_template_include', self::locate_template ( 'main', '' ) );
    }

    /**
     * Template Locator
     *
     * @param type $template
     * @return string
     */
    static function locate_template ( $template = false, $context = false, $url = false ) {
        $located = '';
        if ( ! $template ) {
            global $rtmedia_query;

            if ( is_rtmedia_album_gallery () ) {
                $template = 'album-gallery';
            } elseif ( is_rtmedia_album () || is_rtmedia_gallery () ) {
                $template = 'media-gallery';
                if (
                        is_rtmedia_album () &&
                        isset ( $rtmedia_query->media_query ) &&
                        $rtmedia_query->action_query->action == 'edit'
                ) {
                    if ( isset ( $rtmedia_query->media_query[ 'media_author' ] ) && (get_current_user_id () == $rtmedia_query->media_query[ 'media_author' ]) ) {
                        $template = 'album-single-edit';
                    }
                }
            } else if ( is_rtmedia_single () ) {
                $template = 'media-single';
                if ( $rtmedia_query->action_query->action == 'edit' )
                    $template = 'media-single-edit';
            }else {
                return;
            }
        }


        $template_name = $template . '.php';

        if ( $context === false ) {
            $context = 'media/';
        }
        if ( ! $context === '' ) {
            $context .='/';
        }


        $path = 'rtmedia/' . $context;
        $ogpath = 'templates/' . $context;



        if ( file_exists ( trailingslashit ( STYLESHEETPATH ) . $path . $template_name ) ) {
            if ( $url ) {
                $located = trailingslashit ( get_stylesheet_directory_uri () ) . $path . $template_name;
            } else {
                $located = trailingslashit ( STYLESHEETPATH ) . $path . $template_name;
            }
        } else if ( file_exists ( trailingslashit ( TEMPLATEPATH ) . $path . $template_name ) ) {
            if ( $url ) {
                $located = trailingslashit ( get_template_directory_uri () ) . $path . $template_name;
            } else {
                $located = trailingslashit ( TEMPLATEPATH ) . $path . $template_name;
            }
        } else {
            if ( $url ) {
                $located = trailingslashit ( RTMEDIA_URL ) . $ogpath . $template_name;
            } else {
                $located = trailingslashit ( RTMEDIA_PATH ) . $ogpath . $template_name;
            }
        }

        return $located;
    }

}

?>
