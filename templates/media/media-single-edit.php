<div class="rtmedia-container rtmedia-single-container row">

    
    <?php if (have_rtmedia()) : rtmedia(); ?>

	<?php
        
            if( rtmedia_delete_allowed() ) {
                    rtmedia_delete_form();
            }
            if(rtmedia_edit_allowed ()) {
            ?>

            <form method="post" action="">
                <div class="rtmedia-editor-main columns large-12 small">
                <?php rtmedia_title_input(); ?>

                <?php do_action('rtmedia_add_edit_fields', rtmedia_type()); ?>

                </div>
                <div class="rtmedia-editor-description columns large-12 small">
                <?php

                        echo rtmedia_description_input();
                                RTMediaMedia::media_nonce_generator(rtmedia_id());
                ?>
                </div>
                <div class="rtmedia-editor-buttons columns large-12 small">

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
