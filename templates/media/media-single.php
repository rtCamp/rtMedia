<div class="rtmedia-container rtmedia-single-container">
    <div class="row rtm-lightbox-container">
        <?php
        global $rt_ajax_request;
        
        if ( $rt_ajax_request ) { ?>
            <div class='rtm-mfp-close'>
                <i class='icon-remove mfp-close'title='Close (Esc)'></i>
            </div>
        <?php  }
        
        if ( have_rtmedia () ) : rtmedia ();
            ?>
            <div id="rtmedia-single-media-container" class="rtmedia-single-media columns <?php echo ($rt_ajax_request) ? "large-8" : "large-12"; ?>">
                <?php if ( !$rt_ajax_request ) { ?>
		<span class="rtmedia-media-title">
		    <?php echo rtmedia_title (); ?>
		</span>
                <?php } else{ ?>
                <button class="mfp-arrow mfp-arrow-left mfp-prevent-close rtm-lightbox-arrows" type="button" title="Previous Media"></button>
                <button class="mfp-arrow mfp-arrow-right mfp-prevent-close" type="button" title="Next Media"></button>
                <?php } ?>
                <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>">
                    <?php rtmedia_media ( true ); ?>
                </div>
                <?php if ( $rt_ajax_request ) { ?>
<!--                    <button class="mfp-arrow mfp-arrow-left mfp-prevent-close rtm-lightbox-arrows" type="button" title="Previous Media"></button>
                    <button class="mfp-arrow mfp-arrow-right mfp-prevent-close" type="button" title="Next Media"></button>-->
                <?php } ?>
            </div>
            <div class="rtmedia-single-meta columns <?php echo ($rt_ajax_request) ? "large-4" : "large-12"; ?>">
                <?php if ( $rt_ajax_request ) { ?>
                <div class="rtm-single-meta-contents">
                    <div>
                        <div class="userprofile">
                            <?php rtmedia_author_profile_pic ( true ); ?>
                        </div>
                        <div class="username">
                            <?php rtmedia_author_name ( true ); ?>
                        </div>
                    </div>
                    
                    <div class="rtmedia-item-actions">
                        <?php rtmedia_actions (); ?>
                    </div>
                    
                    <h2 class="rtmedia-media-title">
                            <?php echo rtmedia_title (); ?>
                    </h2>
                    
                    <div class="rtmedia-media-description">
                        <?php rtmedia_description (); ?>
                    </div>
                
                    <?php if ( rtmedia_comments_enabled () ) { ?>
                        <div class="rtmedia-item-comments row">
                            <div class="large-12 columns">
                                <div class="rtmedia-comments-container">
                                    <?php rtmedia_comments (); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ( rtmedia_comments_enabled () && is_user_logged_in ()) { ?>
                    <div class='rtm-media-single-comments'>
                        <?php rtmedia_comment_form (); ?>
                    </div>
                <?php } ?>
                    
                <?php } else { ?>

                <div class="rtmedia-item-actions">
                    <?php rtmedia_actions (); ?>
                </div>
                <div class="rtmedia-media-description">
                    <?php rtmedia_description (); ?>
                </div>
                
                <?php if ( rtmedia_comments_enabled () ) { ?>
                    <div class="rtmedia-item-comments row">
                        <div class="large-12 columns">
                            <h2><?php echo __( "Comments", "rtmedia" ); ?></h2>
                            <div class="rtmedia-comments-container">
                                <?php rtmedia_comments (); ?>
                            </div>
                            <?php rtmedia_comment_form (); ?>
                        </div>
                    </div>
                
                <?php } ?>
                <?php } ?>
            </div>

        <?php else: ?>
            <p><?php echo __ ( "Oops !! There's no media found for the request !!", "rtmedia" ); ?></p>
        <?php endif; ?>

    </div>
</div>