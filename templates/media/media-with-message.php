<?php

function rtm_bp_message_media_add_upload_media_button() {
    ?>
    <span class="primary rtm-media-msg-upload-button rtmedia-upload-media-link" id="rtm_show_upload_ui" title="Upload Media"><i class="dashicons dashicons-upload rtmicon"></i>Upload Media File</span>
    <div id="rtm-media-gallery-uploader" class="rtm-media-gallery-uploader">
        <?php rtmedia_uploader(array('is_up_shortcode' => false, 'allow_anonymous' => true)); ?>
    </div>
    <input type="hidden" id="rtm_bpm_uploaded_media" name="rtm_bpm_uploaded_media" />
    <?php
}

add_action('bp_after_messages_compose_content', 'rtm_bp_message_media_add_upload_media_button');

add_action('bp_after_message_reply_box', 'rtm_bp_message_media_add_upload_media_button');
add_action('messages_message_sent', 'add_message_params');

function add_message_params($message) {
    $insert_media_object = new RTDBModel('rtm_media_meta');
    $message->media_array = $_POST['rtm_bpm_uploaded_media'];
    $media = explode(",", $message->media_array);
    foreach ($media as $media_id) {
        $insert_media_object->insert(
                [
                    'media_id' => $media_id,
                    'meta_key' => 'rtm-bp-message-media',
                    'meta_value' => $message->id,
                ]
        );
    }
    ?>
    <script>
        jQuery("#msg-success-bp-msg-media").hide();
        jQuery(".rtm-media-msg-upload-button").attr("id", "rtm_show_upload_ui");
        jQuery(".rtm-media-msg-upload-button").html("");
        jQuery(".rtm-media-msg-upload-button").html("<i class='dashicons dashicons-upload rtmicon'></i>Upload Media File");
    </script>
    <?php
}

add_action('bp_after_message_content', 'show_trm_bp_msg_media');

function show_trm_bp_msg_media() {
   
        if (have_rtmedia()) {
        ?>
        <ul class="rtmedia-list rtmedia-list-media rtm-gallery-list clearfix <?php rtmedia_media_gallery_class(); ?>">

            <?php while (have_rtmedia()) : rtmedia(); ?>

                <?php include( plugin_dir_path( __FILE__ ) . 'media-gallery-item.php' ); ?>

        <?php endwhile; ?>

        </ul>

        <div class="rtmedia_next_prev rtm-load-more clearfix">
            <!-- these links will be handled by backbone -->
            <?php
            global $rtmedia;
            $general_options = $rtmedia->options;
            if (isset($rtmedia->options['general_display_media']) && 'pagination' === $general_options['general_display_media']) {
                rtmedia_media_pagination();
            } else {
                $display = '';
                if (rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count()) {
                    $display = 'display:block;';
                } else {
                    $display = 'display:none;';
                }
                ?>
                <a id="rtMedia-galary-next" style="<?php echo esc_attr($display); ?>"
                   href="<?php esc_url(rtmedia_pagination_next_link()); ?>"><?php esc_html_e('Load More', 'buddypress-media'); ?></a>
                   <?php
               }
               ?>
        </div>
    <?php
    }
}
