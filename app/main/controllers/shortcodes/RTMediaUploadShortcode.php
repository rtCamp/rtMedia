<?php

/**
 * Description of RTMediaUploadShortcode
 *
 * rtMedia uploader shortcode
 *
 * @author joshua
 */
class RTMediaUploadShortcode {

    static $add_sc_script = false;
    var $deprecated = false;
    static $uploader_displayed = false;

    /**
     *
     */
    public function __construct () {



        add_shortcode ( 'rtmedia_uploader', array( 'RTMediaUploadShortcode', 'pre_render' ) );
        $method_name = strtolower ( str_replace ( 'RTMedia', '', __CLASS__ ) );

        if ( is_callable ( "RTMediaDeprecated::{$method_name}", true, $callable_name ) ) {
            $this->deprecated = RTMediaDeprecated::$method_name ();
        }
    }

    /**
     * Helper function to check whether the shortcode should be rendered or not
     *
     * @return type
     */
    static function display_allowed () {

        $flag = ( ! (is_home () || is_post_type_archive () || is_author ())) && is_user_logged_in () && (is_rtmedia_upload_music_enabled () || is_rtmedia_upload_photo_enabled () || is_rtmedia_upload_video_enabled ());
        $flag = apply_filters ( 'before_rtmedia_uploader_display', $flag );
        return $flag;
    }

    /**
     * Render the uploader shortcode and attach the uploader panel
     *
     * @param type $attr
     */
    static function pre_render ( $attr ) {

        global $post;
        if ( isset ( $attr ) && isset ( $attr[ "attr" ] ) ) {
            if ( ! is_array ( $attr ) ) {
                $attr = Array( );
            }
            if ( ! isset ( $attr[ "context_id" ] ) && isset ( $post->ID ) ) {
                $attr[ "context_id" ] = $post->ID;
            }
            if ( ! isset ( $attr[ "context" ] ) && isset ( $post->post_type ) ) {
                $attr[ "context" ] = $post->post_type;
            }
        }

        if ( self::display_allowed () ) {

            ob_start ();

            self::$add_sc_script = true;
            RTMediaUploadTemplate::render ( $attr );

            self::$uploader_displayed = true;
            return ob_get_clean ();
        }
    }

}

?>