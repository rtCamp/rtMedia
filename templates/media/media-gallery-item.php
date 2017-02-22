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
<!-- rtmedia_id is return backbone object if we use esc_attr then it will create > & < into &lt; &gt; it will not interpret backbone object into media id -->
<li class="rtmedia-list-item" id="<?php echo rtmedia_id(); // @codingStandardsIgnoreLine ?>">

	<?php do_action( 'rtmedia_before_item' ); ?>

	<a href="<?php rtmedia_permalink(); ?>" title="<?php echo esc_attr( rtmedia_title() ); ?>"
	   class="<?php echo esc_attr( apply_filters( 'rtmedia_gallery_list_item_a_class', 'rtmedia-list-item-a' ) ); ?>">

		<div class="rtmedia-item-thumbnail">
			<?php echo wp_kses( rtmedia_duration(), array( 'span' => array( 'class' => array() ) ) ); ?>
			<img src="<?php rtmedia_image( 'rt_media_thumbnail' ); ?>" alt="<?php rtmedia_image_alt(); ?>">
		</div>

		<?php if ( apply_filters( 'rtmedia_media_gallery_show_media_title', true ) ) { ?>
			<div class="rtmedia-item-title">
				<h4 title="<?php echo esc_attr( rtmedia_title() ); ?>">
					<?php echo esc_html( rtmedia_title() ); ?>
				</h4>
			</div>
		<?php } ?>

	</a>

	<?php do_action( 'rtmedia_after_item' ); ?>
</li>
