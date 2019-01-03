<?php

// Generate random number for gallery container
// This will be useful when multiple gallery shortcodes are used in a single page
$rand_id = rand( 0, 1000 );

?>
<div class="rtmedia-container" id="rtmedia_gallery_container_<?php echo $rand_id;?>">
	<?php
	do_action( 'rtmedia_before_album_gallery' );

	$title = get_rtmedia_gallery_title();
	?>

	<div id="rtm-gallery-title-container" class="clearfix">
		<h2 class="rtm-gallery-title">
			<?php
			if ( $title ) {
				echo esc_html( $title );
			} else {
				esc_html_e( 'Album List', 'buddypress-media' );
			}
			?>
		</h2>

		<div id="rtm-media-options" class="rtm-media-options">
			<?php do_action( 'rtmedia_album_gallery_actions' ); ?>
		</div>
	</div>

	<?php do_action( 'rtmedia_after_album_gallery_title' ); ?>

	<div id="rtm-media-gallery-uploader" class="rtm-media-gallery-uploader">
		<?php rtmedia_uploader( array( 'is_up_shortcode' => false ) ); ?>
	</div>

	<?php
	do_action( 'rtmedia_after_media_gallery_title' );
	if ( have_rtmedia() ) { ?>

		<!-- addClass 'rtmedia-list-media' for work properly selectbox -->
		<ul class="rtmedia-list-media rtmedia-list rtmedia-album-list clearfix">
			<?php while ( have_rtmedia() ) : rtmedia(); ?>
					<?php include( 'album-gallery-item.php' ); ?>
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
				<a id="rtMedia-galary-next" style='<?php echo esc_attr( $display ); ?>'
				   href="<?php echo esc_url( rtmedia_pagination_next_link() ); ?>"><?php esc_html_e( 'Load More', 'buddypress-media' ); ?></a>
				<?php
			}
			?>
		</div><!--/.rtmedia_next_prev-->
	<?php } else { ?>
		<p class="rtmedia-no-media-found">
			<?php
			$message = apply_filters( 'rtmedia_no_media_found_message_filter', 'Sorry !! There\'s no media found for the request !!' );
			echo esc_html__( $message, 'buddypress-media' );
			?>
		</p>
	<?php } ?>

	<?php do_action( 'rtmedia_after_album_gallery' ); ?>
	<?php do_action( 'rtmedia_after_media_gallery' ); ?>
</div>

<!-- template for single media in gallery -->
<script id="rtmedia-gallery-item-template" type="text/template">
	<div class="rtmedia-item-thumbnail">
		<a href="media/<%= id %>">
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
