<div class="rtmedia-container rtmedia-single-container">
    <div class="row">
        <?php
        global $rt_ajax_request;
        if ( have_rtmedia () ) : rtmedia ();
            ?>
            <div id="rtmedia-single-media-container" class="rtmedia-single-media columns <?php echo ($rt_ajax_request) ? "large-9" : "large-12"; ?>">
		<span class="rtmedia-media-title">
		    <?php echo rtmedia_title (); ?>
		</span>
                <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>">
                    <?php rtmedia_media ( true ); ?>
                </div>
            </div>
            <div class="rtmedia-single-meta columns <?php echo ($rt_ajax_request) ? "large-3" : "large-12"; ?>">
                <?php if ( $rt_ajax_request ) { ?>
                    <div>
                        <div class="userprofile">
                            <?php rtmedia_author_profile_pic ( true ); ?>
                        </div>
                        <div class="username">
                            <?php rtmedia_author_name ( true ); ?>
                        </div>
                    </div>
                <?php }
		    rtmedia_description ();
                ?>

                <div class="rtmedia-item-actions">
                    <?php rtmedia_actions (); ?>
                </div>

                <?php if ( rtmedia_comments_enabled () ) { ?>
                    <div class="rtmedia-item-comments row">
                        <div class="large-12 columns">
                            <h2><?php echo __( 'Comments', 'rtmedia' ); ?></h2>
                            <div class="rtmedia-comments-container">
                                <?php rtmedia_comments (); ?>
                            </div>
                            <?php rtmedia_comment_form (); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

        <?php else: ?>
            <p><?php echo __ ( "Oops !! There's no media found for the request !!", "rtmedia" ); ?></p>
        <?php endif; ?>

    </div>
</div>
