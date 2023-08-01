<?php
/**
 * Template for pagination - rtmedia_get_pagination_values().
 *
 * @package rtMedia
 */

?>

<div class="rtm-pagination clearfix">

	<div class="rtmedia-page-no rtm-page-number">

		<span class="rtm-label">
			<?php echo esc_html( apply_filters( 'rtmedia_goto_page_label', esc_html__( 'Go to page no : ', 'buddypress-media' ) ) ); ?>
		</span>

		<input type="hidden" id="rtmedia_first_page" value="1" />
		<input type="hidden" id="rtmedia_last_page" value="<?php echo esc_attr( $pages ); ?>" />
		<input type="number" value="<?php echo esc_attr( $paged ); ?>" min="1" max="<?php echo esc_attr( $pages ); ?>"
			class="rtm-go-to-num" id="rtmedia_go_to_num" />

		<a class="rtmedia-page-link button" data-page-type="num" data-page-base-url="<?php echo esc_url( $page_base_url ); ?>" href="#">
			<?php esc_html_e( 'Go', 'buddypress-media' ); ?>
		</a>

	</div>

	<div class="rtm-paginate">

	</div>

</div>
