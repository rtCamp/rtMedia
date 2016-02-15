<div class="rtmedia-container">
	<?php do_action( 'rtmedia_before_media_gallery' ); ?>
	<?php
	$title = get_rtmedia_gallery_title();
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) { // if gallery is displayed using gallery shortcode
		?>
		<div id="rtm-gallery-title-container" class="clearfix">
			<h2 class="rtm-gallery-title"><?php esc_html_e( 'Media Gallery', 'buddypress-media' ); ?></h2>
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
					echo esc_html( $title );
				} else {
					esc_html_e( 'Media Gallery', 'buddypress-media' );
				}
				?>
			</h2>
			<div id="rtm-media-options"
			     class="rtm-media-options"><?php do_action( 'rtmedia_media_gallery_actions' ); ?></div>
		</div>

		<?php do_action( 'rtmedia_gallery_after_title' ); ?>

		<div id="rtm-media-gallery-uploader" class="rtm-media-gallery-uploader">
			<?php rtmedia_uploader( array( 'is_up_shortcode' => false ) ); ?>
		</div>
	<?php }
	?>
	<?php do_action( 'rtmedia_after_media_gallery_title' ); ?>
	<?php if ( have_rtmedia() ) { ?>
		<ul class="rtmedia-list rtmedia-list-media rtm-gallery-list clearfix <?php rtmedia_media_gallery_class(); ?>">

			<?php while ( have_rtmedia() ) : rtmedia(); ?>

				<?php include( 'media-gallery-item.php' ); ?>

			<?php endwhile; ?>

		</ul>

		<div class="rtmedia_next_prev rtm-load-more clearfix">
			<!-- these links will be handled by backbone -->
			<?php
			global $rtmedia;
			$general_options = $rtmedia->options;
			if ( isset( $rtmedia->options['general_display_media'] ) && 'pagination' === $general_options['general_display_media'] ) {
				rtmedia_media_pagination();
			} else {
				$display = '';
				if ( rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count() ) {
					$display = 'display:block;';
				} else {
					$display = 'display:none;';
				}
				?>
				<a id="rtMedia-galary-next" style="<?php echo esc_attr( $display ); ?>"
				   href="<?php esc_url( rtmedia_pagination_next_link() ); ?>"><?php esc_html_e( 'Load More', 'buddypress-media' ); ?></a>
				<?php
			}
			?>
		</div>
	<?php } else { ?>
		<p class="rtmedia-no-media-found">
			<?php
			esc_html_e( apply_filters( 'rtmedia_no_media_found_message_filter', 'Oops !! There\'s no media found for the request !!' ), 'buddypress-media' );
			?>
		</p>
	<?php } ?>

	<?php do_action( 'rtmedia_after_media_gallery' ); ?>

</div>
