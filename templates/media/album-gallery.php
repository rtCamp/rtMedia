<div class="rtmedia-container">
	<?php do_action('rtmedia_before_album_gallery'); ?>
	<?php rtmedia_uploader() ?>

    <?php if (have_rtmedia()) { ?>

        <h2><?php echo __('Album Gallery','rtmedia'); ?></h2>

        <ul class="rtmedia-list rtmedia-album-list large-block-grid-4">

            <?php while (have_rtmedia()) : rtmedia(); ?>

                <?php include ('album-gallery-item.php'); ?>

            <?php endwhile; ?>

        </ul>


        <!--  these links will be handled by backbone later
                        -- get request parameters will be removed  -->
        <?php if(rtmedia_offset() != 0) { ?>
            <a href="?rtmedia_page=<?php echo rtmedia_page()-1; ?>"><?php echo __('Prev','rtmedia'); ?></a>
        <?php } ?>
        <?php if(rtmedia_offset()+ rtmedia_per_page_media() < rtmedia_count()) { ?>
            <a href="?rtmedia_page=<?php echo rtmedia_page()+1; ?>"><?php echo __('Next','rtmedia'); ?></a>
        <?php } ?>

	<?php } else { ?>
		<p><?php echo __("Oops !! There's no media found for the request !!","rtmedia"); ?></p>
	<?php } ?>

		<?php do_action('rtmedia_after_album_gallery'); ?>

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