<?php

// Generate random number for gallery container
// This will be useful when multiple gallery shortcodes are used in a single page
$rand_id = rand( 0, 1000 );

?>
<div class="rtmedia-container" id="rtmedia_gallery_container_<?php echo intval( $rand_id );?>">
	<?php do_action( 'rtmedia_before_media_gallery' ); ?>
	<?php
	$title = get_rtmedia_gallery_title();
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) { // if gallery is displayed using gallery shortcode
		?>
		<div id="rtm-gallery-title-container" class="clearfix rtm-gallery-shortcode-title-container">
			<h2 class="rtm-gallery-title">
				<?php esc_html_e( 'Media Gallery', 'buddypress-media' ); ?>
			</h2>

			<?php do_action( 'rtmedia_gallery_after_title' ); ?>

			<div id="rtm-media-options" class="rtm-media-options <?php echo ( function_exists( 'rtmedia_media_search_enabled' ) && rtmedia_media_search_enabled() ? 'rtm-media-search-enable': '' );  ?>">
				<?php do_action( 'rtmedia_media_gallery_shortcode_actions' ); ?>

				<?php /**
				 * Show media search if search_filter="true"
				 */
				if ( isset( $shortcode_attr['attr']['search_filter'] )  ) {
					if ( 'true' === $shortcode_attr['attr']['search_filter'] ) {
						add_search_filter( $shortcode_attr['attr'] );
					}
			    	unset( $shortcode_attr['attr']['search_filter'] );
				} ?>

			</div>
		</div>

		<?php do_action( 'rtmedia_gallery_after_title_container' ); ?>

	<?php } else {
		?>
		<div id="rtm-gallery-title-container" class="clearfix rtm-gallery-media-title-container">
			<h2 class="rtm-gallery-title">
				<?php
				if ( $title ) {
					echo esc_html( $title );
				} else {
					esc_html_e( 'Media Gallery', 'buddypress-media' );
				}
				?>
			</h2>

			<?php do_action( 'rtmedia_gallery_after_title' ); ?>

			<div id="rtm-media-options" class="rtm-media-options <?php echo ( function_exists( 'rtmedia_media_search_enabled' ) && rtmedia_media_search_enabled() ? 'rtm-media-search-enable': '' );  ?>">
				<?php do_action( 'rtmedia_media_gallery_actions' ); ?>
			</div>
		</div>

		<?php do_action( 'rtmedia_gallery_after_title_container' ); ?>

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
			apply_filters( 'rtmedia_no_media_found_message_filter', esc_html_e( 'Oops !! There\'s no media found for the request !!','buddypress-media' ) );
			?>
		</p>
	<?php } // End if().
	?>

	<?php do_action( 'rtmedia_after_media_gallery' ); ?>

</div>
