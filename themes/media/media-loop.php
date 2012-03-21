<?php
/**
 * @package Media_Component
 */
?>
<?php /* Querystring is set via AJAX in _inc/ajax.php - bp_dtheme_object_filter() */ ?>

<?php do_action( 'bp_before_media_loop' ) ?>

<?php if ( bp_has_media( bp_ajax_querystring( 'media' ) ) ) : ?>

<div class="pagination">

    <div class="pag-count" id="media-dir-count">
            <?php bp_pictures_pagination_count() ?>
    </div>

    <div class="pagination-links" id="media-dir-pag">
            <?php bp_pictures_pagination_links() ?>
    </div>

</div>

<ul id="media-list" class="item-list">
        <?php while ( bp_pictures() ) : bp_the_picture(); ?>

    <div class="picture-thumb">
        <a href='<?php bp_picture_view_link() ?>'><img src='<?php bp_picture_small_link() ?>' /></a><br />
        <span class="title"><?php bp_picture_title() ?><br><h6><?php bp_media_rating() ?></h6></span>
    </div>

        <?php endwhile; ?>
            <div class="clear"></div>

</ul>

    <?php do_action( 'bp_after_media_loop' ) ?>

<?php else: ?>

<div id="message" class="info">
    <p><?php _e( 'There were no Media Content found.', 'buddypress' ) ?></p>
</div>

<?php endif; ?>