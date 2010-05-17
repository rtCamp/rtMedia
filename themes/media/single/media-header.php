<?php
/**
 * @package Media_Component
 */
?>
<?php do_action( 'bp_before_media_header' ) ?>

<div id="item-actions">
</div><!-- #item-actions -->

<div id="item-header-avatar">
    <h6><a href ="<?php bp_root_domain() ?>/members/<?php bp_media_displayed_user_username() ?>"><?php _e( 'Media Owner', 'buddypress' ) ?></a></h6>
    <a href ="<?php bp_root_domain() ?>/members/<?php bp_media_displayed_user_username() ?>"> <?php  bp_media_user_avatar_thumb() ?></a>
   

</div><!-- #item-header-avatar -->

<div id="item-header-content">
    <h2 class="fn"><a href="<?php bp_root_domain() ?>/members/<?php bp_media_displayed_user_username() ?>"><span class="highlight">@<?php bp_media_displayed_user_username() ?> <span>?</span></span></a></h2>
    <span class="highlight"><?php echo rt_get_media_visibility()?></span> <span class="activity"><?php bp_media_creation_time();?></span><br>
    <hr>
        <!--add single page here-->
        <?php
        if(!get_site_option('bp_rt_kaltura_url')) :
            ?>
        <div id="message" class="info"><p>Please Configure Kaltura Setting</p></div>
        <?php else :?>
        <input type ="hidden" id="url" value="">
            <?php if ( bp_single_pic_exist() && bp_single_pic_check_owner()):?>
                      <?php if (isMediaOwner(bp_single_media_id()) && is_user_logged_in()): ?>
        <div id="user-title" style="cursor: pointer;"><h2 title="Click Here to Edit Media Title"><p><?php bp_single_picture_title() ?></p></h2></div>
                              <?php else: ?>
                    <div id="user-title"><h2><?php bp_single_picture_title() ?></h2></div>
            <?php endif; ?>
                    <div id="item-meta">
        <div class="picture-single">
            <div id='display_media'>
                        <?php  echo get_media_data(); ?><br>
            </div>
                    <?php bp_media_user_rated(); ?>

            <div class="thanks">
                        <?php get_media_views(); ?>
            </div>
            <div class="picture-nav">
                <div class="prev"><?php previous_picture_link('%link', '&lsaquo;') ?></div>
        <?php if (isMediaOwner(bp_single_media_id()) && is_user_logged_in()): ?>

                <div id="pic-description" style="cursor: pointer;" ><h3 title="Click Here to Edit Media Description"><p><?php bp_single_picture_description() ?></p></h3></div>
                <div class="update_desc_load" style="position: relative;top:-40px;left:-160px"></div>
                            <?php// bp_single_pic_delete_link()?>
                <span><a class="delete" style="text-decoration: none;cursor: pointer">Delete</a></span>
                <div class="confirm">Delete From <b>Kaltura</b> Server too ? <a id="yes" style="cursor: pointer">YES</a> or <a id="no" style="cursor: pointer">NO</a> or <a id="cancel" style="text-decoration: none;cursor: pointer">Cancel</a></div>
                        <?php else:?>
                    <div id="user_pic-description"><h3><?php bp_single_picture_description() ?></h3></div>
                        <?php endif; ?>
                <div class="next"><?php next_picture_link('%link','&rsaquo;') ?></div>
            </div>
        </div>



            <?php endif;?>
        <?php endif;?>

    </div>
</div><!-- #item-header-content -->

<?php do_action( 'bp_after_media_header' ) ?>

<?php do_action( 'template_notices' ) ?>