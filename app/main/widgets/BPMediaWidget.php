<?php

/**
 * Description of BPMediaRecentMediaWidget
 *
 * @author saurabh
 */
class BPMediaWidget extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'buddypress-media-widget', 'description' => __("The most recent/popular media uploaded on your site", BP_MEDIA_TXT_DOMAIN));
        parent::__construct('buddypress-media-wid', __('BuddyPressMedia Widget', BP_MEDIA_TXT_DOMAIN), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', empty($instance['title']) ? __('BuddyPress Media', BP_MEDIA_TXT_DOMAIN) : $instance['title'], $instance, $this->id_base);

        if (empty($instance['number']) || !$number = absint($instance['number']))
            $number = 10;
        $wdType = isset($instance['wdType']) ? esc_attr($instance['wdType']) : 'recent';
        $allowAudio = isset($instance['allow_audio']) ? (bool) $instance['allow_audio'] : true;
        $allowVideo = isset($instance['allow_video']) ? (bool) $instance['allow_video'] : true;
        $allowImage = isset($instance['allow_image']) ? (bool) $instance['allow_image'] : true;
        $allowMimeType = array();
        echo $before_widget;
        echo $before_title . $title . $after_title;
        if ($wdType == "popular"){
            $orderby='comment_count';
         }else {
            $orderby='date';
         }
         $widgetid=$args['widget_id'];            
        if (!($allowAudio || $allowVideo || $allowImage)) {
            ?>
            <p><?php printf(__('Please configure this widget <a href="%s" target="_blank" title="Configure BuddyPress Media Widget">here</a>.', 'rtPanel'), admin_url('/widgets.php')); ?></p><?php } else {
            ?>

            <div id="<?php echo $wdType; ?>-media-tabs" class="media-tabs-container">
                <ul>
                    <li><a href="#<?php echo $wdType; ?>-media-tabs-all-<?php echo $widgetid; ?>"><?php _e('All', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                    <?php
                    if ($allowImage) {
                        array_push($allowMimeType, "image");
                        ?>
                        <li><a href="#<?php echo $wdType; ?>-media-tabs-photos-<?php echo $widgetid; ?>"><?php _e('Photos', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                        <?php
                    }

                    if ($allowAudio) {
                        array_push($allowMimeType, "audio");
                        ?>
                        <li><a href="#<?php echo $wdType; ?>-media-tabs-music-<?php echo $widgetid; ?>"><?php _e('Music', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                        <?php
                    }

                    if ($allowVideo) {
                        array_push($allowMimeType, "video");
                        ?>
                        <li><a href="#<?php echo $wdType; ?>-media-tabs-videos-<?php echo $widgetid; ?>"><?php _e('Videos', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                    <?php }
                    ?>
                </ul>
                <div id="<?php echo $wdType; ?>-media-tabs-all-<?php echo $widgetid; ?>" class="bp-media-tab-panel">
                    <?php
                    // All Media
                    $args = array('post_type' => 'attachment',
                        'post_status' => 'any',
                        'posts_per_page' => $number,
                        'meta_key' => 'bp-media-key',
                        'meta_value' => 0,
                        'post_mime_type' => $allowMimeType,
                        'meta_compare' => '>',
                        'orderby'=>$orderby
                        );
                    
                    $bp_media_widget_query = new WP_Query($args);

                    if ($bp_media_widget_query->have_posts()) {
                        ?>

                        <ul class="widget-item-listing"><?php
                while ($bp_media_widget_query->have_posts()) {
                    $bp_media_widget_query->the_post();

                    $entry = new BPMediaHostWordpress(get_the_ID());
                            ?>

                                <?php echo $entry->get_media_gallery_content(); ?><?php }
                            ?>

                        </ul><!-- .widget-item-listing --><?php
            }
            else
                _e('No ' . $wdType .' media found', BP_MEDIA_TXT_DOMAIN);

            wp_reset_query();
                        ?>

                </div><!-- #recent-media-tabs-all -->
                <?php if ($allowImage) { ?>     
                    <div id="<?php echo $wdType; ?>-media-tabs-photos-<?php echo $widgetid; ?>" class="bp-media-tab-panel">
                        <?php
                        // Recent photos
                        $args = array('post_type' => 'attachment',
                            'post_status' => 'any',
                            'post_mime_type' => 'image',
                            'posts_per_page' => $number,
                            'meta_key' => 'bp-media-key',
                            'meta_value' => 0,
                            'meta_compare' => '>',
                            'orderby'=>$orderby);
                        
                        $bp_media_widget_query = new WP_Query($args);

                        if ($bp_media_widget_query->have_posts()) {
                            ?>

                            <ul class="widget-item-listing"><?php
                    while ($bp_media_widget_query->have_posts()) {
                        $bp_media_widget_query->the_post();

                        $entry = new BPMediaHostWordpress(get_the_ID());
                                ?>

                                    <?php echo $entry->get_media_gallery_content(); ?><?php }
                                ?>

                            </ul><!-- .widget-item-listing --><?php
                }
                else
                    _e('No ' . $wdType .' photo found', BP_MEDIA_TXT_DOMAIN);

                wp_reset_query();
                            ?>

                    </div><!-- #media-tabs-photos -->
                <?php }
                if ($allowAudio) {
                    ?>

                    <div id="<?php echo $wdType; ?>-media-tabs-music-<?php echo $widgetid; ?>" class="bp-media-tab-panel">
                        <?php
                        // Recent Audio
                        $args = array('post_type' => 'attachment',
                            'post_status' => 'any',
                            'post_mime_type' => 'audio',
                            'posts_per_page' => $number,
                            'meta_key' => 'bp-media-key',
                            'meta_value' => 0,
                            'meta_compare' => '>',
                        'orderby'=>$orderby);
                        $bp_media_widget_query = new WP_Query($args);

                        if ($bp_media_widget_query->have_posts()) {
                            ?>

                            <ul class="widget-item-listing">
                                <?php
                                while ($bp_media_widget_query->have_posts()) {
                                    $bp_media_widget_query->the_post();

                                    $entry = new BPMediaHostWordpress(get_the_ID());
                                    echo $entry->get_media_gallery_content();
                                }
                                ?>

                            </ul><!-- .widget-item-listing --><?php
                            }
                            else
                                _e('No ' . $wdType .' audio found', BP_MEDIA_TXT_DOMAIN);

                            wp_reset_query();
                            ?>

                    </div><!-- #recent-media-tabs-music -->

                <?php }
                if ($allowVideo) {
                    ?>
                    <div id="<?php echo $wdType; ?>-media-tabs-videos-<?php echo $widgetid; ?>" class="bp-media-tab-panel">
                        <?php
                        // Recent Video
                        $args = array('post_type' => 'attachment',
                            'post_status' => 'any',
                            'post_mime_type' => 'video',
                            'posts_per_page' => $number,
                            'meta_key' => 'bp-media-key',
                            'meta_value' => 0,
                            'meta_compare' => '>',
                            'orderby'=>$orderby);
                        $bp_media_widget_query = new WP_Query($args);

                        if ($bp_media_widget_query->have_posts()) {
                            ?>

                            <ul class="widget-item-listing"><?php
                            while ($bp_media_widget_query->have_posts()) {
                                $bp_media_widget_query->the_post();

                                $entry = new BPMediaHostWordpress(get_the_ID());
                                ?>

                        <?php echo $entry->get_media_gallery_content(); ?><?php }
                    ?>

                            </ul><!-- .widget-item-listing --><?php
                }
                else
                    _e('No ' . $wdType .' video found', BP_MEDIA_TXT_DOMAIN);

                wp_reset_query();
                ?>

                    </div><!-- #media-tabs-videos -->
            <?php } ?>
            </div>
            <?php
        }
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['wdType'] = strip_tags($new_instance['wdType']);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['allow_audio'] = !empty($new_instance['allow_audio']) ? 1 : 0;
        $instance['allow_video'] = !empty($new_instance['allow_video']) ? 1 : 0;
        $instance['allow_image'] = !empty($new_instance['allow_image']) ? 1 : 0;
        return $instance;
    }

    function form($instance) {
        $wdType = isset($instance['wdType']) ? esc_attr($instance['wdType']) : '';
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 10;
        $allowAudio = isset($instance['allow_audio']) ? (bool) $instance['allow_audio'] : true;
        $allowVideo = isset($instance['allow_video']) ? (bool) $instance['allow_video'] : true;
        $allowImage = isset($instance['allow_image']) ? (bool) $instance['allow_image'] : true;
        ?>
        <p><label for="<?php echo $this->get_field_id('wdType'); ?>"><?php _e('Widget Type:', BP_MEDIA_TXT_DOMAIN); ?></label>
            <select  class="widefat" id="<?php echo $this->get_field_id('wdType'); ?>" name="<?php echo $this->get_field_name('wdType'); ?>"> 
                <option value="recent" <?php if ($wdType == "recent") echo 'selected="selected"'; ?>><?php _e('Recent Media', BP_MEDIA_TXT_DOMAIN); ?></option>
                <option value="popular" <?php if ($wdType == "popular") echo 'selected="selected"'; ?>><?php _e('Popular Media', BP_MEDIA_TXT_DOMAIN); ?></option>
            </select>
        </p>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', BP_MEDIA_TXT_DOMAIN); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', BP_MEDIA_TXT_DOMAIN); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_image'); ?>" id="<?php echo $this->get_field_id('allow_image'); ?>" <?php checked($allowImage); ?> /><label for="<?php echo $this->get_field_id('allow_image'); ?>"><?php _e('Show Photos', BP_MEDIA_TXT_DOMAIN); ?></label>


        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_audio'); ?>" id="<?php echo $this->get_field_id('allow_audio'); ?>" <?php checked($allowAudio); ?> /> <label for="<?php echo $this->get_field_id('allow_audio'); ?>"><?php _e('Show Music', BP_MEDIA_TXT_DOMAIN); ?></label>


        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_video'); ?>" id="<?php echo $this->get_field_id('allow_video'); ?>" <?php checked($allowVideo); ?> />
            <label for="<?php echo $this->get_field_id('allow_video'); ?>"><?php _e('Show Videos', BP_MEDIA_TXT_DOMAIN); ?></label>


        </p>

        <?php
    }

}
?>
