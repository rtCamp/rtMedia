<div class="rtmedia-container">
    <?php do_action ( 'rtmedia_before_media_gallery' ); ?>
    <?php
        $title = get_rtmedia_gallery_title();
        global $rtmedia_query;
        if( isset($rtmedia_query->is_gallery_shortcode) && $rtmedia_query->is_gallery_shortcode == true) { // if gallery is displayed using gallery shortcode
        ?>
            <h2><?php _e( 'Media Gallery', 'rtmedia' ); ?></h2>
        <?php }
        else { ?>
            <div id="rtm-gallery-title-container">
                <h2 class="rtm-gallery-title">
                    <?php if( $title ) { echo $title; }
                            else { _e( 'Media Gallery', 'rtmedia' ); } ?>
                </h2>
                <div id="rtm-media-options"><?php do_action ( 'rtmedia_media_gallery_actions' ); ?></div>
            </div>

            <div id="rtm-media-gallery-uploader">
                <?php rtmedia_uploader ( array('is_up_shortcode'=> false) ); ?>
            </div>
        <?php }
        ?>
     <?php do_action ( 'rtmedia_after_media_gallery_title' ); ?>
    <?php if ( have_rtmedia () ) { ?>
	<ul class="rtmedia-list rtmedia-list-media <?php echo rtmedia_media_gallery_class (); ?>">


            <?php while ( have_rtmedia () ) : rtmedia (); ?>

                <?php include ('media-gallery-item.php'); ?>

            <?php endwhile; ?>

        </ul>

        <div class='rtmedia_next_prev row'>
            <!--  these links will be handled by backbone later
                                            -- get request parameters will be removed  -->
            <?php
            $display = '';
            if ( rtmedia_offset () != 0 )
                $display = 'style="display:block;"';
            else
                $display = 'style="display:none;"';
            ?>
            <a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rtmedia_pagination_prev_link (); ?>"><?php _e( 'Prev', 'rtmedia' ); ?></a>

            <?php
            $display = '';
            if ( rtmedia_offset () + rtmedia_per_page_media () < rtmedia_count () )
                $display = 'style="display:block;"';
            else
                $display = 'style="display:none;"';
            ?>
            <a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rtmedia_pagination_next_link (); ?>"><?php echo __ ( 'Load more', 'rtmedia' ); ?></a>
        </div>
    <?php } else { ?>
	    <p class="rtmedia-no-media-found">
            <?php
                $message = __ ( "Oops !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
		</p>
    <?php } ?>

<?php do_action ( 'rtmedia_after_media_gallery' ); ?>

</div>
