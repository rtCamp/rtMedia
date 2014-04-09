<div class="rtmedia-container">
    <?php do_action ( 'rtmedia_before_album_gallery' ); ?>
    <div id="rtm-gallery-title-container">
        <h2 class="rtm-gallery-title"><?php _e( 'Album List' , 'rtmedia' ) ; ?></h2>
        <div id="rtm-media-options"><?php do_action ( 'rtmedia_album_gallery_actions' ); ?></div>
    </div>   
    <?php do_action ( 'rtmedia_after_album_gallery_title' ); ?>
    <div id="rtm-media-gallery-uploader">
        <?php rtmedia_uploader ( array('is_up_shortcode'=> false) ); ?>
    </div>

    <?php if ( have_rtmedia () ) { ?>

        <ul class="rtmedia-list rtmedia-album-list">

            <?php while ( have_rtmedia () ) : rtmedia () ; ?>

                <?php include ('album-gallery-item.php') ; ?>

            <?php endwhile ; ?>

        </ul>

        <div class='rtmedia_next_prev row'>
            <!--  these links will be handled by backbone later
                            -- get request parameters will be removed  -->
            <?php
            $display = '' ;
            if ( rtmedia_offset () != 0 )
                $display = 'style="display:block;"' ;
            else
                $display = 'style="display:none;"' ;
            ?>
            <a id="rtMedia-galary-prev" <?php echo $display ; ?> href="<?php echo rtmedia_pagination_prev_link () ; ?>"><?php _e( 'Prev' , 'rtmedia' ) ; ?></a>

            <?php
            $display = '' ;
            if ( rtmedia_offset () + rtmedia_per_page_media () < rtmedia_count () )
                $display = 'style="display:block;"' ;
            else
                $display = 'style="display:none;"' ;
            ?>
            <a id="rtMedia-galary-next" <?php echo $display ; ?> href="<?php echo rtmedia_pagination_next_link () ; ?>"><?php _e( 'Load More' , 'rtmedia' ) ; ?></a>

        </div><!--/.rtmedia_next_prev-->

    <?php } else { ?>
        <p class="rtmedia-no-media-found">
            <?php 
                $message = __ ( "Sorry !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
		</p>
    <?php } ?>
    <?php do_action ( 'rtmedia_after_album_gallery' ) ; ?>

</div>

<!-- template for single media in gallery -->
<script id="rtmedia-gallery-item-template" type="text/template">
    <div class="rtmedia-item-thumbnail">
    <a href ="media/<%= id %>">
    <img src="<%= guid %>">
    </a>
    </div>

    <div class="rtmedia-item-title">
    <h4 title="<%= media_title %>">
    <a href="media/<%= id %>">
    <%= media_title %>
    </a>
    </h4>
    </div>
</script>
<!-- rtmedia_actions remained in script tag -->
