<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaPopularMediaWidget
 *
 * @author saurabh
 */
class BPMediaPopularMedia extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'BPMediaPopularMedia', 'description' => __( "The most popular media on your site", BP_MEDIA_TXT_DOMAIN ) );
		parent::__construct( 'popular-media', __( 'Popular Media', BP_MEDIA_TXT_DOMAIN ), $widget_ops );
                trigger_error( sprintf( __('%1$s will be <strong>deprecated</strong> from version %2$s! Use %3$s instead.'), "Popular Media Widget", "2.5", "BuddyPressMedia Widget" ) );
	}

	function widget( $args, $instance ) {
            	extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? __( 'Popular Media', BP_MEDIA_TXT_DOMAIN ) : $instance[ 'title' ], $instance, $this->id_base );

		if ( empty( $instance[ 'number' ] ) || ! $number = absint( $instance[ 'number' ] ) )
			$number = 10;

		echo $before_widget;
		echo $before_title . $title . $after_title;
		?>
		<div id="popular-media-tabs" class="media-tabs-container">
			<!--                <ul>
								<li><a href="#popular-media-tabs-comments"><?php _e( 'comments', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
								<li><a href="#popular-media-tabs-views"><?php _e( 'Views', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
							</ul>-->
			<div id="popular-media-tabs-comments" class="bp-media-tab-panel">
				<?php
				$args = array( 'post_type' => 'attachment',
					'post_status' => 'any',
					'posts_per_page' => $number,
					'meta_key' => 'bp-media-key',
					'meta_value' => 0,
					'meta_compare' => '>',
					'orderby' => 'comment_count' );

				$bp_media_widget_query = new WP_Query( $args );

				if ( $bp_media_widget_query->have_posts() ) {
					?>

					<ul class="widget-item-listing"><?php
			while ( $bp_media_widget_query->have_posts() ) {
				$bp_media_widget_query->the_post();

				$entry = new BPMediaHostWordpress( get_the_ID() );
						?>

				<?php echo $entry->get_media_gallery_content(); ?><?php }
			?>

					</ul><!-- .widget-item-listing --><?php
		}else
			_e( 'No popular media found', BP_MEDIA_TXT_DOMAIN );

		wp_reset_query();
		?>

			</div><!-- #popular-media-tabs-comments -->
		</div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'number' ] = (int) $new_instance[ 'number' ];
		return $instance;
	}

	function form( $instance ) {
                $title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 10;
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', BP_MEDIA_TXT_DOMAIN ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', BP_MEDIA_TXT_DOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		<?php
	}

}
?>
