<div class="rtmedia-container rtmedia-single-container">
    <div class="row rtm-lightbox-container">
        <?php
        global $rt_ajax_request;
        do_action('rtmedia_before_media');

        if ( have_rtmedia () ) : rtmedia ();
            ?>
            <div id="rtmedia-single-media-container" class="rtmedia-single-media columns <?php echo ($rt_ajax_request) ? "large-8" : "large-12"; ?>">
                <?php if ( !$rt_ajax_request ) { ?>
                
                    <span class="rtmedia-media-title">
                        <?php  echo rtmedia_title (); ?>
                    </span>
                    <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>"><?php rtmedia_media ( true ); ?></div>
                    
                <?php } else { ?>
                    
                    <button class="mfp-arrow mfp-arrow-left mfp-prevent-close rtm-lightbox-arrows" type="button" title="Previous Media"></button>
                    <button class="mfp-arrow mfp-arrow-right mfp-prevent-close" type="button" title="Next Media"></button>
                    <!--author actions-->
                    <div class='rtm-ltb-title-container rt-clear'>
                        <h2 class='rtm-ltb-title'>
                            <a href="<?php echo rtmedia_permalink();?>" title="<?php echo rtmedia_title (); ?>"><?php echo rtmedia_title (); ?></a>
                        </h2>
                        <div class='rtmedia-author-actions'>
                            <?php rtmedia_actions(); ?>
                        </div>
                    </div>
                    <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>"><?php rtmedia_media ( true ); ?></div>

                    <div class='rtm-ltb-action-container rt-clear'>
                        <div class="rtm-ltb-gallery-title"><span class='ltb-title'></span><span class='media-index'></span><span class='total-medias'></span></div>
                        <div class="rtmedia-actions">
                            <?php do_action('rtmedia_action_buttons_after_media', rtmedia_id());?>
                        </div>
                    </div>
                <?php  } ?>
            </div>
            <div class="rtmedia-single-meta columns <?php echo ($rt_ajax_request) ? "large-4" : "large-12"; ?>">
                
                <?php if ( $rt_ajax_request ) { ?>
                
                    <div class="rtm-single-meta-contents<?php if(is_user_logged_in()) echo " logged-in"; ?>">
                        <div>
                            <div class="userprofile">
                                <?php rtmedia_author_profile_pic ( true ); ?>
                            </div>
                            <div class="username">
                                <?php rtmedia_author_name ( true ); ?>
                            </div>
                        </div>
                        <div class="rtm-time-privacy rt-clear">
                            <?php echo get_rtmedia_date_gmt();?> <?php echo get_rtmedia_privacy_symbol(); ?>
                        </div>

                        <div class="rtmedia-actions-before-description rt-clear">
                            <?php do_action('rtmedia_actions_before_description', rtmedia_id()) ;?>
                        </div>

                        <div class="rtmedia-media-description rtm-more">
                            <?php echo strip_tags(rtmedia_description ( $echo = false)); ?>
                        </div>

                        <?php if ( rtmedia_comments_enabled () ) { ?>
                            <div class="rtmedia-item-comments row">
                                <div class="large-12 columns">
                                    <div class='rtmedia-actions-before-comments'>
                                        <?php do_action('rtmedia_actions_before_comments'); ?>
                                        <?php if(is_user_logged_in ()) {?>
                                            <span><a href='#' class='rtmedia-comment-link'><?php _e('Comment', 'rtmedia');?></a></span>
                                        <?php }?>
                                    </div>
                                    <div class="rtm-like-comments-info">
                                        <?php show_rtmedia_like_counts(); ?>
                                        <div class="rtmedia-comments-container">
                                            <?php rtmedia_comments (); ?>
                                        </div>
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

                <?php } else { // else for if ( $rt_ajax_request )?>

                <div class="rtmedia-item-actions rt-clear">
		    <?php do_action('rtmedia_actions_without_lightbox'); ?>
                    <?php rtmedia_actions (); ?>
		</div>
		<div class="rtmedia-actions-before-description rt-clear">
                    <?php do_action('rtmedia_actions_before_description', rtmedia_id()) ;?>
                </div>

                <div class="rtmedia-media-description more">
                    <?php rtmedia_description (); ?>
                </div>

                <?php if ( rtmedia_comments_enabled () ) { ?>
                    <div class="rtmedia-item-comments row">
                        <div class="large-12 columns">
                            <div class='rtmedia-actions-before-comments'>
                                    <?php do_action('rtmedia_actions_before_comments'); ?>
                                    <?php if(is_user_logged_in ()) {?>
                                        <span><a href='#' class='rtmedia-comment-link'><?php _e('Comment', 'rtmedia');?></a></span>
                                    <?php }?>
                                </div>
                                <div class="rtm-like-comments-info">
                                    <?php show_rtmedia_like_counts(); ?>
                                    <div class="rtmedia-comments-container">
                                        <?php rtmedia_comments (); ?>
                                    </div>
                                </div>
                            <?php if(is_user_logged_in ()) { rtmedia_comment_form (); } ?>
                        </div>
                    </div>

                <?php } ?>
                <?php } ?>
            </div>

        <?php else: ?>
            <p class="rtmedia-no-media-found"><?php
                $message = __ ( "Sorry !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
            </p>
        <?php endif; ?>

       <?php do_action('rtmedia_after_media'); ?>
    </div>
</div>
