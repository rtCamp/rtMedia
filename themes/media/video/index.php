<?php
/**
 * @package Media_Component
 */
?>
<?php get_header() ?>

<div id="content">
    <div class="padder">


            <h3><?php _e( 'Video Directory', 'media' ) ?>

                <!--Here Upload button can be placed-->
                <?php //if ( is_user_logged_in() ) :?>
                        <!--<a class="button" href="<?php //echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG . '/create/' ?>">
                <?php //_e( 'Create a Group', 'buddypress' ) ?>
                        </a>-->
                <?php //endif; ?>
            </h3>

            <?php do_action( 'bp_before_directory_media_content' ) ?>


            <div id="media-dir-list" class="media dir-list">
                <?php if(is_kaltura_configured()):?>
                <?php bp_media_locate_template( array( 'media/media-loop.php' ), true ) ?>
                <?php else: ?>
                    <div id="message" class="info">
                        <p>Kaltura is not configured. Please contact Admin</p>
                    </div>
                <?php endif;?>
            </div><!-- #groups-dir-list -->

            <?php do_action( 'bp_directory_media_content' ) ?>

            <?php wp_nonce_field( 'directory_media', '_wpnonce-media-filter' ) ?>


        <?php do_action( 'bp_after_directory_media_content' ) ?>

    </div><!-- .padder -->
</div><!-- #content -->
<?php locate_template( array( 'sidebar.php' ), true ) ?>
<?php get_footer() ?>