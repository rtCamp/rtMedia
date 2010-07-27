<?php
/**
 * @package Media_Component
 */
?>
<?php get_header() ?>

<div id="content">
    <div class="padder">

        <div id="rt-header">

                    <!--<h3><?php _e( 'Video Directory', 'media' ) ?></h3>-->
                <span id ="rt-video-dir">Photo Directory </span>
                <span id="rt-media-dir"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG  ?>"><?php printf( __( 'View All Media Types', 'buddypress' )) ?></a></span>

            <!--<div id="rt-other-directory">
                <a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG  ?>"><?php printf( __( 'View All Media Types', 'buddypress' )) ?></a>
            </div>
            <div class="clear"></div>-->
        </div>


            <?php do_action( 'bp_before_directory_media_content' ) ?>


            <!-- New code from here for filtering-->


            <div class="item-list-tabs rt-item-list-tabs">
                <ul>
                                <?php do_action( 'bp_before_media_type_tab_photos' ) ?>
                            <li id="media-photo" class="selected"><a href="<?php echo bp_get_root_domain() . '/' . BP_MEDIA_SLUG . '/photo/' ?>"><?php printf( __( 'Photos Directory', 'buddypress' )) ?></a></li>
                                <?php do_action( 'bp_media_type_tabs' ) ?>

                                <?php do_action( 'bp_media_directory_media_types' ) ?>
<?php if(is_user_logged_in()):?>
                    <li id="media-sort-album-select" class="last filter">
                                    <?php// _e( 'Album:', 'buddypress' ) ?>
                        <select>
                                <?php
                                        global $bp,$wpdb;
                                        $album_table =$bp->media->table_media_album;

                                        $query = "SELECT album_id,name,user_id,visibility FROM $album_table";
                                        $result = $wpdb->get_results($query);
                                ?><option value=""><?php _e( 'All Album', 'buddypress' ) ?></option><?php

//no private albums will be listed in here!
                                        foreach ($result as $key => $value) {
                                            if(($bp->loggedin_user->id != $value->user_id) && ($value->visibility == 'private'))
                                                continue;

                                            ?>
                            <option class="<?php if($bp->loggedin_user->id == $value->user_id) {
                    echo "rt-my-album";
                                            } else {
                                                echo "rt-others-album";
                                            }?>" value="<?php echo 'rt-album-filter_' . $value->album_id;?>"><?php _e( "$value->name", 'buddypress' ) ?></option>

                                            <?php }

            ?>
                            <!-- Get the album ids in the value of options-->
            <?php do_action( 'bp_media_directory_order_options' ) ?>
                    </select>
                    </li>

                    <!--Filters to implement-->
                    <li id="media-order-select" class="last filter">

            <?php _e( 'Sort By:', 'buddypress' ) ?>
                        <select>
                            <option value="all-media-data"><?php _e( 'All Media', 'buddypress' ) ?></option>
                            <option value="my-media-data"><?php _e( 'My Media', 'buddypress' ) ?></option>
                            <option value="recent-media-data"><?php _e( 'Recent', 'buddypress' ) ?></option>
                            <option value="commented-media-data"><?php _e( 'Most Commented', 'buddypress' ) ?></option>
                            <option value="top-rated-media-data"><?php _e( 'Top Rated', 'buddypress' ) ?></option>

            <?php do_action( 'bp_media_directory_order_options' ) ?>
                        </select>
                    </li>
        <?php endif; ?>


                </ul>
            </div><!-- .item-list-tabs -->

            <div id="media-dir-list" class="media dir-list">
                <?php if(is_kaltura_configured()):?>
                <?php bp_media_locate_template( array( 'media/photo/media-loop.php' ), true ) ?>
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