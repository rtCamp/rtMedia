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
		<?php
		if ( $paged > 1 && $showitems < $pages ) {
			$page_url = ( ( rtmedia_page() - 1 ) === 1 ) ? '' : $page_base_url . ( rtmedia_page() - 1 );
			?>
			<a class="rtmedia-page-link" data-page-type="prev" href="<?php echo esc_url( $page_url ); ?>">
				<i class='dashicons dashicons-arrow-left-alt2'></i>
			</a>
			<?php
		}

		if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
			$page_url = $page_base_url . '1';
			?>
			<a class="rtmedia-page-link" data-page-type="page" data-page="1" href="<?php echo esc_url( $page_url ); ?>">1</a>
			<?php if ( $paged > 3 ) { ?>
				<span>...</span>
				<?php
			}
		}

		for ( $i = 1; $i <= $pages; $i ++ ) {
			if ( 1 !== $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				$page_url = $page_base_url . $i;

				if ( $paged === $i ) {
					?>
					<span class="current"><?php echo esc_html( $i ); ?></span>
					<?php
				} else {
					?>
					<a class="rtmedia-page-link" data-page-type="page" data-page="<?php echo esc_attr( $i ); ?>" href="<?php echo esc_url( $page_url ); ?>" class="inactive">
						<?php echo esc_html( $i ); ?>
					</a>
					<?php
				}
			}
		}

		if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
			$page_url = $page_base_url . $pages;

			if ( $paged + 2 < $pages ) {
				?>
				<span>...</span>
				<?php
			}
			?>
			<a class="rtmedia-page-link" data-page-type="page" data-page="<?php echo esc_attr( $pages ); ?>" href="<?php echo esc_url( $page_url ); ?>">
				<?php echo esc_html( $pages ); ?>
			</a>
			<?php
		}

		if ( $paged < $pages && $showitems < $pages ) {
			$page_url = $page_base_url . ( rtmedia_page() + 1 );
			?>
			<a class="rtmedia-page-link" data-page-type="next" href="<?php echo esc_url( $page_url ); ?>">
				<i class="dashicons dashicons-arrow-right-alt2"></i>
			</a>
			<?php
		}
		?>
	</div>

</div>
