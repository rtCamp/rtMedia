<div class="rtmedia-container rtmedia-single-container row rtmedia-media-edit">

    
    <?php if (have_rtmedia()) : rtmedia(); ?>

	<?php
        
//            if( rtmedia_delete_allowed() ) {
//                    rtmedia_delete_form();
//            }
            if(rtmedia_edit_allowed ()) {
                global $rtmedia_media;
            ?>
    
            <div class="rtmedia-single-edit-title-container">
                <h2 class="rtmedia-title"><?php echo __ ( 'Edit Media' , 'rtmedia' ); ?></h2>
<!--                <div id="rtm-media-options" class="rtm-media-options">
                    <span class="rtmedia-instagram-effects primary" title="Add Instagram Effects"><i class="rtmicon-instagramm"></i>Instagram Effects</span>
                    <?php //do_action('rtmedia_single_media_edit_actions');?>
                    <span title="Modify Image" class="rtmedia-modify-image primary"><i class="rtmicon-edit"></i>Modify Image</span>
                </div>-->
            </div>    
            
            <form method="post" action="">
                <div class="rtmedia-editor-main columns large-12 small">
                    <div class="rtmedia-edit-media-tabs auto section-container" data-section='tabs'>
                    <section class="active">
                      <p class="tab-title" data-section-title><a href="#panel1"><i class="rtmicon-edit"></i><?php _e('Details', 'rtmedia'); ?></a></p>
                      <div class="tab-content rtmedia-details" data-section-content>
                          <div class="rtmedia-edit-title">
                                <label><?php _e('Title : ', 'rtmedia'); ?></label><?php rtmedia_title_input(); ?>
                            </div>
                            <?php echo rtmedia_edit_media_privacy_ui(); ?>

                        <div class="rtmedia-editor-description">
                            <label><?php _e('Description: ', 'rtmedia') ?></label>
                            <?php

                                    echo rtmedia_description_input( $editor = false);
                                            RTMediaMedia::media_nonce_generator(rtmedia_id());
                            ?>
                        </div> 
                    </div>
                    </section>
                    <?php do_action('rtmedia_add_edit_fields', rtmedia_type()); ?>
                    </div>
                
                <div class="rtmedia-editor-main columns large-12 small">
                    <?php do_action('rtmedia_add_edit_fields_after_description', rtmedia_type()); ?>
                </div>
                <div class="rtmedia-editor-buttons">

                        <input type="submit" value="<?php _e('Save', 'rtmedia')?>">
                                <a href="<?php rtmedia_permalink(); ?>"><input type="button" value="<?php _e('Back','rtmedia') ?>"></a>
                </div>
           </form>
    
    

       
            <?php } else { 
                
                ?>
            
            <p><?php echo __("Oops !! You do not have rights to edit this media","rtmedia"); ?></p>
            
            <?php } ?>
            
    <?php else: ?>
        <p><?php echo __("Oops !! There's no media found for the request !!","rtmedia"); ?></p>
    <?php endif; ?>
</div>
