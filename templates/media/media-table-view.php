<div class="rtmedia-container">
	<?php do_action( 'rtmedia_before_media_gallery' ); ?>
	<?php
	$title = get_rtmedia_gallery_title();
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && $rtmedia_query->is_gallery_shortcode == true ) { // if gallery is displayed using gallery shortcode
		?>
		<div id="rtm-gallery-title-container" class="clearfix">
			<h2 class="rtm-gallery-title"><?php _e( 'Media Gallery', 'rtmedia' ); ?></h2>
			<div id="rtm-media-options" class="rtm-media-options">
				<?php do_action( 'rtmedia_media_gallery_shortcode_actions' ); ?>
			</div>
		</div>

		<?php do_action( 'rtmedia_gallery_after_title' ); ?>

	<?php } else {
		?>
		<div id="rtm-gallery-title-container" class="clearfix">
			<h2 class="rtm-gallery-title">
				<?php
				if ( $title ) {
					echo $title;
				} else {
					_e( 'Media Gallery', 'rtmedia' );
				}
				?>
			</h2>
			<div id="rtm-media-options" class="rtm-media-options"><?php do_action( 'rtmedia_media_gallery_actions' ); ?></div>
		</div>

		<?php do_action( 'rtmedia_gallery_after_title' ); ?>

		<div id="rtm-media-gallery-uploader" class="rtm-media-gallery-uploader">
			<?php rtmedia_uploader( array( 'is_up_shortcode' => false ) ); ?>
		</div>
	<?php }
	?>
	<?php do_action( 'rtmedia_after_media_gallery_title' ); ?>
    <?php if (have_rtmedia()) { ?>
        <table class="rtmedia-list rtmedia-list-media rtmedia-table-list <?php echo rtmedia_media_gallery_class(); ?>">
            <thead>
                <tr class="rtmedia-table-list-row">
                    <th width="8%"><?php _e('Media', 'rtmedia'); ?></th>
                    <th width="42%"><?php _e('Title', 'rtmedia'); ?></th>
                    <th width="17%"><?php _e('Uploaded', 'rtmedia'); ?></th>
                    <th width="10%"><?php _e('Size', 'rtmedia'); ?></th>
                    <?php if (( $rtmedia_query->media[0]->media_author == get_current_user_id() ) || is_super_admin()) { ?>
                        <th width="7.5%"><?php _e('Edit', 'rtmedia'); ?></th>
                        <th width="10.5%"><?php _e('Delete', 'rtmedia'); ?></th>
                    <?php } ?>
                </tr>
            </thead>
           
                <?php while (have_rtmedia()) : rtmedia(); ?>
                        <?php include ('media-table-view-item.php'); ?>
                <?php endwhile; ?>
           

        </table>

       <div class="rtmedia_next_prev rtm-load-more clearfix">
			<!-- these links will be handled by backbone -->
			<?php
			global $rtmedia;
			$general_options = $rtmedia->options;
			if ( isset( $rtmedia->options[ 'general_display_media' ] ) && $general_options[ 'general_display_media' ] == 'pagination' ) {
				echo rtmedia_media_pagination();
			} else {
				$display = '';
				if ( rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count() )
					$display = 'style="display:block;"';
				else
					$display = 'style="display:none;"';
				?>
				<a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rtmedia_pagination_next_link(); ?>"><?php echo __( 'Load More', 'rtmedia' ); ?></a>
				<?php
			}
			?>
		</div>
	<?php } else { ?>
		<p class="rtmedia-no-media-found">
			<?php
			$message = __( "Oops !! There's no media found for the request !!", "rtmedia" );
			echo apply_filters( 'rtmedia_no_media_found_message_filter', $message );
			?>
		</p>
	<?php } ?>

	<?php do_action( 'rtmedia_after_media_gallery' ); ?>

</div>
