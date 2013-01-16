<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaRecentMediaWidget
 *
 * @author saurabh
 */
class BPMediaRecentMedia extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_media', 'description' => __( "The most recent media uploaded on your site", BP_MEDIA_TXT_DOMAIN ) );
		parent::__construct( 'recent-media', __( 'Recent Media', BP_MEDIA_TXT_DOMAIN ), $widget_ops );
                trigger_error( sprintf( __('%1$s will be <strong>deprecated</strong> from version %2$s! Use %3$s instead.'), "Recent Media Widget", "2.5", "BuddyPressMedia Widget" ) );
	}

	function widget( $args, $instance ) {
                extract( $args );
                
		$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? __( 'Recent Media', BP_MEDIA_TXT_DOMAIN ) : $instance[ 'title' ], $instance, $this->id_base );
		
                if ( empty( $instance[ 'number' ] ) || ! $number = absint( $instance[ 'number' ] ) )
			$number = 10;

		echo $before_widget;
		echo $before_title . $title . $after_title;
		?>
		<div id="recent-media-tabs" class="media-tabs-container">
			<ul>
				<li><a href="#recent-media-tabs-all"><?php _e( 'All', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
				<li><a href="#recent-media-tabs-photos"><?php _e( 'Photos', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
				<li><a href="#recent-media-tabs-music"><?php _e( 'Music', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
				<li><a href="#recent-media-tabs-videos"><?php _e( 'Videos', BP_MEDIA_TXT_DOMAIN ); ?></a></li>
			</ul>
			<div id="recent-media-tabs-all" class="bp-media-tab-panel">
				<?php
				// All Media
				$args = array( 'post_type' => 'attachment',
					'post_status' => 'any',
					'posts_per_page' => $number,
					'meta_key' => 'bp-media-key',
					'meta_value' => 0,
					'meta_compare' => '>' );

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
			_e( 'No recent media found', BP_MEDIA_TXT_DOMAIN );

		wp_reset_query();
					?>

			</div><!-- #recent-media-tabs-all -->

			<div id="recent-media-tabs-photos" class="bp-media-tab-panel">
				<?php
				// Recent photos
				$args = array( 'post_type' => 'attachment',
					'post_status' => 'any',
					'post_mime_type' => 'image',
					'posts_per_page' => $number,
					'meta_key' => 'bp-media-key',
					'meta_value' => 0,
					'meta_compare' => '>' );


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
			_e( 'No recent photo found', BP_MEDIA_TXT_DOMAIN );

		wp_reset_query();
					?>

			</div><!-- #media-tabs-photos -->

			<div id="recent-media-tabs-music" class="bp-media-tab-panel">
				<?php
				// Recent Audio
				$args = array( 'post_type' => 'attachment',
					'post_status' => 'any',
					'post_mime_type' => 'audio',
					'posts_per_page' => $number,
					'meta_key' => 'bp-media-key',
					'meta_value' => 0,
					'meta_compare' => '>' );

				$bp_media_widget_query = new WP_Query( $args );

				if ( $bp_media_widget_query->have_posts() ) {
					?>

					<ul class="widget-item-listing">
						<?php
						while ( $bp_media_widget_query->have_posts() ) {
							$bp_media_widget_query->the_post();

							$entry = new BPMediaHostWordpress( get_the_ID() );
							echo $entry->get_media_gallery_content();
						}
						?>

					</ul><!-- .widget-item-listing --><?php
		}else
			_e( 'No recent audio found', BP_MEDIA_TXT_DOMAIN );

		wp_reset_query();
				?>

			</div><!-- #recent-media-tabs-music -->

			<div id="recent-media-tabs-videos" class="bp-media-tab-panel">
				<?php
				// Recent Video
				$args = array( 'post_type' => 'attachment',
					'post_status' => 'any',
					'post_mime_type' => 'video',
					'posts_per_page' => $number,
					'meta_key' => 'bp-media-key',
					'meta_value' => 0,
					'meta_compare' => '>' );

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
			_e( 'No recent video found', BP_MEDIA_TXT_DOMAIN );

		wp_reset_query();
					?>

			</div><!-- #media-tabs-videos -->

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
