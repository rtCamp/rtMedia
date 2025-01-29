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
			<img width="240" height="184" title="<?php echo esc_attr( $args['title'] ); ?>" alt="<?php echo esc_attr( $args['title'] ); ?>" src="<?php echo esc_url( $args['img_src'] ); ?>" />
		</a>

		<div class="name column-name">
			<h4>
				<a href="<?php echo esc_url( $args['product_link'] ); ?>" title="<?php echo esc_attr( $args['title'] ); ?>" target="_blank">
					<?php echo esc_html( $args['title'] ); ?>
				</a>
			</h4>
		</div>

		<div class="column-description">
			<?php echo wp_kses_post( $args['desc'] ); ?>
		</div>
	</div>

	<div class="plugin-card-bottom">

		<span class="price alignleft">
			<a class="alignright rtm-doc-link button" href="<?php echo esc_url( $args['doc_link'] ); ?>"
				title="<?php echo esc_attr( $args['title'] ); ?>" target="_blank"
			>
				<?php esc_html_e( 'Docs', 'buddypress-media' ); ?>
			</a>
		</span>

		<span class="rtm-addon-purchased alignright product_type_simple">
			<a href="<?php echo esc_url( 'https://rtmedia.io/rtmedia-premium' ); ?>" class="button button-primary">
				<?php echo esc_html__( 'Get this', 'buddypress-media' ); ?>
			</a>
		</span>

		<?php if ( '' !== $args['demo_link'] ) { ?>
			<a class="alignright rtm-live-demo button" href="<?php echo esc_url( $args['demo_link'] ); ?>"
				title="<?php echo esc_attr( $args['title'] ); ?>" target="_blank"
			>
				<?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?>
			</a>
		<?php } ?>

	</div>

</div>
