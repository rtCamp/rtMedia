<?php
/**
 * Show album gallery container.
 *
 * @package rtMedia
 */

// Generate random number for gallery container.
// This will be useful when multiple gallery shortcodes are used in a single page.
$rand_id = wp_rand( 0, 1000 );

?>
<div class="rtmedia-container" id="rtmedia_gallery_container_<?php echo esc_attr( $rand_id ); ?>">
	<?php
	do_action( 'rtmedia_before_album_gallery' );

	$gallery_title = get_rtmedia_gallery_title();
	?>

	<div id="rtm-gallery-title-container" class="clearfix">
		<h2 class="rtm-gallery-title">
			<?php
			if ( $gallery_title ) {
				echo esc_html( $gallery_title );
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
	$global_albums = rtmedia_global_albums();
	$model         = new RTMediaModel();
	$album_objects = $model->get_media(
		array(
			'id' => ( $global_albums ),
		)
	);

	if ( have_rtmedia() || ! empty( $album_objects ) ) {
		?>

		<!-- addClass 'rtmedia-list-media' for work properly selectbox -->
		<?php
		if ( have_rtmedia() || ! empty( $album_objects ) ) {
			?>
			<ul class="rtmedia-list-media rtmedia-list rtmedia-album-list clearfix">
			<?php
			while ( have_rtmedia() ) :
				rtmedia();
				include 'album-gallery-item.php';
			endwhile;
			if ( ! empty( $album_objects ) && ! current_user_can( 'manage_options' ) ) {
				foreach ( $album_objects as $album_object ) {
					$media_id    = $album_object->media_id;
					$album_id    = $album_object->id;
					$media_title = $album_object->media_title;
					?>
					<!--    start default album gallery-->
					<li class="rtmedia-list-item" id="<?php echo esc_attr( $media_id ); ?>">
						<a href="<?php rtmedia_permalink( $media_id ); ?>" title="<?php echo esc_attr( $media_title ); ?>">
							<div class="rtmedia-item-thumbnail">
								<img src="<?php rtmedia_image( 'rt_media_thumbnail', $album_object->id ); ?>" alt="<?php echo esc_attr( $media_title ); ?>">
							</div>

							<?php
							/**
							 * Filter to hide or show media titles in gallery.
							 *
							 * @param bool true Default value is true.
							 */
							if ( apply_filters( 'rtmedia_media_gallery_show_media_title', true ) ) {
								?>
								<div class="rtmedia-item-title">
									<h4><?php echo esc_html( $media_title ); ?><span></h4>
								</div>
								<?php
							}
							?>
						</a>
						<?php
						/**
						 * Fires after album gallery item.
						 */
						do_action( 'rtmedia_after_album_gallery_item', $media_id, $album_id );
						?>
					</li> <!-- End of .default- rtmedia-list-item -->
					<?php
				}
			}
			?>
		</ul>
		<?php } else { ?>
		<p class="rtmedia-no-media-found">
			<?php
			echo esc_html( apply_filters( 'rtmedia_no_media_found_message_filter', __( 'Sorry !! There\'s no media found for the request !!', 'buddypress-media' ) ) );
			?>
		</p>
		<?php } ?>

		<div class="rtmedia_next_prev rtm-load-more clearfix">
			<!-- these links will be handled by backbone -->
			<?php
			global $rtmedia;

			$general_options = $rtmedia->options;

			if ( isset( $rtmedia->options['general_display_media'] ) && 'pagination' === $general_options['general_display_media'] ) {
				rtmedia_media_pagination();
			} else {
				$display = '';

				if ( rtmedia_offset() + rtmedia_per_page_media() > rtmedia_count() ) {
					$display = 'display:none;';
				}
				?>
				<a id="rtMedia-galary-next" style='<?php echo esc_attr( $display ); ?>' href="<?php echo esc_url( rtmedia_pagination_next_link() ); ?>"><?php esc_html_e( 'Load More', 'buddypress-media' ); ?></a>
				<?php
			}
			?>
		</div><!--/.rtmedia_next_prev-->
	<?php } else { ?>
		<p class="rtmedia-no-media-found">
			<?php
			echo esc_html( apply_filters( 'rtmedia_no_media_found_message_filter', __( 'Sorry !! There\'s no media found for the request !!', 'buddypress-media' ) ) );
			?>
		</p>
	<?php } ?>

	<?php do_action( 'rtmedia_after_album_gallery' ); ?>
	<?php do_action( 'rtmedia_after_media_gallery' ); ?>
</div>
<?php
// phpcs:disable Generic.PHP.DisallowAlternativePHPTags.MaybeASPShortOpenTagFound, PHPCompatibility.Miscellaneous.RemovedAlternativePHPTags.MaybeASPOpenTagFound
?>
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
<?php
// phpcs:enable Generic.PHP.DisallowAlternativePHPTags.MaybeASPShortOpenTagFound, PHPCompatibility.Miscellaneous.RemovedAlternativePHPTags.MaybeASPOpenTagFound
