<?php
/**
 * RTMediaAddon::addon().
 *
 * @package rtMedia
 */

?>

<div class="plugin-card clearfix rtm-plugin-card">

	<div class="plugin-card-top">
		<a class="rtm-logo" href="<?php echo esc_url( $args['product_link'] ); ?>" title="<?php echo esc_attr( $args['title'] ); ?>" target="_blank">
			<img width="240" height="184"
				title="<?php echo esc_attr( $args['title'] ); ?>" alt="<?php echo esc_attr( $args['title'] ); ?>"
				src="<?php echo esc_url( $args['img_src'] ); ?>" />
		</a>

		<div class="name column-name">
			<h4>
				<a href="<?php echo esc_url( $args['product_link'] ); ?>" title="<?php echo esc_attr( $args['title'] ); ?>"
					target="_blank">
					<?php echo esc_html( $args['title'] ); ?>
				</a>
			</h4>
		</div>

		<div class="desc column-description">
			<?php echo wp_kses_post( $args['desc'] ); ?>
		</div>
	</div>

	<div class="plugin-card-bottom">

		<span class="price alignleft">
			<span class="amount"><?php echo esc_html( $args['price'] ); ?></span>
		</span>

		<?php
		echo wp_kses( $purchase_link, $allowed_html );

		if ( ! empty( $args['demo_link'] ) ) {

			printf(
				'<a class="alignright rtm-live-demo button" href="%1$s" title="%2$s" target="_blank">%3$s</a>',
				esc_url( $args['demo_link'] ),
				esc_attr( $args['title'] ),
				esc_html__( 'Live Demo', 'buddypress-media' )
			);
		}
		?>
	</div>

</div>
