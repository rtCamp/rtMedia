<?php
/** That's all, stop editing from here * */
global $rtmedia_backbone;

$rtmedia_backbone = array(
	'backbone'        => false,
	'is_album'        => false,
	'is_edit_allowed' => false,
);
//todo: nonce verification
$rtmedia_backbone['backbone'] = filter_input( INPUT_POST, 'backbone', FILTER_VALIDATE_BOOLEAN );

$is_album = filter_input( INPUT_POST, 'is_album', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
if ( isset( $is_album[0] ) ) {
	$rtmedia_backbone['is_album'] = $is_album[0];
}

$is_edit_allowed = filter_input( INPUT_POST, 'is_edit_allowed', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
if ( isset( $is_edit_allowed[0] ) ) {
	$rtmedia_backbone['is_edit_allowed'] = $is_edit_allowed[0];
}

?>
<?php // rtmedia_id is return backbone object if we use esc_attr then it will create > & < into &lt; &gt; it will not interpret backbone object into media id ?>
<li class="rtmedia-list-item" id="<?php echo rtmedia_id(); // @codingStandardsIgnoreLine ?>">

	<?php do_action( 'rtmedia_before_item' ); ?>

	<a href="<?php rtmedia_permalink(); ?>" title="<?php echo esc_attr( rtmedia_title() ); ?>"
	   class="<?php echo esc_attr( apply_filters( 'rtmedia_gallery_list_item_a_class', 'rtmedia-list-item-a' ) ); ?>">

		<?php
			global $rtmedia_query;

			$alt_text      = rtmedia_image_alt( false, false );
			$rtmedia_media = '';
                        if ( ! empty( $rtmedia_query ) && isset( $rtmedia_query->rtmedia ) ) {
                            $rtmedia_media = $rtmedia_query->rtmedia;
                        }
			$allowed_html  = array(
				'span' => array(
					'class' => array(),
				),
			);
		?>
		<div class="rtmedia-item-thumbnail">
			<?php echo wp_kses( rtmedia_duration(), $allowed_html ); ?>
			<img src="<?php rtmedia_image( 'rt_media_thumbnail' ); ?>" alt="<?php echo esc_attr( apply_filters( 'rtmc_change_alt_text', $alt_text, $rtmedia_media ) ); ?>">
		</div>

		<?php
		/**
		 * Filter to hide or show media titles in gallery.
		 *
		 * @param bool true Default value is true.
		 */
		if ( apply_filters( 'rtmedia_media_gallery_show_media_title', true ) ) { ?>
			<div class="rtmedia-item-title <?php echo esc_html( rtmedia_show_title() ); ?>" >
				<h4 title="<?php echo esc_attr( rtmedia_title() ); ?>">
					<?php echo esc_html( rtmedia_title() ); ?>
				</h4>
			</div>
		<?php } ?>

	</a>

	<?php do_action( 'rtmedia_after_item' ); ?>
</li>
